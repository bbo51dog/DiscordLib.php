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

    public function send(string $data) {
        $bin = chr(0x8); //1000
        $bin .= chr(0x1); //opcode text
        $bin .= chr(1); //mask
        if (strlen($data) < 126) {
            $bin .= chr(strlen($data));
        } elseif (strlen($data) < 0xFFFF) {
            $bin .= chr(126) . pack("n", strlen($data));
        } else {
            $bin .= chr(127) . pack("N", 0) . pack("N", strlen($data));
        }
        $maskKey = pack("N", rand(1, 0x7FFFFFFF));
        $masked = "";
        for ($i = 0; $i < strlen($data); $i++) {
            $masked[$i] = chr(ord($data[$i]) ^ ord($maskKey[$i % 4]));
        }
        $bin .= $maskKey . $masked;
        fwrite($this->resource, $bin);
    }

    /**
     * @return string
     * @throws WebSocketException
     */
    public function read(): string {
        $data = "";
        $final = 0;
        while ($final === 0) {
            $header = fread($this->resource, 2);
            if (!$header) {
                throw new WebSocketException("Reading failed");
            }
            $opcode = ord($header[0]) & 0x0F;
            $final = ord($header[0]) & 0x80;
            $masked = ord($header[1]) & 0x80;
            $payload_len = ord($header[1]) & 0x7F;
            if ($payload_len >= 0x7E) {
                $ext_len = 2;
                if ($payload_len == 0x7F) $ext_len = 8;
                $header = fread($this->resource, $ext_len);
                if (!$header) {
                    throw new WebSocketException("Reading failed");
                }
                $payload_len = 0;
                for ($i = 0; $i < $ext_len; $i++)
                    $payload_len += ord($header[$i]) << ($ext_len - $i - 1) * 8;
            }
            if ($masked) {
                $mask = fread($this->resource, 4);
                if (!$mask) {
                    throw new WebSocketException("Reading failed");
                }
            }
            $frame_data = "";
            while ($payload_len > 0) {
                $frame = fread($this->resource, $payload_len);
                if (!$frame) {
                    throw new WebSocketException("Reading failed");
                }
                $payload_len -= strlen($frame);
                $frame_data .= $frame;
            }
            if ($opcode === 9) {
                fwrite($this->resource, chr(0x8A) . chr(0x80) . pack("N", rand(1, 0x7FFFFFFF)));
                continue;
            } elseif ($opcode === 8) {
                $this->close();
            } elseif ($opcode < 3) {
                $data_len = strlen($frame_data);
                if ($masked) {
                    for ($i = 0; $i < $data_len; $i++) {
                        $data .= $frame_data[$i] ^ $mask[$i % 4];
                    }
                } else {
                    $data .= $frame_data;
                }
            } else {
                continue;
            }
        }
        return $data;
    }

    public function close() {
        fclose($this->resource);
    }
}