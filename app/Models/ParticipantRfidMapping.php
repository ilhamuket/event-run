<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParticipantRfidMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'rfid_tag',
        'assigned_at',
        'assigned_by',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRfidTag($query, string $tag)
    {
        return $query->where('rfid_tag', $tag);
    }

    /**
     * Cari participant berdasarkan salah satu RFID tag aktifnya.
     * Satu participant bisa punya beberapa tag (karena hex raw bisa bervariasi).
     */
    public static function findParticipantByRfid(string $rfidTag): ?Participant
    {
        $mapping = static::where('rfid_tag', $rfidTag)
            ->where('is_active', true)
            ->first();

        return $mapping?->participant;
    }

    /**
     * Cek apakah tag ini sudah terdaftar ke participant manapun (aktif).
     */
    public static function isTagRegistered(string $rfidTag): bool
    {
        return static::where('rfid_tag', $rfidTag)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Assign RFID tag baru ke participant.
     * Tidak menonaktifkan tag lama — satu participant bisa punya banyak tag aktif.
     */
    public static function assignTag(
        int $participantId,
        string $rfidTag,
        int $assignedBy,
        ?string $notes = null
    ): static {
        // Jika tag ini sudah ada untuk participant yang sama, aktifkan kembali saja
        $existing = static::where('participant_id', $participantId)
            ->where('rfid_tag', $rfidTag)
            ->first();

        if ($existing) {
            $existing->update(['is_active' => true, 'notes' => $notes]);
            return $existing;
        }

        return static::create([
            'participant_id' => $participantId,
            'rfid_tag' => $rfidTag,
            'assigned_at' => now(),
            'assigned_by' => $assignedBy,
            'is_active' => true,
            'notes' => $notes,
        ]);
    }

    /**
     * Deactivate satu tag tertentu dari participant.
     */
    public static function deactivateTag(int $participantId, string $rfidTag): bool
    {
        return (bool) static::where('participant_id', $participantId)
            ->where('rfid_tag', $rfidTag)
            ->update(['is_active' => false]);
    }
}
