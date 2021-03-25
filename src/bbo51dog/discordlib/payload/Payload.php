<?php

namespace bbo51dog\discordlib\payload;

abstract class Payload {

    /** @var Payload[] */
    private static $list;

    final public static function init() {
        self::register(new DispatchPayload());
        self::register(new HelloPayload());
    }

    final public static function createFromJson(string $json): self {
        /** @var array $data */
        $data = json_decode($json, true);
        /** @var int $op */
        $op = $data["op"];
        $payload = self::get($op);
        if ($payload instanceof DispatchPayload) {
            $payload->setSequenceNum($data["s"]);
            $payload->setEventName($data["t"]);
        }
        $payload->parseEventData($data["d"]);
        return $payload;
    }

    final public static function get(int $opcode): self {
        return clone self::$list[$opcode];
    }

    private static function register(Payload $payload) {
        self::$list[$payload->getOp()] = clone $payload;
    }

    /**
     * @param array|null $data
     */
    abstract public function parseEventData(?array $data): void;

    /**
     * @return int
     */
    abstract public function getOp(): int;
}