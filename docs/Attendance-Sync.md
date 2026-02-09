# Attendance Sync Architecture

## Update Flow
- Logs fetched from device are normalized and sorted.
- Each punch evaluates day boundary, dedup, and session thresholds.
- Transaction with row lock updates check-in/check-out safely.
- Status recalculation applies late/early thresholds.
- AttendanceUpdated event is dispatched for monitoring/broadcast.

## Event-Driven Propagation
- Event: AttendanceUpdated(employee_id, date, check_in, check_out, status, device_status).
- Consumers can subscribe to reflect real-time UI or analytics.
- Listener is registered via AppServiceProvider boot.

## Consistency Validation
- check_out must be later than check_in.
- Minimum session minutes enforced before setting check_out.
- Dedup window prevents rapid duplicates.
- Day boundary assigns early morning punches to previous date.

## Error Handling & Rollback
- Per-punch DB transaction ensures atomicity.
- Exceptions rollback changes automatically.
- Missing employee mapping recorded as warnings.
- Agent/API failures surface errors with context.

## Monitoring
- Logs record attendance update payloads.
- Counts can be aggregated externally for dashboards.

## Concurrency & Thread Safety
- lockForUpdate prevents write races.
- Single-row transactions avoid deadlocks.

## Troubleshooting
- Device unreachable: validate ZK_HOST/ZK_PORT and network.
- No employee match: ensure fingerprint_id mapping.
- Unexpected status: verify device mode and status keys.
- Split days: adjust ATTENDANCE_DAY_BOUNDARY.
- Too many updates: tune ATTENDANCE_DEDUP_MINUTES and ATTENDANCE_MIN_SESSION_MINUTES.

## Benchmarks
- Target propagation: <100ms per punch in testing.
- Measure end-to-end by wrapping sync calls and logging durations.
