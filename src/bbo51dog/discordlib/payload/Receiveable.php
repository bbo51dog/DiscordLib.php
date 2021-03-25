<?php

namespace bbo51dog\discordlib\payload;

interface Receiveable {

    /**
     * @param mixed $data
     */
    public function parseEventData($data): void;
}