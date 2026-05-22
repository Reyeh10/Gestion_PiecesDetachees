<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {

            $table->decimal('quantity', 10, 2)
                ->change();

            $table->decimal('min_stock', 10, 2)
                ->change();

            $table->decimal('max_stock', 10, 2)
                ->change();
        });

        Schema::table('sale_items', function (Blueprint $table) {

            $table->decimal('quantity', 10, 2)
                ->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {

            $table->decimal('quantity', 10, 2)
                ->change();
        });
    }

    public function down(): void
    {
        //
    }
};
