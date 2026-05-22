<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_depot_stocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('depot_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('quantity')->default(0);

            $table->timestamps();

            $table->unique(['product_id', 'depot_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_depot_stocks');
    }
};
