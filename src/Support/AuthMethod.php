<?php

namespace UptimeKuma\LaravelApi\Support;

/**
 * Authentication methods for HTTP monitors.
 */
enum AuthMethod: string
{
    case NONE = '';
    case HTTP_BASIC = 'basic';
    case NTLM = 'ntlm';
    case MTLS = 'mtls';
    case OAUTH2_CC = 'oauth2-cc';
}
