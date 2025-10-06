## Changelog

### Unreleased

#### Features
- rewrite the project as a Laravel-ready Composer package that exposes a HTTP based client and facade.
- provide configuration publishing support and sensible defaults for connecting to Uptime Kuma.
- add PHP enums that mirror core Uptime Kuma concepts such as monitor types and statuses.
- ship a mocked HTTP test-suite that verifies authentication and error handling flows.

### Release 1.2.1

#### Bugfixes
- drop first info event without a version

### Release 1.2.0

#### Features
- add support for uptime kuma 1.23.0 and 1.23.1

#### Bugfixes
- remove `name` from maintenance monitors and status pages
- rstip url globally
- convert sendUrl from bool to int
- validate accepted status codes types

### Release 1.1.0

#### Features
- add support for uptime kuma 1.22.0 and 1.22.1

### Release 1.0.1

#### Bugfixes
- fix ValueError if monitor authMethod is None

### Release 1.0.0

#### Features
- add `ssl_verify` parameter
- add `wait_events` parameter
- implement context manager for UptimeKumaApi class
- drop Python 3.6 support
- implement `get_monitor_status` helper method
- implement timeouts for all methods (`timeout` parameter)
- add support for uptime kuma 1.21.3
- drop support for Uptime Kuma versions < 1.21.3
- check for required notification arguments
- raise exception when deleting an element that does not exist
- replace raw return values with enum values

#### Bugfixes
- adjust monitor `status` type to allow all used values
- fix memory leak

#### BREAKING CHANGES
- Python 3.7+ required
- maintenance parameter `timezone` renamed to `timezoneOption`
- Removed the `wait_timeout` parameter. Use the new `timeout` parameter instead. The `timeout` parameter specifies how many seconds the client should wait for the connection, an expected event or a server response.
- changed return values of methods `get_heartbeats`, `get_important_heartbeats`, `avg_ping`, `uptime`, `cert_info`
- Uptime Kuma versions < 1.21.3 are not supported in uptime-kuma-api 1.0.0+
- Removed the `get_heartbeat` method. This method was never intended to retrieve information. Use `get_heartbeats` or `get_important_heartbeats` instead.
- Types of return values changed to enum values:
  - monitor: `type` (str -> MonitorType), `status` (bool -> MonitorStatus), `authMethod` (str -> AuthMethod)
  - notification: `type` (str -> NotificationType)
  - docker host: `dockerType` (str -> DockerType)
  - status page: `style` (str -> IncidentStyle)
  - maintenance: `strategy` (str -> MaintenanceStrategy)
  - proxy: `protocol` (str -> ProxyProtocol)

### Release 0.13.0

#### Feature
- add support for uptime kuma 1.21.2
- implement custom socketio headers

#### Bugfix
- do not wait for events that have already arrived

### Release 0.12.0

#### Feature
- add support for uptime kuma 1.21.1

### Release 0.11.0

#### Feature
- add support for uptime kuma 1.21.0
