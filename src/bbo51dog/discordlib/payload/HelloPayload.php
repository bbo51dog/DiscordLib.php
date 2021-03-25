<?php

namespace bbo51dog\discordlib\payload;

class HelloPayload extends Payload implements Receiveable {

    /** @var int */
    private $heartbeatInterval;

    /**
     * @inheritDoc
     */
    public function parseEventData(?array $data): void {
        $this->heartbeatInterval = $data["heartbeat_interval"];
    }

    /**
     * @inheritDoc
     */
    public function getOp(): int {
        return OpCode::OP_HELLO;
    }

    /**
     * @return int
     */
    public function getHeartbeatInterval(): int {
        return $this->heartbeatInterval;
    }
}