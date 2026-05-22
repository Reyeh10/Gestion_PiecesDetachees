<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | TYPE PRODUIT
            |--------------------------------------------------------------------------
            */

            $table->string('unit_type')
                ->default('piece')
                ->after('designation');

            /*
            |--------------------------------------------------------------------------
            | UNITE
            |--------------------------------------------------------------------------
            */

            $table->string('unit_label')
                ->default('Pièce')
                ->after('unit_type');

        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            $table->dropColumn([
                'unit_type',
                'unit_label'
            ]);
        });
    }
};
