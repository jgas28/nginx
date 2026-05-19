<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mark the conflicting role_user migration as done if the table already exists
        if (Schema::hasTable('role_user')) {
            $alreadyRun = DB::table('migrations')
                ->where('migration', '0_create_role_user_table')
                ->exists();

            if (!$alreadyRun) {
                DB::table('migrations')->insert([
                    'migration' => '0_create_role_user_table',
                    'batch' => DB::table('migrations')->max('batch') ?? 1,
                ]);
            }
        }

        $existing = DB::table('roles')->whereIn('id', [46, 47, 48])->pluck('id')->toArray();

        $toInsert = [];
        if (!in_array(46, $existing)) {
            $toInsert[] = ['id' => 46, 'name' => 'Billing', 'created_at' => now(), 'updated_at' => now()];
        }
        if (!in_array(47, $existing)) {
            $toInsert[] = ['id' => 47, 'name' => 'Human Resource', 'created_at' => now(), 'updated_at' => now()];
        }
        if (!in_array(48, $existing)) {
            $toInsert[] = ['id' => 48, 'name' => 'Attendance', 'created_at' => now(), 'updated_at' => now()];
        }

        if (!empty($toInsert)) {
            DB::table('roles')->insert($toInsert);
        }
    }

    public function down(): void
    {
        DB::table('roles')->whereIn('id', [46, 47, 48])->delete();
    }
};
