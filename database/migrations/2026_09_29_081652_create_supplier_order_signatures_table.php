<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     */
    public function up(): void
    {
        Schema::create('supplier_order_signatures', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | BON DE COMMANDE
            |--------------------------------------------------------------------------
            |
            | Chaque signature appartient à un bon de commande.
            |
            | Si le BC est supprimé, ses signatures sont également supprimées.
            |
            */

            $table->foreignId('supplier_order_id')
                ->constrained('supplier_orders')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | UTILISATEUR SIGNATAIRE
            |--------------------------------------------------------------------------
            |
            | L'utilisateur ayant réellement effectué la signature.
            |
            | Nous utilisons restrictOnDelete() afin de préserver la traçabilité :
            | un utilisateur ayant signé un document ne doit pas pouvoir être
            | supprimé sans traiter préalablement ses signatures.
            |
            */

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | TYPE DE SIGNATURE
            |--------------------------------------------------------------------------
            |
            | Valeurs utilisées par l'application :
            |
            | prepared = signature du préparateur
            | approved = signature de l'approbateur
            |
            */

            $table->string('type', 30);


            /*
            |--------------------------------------------------------------------------
            | FICHIER DE SIGNATURE
            |--------------------------------------------------------------------------
            |
            | Nous n'enregistrons pas directement l'image Base64 dans la base.
            |
            | Le contrôleur transformera la signature dessinée dans le navigateur
            | en fichier PNG privé et enregistrera ici uniquement son chemin.
            |
            */

            $table->string('signature_path');


            /*
            |--------------------------------------------------------------------------
            | DATE RÉELLE DE SIGNATURE
            |--------------------------------------------------------------------------
            */

            $table->timestamp('signed_at');


            /*
            |--------------------------------------------------------------------------
            | INFORMATIONS DE TRAÇABILITÉ
            |--------------------------------------------------------------------------
            |
            | Ces informations permettent de conserver un journal technique
            | associé à l'acte de signature.
            |
            */

            $table->string('ip_address', 45)
                ->nullable();

            $table->text('user_agent')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | EMPREINTE DU BON DE COMMANDE
            |--------------------------------------------------------------------------
            |
            | SHA-256 du contenu important du BC au moment de la signature.
            |
            | Cette empreinte permettra ultérieurement de vérifier si les données
            | importantes du document ont changé après la signature.
            |
            */

            $table->string('document_hash', 64);


            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | UNE SEULE SIGNATURE DE CHAQUE TYPE PAR BC
            |--------------------------------------------------------------------------
            |
            | Un BC ne peut avoir qu'une seule signature "prepared" et une seule
            | signature "approved".
            |
            | Cette contrainte protège également contre les doubles soumissions.
            |
            */

            $table->unique(
                [
                    'supplier_order_id',
                    'type',
                ],
                'supplier_order_signatures_order_type_unique'
            );


            /*
            |--------------------------------------------------------------------------
            | INDEX
            |--------------------------------------------------------------------------
            */

            $table->index(
                [
                    'user_id',
                    'signed_at',
                ],
                'supplier_order_signatures_user_date_index'
            );
        });
    }


    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'supplier_order_signatures'
        );
    }
};
