<?php

namespace bbo51dog\discordlib\payload;

class DispatchPayload extends Payload implements Receiveable {

    /** @var int */
    private $sequenceNum;

    /** @var string */
    private $eventName;

    /**
     * @inheritDoc
     */
    public function parseEventData($data): void {

    }

    public function getOp(): int {
        return OpCode::OP_DISPATCH;
    }

    /**
     * @return int
     */
    public function getSequenceNum(): int {
        return $this->sequenceNum;
    }

    /**
     * @param int $sequenceNum
     */
    public function setSequenceNum(int $sequenceNum): void {
        $this->sequenceNum = $sequenceNum;
    }

    /**
     * @return string
     */
    public function getEventName(): string {
        return $this->eventName;
    }

    /**
     * @param string $eventName
     */
    public function setEventName(string $eventName): void {
        $this->eventName = $eventName;
    }
}