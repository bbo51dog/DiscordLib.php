<?php

namespace bbo51dog\discordlib\payload;

interface OpCode {

    public const OP_DISPATCH = 0;
    public const OP_HEARTBEAT = 1;
    public const OP_IDENTIFY = 2;
    public const OP_PRESENCE_UPDATE = 3;
    public const OP_VOICE_STATE_UPDATE = 4;
    public const OP_RESUME = 6;
    public const OP_RECONNECT = 7;
    public const OP_REQUEST_GUILD_MEMBERS = 8;
    public const OP_INVALID_SESSION = 9;
    public const OP_HELLO = 10;
    public const OP_HEARTBEAT_ACK = 11;
}