<?php

namespace UptimeKuma\LaravelApi\Support;

enum NotificationType: string
{
    case DISCORD = 'discord';
    case EMAIL = 'email';
    case SLACK = 'slack';
    case TELEGRAM = 'telegram';
    case WEBHOOK = 'webhook';
}
