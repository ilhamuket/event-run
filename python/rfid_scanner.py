#!/usr/bin/env python3
"""
RFID Race Timing Scanner
========================
This script connects to RFID UHF readers via TCP socket and sends
scan data to the Laravel backend for processing.

Features:
- Connects to multiple RFID readers simultaneously
- Sends scans to Laravel API endpoint
- Handles reconnection on connection loss
- Logs all activity for debugging
- Supports configuration via .env file

Hardware: UHF RFID Reader (4 meter range)
Protocol: TCP Socket

Usage:
    python rfid_scanner.py

Configuration (.env file):
    API_BASE_URL=http://your-laravel-app.com
    EVENT_ID=1
    CHECKPOINT_ID=1
    READER_HOST=192.168.1.100
    READER_PORT=5000
    RFID_START=4
    RFID_LENGTH=24
"""

import json
import socket
import binascii
import sys
import os
import logging
import time
from datetime import datetime
from threading import Thread
from queue import Queue
import requests
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

# Configuration
CONFIG = {
    'api_base_url': os.getenv('API_BASE_URL', 'http://localhost'),
    'event_id': int(os.getenv('EVENT_ID', 1)),
    'checkpoint_id': int(os.getenv('CHECKPOINT_ID', 1)),
    'reader_host': os.getenv('READER_HOST', ''),  # Empty = listen mode
    'reader_port': int(os.getenv('READER_PORT', 5000)),
    'rfid_start': int(os.getenv('RFID_START', 4)),  # Byte position where RFID data starts
    'rfid_length': int(os.getenv('RFID_LENGTH', 24)),  # Length of RFID tag in hex chars
    'reader_id': os.getenv('READER_ID', 'READER-01'),
    'reconnect_delay': int(os.getenv('RECONNECT_DELAY', 5)),
    'log_level': os.getenv('LOG_LEVEL', 'INFO'),
}

# Setup logging
logging.basicConfig(
    level=getattr(logging, CONFIG['log_level']),
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.StreamHandler(),
        logging.FileHandler('rfid_scanner.log')
    ]
)
logger = logging.getLogger(__name__)

# Setup HTTP session with retry
session = requests.Session()
retry_strategy = Retry(
    total=3,
    backoff_factor=1,
    status_forcelist=[500, 502, 503, 504]
)
adapter = HTTPAdapter(max_retries=retry_strategy)
session.mount('http://', adapter)
session.mount('https://', adapter)


class RfidScanner:
    """
    RFID Scanner class that handles connection to reader and API communication
    """

    def __init__(self, config):
        self.config = config
        self.running = False
        self.scan_queue = Queue()
        self.stats = {
            'total_scans': 0,
            'valid_scans': 0,
            'failed_scans': 0,
            'api_errors': 0,
        }

    def parse_rfid_data(self, raw_data):
        """
        Parse raw data from RFID reader to extract the RFID tag

        Args:
            raw_data: Raw bytes from the reader

        Returns:
            str: Extracted RFID tag or None if parsing fails
        """
        try:
            hex_data = binascii.hexlify(raw_data).decode('utf-8')
            logger.debug(f"Raw hex data: {hex_data}")

            # Extract RFID tag based on configured positions
            rfid_start = self.config['rfid_start']
            rfid_length = self.config['rfid_length']

            if len(hex_data) < rfid_start + rfid_length:
                logger.warning(f"Data too short: {len(hex_data)} < {rfid_start + rfid_length}")
                return None

            rfid_tag = hex_data[rfid_start:rfid_start + rfid_length]

            # Validate RFID tag (should be hex characters)
            if not all(c in '0123456789abcdefABCDEF' for c in rfid_tag):
                logger.warning(f"Invalid RFID format: {rfid_tag}")
                return None

            return rfid_tag.upper()

        except Exception as e:
            logger.error(f"Error parsing RFID data: {e}")
            return None

    def send_to_api(self, rfid_tag, signal_strength=None):
        """
        Send RFID scan to Laravel API

        Args:
            rfid_tag: The RFID tag string
            signal_strength: Optional signal strength from reader

        Returns:
            dict: API response or None on error
        """
        url = f"{self.config['api_base_url']}/api/rfid/scan"

        payload = {
            'event_id': self.config['event_id'],
            'checkpoint_id': self.config['checkpoint_id'],
            'rfid_tag': rfid_tag,
            'reader_id': self.config['reader_id'],
            'signal_strength': signal_strength,
        }

        headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }

        try:
            logger.info(f"Sending scan to API: {rfid_tag}")
            response = session.post(url, json=payload, headers=headers, timeout=10)
            response_data = response.json()

            if response_data.get('success'):
                self.stats['valid_scans'] += 1
                participant = response_data.get('participant', {})
                timing = response_data.get('timing', {})

                logger.info(
                    f"✓ VALID SCAN: BIB {participant.get('bib')} - {participant.get('name')} | "
                    f"Elapsed: {timing.get('elapsed_time')} | Position: {timing.get('position')}"
                )

                # Print to console for operator visibility
                self._print_scan_result(response_data)
            else:
                self.stats['failed_scans'] += 1
                logger.warning(
                    f"✗ INVALID SCAN: {rfid_tag} - {response_data.get('error')}: {response_data.get('message')}"
                )

            return response_data

        except requests.exceptions.RequestException as e:
            self.stats['api_errors'] += 1
            logger.error(f"API request failed: {e}")
            return None

    def _print_scan_result(self, response_data):
        """Print formatted scan result to console"""
        participant = response_data.get('participant', {})
        checkpoint = response_data.get('checkpoint', {})
        timing = response_data.get('timing', {})

        print("\n" + "=" * 60)
        print(f"  {checkpoint.get('type', '').upper()}: {response_data.get('message')}")
        print("-" * 60)
        print(f"  BIB: {participant.get('bib'):<10} Name: {participant.get('name')}")
        print(f"  Category: {participant.get('category')}")
        print(f"  Time: {timing.get('checkpoint_time')}")
        print(f"  Elapsed: {timing.get('elapsed_time'):<15} Position: #{timing.get('position')}")
        print("=" * 60 + "\n")

    def handle_connection(self, conn, addr):
        """
        Handle incoming connection from RFID reader

        Args:
            conn: Socket connection
            addr: Client address tuple (ip, port)
        """
        ip, port = addr
        logger.info(f"Reader connected from {ip}:{port}")

        try:
            while self.running:
                data = conn.recv(1024)
                if not data:
                    logger.info(f"Connection closed by reader {ip}")
                    break

                self.stats['total_scans'] += 1
                rfid_tag = self.parse_rfid_data(data)

                if rfid_tag:
                    logger.info(f"RFID scanned: {rfid_tag}")
                    self.scan_queue.put(rfid_tag)

        except socket.error as e:
            logger.error(f"Socket error with {ip}: {e}")
        finally:
            conn.close()

    def api_worker(self):
        """Worker thread that processes the scan queue and sends to API"""
        while self.running:
            try:
                rfid_tag = self.scan_queue.get(timeout=1)
                self.send_to_api(rfid_tag)
                self.scan_queue.task_done()
            except:
                continue

    def run_server_mode(self):
        """Run in server mode - wait for readers to connect"""
        server_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        server_socket.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)

        try:
            server_socket.bind(('', self.config['reader_port']))
            server_socket.listen(10)
            server_socket.settimeout(1)

            logger.info(f"RFID Scanner server started on port {self.config['reader_port']}")
            logger.info(f"Event ID: {self.config['event_id']}, Checkpoint ID: {self.config['checkpoint_id']}")
            logger.info("Waiting for RFID readers to connect...")

            while self.running:
                try:
                    conn, addr = server_socket.accept()
                    # Start thread to handle this connection
                    thread = Thread(target=self.handle_connection, args=(conn, addr))
                    thread.daemon = True
                    thread.start()
                except socket.timeout:
                    continue

        except socket.error as e:
            logger.error(f"Server socket error: {e}")
        finally:
            server_socket.close()

    def run_client_mode(self):
        """Run in client mode - connect to a specific reader"""
        while self.running:
            try:
                logger.info(f"Connecting to reader at {self.config['reader_host']}:{self.config['reader_port']}")

                client_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
                client_socket.settimeout(30)
                client_socket.connect((self.config['reader_host'], self.config['reader_port']))

                logger.info("Connected to RFID reader!")

                while self.running:
                    data = client_socket.recv(1024)
                    if not data:
                        break

                    self.stats['total_scans'] += 1
                    rfid_tag = self.parse_rfid_data(data)

                    if rfid_tag:
                        logger.info(f"RFID scanned: {rfid_tag}")
                        self.scan_queue.put(rfid_tag)

            except socket.error as e:
                logger.error(f"Connection error: {e}")
            finally:
                try:
                    client_socket.close()
                except:
                    pass

            if self.running:
                logger.info(f"Reconnecting in {self.config['reconnect_delay']} seconds...")
                time.sleep(self.config['reconnect_delay'])

    def start(self):
        """Start the RFID scanner"""
        self.running = True

        # Start API worker thread
        api_thread = Thread(target=self.api_worker)
        api_thread.daemon = True
        api_thread.start()

        # Run in appropriate mode
        if self.config['reader_host']:
            self.run_client_mode()
        else:
            self.run_server_mode()

    def stop(self):
        """Stop the RFID scanner"""
        self.running = False
        logger.info("Scanner stopped")
        logger.info(f"Statistics: {self.stats}")


def fetch_config_from_api(base_url, reader_ip):
    """
    Fetch checkpoint configuration from API based on reader IP

    Args:
        base_url: API base URL
        reader_ip: IP address of the reader

    Returns:
        dict: Configuration from API or None
    """
    url = f"{base_url}/api/rfid/device?ip={reader_ip}"

    try:
        response = session.get(url, timeout=10)
        data = response.json()

        if data.get('status'):
            config_data = data.get('data', {})
            logger.info(f"Loaded config from API for checkpoint: {config_data.get('checkpoint_name')}")
            return config_data
        else:
            logger.warning(f"Device not registered in API: {data.get('message')}")
            return None

    except Exception as e:
        logger.error(f"Failed to fetch config from API: {e}")
        return None


def main():
    """Main entry point"""
    print("""
    ╔══════════════════════════════════════════════════════════╗
    ║           RFID RACE TIMING SCANNER                       ║
    ║           Event Run System v1.0                          ║
    ╚══════════════════════════════════════════════════════════╝
    """)

    # Check if we should fetch config from API
    if os.getenv('AUTO_CONFIG') == 'true':
        reader_ip = os.getenv('READER_HOST', socket.gethostbyname(socket.gethostname()))
        api_config = fetch_config_from_api(CONFIG['api_base_url'], reader_ip)

        if api_config:
            CONFIG['event_id'] = api_config.get('event_id', CONFIG['event_id'])
            CONFIG['checkpoint_id'] = api_config.get('checkpoint_id', CONFIG['checkpoint_id'])
            CONFIG['rfid_start'] = api_config.get('rfid_start', CONFIG['rfid_start'])
            CONFIG['rfid_length'] = api_config.get('rfid_length', CONFIG['rfid_length'])

    logger.info("Configuration:")
    logger.info(f"  API URL: {CONFIG['api_base_url']}")
    logger.info(f"  Event ID: {CONFIG['event_id']}")
    logger.info(f"  Checkpoint ID: {CONFIG['checkpoint_id']}")
    logger.info(f"  Reader Host: {CONFIG['reader_host'] or 'Server Mode (listening)'}")
    logger.info(f"  Reader Port: {CONFIG['reader_port']}")
    logger.info(f"  RFID Start Position: {CONFIG['rfid_start']}")
    logger.info(f"  RFID Length: {CONFIG['rfid_length']}")

    scanner = RfidScanner(CONFIG)

    try:
        scanner.start()
    except KeyboardInterrupt:
        logger.info("\nShutting down...")
        scanner.stop()


if __name__ == '__main__':
    main()
