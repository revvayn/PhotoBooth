<?php

namespace Tests\Feature;

use App\Support\QueueNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class QueueNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_number_uses_date_and_daily_sequence(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-10 09:00:00'));

        try {
            $this->assertSame('#101126-001', QueueNumber::next());
            $this->assertSame('#101126-002', QueueNumber::next());

            Carbon::setTestNow(Carbon::parse('2026-11-11 09:00:00'));

            $this->assertSame('#111126-001', QueueNumber::next());
        } finally {
            Carbon::setTestNow();
        }
    }
}
