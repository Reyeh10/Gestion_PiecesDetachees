<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {

            if (!Schema::hasColumn('products', 'initial_quantity')) {
                $table->decimal('initial_quantity', 15, 2)
                    ->default(0)
                    ->after('unit_label');
            }

            if (!Schema::hasColumn('products', 'received_quantity')) {
                $table->decimal('received_quantity', 15, 2)
                    ->default(0)
                    ->after('initial_quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            if (Schema::hasColumn('products', 'received_quantity')) {
                $table->dropColumn('received_quantity');
            }

            if (Schema::hasColumn('products', 'initial_quantity')) {
                $table->dropColumn('initial_quantity');
            }
        });
    }
};
