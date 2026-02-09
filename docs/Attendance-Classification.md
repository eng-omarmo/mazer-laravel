# Attendance Classification Logic

## Purpose
- Transform JSON device logs into structured records with a `type` field: `check-in` or `check-out`.
- Handle multiple entries per user/day, midnight crossings, weekends, and timezone differences.

## Rules
- Timezone-aware parsing using application timezone unless overridden.
- Day boundary: times before `04:00` are assigned to the previous calendar date and classified as `check-out`.
- Noon split: times `< 12:00` classify as `check-in`; times `>= 12:00` classify as `check-out`.
- Multiple entries (same user/day):
  - Aggregation keeps earliest `check-in` and latest `check-out`.
  - Other same-type entries are recorded in `duplicates`.
- Weekends:
  - Classified the same as weekdays; no special handling.

## Validation
- Timestamps must be parseable; invalid entries raise errors.

## Outputs
- `classify(records, timezone, dayBoundary, noonBoundary)`: returns typed records with `date`, `localTime`, and ISO string.
- `aggregateDaily(typedRecords)`: returns per-user/day summary with `check_in`, `check_out`, and `duplicates`.
