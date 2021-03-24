<?php

namespace bbo51dog\discordlib\client;

use bbo51dog\discordlib\payload\HelloPayload;
use bbo51dog\discordlib\payload\Payload;
use bbo51dog\discordlib\websocket\WebSocketClient;
use bbo51dog\discordlib\websocket\WebSocketReadHandler;

abstract class DiscordClient implements WebSocketReadHandler {

    public const GATEWAY_HOST = "gateway.discord.gg";

    public const GATEWAY_VERSION = 8;

    public const GATEWAY_PACKET_ENCODE = "json";

    /** @var WebSocketClient */
    private $wsClient;

    /** @var string */
    private $token;

    /** @var int */
    private $heartbeatInterval;

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
        $this->wsClient->registerReadHandler($this);
        $this->wsClient->run();
    }

    public function onRead(string $data) {
        $payload = Payload::createFromJson($data);
        if ($payload instanceof HelloPayload) {
            $this->onHello($payload);
        }
    }

    private function onHello(HelloPayload $payload) {
        $this->heartbeatInterval = $payload->getHeartbeatInterval();
    }
}