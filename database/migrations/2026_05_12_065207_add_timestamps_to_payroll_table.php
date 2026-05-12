<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('payroll', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payroll', function (Blueprint $table) {
            foreach (['created_at', 'updated_at'] as $col) {
                if (Schema::hasColumn('payroll', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
