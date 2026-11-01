<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $tables = DB::select(
            "SELECT table_name FROM information_schema.columns
             WHERE table_schema = current_schema() AND column_name = 'id'"
        );

        foreach ($tables as $table) {
            $tableName = $table->table_name;
            $sequence = DB::selectOne(
                "SELECT pg_get_serial_sequence(?, 'id') AS sequence",
                [$tableName]
            )?->sequence;

            if (! $sequence) {
                continue;
            }

            $maxId = DB::table($tableName)->max('id');
            DB::selectOne(
                'SELECT setval(?::regclass, ?::bigint, ?)',
                [$sequence, $maxId ?? 1, $maxId !== null]
            );
        }
    }

    public function down(): void
    {
    }
};
