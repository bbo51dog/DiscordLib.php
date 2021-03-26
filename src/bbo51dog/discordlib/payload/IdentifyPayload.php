<?php

namespace bbo51dog\discordlib\payload;

class IdentifyPayload extends Payload implements Sendable {

    /** @var string */
    private $token;

    /**
     * @inheritDoc
     */
    public function getOp(): int {
        return OpCode::OP_IDENTIFY;
    }

    /**
     * @inheritDoc
     */
    public function encode(): string {
        $intents = 1 << 0 | 1 << 1 | 1 << 2 | 1 << 3 | 1 << 4 | 1 << 5 | 1 << 6 | 1 << 7 | 1 << 8 | 1 << 9 | 1 << 10 | 1 << 11 | 1 << 12 | 1 << 13 | 1 << 14;
        $data = [
            "op" => $this->getOp(),
            "d" => [
                "token" => $this->token,
                "intents" => $intents,
                "properties" => [
                    "\$os" => php_uname("s"),
                    "\$browser" => "DiscordLib.php",
                    "\$device" => "DiscordLib.php",
                ],
            ],
        ];
        return json_encode($data);
    }

    /**
     * @return string
     */
    public function getToken(): string {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void {
        $this->token = $token;
    }
}