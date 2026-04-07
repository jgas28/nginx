<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'monthly_salary')) {
                $table->decimal('monthly_salary', 10, 2)->default(0)->after('daily_rate');
            }
            if (!Schema::hasColumn('users', 'sss_no')) {
                $table->string('sss_no')->nullable()->after('monthly_salary');
            }
            if (!Schema::hasColumn('users', 'philhealth_no')) {
                $table->string('philhealth_no')->nullable()->after('sss_no');
            }
            if (!Schema::hasColumn('users', 'tin_no')) {
                $table->string('tin_no')->nullable()->after('philhealth_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['monthly_salary', 'sss_no', 'philhealth_no', 'tin_no'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
