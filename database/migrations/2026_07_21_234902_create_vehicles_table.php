<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Création de la table des véhicules.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | CLIENT PROPRIÉTAIRE
            |--------------------------------------------------------------------------
            |
            | Le véhicule peut appartenir à un client existant.
            | Le champ reste nullable pour permettre les ventes comptoir.
            |
            */

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | INFORMATIONS DU VÉHICULE
            |--------------------------------------------------------------------------
            */

            $table->string('plate_number', 50)->unique();

            $table->string('vin', 100)
                ->nullable()
                ->unique();

            $table->string('brand', 100)
                ->nullable();

            $table->string('model', 100)
                ->nullable();

            $table->unsignedSmallInteger('year')
                ->nullable();

            $table->string('color', 100)
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEX DE RECHERCHE
            |--------------------------------------------------------------------------
            */

            $table->index('plate_number');
            $table->index('customer_id');
        });
    }

    /**
     * Suppression de la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
