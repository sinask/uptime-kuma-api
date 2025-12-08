<?php

namespace UptimeKuma\LaravelApi\Support;

/**
 * Incident display styles for status pages.
 */
enum IncidentStyle: string
{
    case PRIMARY = 'primary';
    case INFO = 'info';
    case SUCCESS = 'success';
    case WARNING = 'warning';
    case DANGER = 'danger';
}
