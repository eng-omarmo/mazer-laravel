<?php

namespace App\Services;

use Carbon\Carbon;

class ShiftScheduler
{
    public function determineShift(string $checkIn, string $morningStart = '06:00', string $afternoonStart = '15:00'): string
    {
        if ($checkIn < $afternoonStart) {
            return 'morning';
        }
        return 'afternoon';
    }

    public function expectedCheckout(string $checkIn, int $hours): string
    {
        $dt = Carbon::createFromFormat('H:i', $checkIn)->addHours($hours);
        return $dt->format('H:i');
    }

    public function flags(string $checkIn, ?string $checkOut, string $shift, string $morningStart = '06:00', string $afternoonStart = '15:00', int $graceMinutes = 15): array
    {
        $start = $shift === 'morning' ? $morningStart : $afternoonStart;
        $early = $checkIn < $start;
        $late = $checkIn > $this->addMinutes($start, $graceMinutes);
        $missed = empty($checkOut);
        return ['early_arrival' => $early, 'late_checkin' => $late, 'missed_checkout' => $missed];
    }

    public function overtimeMinutes(?string $checkOut, string $expected): int
    {
        if (empty($checkOut)) {
            return 0;
        }
        $outTs = Carbon::createFromFormat('H:i', $checkOut)->getTimestamp();
        $expTs = Carbon::createFromFormat('H:i', $expected)->getTimestamp();
        return max(0, intval(($outTs - $expTs) / 60));
    }

    private function addMinutes(string $time, int $minutes): string
    {
        return Carbon::createFromFormat('H:i', $time)->addMinutes($minutes)->format('H:i');
    }
}
