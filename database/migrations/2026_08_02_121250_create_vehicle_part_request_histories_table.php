<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Création de l'historique.
     */
    public function up(): void
    {
        Schema::create('vehicle_part_request_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_part_request_id')
                ->constrained('vehicle_part_requests')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            /*
             * Ancien statut.
             * Peut être vide lors de la création initiale.
             */
            $table->string('old_status')->nullable();

            /*
             * Nouveau statut.
             */
            $table->string('new_status');

            /*
             * Commentaire ajouté lors du changement.
             */
            $table->text('comment')->nullable();

            /*
             * Utilisateur ayant effectué le changement.
             */
            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             * Date exacte du changement.
             */
            $table->timestamp('changed_at');

            $table->timestamps();

            $table->index('new_status');
            $table->index('changed_at');
        });
    }

    /**
     * Suppression de l'historique.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_part_request_histories');
    }
};
