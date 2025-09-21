# Swoole Coroutine Server Demo

A demonstration of Swoole's coroutine capabilities showing the difference between blocking and non-blocking operations in PHP.

## Overview

This project demonstrates how Swoole coroutines can handle concurrent requests efficiently by using non-blocking operations instead of traditional blocking PHP code.

## Features

- **Blocking vs Non-blocking comparison**: Shows commented code for traditional blocking approach
- **Coroutine implementation**: Demonstrates Swoole's coroutine functionality
- **Non-blocking sleep**: Uses `Co::sleep()` instead of `sleep()`
- **Concurrent request handling**: Multiple requests can be processed simultaneously

## Code Structure

The `server.php` file contains:

1. **Commented blocking code**: Traditional PHP server approach (lines 3-14)
2. **Active coroutine code**: Swoole coroutine implementation (lines 17-32)

## Key Differences

### Blocking Approach (Commented)
```php
sleep(1); // Blocks the entire process
```

### Coroutine Approach (Active)
```php
Co::sleep(1); // Non-blocking, allows other requests to be processed
```

## Requirements

- PHP 7.4+
- Swoole extension installed
- Linux/macOS (Swoole doesn't support Windows)

## Installation

1. Install Swoole extension:
```bash
# Using PECL
pecl install swoole

# Or using package manager (Ubuntu/Debian)
sudo apt-get install php-swoole
```

## Usage

1. Run the server:
```bash
php server.php
```

2. Test with multiple concurrent requests:
```bash
# In separate terminals
curl http://127.0.0.1:9501
curl http://127.0.0.1:9501
curl http://127.0.0.1:9501
```

## Expected Behavior

With coroutines enabled, all three requests should start at nearly the same time and complete after 1 second, demonstrating that the server can handle multiple requests concurrently without blocking.

## License

MIT License
