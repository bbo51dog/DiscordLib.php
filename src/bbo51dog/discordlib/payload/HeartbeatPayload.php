<?php

namespace bbo51dog\discordlib\payload;

class HeartbeatPayload extends Payload implements Receiveable, Sendable {

    /** @var int|null */
    private $sequenceNum;

    /**
     * @inheritDoc
     */
    public function encode(): string {
        $data = [
            "op" => $this->getOp(),
            "d" => $this->sequenceNum,
        ];
        return json_encode($data);
    }

    /**
     * @inheritDoc
     */
    public function parseEventData($data): void {
        $this->sequenceNum = $data;
    }

    /**
     * @inheritDoc
     */
    public function getOp(): int {
        return OpCode::OP_HEARTBEAT;
    }

    /**
     * @return int|null
     */
    public function getSequenceNum(): ?int {
        return $this->sequenceNum;
    }

    /**
     * @param int|null $sequenceNum
     */
    public function setSequenceNum(?int $sequenceNum): void {
        $this->sequenceNum = $sequenceNum;
    }
}