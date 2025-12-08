<?php

namespace UptimeKuma\LaravelApi\Support;

/**
 * DNS record types for DNS monitors.
 */
enum DnsRecordType: string
{
    case A = 'A';
    case AAAA = 'AAAA';
    case CAA = 'CAA';
    case CNAME = 'CNAME';
    case MX = 'MX';
    case NS = 'NS';
    case PTR = 'PTR';
    case SOA = 'SOA';
    case SRV = 'SRV';
    case TXT = 'TXT';
}
