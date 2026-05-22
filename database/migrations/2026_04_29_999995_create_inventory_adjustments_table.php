<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_adjustments', function (Blueprint $table) {
        $table->id();

        $table->foreignId('product_id')->constrained()->cascadeOnDelete();

        $table->integer('old_qty');
        $table->integer('new_qty');

        $table->text('reason');

        $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
