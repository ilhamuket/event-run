#!/usr/bin/env python3
"""
RFID API Direct Test
====================
This script tests the Laravel RFID API directly without going through
the socket scanner. Use this to verify your API endpoints are working.

Usage:
    # Interactive mode (RECOMMENDED)
    python test_api_direct.py --interactive

    # Test specific participant by BIB
    python test_api_direct.py --scan --bib 001

    # Test with custom RFID
    python test_api_direct.py --scan --rfid E28011606000020471A0E5C9

    # Test full race simulation
    python test_api_direct.py --full-test

    # Get checkpoint status
    python test_api_direct.py --checkpoint-status 1
"""

import requests
import json
import argparse
import time
from datetime import datetime


# Preset RFIDs from test database (matching test_data.sql)
PRESET_PARTICIPANTS = {
    '001': {'rfid': 'E28011606000020471A0E5C9', 'name': 'Jaka Prasetya'},
    '002': {'rfid': 'E28011606000020471A0E5CA', 'name': 'Siti Nurhaliza'},
    '003': {'rfid': 'E28011606000020471A0E5CB', 'name': 'Budi Santoso'},
    '004': {'rfid': 'E28011606000020471A0E5CC', 'name': 'Ani Wijaya'},
}


class RfidApiTester:
    def __init__(self, base_url, event_id=1, checkpoint_id=1):
        self.base_url = base_url.rstrip('/')
        self.event_id = event_id
        self.checkpoint_id = checkpoint_id
        self.session = requests.Session()
        self.session.headers.update({
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        })

    def test_scan(self, rfid_tag=None, checkpoint_id=None, participant_name=None):
        """Test the scan endpoint"""
        if not checkpoint_id:
            checkpoint_id = self.checkpoint_id

        url = f"{self.base_url}/api/rfid/scan"
        payload = {
            'event_id': self.event_id,
            'checkpoint_id': checkpoint_id,
            'rfid_tag': rfid_tag,
            'reader_id': 'TEST-READER',
            'signal_strength': 85,
        }

        print(f"\n{'='*60}")
        if participant_name:
            print(f"Testing SCAN: {participant_name}")
        else:
            print(f"Testing SCAN endpoint")
        print(f"{'='*60}")
        print(f"URL: {url}")
        print(f"RFID: {rfid_tag}")
        print(f"Checkpoint ID: {checkpoint_id}")

        try:
            response = self.session.post(url, json=payload, timeout=10)
            print(f"\nStatus Code: {response.status_code}")

            if response.status_code == 200:
                data = response.json()
                print(f"Response: {json.dumps(data, indent=2)}")

                if data.get('success'):
                    print(f"\n✓ SUCCESS")
                    participant = data.get('participant', {})
                    timing = data.get('timing', {})
                    print(f"  BIB: {participant.get('bib_number')} - {participant.get('name')}")
                    print(f"  Time: {timing.get('checkpoint_time')}")
                    print(f"  Elapsed: {timing.get('elapsed_time')}")
                    print(f"  Position: #{timing.get('position')}")
                else:
                    print(f"\n✗ FAILED: {data.get('message')}")
            else:
                print(f"Response: {response.text}")

            return response.json() if response.status_code == 200 else None

        except Exception as e:
            print(f"Error: {e}")
            return None

    def test_checkpoint_status(self, checkpoint_id):
        """Test the checkpoint status endpoint"""
        url = f"{self.base_url}/api/rfid/checkpoint/{checkpoint_id}/status"

        print(f"\n{'='*60}")
        print(f"Testing CHECKPOINT STATUS endpoint")
        print(f"{'='*60}")
        print(f"URL: {url}")

        try:
            response = self.session.get(url, timeout=10)
            print(f"\nStatus Code: {response.status_code}")
            print(f"Response: {json.dumps(response.json(), indent=2)}")
            return response.json()
        except Exception as e:
            print(f"Error: {e}")
            return None

    def test_results(self, category_id):
        """Test the live results endpoint"""
        url = f"{self.base_url}/api/rfid/results/{category_id}"

        print(f"\n{'='*60}")
        print(f"Testing LIVE RESULTS endpoint")
        print(f"{'='*60}")
        print(f"URL: {url}")

        try:
            response = self.session.get(url, timeout=10)
            print(f"\nStatus Code: {response.status_code}")
            print(f"Response: {json.dumps(response.json(), indent=2)}")
            return response.json()
        except Exception as e:
            print(f"Error: {e}")
            return None

    def test_event_checkpoints(self, event_id):
        """Test getting event checkpoints"""
        url = f"{self.base_url}/api/rfid/event/{event_id}/checkpoints"

        print(f"\n{'='*60}")
        print(f"Testing EVENT CHECKPOINTS endpoint")
        print(f"{'='*60}")
        print(f"URL: {url}")

        try:
            response = self.session.get(url, timeout=10)
            print(f"\nStatus Code: {response.status_code}")
            print(f"Response: {json.dumps(response.json(), indent=2)}")
            return response.json()
        except Exception as e:
            print(f"Error: {e}")
            return None

    def test_participant_by_rfid(self, rfid_tag):
        """Test getting participant by RFID"""
        url = f"{self.base_url}/api/rfid/participant/by-rfid/{rfid_tag}"

        print(f"\n{'='*60}")
        print(f"Testing PARTICIPANT BY RFID endpoint")
        print(f"{'='*60}")
        print(f"URL: {url}")

        try:
            response = self.session.get(url, timeout=10)
            print(f"\nStatus Code: {response.status_code}")
            print(f"Response: {json.dumps(response.json(), indent=2)}")
            return response.json()
        except Exception as e:
            print(f"Error: {e}")
            return None

    def full_test_simulation(self):
        """
        Simulate a full race with multiple checkpoints
        Uses preset participants from test database
        """
        print("\n" + "="*60)
        print("FULL RACE SIMULATION TEST")
        print("Using participants from test database")
        print("="*60)

        # Get event checkpoints first
        print("\n1. Getting event checkpoints...")
        checkpoints = self.test_event_checkpoints(self.event_id)

        if not checkpoints or not checkpoints.get('success'):
            print("Failed to get checkpoints. Make sure you have checkpoints set up.")
            return

        checkpoint_list = checkpoints.get('data', [])
        if not checkpoint_list:
            print("No checkpoints found!")
            return

        # Sort by order
        checkpoint_list.sort(key=lambda x: x['checkpoint_order'])
        print(f"\nFound {len(checkpoint_list)} checkpoints:")
        for cp in checkpoint_list:
            print(f"  - {cp['checkpoint_name']} ({cp['checkpoint_type']})")

        # Use first 3 participants
        test_participants = ['001', '002', '003']

        print(f"\nTesting with {len(test_participants)} participants:")
        for bib in test_participants:
            p = PRESET_PARTICIPANTS[bib]
            print(f"  - BIB {bib}: {p['name']}")

        # Simulate passing through each checkpoint
        for i, cp in enumerate(checkpoint_list):
            print(f"\n{'='*60}")
            print(f"CHECKPOINT {i+1}: {cp['checkpoint_name']} ({cp['checkpoint_type']})")
            print(f"{'='*60}")

            for bib in test_participants:
                p = PRESET_PARTICIPANTS[bib]
                result = self.test_scan(p['rfid'], cp['id'], f"BIB {bib} - {p['name']}")
                time.sleep(0.5)

            # Wait between checkpoints
            if i < len(checkpoint_list) - 1:
                print(f"\nWaiting 2 seconds before next checkpoint...")
                time.sleep(2)

        # Get final results
        print("\n" + "="*60)
        print("FINAL RESULTS")
        print("="*60)

        if checkpoint_list:
            category_id = checkpoint_list[0]['category_id']
            self.test_results(category_id)

    def interactive_mode(self):
        """Interactive testing mode"""
        print("""
    ╔══════════════════════════════════════════════════════════╗
    ║           RFID API Direct Tester - Interactive           ║
    ╚══════════════════════════════════════════════════════════╝

    Available Test Participants (from database):
    ┌─────┬─────────────────┬──────────────────────────┐
    │ BIB │ Name            │ RFID Tag                 │
    ├─────┼─────────────────┼──────────────────────────┤
    │ 001 │ Jaka Prasetya   │ E28011606000020471A0E5C9 │
    │ 002 │ Siti Nurhaliza  │ E28011606000020471A0E5CA │
    │ 003 │ Budi Santoso    │ E28011606000020471A0E5CB │
    │ 004 │ Ani Wijaya      │ E28011606000020471A0E5CC │
    └─────┴─────────────────┴──────────────────────────┘

    Commands:
      scan <bib>        → Scan participant by BIB (1-5 or 001-005)
      checkpoints       → Get event checkpoints
      results <cat_id>  → Get live results
      status <cp_id>    → Get checkpoint status
      full              → Run full race simulation
      help              → Show this help
      quit              → Exit
        """)

        while True:
            try:
                cmd = input("\nAPI> ").strip().split()
                if not cmd:
                    continue

                command = cmd[0].lower()

                if command == 'quit':
                    print("Goodbye!")
                    break

                elif command == 'help':
                    print("\nAvailable commands:")
                    print("  scan <bib>        - Test scan endpoint")
                    print("  checkpoints       - Get event checkpoints")
                    print("  results <cat_id>  - Get live results")
                    print("  status <cp_id>    - Get checkpoint status")
                    print("  full              - Full race simulation")
                    print("  quit              - Exit")

                elif command == 'scan' and len(cmd) > 1:
                    bib = cmd[1].zfill(3)
                    if bib in PRESET_PARTICIPANTS:
                        p = PRESET_PARTICIPANTS[bib]
                        self.test_scan(p['rfid'], participant_name=f"BIB {bib} - {p['name']}")
                    else:
                        print(f"Unknown BIB: {cmd[1]}. Valid: 001-005")

                elif command == 'checkpoints':
                    self.test_event_checkpoints(self.event_id)

                elif command == 'results' and len(cmd) > 1:
                    category_id = int(cmd[1])
                    self.test_results(category_id)

                elif command == 'status' and len(cmd) > 1:
                    checkpoint_id = int(cmd[1])
                    self.test_checkpoint_status(checkpoint_id)

                elif command == 'full':
                    self.full_test_simulation()

                else:
                    print("Unknown command. Type 'help' for available commands.")

            except KeyboardInterrupt:
                print("\n\nExiting...")
                break
            except Exception as e:
                print(f"Error: {e}")


def main():
    parser = argparse.ArgumentParser(description='RFID API Direct Tester')
    parser.add_argument('--url', default='http://localhost:8000', help='Laravel app base URL')
    parser.add_argument('--event-id', type=int, default=1, help='Event ID')
    parser.add_argument('--checkpoint-id', type=int, default=1, help='Default checkpoint ID')
    parser.add_argument('--scan', action='store_true', help='Test scan endpoint')
    parser.add_argument('--bib', help='BIB number to scan (001-005)')
    parser.add_argument('--rfid', help='Custom RFID tag to scan')
    parser.add_argument('--checkpoint-status', type=int, help='Get checkpoint status')
    parser.add_argument('--results', type=int, help='Get live results for category')
    parser.add_argument('--checkpoints', action='store_true', help='Get event checkpoints')
    parser.add_argument('--participant-rfid', help='Get participant by RFID')
    parser.add_argument('--full-test', action='store_true', help='Run full race simulation')
    parser.add_argument('--interactive', action='store_true', help='Interactive mode (RECOMMENDED)')

    args = parser.parse_args()

    tester = RfidApiTester(args.url, args.event_id, args.checkpoint_id)

    if args.interactive:
        tester.interactive_mode()

    elif args.scan:
        if args.bib:
            bib = args.bib.zfill(3)
            if bib in PRESET_PARTICIPANTS:
                p = PRESET_PARTICIPANTS[bib]
                tester.test_scan(p['rfid'], participant_name=f"BIB {bib} - {p['name']}")
            else:
                print(f"Error: Unknown BIB '{args.bib}'. Valid: 001-005")
        elif args.rfid:
            tester.test_scan(args.rfid)
        else:
            print("Error: --scan requires --bib or --rfid")

    elif args.checkpoint_status:
        tester.test_checkpoint_status(args.checkpoint_status)

    elif args.results:
        tester.test_results(args.results)

    elif args.checkpoints:
        tester.test_event_checkpoints(args.event_id)

    elif args.participant_rfid:
        tester.test_participant_by_rfid(args.participant_rfid)

    elif args.full_test:
        tester.full_test_simulation()

    else:
        # Default: interactive mode
        print("Starting interactive mode...\n")
        tester.interactive_mode()


if __name__ == '__main__':
    main()
