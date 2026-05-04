<?php

namespace Tests\Unit;

use App\Models\Liquidation;
use PHPUnit\Framework\TestCase;

class LiquidationNormalizationTest extends TestCase
{
    public function test_it_normalizes_negative_cent_drift_to_zero(): void
    {
        $this->assertSame('0.00', Liquidation::normalizeCurrencyValue('-0.01'));
        $this->assertSame('12.35', Liquidation::normalizeCurrencyValue('12.345'));
        $this->assertNull(Liquidation::normalizeCurrencyValue(''));
    }

    public function test_it_drops_empty_placeholder_line_items_and_normalizes_amounts(): void
    {
        $items = Liquidation::normalizeLineItems([
            ['amount' => null, 'description' => null],
            ['amount' => '-0.01', 'description' => 'Allowance adjustment'],
            ['amount' => '15.236', 'type' => 'cash'],
        ]);

        $this->assertCount(2, $items);
        $this->assertSame('0.00', $items[0]['amount']);
        $this->assertSame('Allowance adjustment', $items[0]['description']);
        $this->assertSame('15.24', $items[1]['amount']);
        $this->assertSame('cash', $items[1]['type']);
    }
}
