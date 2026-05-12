<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll', function (Blueprint $table) {
            $cols = [
                'incentive_amount'   => ['after' => 'total_allowance'],
                'pagibig_deduction'  => ['after' => 'sss_deduction'],
                'cellphone_loan'     => ['after' => 'tax_deduction'],
                'gasul_fund'         => ['after' => 'cellphone_loan'],
                'unreturn_budget'    => ['after' => 'gasul_fund'],
                'cash_bond'          => ['after' => 'unreturn_budget'],
                'other_deduction'    => ['after' => 'cash_bond'],
            ];

            foreach ($cols as $col => $opts) {
                if (!Schema::hasColumn('payroll', $col)) {
                    $table->decimal($col, 10, 2)->default(0)->after($opts['after']);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('payroll', function (Blueprint $table) {
            $cols = ['incentive_amount','pagibig_deduction','cellphone_loan','gasul_fund','unreturn_budget','cash_bond','other_deduction'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('payroll', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
