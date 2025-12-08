<?php

namespace UptimeKuma\LaravelApi\Support;

/**
 * Proxy protocol types.
 */
enum ProxyProtocol: string
{
    case HTTP = 'http';
    case HTTPS = 'https';
    case SOCKS = 'socks';
    case SOCKS5 = 'socks5';
}
