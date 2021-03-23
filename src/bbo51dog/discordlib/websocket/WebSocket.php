<?php

namespace bbo51dog\discordlib\websocket;

class WebSocket {

    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var string */
    private $path;

    /** @var resource */
    private $resource;

    /**
     * WebSocket constructor.
     *
     * @param string $host
     * @param int $port
     * @param string $path
     */
    public function __construct(string $host, int $port = 443, string $path = "/") {
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
    }

    /**
     * @throws WebSocketException
     */
    public function open() {
        $key = base64_encode(random_bytes(16));
        $host = $this->port === 443 ? "ssl://" . $this->host : $this->host;
        $resource = fsockopen($host, $this->port, $error_code, $error_message);
        if (!$resource) {
            throw new WebSocketException("WebSocket connection failed ({$error_code} {$error_message})");
        }
        $header =
            "GET {$this->path} HTTP/1.1\n" .
            "Host: {$this->host}\n" .
            "Upgrade: websocket\n" .
            "Connection: Upgrade\n" .
            "Sec-WebSocket-Version: 13\n" .
            "Sec-WebSocket-Key: {$key}\n\n";
        if (!fwrite($resource, $header)) {
            throw new WebSocketException("Sending header failed");
        }
        stream_set_timeout($resource, 5);
        $server_response = fread($resource, 1024);
        $secWebsocketAccept = base64_encode(sha1($key . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));
        if (stripos($server_response, "101") === false ||
            stripos($server_response, "Sec-WebSocket-Accept: {$secWebsocketAccept}") === false) {
            throw new WebSocketException("WebSocket response failed");
        }
        $this->resource = $resource;
    }
}