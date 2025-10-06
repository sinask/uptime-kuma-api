<?php

namespace UptimeKuma\LaravelApi\Support;

enum MonitorType: string
{
    case GROUP = 'group';
    case HTTP = 'http';
    case PORT = 'port';
    case PING = 'ping';
    case KEYWORD = 'keyword';
    case JSON_QUERY = 'json-query';
    case GRPC_KEYWORD = 'grpc-keyword';
    case DNS = 'dns';
    case DOCKER = 'docker';
    case REAL_BROWSER = 'real-browser';
    case PUSH = 'push';
    case STEAM = 'steam';
    case GAMEDIG = 'gamedig';
    case MQTT = 'mqtt';
    case KAFKA_PRODUCER = 'kafka-producer';
    case SQLSERVER = 'sqlserver';
    case POSTGRES = 'postgres';
    case MYSQL = 'mysql';
    case MONGODB = 'mongodb';
    case RADIUS = 'radius';
    case REDIS = 'redis';
    case TAILSCALE_PING = 'tailscale-ping';
}
