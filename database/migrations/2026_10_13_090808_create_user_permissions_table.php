<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permissions personnalisées par utilisateur (comme l'app Atelier) : on ne
 * stocke que les écarts par rapport aux permissions par défaut du rôle.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('permission_key');
            $table->boolean('granted');
            $table->unique(['user_id', 'permission_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
