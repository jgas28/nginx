<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll', 'compensation_basis')) {
                $table->string('compensation_basis')->nullable()->after('total_overtime_hours');
            }
            if (!Schema::hasColumn('payroll', 'base_rate')) {
                $table->decimal('base_rate', 10, 2)->default(0)->after('compensation_basis');
            }
            if (!Schema::hasColumn('payroll', 'sss_deduction')) {
                $table->decimal('sss_deduction', 10, 2)->default(0)->after('gross_salary');
            }
            if (!Schema::hasColumn('payroll', 'philhealth_deduction')) {
                $table->decimal('philhealth_deduction', 10, 2)->default(0)->after('sss_deduction');
            }
            if (!Schema::hasColumn('payroll', 'tax_deduction')) {
                $table->decimal('tax_deduction', 10, 2)->default(0)->after('philhealth_deduction');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payroll', function (Blueprint $table) {
            foreach (['compensation_basis', 'base_rate', 'sss_deduction', 'philhealth_deduction', 'tax_deduction'] as $column) {
                if (Schema::hasColumn('payroll', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
