<?php

/**
 * Professional Swoole WebSocket Chat Server
 * 
 * Features:
 * - User management with unique names
 * - Message history storage
 * - Connection status tracking
 * - Error handling and logging
 * - Rate limiting
 * - Professional code structure
 */

class ChatServer
{
    private $server;
    private $users = [];
    private $messageHistory = [];
    private $maxHistorySize = 100;
    private $rateLimits = [];
    private $maxMessagesPerMinute = 30;

    public function __construct($host = "0.0.0.0", $port = 9502)
    {
        $this->server = new Swoole\WebSocket\Server($host, $port);
        $this->setupEventHandlers();
    }

    private function setupEventHandlers()
    {
        $this->server->on('start', [$this, 'onStart']);
        $this->server->on('open', [$this, 'onOpen']);
        $this->server->on('message', [$this, 'onMessage']);
        $this->server->on('close', [$this, 'onClose']);
        $this->server->on('error', [$this, 'onError']);
    }

    public function onStart($server)
    {
        $this->log("Professional Chat Server started at ws://127.0.0.1:9502");
        $this->log("Server PID: " . getmypid());
        $this->log("Memory usage: " . $this->formatBytes(memory_get_usage(true)));
    }

    public function onOpen($server, $request)
    {
        $fd = $request->fd;
        $this->log("New connection from {$fd}");

        // Send welcome message with connection info
        $welcomeMessage = [
            'type' => 'system',
            'from' => 'Server',
            'message' => 'Welcome to Professional Chat! Please set your name.',
            'time' => date('H:i:s'),
            'fd' => $fd
        ];

        $server->push($fd, json_encode($welcomeMessage));

        // Send recent message history
        $this->sendMessageHistory($server, $fd);
    }

    public function onMessage($server, $frame)
    {
        $fd = $frame->fd;

        try {
            $data = json_decode($frame->data, true);

            if (!$data) {
                $this->sendError($server, $fd, "Invalid JSON format");
                return;
            }

            // Rate limiting
            if (!$this->checkRateLimit($fd)) {
                $this->sendError($server, $fd, "Rate limit exceeded. Please slow down.");
                return;
            }

            $messageType = $data['type'] ?? 'message';

            switch ($messageType) {
                case 'set_name':
                    $this->handleSetName($server, $fd, $data);
                    break;
                case 'message':
                    $this->handleMessage($server, $fd, $data);
                    break;
                case 'ping':
                    $this->handlePing($server, $fd);
                    break;
                default:
                    $this->sendError($server, $fd, "Unknown message type: {$messageType}");
            }
        } catch (Exception $e) {
            $this->log("Error processing message from {$fd}: " . $e->getMessage());
            $this->sendError($server, $fd, "Server error processing message");
        }
    }

    private function handleSetName($server, $fd, $data)
    {
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            $this->sendError($server, $fd, "Name cannot be empty");
            return;
        }

        if (strlen($name) > 20) {
            $this->sendError($server, $fd, "Name too long (max 20 characters)");
            return;
        }

        // Check if name is already taken
        if (in_array($name, array_column($this->users, 'name'))) {
            $this->sendError($server, $fd, "Name '{$name}' is already taken");
            return;
        }

        // Update or create user
        $this->users[$fd] = [
            'name' => $name,
            'connected_at' => time(),
            'last_activity' => time()
        ];

        $this->log("User {$fd} set name to: {$name}");

        // Notify all users about new user
        $this->broadcastSystemMessage($server, "User '{$name}' joined the chat");

        // Send confirmation to user
        $response = [
            'type' => 'name_set',
            'from' => 'Server',
            'message' => "Welcome, {$name}!",
            'time' => date('H:i:s'),
            'fd' => $fd
        ];
        $server->push($fd, json_encode($response));
    }

    private function handleMessage($server, $fd, $data)
    {
        if (!isset($this->users[$fd])) {
            $this->sendError($server, $fd, "Please set your name first");
            return;
        }

        $message = trim($data['message'] ?? '');

        if (empty($message)) {
            $this->sendError($server, $fd, "Message cannot be empty");
            return;
        }

        if (strlen($message) > 500) {
            $this->sendError($server, $fd, "Message too long (max 500 characters)");
            return;
        }

        $user = $this->users[$fd];
        $user['last_activity'] = time();

        $payload = [
            'type' => 'message',
            'from' => $user['name'],
            'message' => $message,
            'time' => date('H:i:s'),
            'fd' => $fd
        ];

        // Store in history
        $this->addToHistory($payload);

        // Broadcast to all users
        $this->broadcastMessage($server, $payload);

        $this->log("Message from {$user['name']}: {$message}");
    }

    private function handlePing($server, $fd)
    {
        $response = [
            'type' => 'pong',
            'from' => 'Server',
            'message' => 'pong',
            'time' => date('H:i:s'),
            'fd' => $fd
        ];
        $server->push($fd, json_encode($response));
    }

    private function broadcastMessage($server, $payload)
    {
        $message = json_encode($payload);

        foreach ($server->connections as $fd) {
            if ($server->isEstablished($fd)) {
                $server->push($fd, $message);
            }
        }
    }

    private function broadcastSystemMessage($server, $message)
    {
        $payload = [
            'type' => 'system',
            'from' => 'Server',
            'message' => $message,
            'time' => date('H:i:s'),
            'fd' => 0
        ];
        $this->broadcastMessage($server, $payload);
    }

    private function sendError($server, $fd, $errorMessage)
    {
        $error = [
            'type' => 'error',
            'from' => 'Server',
            'message' => $errorMessage,
            'time' => date('H:i:s'),
            'fd' => $fd
        ];
        $server->push($fd, json_encode($error));
    }

    private function sendMessageHistory($server, $fd)
    {
        if (empty($this->messageHistory)) {
            return;
        }

        $historyMessage = [
            'type' => 'history',
            'from' => 'Server',
            'message' => 'Recent messages:',
            'time' => date('H:i:s'),
            'fd' => $fd,
            'history' => array_slice($this->messageHistory, -10) // Last 10 messages
        ];

        $server->push($fd, json_encode($historyMessage));
    }

    private function addToHistory($message)
    {
        $this->messageHistory[] = $message;

        // Keep only the last N messages
        if (count($this->messageHistory) > $this->maxHistorySize) {
            $this->messageHistory = array_slice($this->messageHistory, -$this->maxHistorySize);
        }
    }

    private function checkRateLimit($fd)
    {
        $now = time();
        $minute = floor($now / 60);

        if (!isset($this->rateLimits[$fd])) {
            $this->rateLimits[$fd] = [];
        }

        // Clean old entries
        $this->rateLimits[$fd] = array_filter(
            $this->rateLimits[$fd],
            function ($timestamp) use ($minute) {
                return floor($timestamp / 60) >= $minute - 1;
            }
        );

        // Check if limit exceeded
        if (count($this->rateLimits[$fd]) >= $this->maxMessagesPerMinute) {
            return false;
        }

        // Add current message
        $this->rateLimits[$fd][] = $now;
        return true;
    }

    public function onClose($server, $fd)
    {
        if (isset($this->users[$fd])) {
            $user = $this->users[$fd];
            $this->log("User '{$user['name']}' (FD: {$fd}) disconnected");

            // Notify other users
            $this->broadcastSystemMessage($server, "User '{$user['name']}' left the chat");

            unset($this->users[$fd]);
        } else {
            $this->log("Client {$fd} disconnected");
        }

        // Clean up rate limits
        unset($this->rateLimits[$fd]);
    }

    public function onError($server, $fd, $reactorId, $data)
    {
        $this->log("WebSocket error on FD {$fd}: " . $data);
    }

    private function log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        echo "[{$timestamp}] {$message}\n";
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public function start()
    {
        $this->log("Starting Professional Chat Server...");
        $this->server->start();
    }
}

// Start the server
$chatServer = new ChatServer();
$chatServer->start();
