<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {

            $table->decimal('subtotal', 15, 2)
                ->default(0)
                ->after('payment_type');

            $table->decimal('discount', 15, 2)
                ->default(0)
                ->after('subtotal');

            $table->decimal('tva', 15, 2)
                ->default(0)
                ->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {

            $table->dropColumn([
                'subtotal',
                'discount',
                'tva'
            ]);
        });
    }
};
