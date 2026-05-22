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
        Schema::create('products', function (Blueprint $table) {

            $table->id();
            $table->string('reference')->unique();
            $table->string('designation');


            // RELATIONS

            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('model_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('family_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subfamily_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('rayon_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();


            // STOCK

            $table->integer('quantity')->default(0);
            $table->integer('min_stock')->default(0);
            $table->integer('max_stock')->default(0);

            // PRIX
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('coef_purchase', 8, 2)->default(1);
            
            $table->decimal('cost_price', 15, 2)->default(0);

            $table->decimal('coef_sale', 8, 2)->default(1);
            $table->decimal('sale_price', 15, 2)->default(0);

            $table->timestamps();

            });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void

    {
        Schema::dropIfExists('products');
    }

};


