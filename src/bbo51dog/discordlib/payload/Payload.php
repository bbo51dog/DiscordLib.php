<?php

namespace bbo51dog\discordlib\payload;

abstract class Payload {

    /** @var Payload[] */
    private static $list;

    public static function init() {
        self::register(new DispatchPayload());
    }

    public static function createFromJson(string $json): self {
        /** @var array $data */
        $data = json_decode($json, true);
        /** @var int $op */
        $op = $data["op"];
        $payload = clone self::$list[$op];
        if ($payload instanceof DispatchPayload) {
            $payload->setSequenceNum($data["s"]);
            $payload->setEventName($data["t"]);
        }
        $payload->parseEventData($data["d"]);
        return $payload;
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