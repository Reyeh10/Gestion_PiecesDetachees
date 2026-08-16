<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'vehicle_part_request_histories',
            function (Blueprint $table) {

                $table->decimal(
                    'old_received_quantity',
                    15,
                    2
                )
                ->nullable()
                ->after('new_status');

                $table->decimal(
                    'new_received_quantity',
                    15,
                    2
                )
                ->nullable()
                ->after('old_received_quantity');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'vehicle_part_request_histories',
            function (Blueprint $table) {

                $table->dropColumn([
                    'old_received_quantity',
                    'new_received_quantity',
                ]);
            }
        );
    }
};