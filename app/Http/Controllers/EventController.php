<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\RfidCheckpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    /**
     * HOME - cached heavily since rarely changes
     */
    public function home()
    {
        $event = Cache::remember('home_event', 300, function () {
            return Event::with([
                'heroImages' => fn($q) => $q->where('is_active', true)->orderBy('order'),
                'categories' => fn($q) => $q->where('is_active', true)->orderBy('order'),
                'racepackItems' => fn($q) => $q->where('is_active', true)->orderBy('order'),
            ])
            ->where('is_published', true)
            ->firstOrFail();
        });

        return view('event.home', compact('event'));
    }

    /**
     * LIST PESERTA - Raw query, no N+1
     */
    public function participants(Event $event, Request $request)
    {
        $search = $request->q;
        $perPage = 25;
        $page = max(1, (int) $request->input('page', 1));
        $offset = ($page - 1) * $perPage;

        // Base WHERE clause
        $baseWhere = "p.event_id = ? AND EXISTS (
            SELECT 1 FROM transactions t
            WHERE t.participant_id = p.id AND t.status = 'PAID' LIMIT 1
        )";
        $bindings = [$event->id];

        // Search filter
        if ($search) {
            $baseWhere .= " AND (
                p.bib LIKE ? OR p.name LIKE ? OR p.bib_name LIKE ?
                OR p.email LIKE ? OR p.community LIKE ?
            )";
            $like = "%{$search}%";
            array_push($bindings, $like, $like, $like, $like, $like);
        }

        // Count total
        $total = DB::selectOne(
            "SELECT COUNT(*) as cnt FROM participants p WHERE {$baseWhere}",
            $bindings
        )->cnt;

        // Fetch page with category join (single query, no N+1)
        $rows = DB::select("
            SELECT
                p.id, p.bib, p.bib_name, p.name, p.gender, p.age,
                p.email, p.phone, p.city, p.community, p.jersey_size,
                p.has_comorbid, p.comorbid_details,
                ec.name as category_name, ec.distance as category_distance
            FROM participants p
            LEFT JOIN event_categories ec ON ec.id = p.event_category_id
            WHERE {$baseWhere}
            ORDER BY p.bib ASC
            LIMIT ? OFFSET ?
        ", array_merge($bindings, [$perPage, $offset]));

        // Add computed attributes for blade compatibility
        $rows = array_map(function ($r) {
            $r->category = (object) [
                'name' => $r->category_name,
                'distance' => $r->category_distance,
            ];
            return $r;
        }, $rows);

        // Manual paginator
        $participants = new \Illuminate\Pagination\LengthAwarePaginator(
            collect($rows)->map(fn($r) => (object) $r),
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('event.participants', compact('event', 'participants'));
    }

    /**
     * RACE RESULTS - Raw query, cached per search/page combo
     */
    public function results(Event $event, Request $request)
    {
        $search = $request->q;
        $perPage = 25;
        $page = max(1, (int) $request->input('page', 1));
        $offset = ($page - 1) * $perPage;

        $cacheKey = "results:{$event->id}:" . md5("{$search}:{$page}");

        $data = Cache::remember($cacheKey, 15, function () use ($event, $search, $perPage, $offset) {

            $baseWhere = "p.event_id = ? AND p.elapsed_time IS NOT NULL
                AND EXISTS (
                    SELECT 1 FROM transactions t
                    WHERE t.participant_id = p.id AND t.status = 'PAID' LIMIT 1
                )";
            $bindings = [$event->id];

            if ($search) {
                $baseWhere .= " AND (p.bib LIKE ? OR p.name LIKE ? OR p.bib_name LIKE ?)";
                $like = "%{$search}%";
                array_push($bindings, $like, $like, $like);
            }

            $total = DB::selectOne(
                "SELECT COUNT(*) as cnt FROM participants p WHERE {$baseWhere}",
                $bindings
            )->cnt;

            $rows = DB::select("
                SELECT
                    p.id, p.bib, p.bib_name, p.name, p.gender, p.age, p.city,
                    p.elapsed_time, p.general_position, p.category_position,
                    p.event_category_id,
                    ec.name as category_name, ec.distance as category_distance
                FROM participants p
                LEFT JOIN event_categories ec ON ec.id = p.event_category_id
                WHERE {$baseWhere}
                ORDER BY p.elapsed_time ASC
                LIMIT ? OFFSET ?
            ", array_merge($bindings, [$perPage, $offset]));

            // Add computed attributes
            $rows = array_map(function ($r) {
                $r->display_name = $r->bib_name ?: $r->name;
                $r->formatted_elapsed_time = $r->elapsed_time
                    ? \Carbon\Carbon::parse($r->elapsed_time)->format('H:i:s')
                    : null;
                // Wrap category as object for blade compatibility
                $r->category = (object) [
                    'name' => $r->category_name,
                    'distance' => $r->category_distance,
                ];
                return $r;
            }, $rows);

            return ['rows' => $rows, 'total' => $total];
        });

        $results = new \Illuminate\Pagination\LengthAwarePaginator(
            collect($data['rows'])->map(fn($r) => (object) $r),
            $data['total'],
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('event.results', compact('event', 'results'));
    }

    /**
     * LIVE RACE TRACKING - Heavily optimized with raw queries + short cache
     */
    public function live(Event $event, Request $request)
    {
        $selectedCategory = $request->category;
        $search = $request->q;

        // Categories - cached longer (rarely change)
        $categories = Cache::remember("live_cats:{$event->id}", 120, function () use ($event) {
            return $event->categories()->active()->ordered()->get();
        });

        // Determine category filter
        $categoryIds = $categories->pluck('id')->toArray();
        $filteredCategoryId = null;
        if ($selectedCategory) {
            $category = $categories->firstWhere('slug', $selectedCategory);
            if ($category) {
                $filteredCategoryId = $category->id;
                $categoryIds = [$category->id];
            }
        }

        // Short cache key based on filters (5s cache for live data)
        $cacheKey = "live:{$event->id}:" . md5("{$selectedCategory}:{$search}");
        $cacheTtl = 5; // 5 seconds for live data

        $data = Cache::remember($cacheKey, $cacheTtl, function () use ($event, $categoryIds, $filteredCategoryId, $search) {

            // ── 1. Get all checkpoints in one query ──
            $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
            $checkpoints = DB::select("
                SELECT id, event_category_id, checkpoint_type, checkpoint_name,
                       checkpoint_order, distance_km
                FROM rfid_checkpoints
                WHERE event_category_id IN ({$placeholders})
                  AND is_active = 1
                ORDER BY checkpoint_order DESC
            ", $categoryIds);

            $checkpointMap = collect($checkpoints)->keyBy('id');

            // ── 2. Get all paid participant IDs with basic info (single query) ──
            $participantWhere = "p.event_id = ? AND EXISTS (
                SELECT 1 FROM transactions t
                WHERE t.participant_id = p.id AND t.status = 'PAID' LIMIT 1
            )";
            $pBindings = [$event->id];

            if ($filteredCategoryId) {
                $participantWhere .= " AND p.event_category_id = ?";
                $pBindings[] = $filteredCategoryId;
            }

            if ($search) {
                $participantWhere .= " AND (p.bib LIKE ? OR p.name LIKE ? OR p.bib_name LIKE ?)";
                $like = "%{$search}%";
                array_push($pBindings, $like, $like, $like);
            }

            $participants = DB::select("
                SELECT
                    p.id, p.bib, p.bib_name, p.name, p.gender, p.age, p.city,
                    p.elapsed_time, p.general_position, p.category_position,
                    p.event_category_id,
                    ec.name as category_name
                FROM participants p
                LEFT JOIN event_categories ec ON ec.id = p.event_category_id
                WHERE {$participantWhere}
                ORDER BY p.bib
            ", $pBindings);

            $participantMap = collect($participants)->keyBy('id');
            $participantIds = $participantMap->keys()->toArray();

            if (empty($participantIds)) {
                return [
                    'checkpointGroups' => [],
                    'notStarted' => [],
                    'totalParticipants' => 0,
                ];
            }

            // ── 3. Get ALL validated times in ONE query (the big optimization) ──
            $pidPlaceholders = implode(',', array_fill(0, count($participantIds), '?'));
            $validatedTimes = DB::select("
                SELECT
                    vt.participant_id,
                    vt.rfid_checkpoint_id,
                    vt.checkpoint_time,
                    vt.elapsed_time,
                    vt.position_at_checkpoint,
                    rc.checkpoint_type,
                    rc.checkpoint_order
                FROM rfid_validated_times vt
                INNER JOIN rfid_checkpoints rc ON rc.id = vt.rfid_checkpoint_id
                WHERE vt.participant_id IN ({$pidPlaceholders})
                ORDER BY rc.checkpoint_order DESC
            ", $participantIds);

            // ── 4. Group validated times by participant, keep only the LATEST ──
            $latestByParticipant = [];
            foreach ($validatedTimes as $vt) {
                $pid = $vt->participant_id;
                if (!isset($latestByParticipant[$pid])) {
                    $latestByParticipant[$pid] = $vt; // First = highest order (DESC)
                }
            }

            // ── 5. Build checkpoint groups ──
            $groups = [];
            foreach ($checkpoints as $cp) {
                $key = $cp->checkpoint_type === 'checkpoint' ? 'cp_' . $cp->id : $cp->checkpoint_type;
                $groups[$key] = [
                    'checkpoint' => $cp,
                    'participants' => [],
                ];
            }

            $notStarted = [];

            foreach ($participantIds as $pid) {
                $p = $participantMap[$pid];
                $p->display_name = $p->bib_name ?: $p->name;
                $p->category = (object) ['name' => $p->category_name];

                if (!isset($latestByParticipant[$pid])) {
                    $notStarted[] = $p;
                    continue;
                }

                $latestVt = $latestByParticipant[$pid];
                $cpId = $latestVt->rfid_checkpoint_id;

                if (!$checkpointMap->has($cpId)) {
                    $notStarted[] = $p;
                    continue;
                }

                $cp = $checkpointMap[$cpId];
                $key = $cp->checkpoint_type === 'checkpoint' ? 'cp_' . $cp->id : $cp->checkpoint_type;

                // Format elapsed time
                $latestVt->formatted_elapsed_time = $latestVt->elapsed_time
                    ? \Carbon\Carbon::parse($latestVt->elapsed_time)->format('H:i:s')
                    : null;
                $latestVt->checkpoint_time = $latestVt->checkpoint_time
                    ? \Carbon\Carbon::parse($latestVt->checkpoint_time)
                    : null;

                // Also format participant elapsed_time for finish fallback
                $p->formatted_elapsed_time = $p->elapsed_time
                    ? \Carbon\Carbon::parse($p->elapsed_time)->format('H:i:s')
                    : null;

                if (isset($groups[$key])) {
                    $groups[$key]['participants'][] = [
                        'participant' => $p,
                        'validated_time' => $latestVt,
                    ];
                }
            }

            // ── 6. Sort participants within each group ──
            foreach ($groups as $key => &$group) {
                if ($key === 'finish') {
                    usort($group['participants'], function ($a, $b) {
                        $aTime = $a['validated_time']->elapsed_time ?? '99:99:99';
                        $bTime = $b['validated_time']->elapsed_time ?? '99:99:99';
                        return strcmp($aTime, $bTime);
                    });
                } else {
                    usort($group['participants'], function ($a, $b) {
                        $aTime = $a['validated_time']->checkpoint_time ?? now()->addYears(10);
                        $bTime = $b['validated_time']->checkpoint_time ?? now()->addYears(10);
                        return $aTime <=> $bTime;
                    });
                }
            }
            unset($group);

            // Sort not started by BIB
            usort($notStarted, fn($a, $b) => $a->bib <=> $b->bib);

            return [
                'checkpointGroups' => $groups,
                'notStarted' => $notStarted,
                'totalParticipants' => count($participants),
            ];
        });

        // Convert arrays back to collections for blade compatibility
        $checkpointGroups = collect($data['checkpointGroups'])->map(function ($group) {
            $group['participants'] = collect($group['participants'])->map(function ($item) {
                $item['participant'] = (object) $item['participant'];
                $item['validated_time'] = (object) $item['validated_time'];
                return $item;
            });
            $group['checkpoint'] = (object) $group['checkpoint'];
            return $group;
        });

        $notStarted = collect($data['notStarted'])->map(fn($p) => (object) $p);
        $totalParticipants = $data['totalParticipants'];

        // Summary stats
        $finishedCount = $checkpointGroups->has('finish')
            ? $checkpointGroups['finish']['participants']->count()
            : 0;
        $notStartedCount = $notStarted->count();
        $startedCount = $totalParticipants - $notStartedCount;
        $onCourseCount = $startedCount - $finishedCount;

        $summary = [
            'not_started' => $notStartedCount,
            'started' => $startedCount,
            'on_course' => $onCourseCount,
            'finished' => $finishedCount,
        ];

        return view('event.live-preview', compact(
            'event',
            'categories',
            'selectedCategory',
            'checkpointGroups',
            'notStarted',
            'totalParticipants',
            'summary'
        ));
    }
}
