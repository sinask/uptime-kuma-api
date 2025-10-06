<?php

namespace UptimeKuma\LaravelApi\Support;

enum MonitorStatus: int
{
    case PAUSED = 0;
    case UP = 1;
    case DOWN = 2;
    case PENDING = 3;
    case UNKNOWN = 4;
}
