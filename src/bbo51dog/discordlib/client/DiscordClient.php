<?php

namespace bbo51dog\discordlib\client;

use bbo51dog\discordlib\payload\HelloPayload;
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
    private $lastTick;

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
        while ($this->wsClient->isConnecting()) {
            $current = microtime(true);
            if ($current - $this->lastTick >= self::TICK_INTERVAL) {
                $this->onTick();
            }
            usleep(10);
        }
    }

    final public function onTick() {
        try {
            $received = $this->wsClient->read();
            $this->onRead($received);
        } catch (WebSocketException $exception) {
        }
        $this->lastTick = microtime(true);
    }

    final public function onRead(string $data) {
        $payload = Payload::createFromJson($data);
        if ($payload instanceof HelloPayload) {
            $this->onHello($payload);
        }
    }

    private function onHello(HelloPayload $payload) {
        $this->heartbeatInterval = $payload->getHeartbeatInterval();
    }
}