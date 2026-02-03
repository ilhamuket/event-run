#!/usr/bin/env python3
"""
RFID Scanner Test Client
========================
This script simulates an RFID reader for testing purposes.
It connects to the scanner server and sends test RFID data.

Usage:
    # Test single scan with preset participant
    python test_scanner_client.py --bib 001

    # Test with custom RFID
    python test_scanner_client.py --rfid E28011606000020471A0E5C9

    # Interactive mode (RECOMMENDED)
    python test_scanner_client.py --interactive

    # Test all participants
    python test_scanner_client.py --all
"""

import socket
import binascii
import argparse
import time
import sys


# Preset RFIDs from test database (matching test_data.sql)
PRESET_PARTICIPANTS = {
    '001': {'rfid': 'E28011606000020471A0E5C9', 'name': 'Jaka Prasetya'},
    '002': {'rfid': 'E28011606000020471A0E5CA', 'name': 'Siti Nurhaliza'},
    '003': {'rfid': 'E28011606000020471A0E5CB', 'name': 'Budi Santoso'},
    '004': {'rfid': 'E28011606000020471A0E5CC', 'name': 'Ani Wijaya'},
}


def create_rfid_packet(rfid_tag, prefix_bytes=4):
    """
    Create an RFID packet as it would come from a real reader

    Args:
        rfid_tag: The RFID tag (hex string)
        prefix_bytes: Number of prefix bytes before RFID data

    Returns:
        bytes: The complete packet
    """
    # Create prefix (header bytes from reader)
    prefix = bytes([0x01, 0x02, 0x00, 0x00])[:prefix_bytes]

    # Convert RFID tag to bytes
    rfid_bytes = binascii.unhexlify(rfid_tag)

    # Suffix (checksum, end marker, etc.)
    suffix = bytes([0xFF])

    return prefix + rfid_bytes + suffix


def send_scan(host, port, rfid_tag, participant_name=None):
    """
    Send a single RFID scan to the server

    Args:
        host: Server host
        port: Server port
        rfid_tag: RFID tag to send
        participant_name: Optional name for display
    """
    try:
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(5)
        sock.connect((host, port))

        packet = create_rfid_packet(rfid_tag)

        if participant_name:
            print(f"\nSending scan for: {participant_name}")
        print(f"RFID: {rfid_tag}")
        print(f"Raw packet (hex): {binascii.hexlify(packet).decode()}")

        sock.send(packet)

        # Wait a moment for the response to process
        time.sleep(0.5)
        sock.close()

        print(f"✓ Sent successfully\n")
        return True

    except socket.error as e:
        print(f"✗ Connection error: {e}\n")
        return False


def send_raw_bytes(host, port, hex_data):
    """
    Send raw bytes to the server

    Args:
        host: Server host
        port: Server port
        hex_data: Hex string to send
    """
    try:
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(5)
        sock.connect((host, port))

        data = binascii.unhexlify(hex_data)
        print(f"Sending raw hex: {hex_data}")

        sock.send(data)
        time.sleep(0.5)
        sock.close()

        print(f"✓ Sent successfully")
        return True

    except socket.error as e:
        print(f"✗ Connection error: {e}")
        return False


def interactive_mode(host, port):
    """
    Interactive mode for testing
    """
    print("""
    ╔══════════════════════════════════════════════════════════╗
    ║       RFID Scanner Test Client - Interactive Mode        ║
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
      1-5 or 001-005   → Scan participant by BIB number
      scan <rfid>      → Send custom RFID tag (24 hex chars)
      all              → Scan all participants
      raw <hex>        → Send raw hex bytes
      help             → Show this help
      quit             → Exit
    """)

    while True:
        try:
            cmd = input("RFID> ").strip().split()
            if not cmd:
                continue

            command = cmd[0].lower()

            if command == 'quit':
                print("Goodbye!")
                break

            elif command == 'help':
                print("\nAvailable commands:")
                print("  1-5, 001-005  - Scan participant")
                print("  scan <rfid>   - Send custom RFID")
                print("  all           - Scan all participants")
                print("  raw <hex>     - Send raw bytes")
                print("  quit          - Exit\n")

            elif command in ['1', '2', '3', '4', '5', '001', '002', '003', '004', '005']:
                # Normalize BIB number
                bib = command.zfill(3)
                if bib in PRESET_PARTICIPANTS:
                    p = PRESET_PARTICIPANTS[bib]
                    send_scan(host, port, p['rfid'], f"BIB {bib} - {p['name']}")
                else:
                    print(f"Unknown BIB number: {bib}")

            elif command == 'scan' and len(cmd) > 1:
                rfid = cmd[1].upper()
                if len(rfid) == 24 and all(c in '0123456789ABCDEF' for c in rfid):
                    send_scan(host, port, rfid)
                else:
                    print("Error: RFID must be 24 hexadecimal characters")

            elif command == 'all':
                print("\nScanning all participants...")
                for bib, p in sorted(PRESET_PARTICIPANTS.items()):
                    send_scan(host, port, p['rfid'], f"BIB {bib} - {p['name']}")
                    time.sleep(1)
                print("All scans completed!")

            elif command == 'raw' and len(cmd) > 1:
                send_raw_bytes(host, port, cmd[1])

            else:
                print("Unknown command. Type 'help' for available commands.")

        except KeyboardInterrupt:
            print("\n\nExiting...")
            break
        except Exception as e:
            print(f"Error: {e}")


def test_all_participants(host, port, delay=1):
    """
    Test all preset participants
    """
    print("\nTesting all participants from database...\n")

    for bib, p in sorted(PRESET_PARTICIPANTS.items()):
        print(f"[{bib}/005] ", end="")
        send_scan(host, port, p['rfid'], f"BIB {bib} - {p['name']}")
        if bib != '005':
            time.sleep(delay)

    print("All participants tested!")


def main():
    parser = argparse.ArgumentParser(description='RFID Scanner Test Client')
    parser.add_argument('--host', default='localhost', help='Scanner server host')
    parser.add_argument('--port', type=int, default=5001, help='Scanner server port')
    parser.add_argument('--bib', help='BIB number (001-005) to scan')
    parser.add_argument('--rfid', help='Custom RFID tag to send (24 hex chars)')
    parser.add_argument('--raw', help='Raw hex bytes to send')
    parser.add_argument('--all', action='store_true', help='Test all participants')
    parser.add_argument('--delay', type=float, default=1.0, help='Delay between scans (seconds)')
    parser.add_argument('--interactive', action='store_true', help='Interactive mode (RECOMMENDED)')

    args = parser.parse_args()

    print(f"Connecting to scanner at {args.host}:{args.port}\n")

    if args.interactive:
        interactive_mode(args.host, args.port)

    elif args.bib:
        bib = args.bib.zfill(3)
        if bib in PRESET_PARTICIPANTS:
            p = PRESET_PARTICIPANTS[bib]
            send_scan(args.host, args.port, p['rfid'], f"BIB {bib} - {p['name']}")
        else:
            print(f"Error: Unknown BIB number '{args.bib}'. Valid: 001-005")

    elif args.rfid:
        rfid = args.rfid.upper()
        if len(rfid) == 24 and all(c in '0123456789ABCDEF' for c in rfid):
            send_scan(args.host, args.port, rfid)
        else:
            print("Error: RFID must be 24 hexadecimal characters")

    elif args.raw:
        send_raw_bytes(args.host, args.port, args.raw)

    elif args.all:
        test_all_participants(args.host, args.port, args.delay)

    else:
        # Default: show interactive mode
        print("No arguments provided. Starting interactive mode...\n")
        interactive_mode(args.host, args.port)


if __name__ == '__main__':
    main()
