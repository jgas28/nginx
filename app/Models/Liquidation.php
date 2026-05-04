<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Liquidation extends Model
{
    use HasFactory;

    public const MONEY_FIELDS = [
        'allowance',
        'manpower',
        'hauling',
        'right_of_way',
        'roro_expense',
        'cash_charge',
    ];

    protected $fillable = [
        // Expense Fields
        'allowance',
        'manpower',
        'hauling',
        'right_of_way',
        'roro_expense',
        'cash_charge',

        // JSON Fields
        'gasoline', // Replaces gasoline_cash/card/type/etc
        'rfid',     // Replaces rfid_autosweep_* and rfid_easytrip_*

        // Approval Workflow
        'prepared_by',
        'noted_by',
        'validated_by',
        'collected_by',
        'approved_by',
        'collector_id',

        // Metadata
        'others',
        'status',
        'cvr_approval_id',
        'cvr_number',
        'cvr_id',
        'mtm',
        'remarks',
    ];

    protected $casts = [
        'others'   => 'array', // dynamic others
        'gasoline' => 'array', // JSON structure for multiple entries
        'rfid'     => 'array', // JSON structure for multiple entries
    ];

    protected static function booted(): void
    {
        static::saving(function (self $liquidation) {
            foreach (self::MONEY_FIELDS as $field) {
                $liquidation->{$field} = self::normalizeCurrencyValue($liquidation->{$field});
            }

            $liquidation->gasoline = self::normalizeLineItems($liquidation->gasoline);
            $liquidation->rfid = self::normalizeLineItems($liquidation->rfid);
            $liquidation->others = self::normalizeLineItems($liquidation->others);
        });
    }

    public static function normalizeCurrencyValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $amount = round((float) $value, 2);

        // Guard against tiny negative-cent drift like -0.01 from auto-balancing math.
        if ($amount >= -0.01 && $amount < 0) {
            $amount = 0.0;
        }

        return number_format($amount, 2, '.', '');
    }

    public static function normalizeLineItems(mixed $items): array
    {
        if (is_string($items)) {
            $items = json_decode($items, true) ?? [];
        }

        if (!is_array($items)) {
            return [];
        }

        $normalized = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $amount = self::normalizeCurrencyValue($item['amount'] ?? null);
            $description = trim((string) ($item['description'] ?? ''));
            $type = trim((string) ($item['type'] ?? ''));
            $tag = trim((string) ($item['tag'] ?? ''));

            if ($amount === null && $description === '' && $type === '' && $tag === '') {
                continue;
            }

            $item['amount'] = $amount;
            $normalized[] = $item;
        }

        return array_values($normalized);
    }

    public function cvrApproval()
    {
        return $this->belongsTo(cvr_approval::class, 'cvr_approval_id');
    }

    public function cashVoucher()
    {
        return $this->belongsTo(CashVoucher::class, 'cvr_number', 'cvr_number');
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function notedBy()
    {
        return $this->belongsTo(User::class, 'noted_by');
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

     public function collector()
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function runningBalances()
    {
        return $this->hasMany(RunningBalance::class, 'cvr_number', 'cvr_number');
    }
}
