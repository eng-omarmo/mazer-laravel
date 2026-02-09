<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class TimeNormalizer
{
    public function normalize(string $isoTimestamp, ?string $targetTz = null, string $dayBoundary = '04:00'): array
    {
        $tz = $targetTz ?: config('app.timezone');
        try {
            $dtUtc = Carbon::parse($isoTimestamp)->setTimezone('UTC');
        } catch (\Throwable $e) {
            throw new InvalidArgumentException("Malformed timestamp '$isoTimestamp'");
        }
        $local = $dtUtc->copy()->setTimezone($tz);
        $localDate = $local->format('Y-m-d');
        $localTime = $local->format('H:i');
        if ($localTime < $dayBoundary) {
            $localDate = $local->copy()->subDay()->format('Y-m-d');
        }
        return [
            'utc' => $dtUtc,
            'local' => $local,
            'date' => $localDate,
            'time' => $localTime,
        ];
    }

    public function withinWindow(Carbon $instant, Carbon $since, Carbon $until): bool
    {
        return $instant->gte($since) && $instant->lt($until);
    }
}
