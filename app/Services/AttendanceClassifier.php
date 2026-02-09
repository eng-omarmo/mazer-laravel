<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class AttendanceClassifier
{
    public function classify(array $records, ?string $timezone = null, string $dayBoundary = '04:00', string $noonBoundary = '12:00'): array
    {
        $tz = $timezone ?: config('app.timezone');
        $out = [];
        foreach ($records as $i => $rec) {
            if (!isset($rec['userId']) || !isset($rec['time'])) {
                throw new InvalidArgumentException("Record at index $i missing userId or time");
            }
            $dt = $this->parseTimestamp($rec['time'], $tz);
            $local = $dt->copy()->setTimezone($tz);
            $localTime = $local->format('H:i');
            $date = $local->format('Y-m-d');
            $type = $this->classifyType($localTime, $dayBoundary, $noonBoundary);
            if ($localTime < $dayBoundary) {
                $date = $local->subDay()->format('Y-m-d');
            }
            $out[] = [
                'userId' => $rec['userId'],
                'time' => $rec['time'],
                'iso' => $local->toIso8601String(),
                'date' => $date,
                'localTime' => $localTime,
                'type' => $type,
            ];
        }
        return $out;
    }

    public function aggregateDaily(array $typedRecords): array
    {
        $daily = [];
        foreach ($typedRecords as $rec) {
            $key = $rec['userId'] . '|' . $rec['date'];
            if (!isset($daily[$key])) {
                $daily[$key] = [
                    'userId' => $rec['userId'],
                    'date' => $rec['date'],
                    'check_in' => null,
                    'check_out' => null,
                    'duplicates' => [],
                ];
            }
            if ($rec['type'] === 'check-in') {
                if ($daily[$key]['check_in'] === null || $rec['localTime'] < $daily[$key]['check_in']) {
                    if ($daily[$key]['check_in'] !== null) {
                        $daily[$key]['duplicates'][] = ['type' => 'check-in', 'time' => $daily[$key]['check_in']];
                    }
                    $daily[$key]['check_in'] = $rec['localTime'];
                } else {
                    $daily[$key]['duplicates'][] = ['type' => 'check-in', 'time' => $rec['localTime']];
                }
            } else {
                if ($daily[$key]['check_out'] === null || $rec['localTime'] > $daily[$key]['check_out']) {
                    if ($daily[$key]['check_out'] !== null) {
                        $daily[$key]['duplicates'][] = ['type' => 'check-out', 'time' => $daily[$key]['check_out']];
                    }
                    $daily[$key]['check_out'] = $rec['localTime'];
                } else {
                    $daily[$key]['duplicates'][] = ['type' => 'check-out', 'time' => $rec['localTime']];
                }
            }
        }
        return array_values($daily);
    }

    private function parseTimestamp(string $timestamp, string $tz): Carbon
    {
        try {
            return Carbon::parse($timestamp, $tz);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException("Invalid timestamp '$timestamp'");
        }
    }

    private function classifyType(string $localTime, string $dayBoundary, string $noonBoundary): string
    {
        if ($localTime < $dayBoundary) {
            return 'check-out';
        }
        return $localTime < $noonBoundary ? 'check-in' : 'check-out';
    }
}
