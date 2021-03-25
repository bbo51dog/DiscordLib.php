<?php

namespace bbo51dog\discordlib\payload;

interface Receiveable {

    /**
     * @param array|null $data
     */
    public function parseEventData(?array $data): void;
}