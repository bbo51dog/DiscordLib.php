<?php

namespace bbo51dog\discordlib\payload;

interface Sendable {

    /**
     * @return string JSON
     */
    public function encode(): string;
}