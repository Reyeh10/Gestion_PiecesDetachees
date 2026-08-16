<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Transformer les anciennes pièces "À rechercher"
         * en pièces "Commandées".
         */
        DB::table('vehicle_part_requests')
            ->where('status', 'pending')
            ->update([
                'status' => 'ordered',
                'ordered_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('vehicle_part_requests')
            ->where('status', 'ordered')
            ->update([
                'status' => 'pending',
                'ordered_at' => null,
            ]);
    }
};
