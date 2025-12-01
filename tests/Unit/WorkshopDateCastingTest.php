<?php

namespace Tests\Unit;

use App\Models\Workshop;
use App\Support\Concerns\ResolvesWorkshopRecordings;
use Tests\TestCase;

class WorkshopDateCastingTest extends TestCase
{
    /** @test */
    public function it_returns_null_when_start_date_is_invalid(): void
    {
        $workshop = new Workshop();
        $workshop->setRawAttributes([
            'start_date' => 'not-a-date',
        ]);

        $this->assertNull($workshop->start_date);
    }

    /** @test */
    public function it_formats_start_date_only_when_valid(): void
    {
        $helper = new class {
            use ResolvesWorkshopRecordings;

            public function format(Workshop $workshop): ?string
            {
                return $this->formatWorkshopDate($workshop);
            }
        };

        $workshop = new Workshop();
        $workshop->setRawAttributes([
            'start_date' => 'not-a-date',
        ]);

        $this->assertNull($helper->format($workshop));
    }
}
