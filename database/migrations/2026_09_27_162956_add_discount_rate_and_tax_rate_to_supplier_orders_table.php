<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_orders', function (Blueprint $table) {
            $table->decimal('discount_rate', 5, 2)
                ->default(0)
                ->after('subtotal');

            $table->decimal('tax_rate', 5, 2)
                ->default(0)
                ->after('shipping_cost');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_orders', function (Blueprint $table) {
            $table->dropColumn([
                'discount_rate',
                'tax_rate',
            ]);
        });
    }
};
