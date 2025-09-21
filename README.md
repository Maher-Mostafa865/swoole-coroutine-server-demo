# Professional Swoole WebSocket Chat

A modern, professional real-time chat application built with Swoole WebSocket server and a beautiful responsive web client.

## Overview

This project demonstrates a production-ready chat application using Swoole's WebSocket capabilities with professional features like user management, message history, rate limiting, and error handling.

## Features

### Server Features

- **Professional OOP Structure**: Clean, maintainable code with proper error handling
- **User Management**: Unique username validation and user tracking
- **Message History**: Automatic storage and retrieval of recent messages
- **Rate Limiting**: Prevents spam with configurable message limits
- **Connection Management**: Proper connection handling
- **Logging**: Comprehensive server activity logging
- **Error Handling**: Robust error handling and user feedback

### Client Features

- **Modern UI**: Beautiful, responsive design with animations
- **Real-time Communication**: Instant message delivery and updates
- **Connection Status**: Visual connection indicators
- **Auto-reconnection**: Automatic reconnection with exponential backoff
- **Message History**: Displays recent messages on connection
- **Mobile Responsive**: Works perfectly on all device sizes
- **Professional Styling**: Modern gradient design with smooth animations

## Architecture

### Server (`server.php`)

- **ChatServer Class**: Main server class with professional structure
- **Event Handlers**: Organized event handling for all WebSocket events
- **User Management**: Track connected users with unique names
- **Message Broadcasting**: Efficient message distribution to all clients
- **Rate Limiting**: Per-user rate limiting to prevent abuse
- **History Management**: Automatic message history with size limits

### Client (`client.html`)

- **Modern CSS**: Professional styling with gradients and animations
- **Responsive Design**: Works on desktop, tablet, and mobile
- **WebSocket Client**: Robust WebSocket communication with error handling
- **Auto-reconnection**: Automatic reconnection with visual feedback
- **Message Types**: Support for different message types (system, user, error)
- **Real-time Updates**: Instant message delivery and status updates

## Requirements

- PHP 7.4+
- Swoole extension installed
- Modern web browser with WebSocket support
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

1. Start the WebSocket server:

```bash
php server.php
```

2. Open the client in your browser:

```bash
# Open client.html in your browser
open client.html
# Or navigate to: file:///path/to/client.html
```

3. Set your name and start chatting!

## Server Features

### Message Types

- `set_name`: Set or change your username
- `message`: Send a chat message
- `ping`: Keep-alive ping (handled automatically)

### Rate Limiting

- Maximum 30 messages per minute per user
- Automatic rate limit enforcement
- User-friendly error messages

### User Management

- Unique username validation
- User join/leave notifications
- Connection tracking and cleanup

## Client Features

### Connection Management

- Visual connection status indicator
- Automatic reconnection with exponential backoff
- Connection error handling

### Message Display

- Different message types (own, other, system, error)
- Message timestamps
- Smooth animations and transitions
- Auto-scroll to latest messages

### User Experience

- Responsive design for all screen sizes
- Professional gradient styling
- Smooth animations and hover effects
- Keyboard shortcuts (Enter to send)

## License

MIT License
