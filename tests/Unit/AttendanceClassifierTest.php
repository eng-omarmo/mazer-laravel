<?php

namespace Tests\Unit;

use App\Services\AttendanceClassifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClassifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_classification_and_aggregation(): void
    {
        $svc = new AttendanceClassifier();
        $records = [
            ['userId' => 1, 'time' => '2026-02-08T09:00:00+00:00'],
            ['userId' => 1, 'time' => '2026-02-08T18:00:00+00:00'],
        ];
        $typed = $svc->classify($records, 'UTC');
        $this->assertCount(2, $typed);
        $this->assertSame('check-in', $typed[0]['type']);
        $this->assertSame('check-out', $typed[1]['type']);
        $daily = $svc->aggregateDaily($typed);
        $this->assertCount(1, $daily);
        $this->assertSame('09:00', $daily[0]['check_in']);
        $this->assertSame('18:00', $daily[0]['check_out']);
    }

    public function test_midnight_crossing_treated_as_previous_day_checkout(): void
    {
        $svc = new AttendanceClassifier();
        $records = [
            ['userId' => 2, 'time' => '2026-02-09T03:30:00+00:00'],
            ['userId' => 2, 'time' => '2026-02-09T09:15:00+00:00'],
        ];
        $typed = $svc->classify($records, 'UTC', dayBoundary: '04:00');
        $this->assertSame('check-out', $typed[0]['type']);
        $this->assertSame('2026-02-08', $typed[0]['date']);
        $this->assertSame('2026-02-09', $typed[1]['date']);
    }

    public function test_weekend_entries_are_classified_normally(): void
    {
        $svc = new AttendanceClassifier();
        $records = [
            ['userId' => 3, 'time' => '2026-02-07T10:00:00+00:00'], // Saturday
            ['userId' => 3, 'time' => '2026-02-07T16:30:00+00:00'],
        ];
        $typed = $svc->classify($records, 'UTC');
        $daily = $svc->aggregateDaily($typed);
        $this->assertSame('10:00', $daily[0]['check_in']);
        $this->assertSame('16:30', $daily[0]['check_out']);
    }

    public function test_consecutive_same_type_entries_are_deduplicated_in_aggregation(): void
    {
        $svc = new AttendanceClassifier();
        $records = [
            ['userId' => 4, 'time' => '2026-02-08T08:45:00+00:00'],
            ['userId' => 4, 'time' => '2026-02-08T09:05:00+00:00'], // another check-in
            ['userId' => 4, 'time' => '2026-02-08T17:00:00+00:00'],
            ['userId' => 4, 'time' => '2026-02-08T17:10:00+00:00'], // another check-out
        ];
        $typed = $svc->classify($records, 'UTC');
        $daily = $svc->aggregateDaily($typed);
        $this->assertSame('08:45', $daily[0]['check_in']);
        $this->assertSame('17:10', $daily[0]['check_out']);
        $this->assertNotEmpty($daily[0]['duplicates']);
    }

    public function test_timezone_handling_shifts_classification(): void
    {
        $svc = new AttendanceClassifier();
        $records = [
            ['userId' => 5, 'time' => '2026-02-08T11:30:00+00:00'],
        ];
        $typedUtc = $svc->classify($records, 'UTC');
        $typedEAT = $svc->classify($records, 'Africa/Nairobi'); // UTC+3
        $this->assertSame('check-in', $typedUtc[0]['type']);
        $this->assertSame('check-out', $typedEAT[0]['type']);
    }

    public function test_invalid_timestamp_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $svc = new AttendanceClassifier();
        $svc->classify([['userId' => 6, 'time' => 'not-a-time']], 'UTC');
    }
}
