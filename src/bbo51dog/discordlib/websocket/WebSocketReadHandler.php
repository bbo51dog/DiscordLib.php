<?php

namespace bbo51dog\discordlib\websocket;

interface WebSocketReadHandler {

    public function onRead(string $data);
}