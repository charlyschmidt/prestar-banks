<?php

namespace Tests\Unit;

use App\Services\Cheques\ChequeLiquidationCalculator;
use PHPUnit\Framework\TestCase;

class ChequeLiquidationCalculatorTest extends TestCase
{
    private array $input = [
        'operation_date' => '2026-11-02',
        'due_date' => '2026-12-02',
        'nominal_amount' => '100000.00',
        'annual_rate' => '62',
        'clearing_days' => 2,
        'cheque_tax_rate' => '1.2',
        'gross_income_rate' => '0.6',
        'vat_rate' => '21',
    ];

    public function test_reproduces_prestar_liquidation(): void
    {
        $r = (new ChequeLiquidationCalculator())->calculate($this->input);
        $this->assertSame(30, $r['calendar_days']);
        $this->assertSame(32, $r['term_days']);
        $this->assertSame('5435.62', $r['interest_amount']);
        $this->assertSame('1200.00', $r['cheque_tax_amount']);
        $this->assertSame('600.00', $r['gross_income_amount']);
        $this->assertSame('1519.48', $r['vat_amount']);
        $this->assertSame('8755.10', $r['total_discount']);
        $this->assertSame('91244.90', $r['net_amount']);
    }

    public function test_rejects_cheque_due_before_operation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new ChequeLiquidationCalculator())->calculate(array_merge($this->input, [
            'due_date' => '2026-11-01',
        ]));
    }
}
