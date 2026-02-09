<?php

namespace Tests\Unit;

use App\Services\TimeNormalizer;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceWindowTest extends TestCase
{
    public function test_midnight_to_midnight_window_accepts_today_and_rejects_outside(): void
    {
        $tz = 'UTC';
        $svc = new TimeNormalizer();
        $today = Carbon::parse('2024-12-12 00:00:00', $tz);
        $start = $today->copy();
        $end = $today->copy()->addDay();

        $inIso = '2024-12-12T09:16:42.000Z';
        $normIn = $svc->normalize($inIso, $tz, '00:00');
        $this->assertTrue($svc->withinWindow($normIn['local'], $start, $end));

        $prevIso = '2024-12-11T23:59:59.000Z';
        $normPrev = $svc->normalize($prevIso, $tz, '00:00');
        $this->assertFalse($svc->withinWindow($normPrev['local'], $start, $end));

        $nextIso = '2024-12-13T00:00:00.000Z';
        $normNext = $svc->normalize($nextIso, $tz, '00:00');
        $this->assertFalse($svc->withinWindow($normNext['local'], $start, $end));
    }
}
