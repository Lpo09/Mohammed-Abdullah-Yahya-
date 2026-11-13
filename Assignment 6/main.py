#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Banner Grabbing Tool
For educational and authorized security testing only.
"""

import socket
import sys
from concurrent.futures import ThreadPoolExecutor


def grab_banner(host, port, timeout=3):
    """
    Connect to a service on a given port and read its banner.
    :param host: Target hostname or IP
    :param port: Target port number
    :param timeout: Timeout in seconds
    :return: Banner string or error message
    """
    try:
        # 1) Create a TCP connection
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(timeout)
        sock.connect((host, port))

        # 2) Interactive services (e.g., HTTP) require an initial request.
        #    Send a basic HEAD request if the port is a web port.
        if port in (80, 8080, 8000, 443, 8443):
            request = f"HEAD / HTTP/1.1\r\nHost: {host}\r\nConnection: close\r\n\r\n"
            sock.sendall(request.encode())

        # 3) Receive the data
        banner_bytes = sock.recv(1024)
        sock.close()

        if not banner_bytes:
            return "[!] No banner (connection OK but empty response)"

        # 4) Decode safely, ignoring encoding errors
        banner = banner_bytes.decode('utf-8', errors='ignore').strip()

        # 5) Extract the first line (usually contains the version)
        first_line = banner.splitlines()[0] if banner else ""
        return first_line if first_line else banner

    except socket.timeout:
        return "[!] Timeout"
    except ConnectionRefusedError:
        return "[-] Port closed (Connection Refused)"
    except socket.gaierror:
        return "[!] Hostname resolution failed"
    except Exception as e:
        return f"[!] Error: {e}"


def scan_target(host, ports, timeout=3):
    """Scan multiple ports on the target."""
    print(f"\n[*] Target: {host}")
    print("=" * 60)

    # Use concurrency to speed up scanning
    with ThreadPoolExecutor(max_workers=10) as executor:
        futures = {
            executor.submit(grab_banner, host, port, timeout): port
            for port in ports
        }
        for future in futures:
            port = futures[future]
            result = future.result()
            print(f"[Port {port:>5}] {result}")
    print("=" * 60)


def main():
    if len(sys.argv) < 2:
        print("Usage: python banner_grab.py <host> [ports]")
        print("Example: python banner_grab.py scanme.nmap.org 21,22,80,443")
        sys.exit(1)

    host = sys.argv[1]

    # Default common ports
    if len(sys.argv) >= 3:
        ports = [int(p.strip()) for p in sys.argv[2].split(",")]
    else:
        ports = [21, 22, 23, 25, 80, 110, 143, 443, 3306, 3389, 8080]

    scan_target(host, ports)


if __name__ == "__main__":
    main()