<?php

namespace UptimeKuma\LaravelApi\Support;

/**
 * Maintenance window scheduling strategies.
 */
enum MaintenanceStrategy: string
{
    case MANUAL = 'manual';
    case ONCE = 'single';
    case RECURRING_INTERVAL = 'recurring-interval';
    case RECURRING_WEEKDAY = 'recurring-weekday';
    case RECURRING_DAY_OF_MONTH = 'recurring-day-of-month';
    case CRON = 'cron';
}
