<?php

namespace Tests\Unit;

use App\Models\Allocation;
use App\Models\CashVoucher;
use App\Models\DeliveryRequest;
use PHPUnit\Framework\TestCase;

class MoneyNormalizationTest extends TestCase
{
    public function test_delivery_request_normalizes_money_values(): void
    {
        $this->assertSame('0.00', DeliveryRequest::normalizeCurrencyValue('-0.01'));
        $this->assertSame('1250.24', DeliveryRequest::normalizeCurrencyValue('1250.235'));
        $this->assertNull(DeliveryRequest::normalizeCurrencyValue(''));
    }

    public function test_cash_voucher_normalizes_money_values(): void
    {
        $this->assertSame('0.00', CashVoucher::normalizeCurrencyValue('-0.01'));
        $this->assertSame('500.10', CashVoucher::normalizeCurrencyValue('500.1'));
        $this->assertNull(CashVoucher::normalizeCurrencyValue(null));
    }

    public function test_allocation_normalizes_money_values(): void
    {
        $this->assertSame('0.00', Allocation::normalizeCurrencyValue('-0.01'));
        $this->assertSame('99.99', Allocation::normalizeCurrencyValue('99.994'));
        $this->assertNull(Allocation::normalizeCurrencyValue(''));
    }
}
