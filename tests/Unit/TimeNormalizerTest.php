<?php

namespace Tests\Unit;

use App\Services\TimeNormalizer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeNormalizerTest extends TestCase
{
    use RefreshDatabase;

    public function test_parses_iso_to_local_and_window_check(): void
    {
        $svc = new TimeNormalizer();
        $iso = '2024-12-12T09:16:42.000Z';
        $norm = $svc->normalize($iso, 'UTC', dayBoundary: '04:00');
        $this->assertSame('2024-12-12', $norm['date']);
        $this->assertSame('09:16', $norm['time']);
        $since = Carbon::parse('2024-12-11T09:16:41Z')->setTimezone('UTC');
        $until = Carbon::parse('2024-12-12T10:00:00Z')->setTimezone('UTC');
        $this->assertTrue($svc->withinWindow($norm['local'], $since, $until));
    }

    public function test_dst_spring_forward_in_new_york(): void
    {
        $svc = new TimeNormalizer();
        $iso = '2024-03-10T06:30:00.000Z'; // 1:30 AM EST becomes 3:30 AM EDT at 2 AM jump
        $norm = $svc->normalize($iso, 'America/New_York', dayBoundary: '04:00');
        $this->assertSame('01:30', Carbon::parse($iso)->setTimezone('America/New_York')->format('H:i'));
        $this->assertSame($norm['time'], Carbon::parse($iso)->setTimezone('America/New_York')->format('H:i'));
    }

    public function test_dst_fall_back_in_new_york(): void
    {
        $svc = new TimeNormalizer();
        $iso = '2024-11-03T06:30:00.000Z'; // 2:30 AM EDT -> 1:30 AM EST after rollback
        $localTime = Carbon::parse($iso)->setTimezone('America/New_York')->format('H:i');
        $norm = $svc->normalize($iso, 'America/New_York', dayBoundary: '04:00');
        $this->assertSame($localTime, $norm['time']);
    }

    public function test_invalid_iso_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $svc = new TimeNormalizer();
        $svc->normalize('2024-13-99T99:99:99Z', 'UTC');
    }
}
