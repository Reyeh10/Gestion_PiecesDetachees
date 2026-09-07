<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('quantity', 15, 2)
                ->default(0)
                ->change();

            $table->decimal('min_stock', 15, 2)
                ->default(0)
                ->change();

            $table->decimal('max_stock', 15, 2)
                ->default(0)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->double('quantity', 15, 2)
                ->default(0)
                ->change();

            $table->double('min_stock', 15, 2)
                ->default(0)
                ->change();

            $table->double('max_stock', 15, 2)
                ->default(0)
                ->change();
        });
    }
};
