#!/usr/bin/env python3
"""
Quick RFID Mock Tester
======================
Test RFID system tanpa perangkat asli menggunakan data dummy
"""

import requests
import time
import json
from datetime import datetime

# Konfigurasi
API_URL = "http://localhost:8000"
EVENT_ID = 1
CHECKPOINT_ID = 1

# Data peserta dummy dengan RFID (matching test database)
PARTICIPANTS = [
    {"bib": "001", "name": "Jaka Prasetya", "rfid": "E28011606000020471A0E5C9"},
    {"bib": "002", "name": "Siti Nurhaliza", "rfid": "E28011606000020471A0E5CA"},
    {"bib": "003", "name": "Budi Santoso", "rfid": "E28011606000020471A0E5CB"},
    {"bib": "004", "name": "Ani Wijaya", "rfid": "E28011606000020471A0E5CC"},
]

def send_scan(rfid_tag, checkpoint_id=None):
    """Kirim scan ke API"""
    url = f"{API_URL}/api/rfid/scan"

    payload = {
        'event_id': EVENT_ID,
        'checkpoint_id': checkpoint_id or CHECKPOINT_ID,
        'rfid_tag': rfid_tag,
        'reader_id': 'MOCK-READER',
        'signal_strength': 85,
    }

    try:
        response = requests.post(url, json=payload, timeout=10)
        data = response.json()

        if data.get('success'):
            participant = data.get('participant', {})
            timing = data.get('timing', {})

            print(f"✓ SCAN SUCCESS")
            print(f"  BIB: {participant.get('bib')} - {participant.get('name')}")
            print(f"  Time: {timing.get('checkpoint_time')}")
            print(f"  Elapsed: {timing.get('elapsed_time')}")
            print(f"  Position: #{timing.get('position')}")
        else:
            print(f"✗ SCAN FAILED: {data.get('message')}")

        return data

    except Exception as e:
        print(f"✗ ERROR: {e}")
        return None

def test_single_checkpoint():
    """Test scan di satu checkpoint"""
    print("\n" + "="*60)
    print("TEST: Single Checkpoint Scan")
    print("="*60)

    for i, p in enumerate(PARTICIPANTS):
        print(f"\n[{i+1}/{len(PARTICIPANTS)}] Scanning {p['name']} ({p['rfid']})...")
        send_scan(p['rfid'])
        time.sleep(1)

def test_multiple_checkpoints():
    """Test scan di multiple checkpoints"""
    print("\n" + "="*60)
    print("TEST: Multiple Checkpoints (Start → Finish)")
    print("="*60)

    # Asumsi: Checkpoint 1=Start, 2=Mid, 3=Finish
    checkpoints = [
        {"id": 1, "name": "START"},
        {"id": 2, "name": "CHECKPOINT"},
        {"id": 3, "name": "FINISH"},
    ]

    # Test dengan 3 peserta pertama
    for p in PARTICIPANTS[:3]:
        print(f"\n\n{'='*60}")
        print(f"Participant: {p['name']} (BIB {p['bib']})")
        print("="*60)

        for cp in checkpoints:
            print(f"\n→ {cp['name']} (ID {cp['id']})")
            send_scan(p['rfid'], cp['id'])
            time.sleep(2)  # Delay antar checkpoint

        time.sleep(1)  # Delay antar peserta

def test_late_arrival():
    """Test peserta yang terlambat scan"""
    print("\n" + "="*60)
    print("TEST: Late Arrival (Out of Order)")
    print("="*60)

    # Peserta 1 finish
    print("\nParticipant 1 finishing...")
    send_scan(PARTICIPANTS[0]['rfid'], 3)
    time.sleep(1)

    # Peserta 2 baru start
    print("\nParticipant 2 just starting...")
    send_scan(PARTICIPANTS[1]['rfid'], 1)

def test_duplicate_scan():
    """Test duplicate scan (peserta scan 2x di checkpoint sama)"""
    print("\n" + "="*60)
    print("TEST: Duplicate Scan")
    print("="*60)

    p = PARTICIPANTS[0]

    print(f"\nFirst scan: {p['name']}")
    send_scan(p['rfid'])

    time.sleep(2)

    print(f"\nSecond scan (duplicate): {p['name']}")
    send_scan(p['rfid'])

def test_invalid_rfid():
    """Test dengan RFID yang tidak terdaftar"""
    print("\n" + "="*60)
    print("TEST: Invalid RFID")
    print("="*60)

    invalid_rfid = "FFFFFFFFFFFFFFFFFFFFFFFF"
    print(f"\nScanning invalid RFID: {invalid_rfid}")
    send_scan(invalid_rfid)

def interactive_test():
    """Mode interaktif"""
    print("\n" + "="*60)
    print("INTERACTIVE TEST MODE")
    print("="*60)
    print("\nAvailable participants:")
    for i, p in enumerate(PARTICIPANTS):
        print(f"  {i+1}. BIB {p['bib']}: {p['name']} - {p['rfid']}")

    print("\nCommands:")
    print("  1-5: Scan participant 1-5")
    print("  all: Scan all participants")
    print("  invalid: Test invalid RFID")
    print("  quit: Exit")

    while True:
        try:
            cmd = input("\n> ").strip().lower()

            if cmd == 'quit':
                break
            elif cmd == 'all':
                test_single_checkpoint()
            elif cmd == 'invalid':
                test_invalid_rfid()
            elif cmd.isdigit() and 1 <= int(cmd) <= len(PARTICIPANTS):
                p = PARTICIPANTS[int(cmd)-1]
                print(f"\nScanning {p['name']}...")
                send_scan(p['rfid'])
            else:
                print("Unknown command")

        except KeyboardInterrupt:
            print("\nExiting...")
            break

def main():
    """Main menu"""
    print("""
    ╔══════════════════════════════════════════════════════════╗
    ║           RFID MOCK TESTER                               ║
    ║           Test tanpa perangkat asli                      ║
    ╚══════════════════════════════════════════════════════════╝

    Test Options:
    1. Single Checkpoint (semua peserta di 1 checkpoint)
    2. Multiple Checkpoints (start → mid → finish)
    3. Late Arrival (peserta scan tidak urut)
    4. Duplicate Scan (scan 2x di checkpoint sama)
    5. Invalid RFID (RFID tidak terdaftar)
    6. Interactive Mode
    0. Exit
    """)

    while True:
        try:
            choice = input("\nPilih test (0-6): ").strip()

            if choice == '1':
                test_single_checkpoint()
            elif choice == '2':
                test_multiple_checkpoints()
            elif choice == '3':
                test_late_arrival()
            elif choice == '4':
                test_duplicate_scan()
            elif choice == '5':
                test_invalid_rfid()
            elif choice == '6':
                interactive_test()
            elif choice == '0':
                print("Goodbye!")
                break
            else:
                print("Invalid choice")

        except KeyboardInterrupt:
            print("\nExiting...")
            break

if __name__ == '__main__':
    main()
