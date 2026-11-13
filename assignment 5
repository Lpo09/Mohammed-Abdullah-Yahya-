#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Multithreaded Port Scanner
A fast port scanner using sockets and concurrent.futures
"""

import socket
import time
import sys
from concurrent.futures import ThreadPoolExecutor, as_completed
from datetime import datetime

# ============ Configuration ============
MAX_WORKERS = 50      # Number of parallel threads
TIMEOUT = 1.0         # Socket timeout in seconds


def scan_port(target: str, port: int) -> tuple:
    """
    Scan a single port on the target host.

    Args:
        target: Target IP or hostname
        port:   Port number to scan

    Returns:
        (port, is_open) tuple
    """
    sock = None
    try:
        # 1) Create TCP socket
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)

        # 2) Set timeout to prevent hanging on closed ports
        sock.settimeout(TIMEOUT)

        # 3) Attempt connection - ex_connect
        result = sock.connect_ex((target, port))  # returns 0 on success

        if result == 0:
            return (port, True)
        return (port, False)

    except socket.gaierror:
        return (port, False)
    except socket.error:
        return (port, False)
    except Exception:
        return (port, False)

    finally:
        # 4) Always close the socket (even on error)
        if sock is not None:
            try:
                sock.close()
            except Exception:
                pass


def get_service_name(port: int) -> str:
    """Try to resolve the service name associated with a port."""
    try:
        return socket.getservbyport(port, 'tcp')
    except OSError:
        return "unknown"


def port_scanner(target: str, start_port: int = 1, end_port: int = 1024):
    """
    Main multithreaded scanning routine.

    Args:
        target:     Target address
        start_port: Range start
        end_port:   Range end
    """
    print("=" * 60)
    print(f"[*] Target        : {target}")
    print(f"[*] Port range    : {start_port} - {end_port}")
    print(f"[*] Threads       : {MAX_WORKERS}")
    print(f"[*] Timeout       : {TIMEOUT}s")
    print(f"[*] Started at    : {datetime.now().strftime('%H:%M:%S')}")
    print("=" * 60)

    open_ports = []
    start_time = time.perf_counter()

    # Use ThreadPoolExecutor to scan in parallel
    with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
        # Submit all tasks (ports) to the thread pool
        futures = {
            executor.submit(scan_port, target, port): port
            for port in range(start_port, end_port + 1)
        }

        # Collect results as each task completes
        for future in as_completed(futures):
            try:
                port, is_open = future.result()
                if is_open:
                    service = get_service_name(port)
                    open_ports.append(port)
                    print(f"[+] Port {port:<5} OPEN   -> service: {service}")
            except Exception:
                pass

    elapsed = time.perf_counter() - start_time

    # Summary
    print("=" * 60)
    print(f"[✓] Open ports found : {len(open_ports)}")
    if open_ports:
        print(f"[✓] Open ports list  : {sorted(open_ports)}")
    print(f"[✓] Elapsed time     : {elapsed:.2f} seconds")
    print(f"[✓] Finished at      : {datetime.now().strftime('%H:%M:%S')}")
    print("=" * 60)

    return sorted(open_ports)


def resolve_target(target: str) -> str:
    """Validate and resolve the target to an IP address."""
    try:
        return socket.gethostbyname(target)
    except socket.gaierror:
        print(f"[-] Error: could not resolve host '{target}'")
        sys.exit(1)


def main():
    # Read inputs from the user
    if len(sys.argv) >= 2:
        target = sys.argv[1]
        start = int(sys.argv[2]) if len(sys.argv) >= 3 else 1
        end = int(sys.argv[3]) if len(sys.argv) >= 4 else 1024
    else:
        print("=== Multithreaded Port Scanner ===")
        target = input("Enter target IP or hostname: ").strip()
        try:
            start = int(input("Start port [1]: ").strip() or 1)
            end = int(input("End port [1024]: ").strip() or 1024)
        except ValueError:
            print("[-] Invalid input, using defaults (1-1024)")
            start, end = 1, 1024

    # Validate port range
    if not (0 < start <= end <= 65535):
        print("[-] Invalid port range (must be between 1 and 65535)")
        sys.exit(1)

    # Resolve target
    ip = resolve_target(target)

    try:
        port_scanner(ip, start, end)
    except KeyboardInterrupt:
        print("\n[!] Scan interrupted by user")
        sys.exit(0)


if __name__ == "__main__":
    main()