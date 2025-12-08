<?php

namespace UptimeKuma\LaravelApi\Support;

/**
 * Notification provider types supported by Uptime Kuma.
 */
enum NotificationType: string
{
    // Communication Platforms
    case DISCORD = 'discord';
    case SLACK = 'slack';
    case MATTERMOST = 'mattermost';
    case TEAMS = 'msteams';
    case ROCKET_CHAT = 'rocket.chat';
    case MATRIX = 'matrix';
    case TELEGRAM = 'telegram';
    case FEISHU = 'Feishu';
    case DINGDING = 'DingDing';
    case WECOM = 'WeCom';
    case LINE = 'line';
    case LINE_NOTIFY = 'LineNotify';
    case SIGNAL = 'signal';
    case GOOGLE_CHAT = 'GoogleChat';

    // Email
    case SMTP = 'smtp';

    // Push Notifications
    case PUSHOVER = 'pushover';
    case PUSHBULLET = 'pushbullet';
    case PUSHY = 'pushy';
    case PUSH_DEER = 'PushDeer';
    case BARK = 'Bark';
    case LUNASEA = 'lunasea';
    case GOTIFY = 'gotify';
    case NTFY = 'ntfy';
    case APPRISE = 'apprise';
    case GORUSH = 'gorush';
    case PUSH_BY_TECHULUS = 'PushByTechulus';
    case PUSHBOTS = 'pushbots';
    case ONE_BOT = 'OneBot';

    // SMS Services
    case TWILIO = 'twilio';
    case ALIYUN_SMS = 'AliyunSms';
    case CLICK_SEND_SMS = 'clicksendsms';
    case FREE_MOBILE = 'FreeMobile';
    case PROMO_SMS = 'promosms';
    case SERWER_SMS = 'serwersms';
    case SMSC = 'smsc';
    case SMS_EAGLE = 'SMSEagle';
    case OCTOPUSH = 'octopush';
    case SMS_MANAGER = 'SMSManager';
    case CELLSYNT = 'cellsynt';
    case SEND_GRID = 'SendGrid';

    // Incident Management
    case PAGERDUTY = 'PagerDuty';
    case OPSGENIE = 'Opsgenie';
    case FLASHDUTY = 'FlashDuty';
    case SQUADCAST = 'squadcast';
    case GOALERT = 'GoAlert';
    case PAGER_TREE = 'PagerTree';
    case SPLUNK = 'Splunk';
    case SPIKE = 'spike';
    case ZENDUTY = 'Zenduty';

    // Webhooks and Custom Endpoints
    case WEBHOOK = 'webhook';
    case HOMEASSISTANT = 'HomeAssistant';

    // Other Services
    case ALERTA = 'alerta';
    case ALERTNOW = 'AlertNow';
    case SERVERCHAN = 'ServerChan';
    case NOSTR = 'nostr';
    case KOOK = 'Kook';
    case WHATSAPP_CALLMEBOT = 'whapi';
    case SEVENIO = 'SevenIO';
    case KEEP = 'Keep';
    case UPTIMEROBOT = 'UptimeRobot';

    // Developer/Infrastructure Tools
    case BITRIX24 = 'Bitrix24';
    case BREEZY = 'breezy';
    case SYNOLOGY_CHAT = 'SynologyChat';
    case ZOHO_CLIQ = 'ZohoCliq';
    case KAFKA = 'kafka';
    case STRIDE = 'stride';
    case MAILGUN = 'Mailgun';

    // Status Page Services
    case STATUSPAGE = 'Statuspage';
    case INSTATUS = 'Instatus';

    // Additional Providers
    case WAHA = 'waha';
    case GET_TIME_ZONE = 'get-time-zone';
    case THREEMA = 'threema';
    case WPUSH = 'WPush';
    case PAGERTREE = 'pagertree';
    case HEIIONCALL = 'HeiiOnCall';
}
