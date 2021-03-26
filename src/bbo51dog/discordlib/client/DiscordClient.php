<?php

namespace bbo51dog\discordlib\client;

use bbo51dog\discordlib\payload\HeartbeatPayload;
use bbo51dog\discordlib\payload\HelloPayload;
use bbo51dog\discordlib\payload\IdentifyPayload;
use bbo51dog\discordlib\payload\OpCode;
use bbo51dog\discordlib\payload\Payload;
use bbo51dog\discordlib\websocket\WebSocketClient;
use bbo51dog\discordlib\websocket\WebSocketException;

abstract class DiscordClient {

    public const GATEWAY_HOST = "gateway.discord.gg";

    public const GATEWAY_VERSION = 8;

    public const GATEWAY_PACKET_ENCODE = "json";

    public const TICK_INTERVAL = 0.1;

    /** @var WebSocketClient */
    private $wsClient;

    /** @var string */
    private $token;

    /** @var int */
    private $heartbeatInterval;

    /** @var float */
    private $lastHeartbeat;

    /** @var float */
    private $lastTick;

    /** @var int|null */
    private $lastSequenceNum = null;

    /**
     * DiscordClient constructor.
     *
     * @param string $token
     */
    public function __construct(string $token) {
        $this->token = $token;
        Payload::init();
    }

    final public function run() {
        $this->wsClient = new WebSocketClient(
            self::GATEWAY_HOST,
            443,
            "/?v=" . self::GATEWAY_VERSION . "&encoding=" . self::GATEWAY_PACKET_ENCODE
        );
        $this->wsClient->open();
        $this->lastTick = microtime(true);
        $this->lastHeartbeat = microtime(true);
        while ($this->wsClient->isConnecting()) {
            $current = microtime(true);
            if ($current - $this->lastTick >= self::TICK_INTERVAL) {
                $this->onTick();
            }
            if ($this->heartbeatInterval !== null && $current - $this->lastHeartbeat >= $this->heartbeatInterval) {
                $this->heartbeat();
            }
            usleep(10000);
        }
    }

    final public function onTick() {
        try {
            $received = $this->wsClient->read();
            if (!empty($received)) {
                $this->onRead($received);
            }
        } catch (WebSocketException $exception) {
        }
        $this->lastTick = microtime(true);
    }

    final public function onRead(string $data) {
        $payload = Payload::createFromJson($data);
        if ($payload instanceof HelloPayload) {
            $this->onHello($payload);
        } elseif ($payload instanceof HeartbeatPayload) {
            $this->onHeartbeat();
        }
    }

    private function onHello(HelloPayload $payload) {
        $this->heartbeatInterval = $payload->getHeartbeatInterval();
        $this->heartbeat();
        $this->identify();
    }

    private function onHeartbeat() {
        $this->heartbeat();
    }

    private function heartbeat() {
        /** @var HeartbeatPayload $payload */
        $payload = Payload::get(OpCode::OP_HEARTBEAT);
        $payload->setSequenceNum($this->lastSequenceNum);
        $this->wsClient->send($payload->encode());
        $this->lastHeartbeat = microtime(true);
    }

    private function identify() {
        /** @var IdentifyPayload $payload */
        $payload = Payload::get(OpCode::OP_IDENTIFY);
        $payload->setToken($this->token);
        $this->wsClient->send($payload->encode());
    }
}