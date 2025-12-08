<?php

namespace UptimeKuma\LaravelApi\Support;

/**
 * Docker connection types.
 */
enum DockerType: string
{
    case SOCKET = 'socket';
    case TCP = 'tcp';
}
