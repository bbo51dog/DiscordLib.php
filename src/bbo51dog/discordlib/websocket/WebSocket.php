<?php

namespace bbo51dog\discordlib\websocket;

class WebSocket {

    /** @var string */
    private $host;

    /** @var resource */
    private $resource;

    /**
     * WebSocket constructor.
     *
     * @param string $host
     */
    public function __construct(string $host) {
        $this->host = $host;
    }

    /**
     * @throws WebSocketException
     */
    public function open() {
        $key = base64_encode(random_bytes(16));
        $address = "wss://" . $this->host . ":443";
        $resource = stream_socket_client($address, $error_code, $error_message);
        if (!$resource) {
            throw new WebSocketException("WebSocket connection failed ({$error_code} {$error_message})");
        }
        $header =
            "GET / HTTP/1.1" .
            "Host: {$this->host}" .
            "Upgrade: websocket" .
            "Connection: upgrade" .
            "Sec-WebSocket-Version: 13" .
            "Sec-WebSocket-Key: {$key}";
        if (!fwrite($resource, $header)) {
            throw new WebSocketException("Sending header failed");
        }
        $server_response = fread($resource, 1024);
        if (stripos($server_response, "101") === false ||
            stripos($server_response, "Sec-WebSocket-Accept: {$key}") === false) {
            throw new WebSocketException("WebSocket response failed");
        }
        $this->resource = $resource;
    }
}