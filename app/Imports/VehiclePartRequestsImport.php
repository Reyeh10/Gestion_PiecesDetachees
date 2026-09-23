<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\VehiclePartRequest;
use App\Models\VehiclePartRequestHistory;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

use RuntimeException;

class VehiclePartRequestsImport implements
    ToCollection,
    WithHeadingRow,
    SkipsEmptyRows
{
    /*
    |--------------------------------------------------------------------------
    | VÉHICULE
    |--------------------------------------------------------------------------
    */

    protected int $vehicleId;


    /*
    |--------------------------------------------------------------------------
    | COMPTEURS
    |--------------------------------------------------------------------------
    */

    protected int $importedCount = 0;

    protected int $matchedProductsCount = 0;

    protected int $unmatchedProductsCount = 0;

    protected int $skippedCount = 0;


    /*
    |--------------------------------------------------------------------------
    | ERREURS
    |--------------------------------------------------------------------------
    */

    protected array $errors = [];


    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTEUR
    |--------------------------------------------------------------------------
    */

    public function __construct(int $vehicleId)
    {
        $this->vehicleId = $vehicleId;
    }


    /*
    |--------------------------------------------------------------------------
    | IMPORT
    |--------------------------------------------------------------------------
    |
    | IMPORTANT :
    |
    | L'import fonctionne maintenant en mode TOUT OU RIEN.
    |
    | 1. Toutes les lignes sont d'abord analysées.
    | 2. Aucune donnée n'est créée pendant la validation.
    | 3. Si une seule ligne contient une erreur :
    |       -> aucune pièce n'est importée.
    | 4. Si toutes les lignes sont valides :
    |       -> toutes les pièces sont créées dans une transaction.
    |
    */

    public function collection(Collection $rows): void
    {
        /*
        |--------------------------------------------------------------------------
        | DONNÉES VALIDÉES
        |--------------------------------------------------------------------------
        */

        $validatedRows = [];


        /*
        |--------------------------------------------------------------------------
        | RÉINITIALISER LES COMPTEURS
        |--------------------------------------------------------------------------
        */

        $this->importedCount = 0;
        $this->matchedProductsCount = 0;
        $this->unmatchedProductsCount = 0;
        $this->skippedCount = 0;
        $this->errors = [];


        /*
        |--------------------------------------------------------------------------
        | PREMIÈRE PASSE : VALIDATION COMPLÈTE
        |--------------------------------------------------------------------------
        */

        foreach ($rows as $index => $row) {

            /*
            |--------------------------------------------------------------------------
            | NUMÉRO DE LIGNE EXCEL
            |--------------------------------------------------------------------------
            |
            | Ligne 1 = en-têtes
            | Première ligne de données = ligne 2
            |
            */

            $excelRowNumber = $index + 2;


            /*
            |--------------------------------------------------------------------------
            | NETTOYAGE DES DONNÉES
            |--------------------------------------------------------------------------
            */

            $reference = $this->cleanString(
                $row['reference'] ?? null
            );


            $designation = $this->cleanString(
                $row['designation']
                    ?? $row['part_name']
                    ?? $row['piece']
                    ?? null
            );


            $quantityValue =
                $row['quantite']
                ?? $row['quantity']
                ?? null;


            $unit = $this->cleanString(
                $row['unite']
                    ?? $row['unit']
                    ?? null
            );


            $supplierName = $this->cleanString(
                $row['fournisseur']
                    ?? $row['supplier']
                    ?? null
            );


            $supplierReference = $this->cleanString(
                $row['reference_fournisseur']
                    ?? $row['supplier_reference']
                    ?? null
            );


            $estimatedPriceValue =
                $row['prix_estime']
                ?? $row['estimated_price']
                ?? null;


            $description = $this->cleanString(
                $row['description'] ?? null
            );


            $notes = $this->cleanString(
                $row['notes']
                    ?? $row['note']
                    ?? null
            );


            /*
            |--------------------------------------------------------------------------
            | IGNORER LES LIGNES COMPLÈTEMENT VIDES
            |--------------------------------------------------------------------------
            |
            | Une ligne réellement vide en fin de fichier Excel n'est pas une erreur.
            |
            */

            if (
                $reference === null
                && $designation === null
                && $this->isEmptyValue($quantityValue)
                && $unit === null
                && $supplierName === null
                && $supplierReference === null
                && $this->isEmptyValue($estimatedPriceValue)
                && $description === null
                && $notes === null
            ) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | ERREURS DE LA LIGNE
            |--------------------------------------------------------------------------
            */

            $rowErrors = [];


            /*
            |--------------------------------------------------------------------------
            | DESIGNATION OBLIGATOIRE
            |--------------------------------------------------------------------------
            */

            if ($designation === null) {

                $rowErrors[] =
                    "Ligne {$excelRowNumber} : "
                    . "le champ DESIGNATION est obligatoire.";

            } elseif (mb_strlen($designation) > 255) {

                $rowErrors[] =
                    "Ligne {$excelRowNumber} : "
                    . "le champ DESIGNATION ne peut pas dépasser "
                    . "255 caractères.";
            }


            /*
            |--------------------------------------------------------------------------
            | RÉFÉRENCE FACULTATIVE
            |--------------------------------------------------------------------------
            */

            if (
                $reference !== null
                && mb_strlen($reference) > 255
            ) {

                $rowErrors[] =
                    "Ligne {$excelRowNumber} : "
                    . "le champ REFERENCE ne peut pas dépasser "
                    . "255 caractères.";
            }


            /*
            |--------------------------------------------------------------------------
            | QUANTITÉ OBLIGATOIRE
            |--------------------------------------------------------------------------
            */

            $quantity = null;

            if ($this->isEmptyValue($quantityValue)) {

                $rowErrors[] =
                    "Ligne {$excelRowNumber} : "
                    . "le champ QUANTITE est obligatoire.";

            } else {

                $quantity = $this->normalizeNumber(
                    $quantityValue
                );

                if (
                    $quantity === null
                    || $quantity <= 0
                ) {

                    $rowErrors[] =
                        "Ligne {$excelRowNumber} : "
                        . "le champ QUANTITE doit être un nombre "
                        . "supérieur à 0.";
                }
            }


            /*
            |--------------------------------------------------------------------------
            | UNITÉ OBLIGATOIRE
            |--------------------------------------------------------------------------
            */

            if ($unit === null) {

                $rowErrors[] =
                    "Ligne {$excelRowNumber} : "
                    . "le champ UNITE est obligatoire.";

            } elseif (mb_strlen($unit) > 255) {

                $rowErrors[] =
                    "Ligne {$excelRowNumber} : "
                    . "le champ UNITE ne peut pas dépasser "
                    . "255 caractères.";
            }


            /*
            |--------------------------------------------------------------------------
            | FOURNISSEUR FACULTATIF
            |--------------------------------------------------------------------------
            */

            if (
                $supplierName !== null
                && mb_strlen($supplierName) > 255
            ) {

                $rowErrors[] =
                    "Ligne {$excelRowNumber} : "
                    . "le champ FOURNISSEUR ne peut pas dépasser "
                    . "255 caractères.";
            }


            /*
            |--------------------------------------------------------------------------
            | RÉFÉRENCE FOURNISSEUR FACULTATIVE
            |--------------------------------------------------------------------------
            */

            if (
                $supplierReference !== null
                && mb_strlen($supplierReference) > 255
            ) {

                $rowErrors[] =
                    "Ligne {$excelRowNumber} : "
                    . "le champ REFERENCE_FOURNISSEUR ne peut pas "
                    . "dépasser 255 caractères.";
            }


            /*
            |--------------------------------------------------------------------------
            | PRIX ESTIMÉ FACULTATIF
            |--------------------------------------------------------------------------
            */

            $estimatedPrice = null;

            if (!$this->isEmptyValue($estimatedPriceValue)) {

                $estimatedPrice =
                    $this->normalizeNumber(
                        $estimatedPriceValue
                    );

                if ($estimatedPrice === null) {

                    $rowErrors[] =
                        "Ligne {$excelRowNumber} : "
                        . "le champ PRIX_ESTIME doit être un nombre.";

                } elseif ($estimatedPrice < 0) {

                    $rowErrors[] =
                        "Ligne {$excelRowNumber} : "
                        . "le champ PRIX_ESTIME ne peut pas être négatif.";
                }
            }


            /*
            |--------------------------------------------------------------------------
            | DESCRIPTION
            |--------------------------------------------------------------------------
            */

            if (
                $description !== null
                && mb_strlen($description) > 65535
            ) {

                $rowErrors[] =
                    "Ligne {$excelRowNumber} : "
                    . "la DESCRIPTION est trop longue.";
            }


            /*
            |--------------------------------------------------------------------------
            | NOTES
            |--------------------------------------------------------------------------
            */

            if (
                $notes !== null
                && mb_strlen($notes) > 65535
            ) {

                $rowErrors[] =
                    "Ligne {$excelRowNumber} : "
                    . "le champ NOTES est trop long.";
            }


            /*
            |--------------------------------------------------------------------------
            | SI LA LIGNE CONTIENT DES ERREURS
            |--------------------------------------------------------------------------
            */

            if (!empty($rowErrors)) {

                foreach ($rowErrors as $error) {
                    $this->errors[] = $error;
                }

                $this->skippedCount++;

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | CONSERVER LA LIGNE VALIDÉE
            |--------------------------------------------------------------------------
            |
            | Toujours aucune écriture en base de données à ce stade.
            |
            */

            $validatedRows[] = [
                'excel_row_number' =>
                    $excelRowNumber,

                'reference' =>
                    $reference,

                'designation' =>
                    $designation,

                'quantity' =>
                    $quantity,

                'unit' =>
                    $unit,

                'supplier_name' =>
                    $supplierName,

                'supplier_reference' =>
                    $supplierReference,

                'estimated_price' =>
                    $estimatedPrice,

                'description' =>
                    $description,

                'notes' =>
                    $notes,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | FICHIER SANS AUCUNE DONNÉE
        |--------------------------------------------------------------------------
        */

        if (empty($validatedRows) && empty($this->errors)) {

            $this->errors[] =
                'Le fichier Excel ne contient aucune ligne de pièce à importer.';

            throw new RuntimeException(
                'Le fichier Excel ne contient aucune ligne de pièce à importer.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ANNULER TOUT L'IMPORT SI UNE ERREUR EXISTE
        |--------------------------------------------------------------------------
        */

        if (!empty($this->errors)) {

            $errorCount = count($this->errors);

            throw new RuntimeException(
                'Import annulé : '
                . $errorCount
                . ' erreur(s) détectée(s) dans le fichier Excel. '
                . 'Aucune pièce n’a été importée.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | DEUXIÈME PASSE : IMPORT
        |--------------------------------------------------------------------------
        |
        | À partir d'ici seulement, nous écrivons en base de données.
        |
        */

        DB::transaction(function () use ($validatedRows) {

            foreach ($validatedRows as $data) {

                /*
                |--------------------------------------------------------------------------
                | RECHERCHE DU PRODUIT
                |--------------------------------------------------------------------------
                */

                $product = null;

                if ($data['reference'] !== null) {

                    $product =
                        Product::query()
                            ->where(
                                'reference',
                                $data['reference']
                            )
                            ->first();
                }


                /*
                |--------------------------------------------------------------------------
                | PRODUIT TROUVÉ / NON TROUVÉ
                |--------------------------------------------------------------------------
                */

                if ($product) {

                    $productId =
                        $product->id;

                    /*
                    |--------------------------------------------------------------------------
                    | CONSERVER LE COMPORTEMENT ACTUEL
                    |--------------------------------------------------------------------------
                    |
                    | Si la référence existe dans le catalogue :
                    | priorité à la désignation du catalogue.
                    |
                    */

                    $partName =
                        $product->designation
                        ?: $data['designation'];

                    $this->matchedProductsCount++;

                } else {

                    $productId = null;

                    $partName =
                        $data['designation'];

                    $this->unmatchedProductsCount++;
                }


                /*
                |--------------------------------------------------------------------------
                | FOURNISSEUR
                |--------------------------------------------------------------------------
                |
                | On conserve votre fonctionnement actuel :
                | le fournisseur doit déjà exister.
                | L'import ne crée pas automatiquement un fournisseur.
                |
                */

                $supplierId = null;

                if ($data['supplier_name'] !== null) {

                    $supplier =
                        Supplier::query()
                            ->where(
                                'name',
                                $data['supplier_name']
                            )
                            ->first();

                    if ($supplier) {

                        $supplierId =
                            $supplier->id;
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | CRÉATION DE LA DEMANDE
                |--------------------------------------------------------------------------
                */

                $partRequest =
                    VehiclePartRequest::create([

                        'vehicle_id' =>
                            $this->vehicleId,

                        'product_id' =>
                            $productId,

                        'supplier_id' =>
                            $supplierId,

                        'created_by' =>
                            Auth::id(),

                        'reference' =>
                            $data['reference'],

                        'part_name' =>
                            $partName,

                        'description' =>
                            $data['description'],

                        'quantity' =>
                            $data['quantity'],

                        'received_quantity' =>
                            0,

                        'unit' =>
                            $data['unit'],

                        'status' =>
                            VehiclePartRequest::STATUS_SEARCHING,

                        'supplier_reference' =>
                            $data['supplier_reference'],

                        'order_reference' =>
                            null,

                        'estimated_price' =>
                            $data['estimated_price'],

                        'purchase_price' =>
                            null,

                        'requested_at' =>
                            now(),

                        'search_started_at' =>
                            now(),

                        'found_at' =>
                            null,

                        'ordered_at' =>
                            null,

                        'received_at' =>
                            null,

                        'not_found_at' =>
                            null,

                        'cancelled_at' =>
                            null,

                        'notes' =>
                            $data['notes'],
                    ]);


                /*
                |--------------------------------------------------------------------------
                | HISTORIQUE
                |--------------------------------------------------------------------------
                */

                VehiclePartRequestHistory::create([

                    'vehicle_part_request_id' =>
                        $partRequest->id,

                    'old_status' =>
                        null,

                    'new_status' =>
                        VehiclePartRequest::STATUS_SEARCHING,

                    'old_received_quantity' =>
                        null,

                    'new_received_quantity' =>
                        0,

                    'comment' =>
                        'Création de la demande par import Excel.',

                    'changed_by' =>
                        Auth::id(),

                    'changed_at' =>
                        now(),
                ]);


                /*
                |--------------------------------------------------------------------------
                | COMPTEUR
                |--------------------------------------------------------------------------
                */

                $this->importedCount++;
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | VALEUR VIDE
    |--------------------------------------------------------------------------
    |
    | Attention : 0 n'est PAS considéré comme vide ici.
    | Cela permet ensuite d'afficher correctement l'erreur "supérieur à 0".
    |
    */

    protected function isEmptyValue(
        mixed $value
    ): bool {

        if ($value === null) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | NETTOYAGE D'UNE CHAÎNE
    |--------------------------------------------------------------------------
    */

    protected function cleanString(
        mixed $value
    ): ?string {

        if ($value === null) {

            return null;
        }


        $value =
            trim(
                (string) $value
            );


        if ($value === '') {

            return null;
        }


        return $value;
    }


    /*
    |--------------------------------------------------------------------------
    | CONVERSION DES NOMBRES EXCEL
    |--------------------------------------------------------------------------
    |
    | Exemples acceptés :
    |
    | 10
    | 10.5
    | 10,5
    | 1 250,50
    | 1.250,50
    | 1,250.50
    |
    */

    protected function normalizeNumber(
        mixed $value
    ): ?float {

        if ($this->isEmptyValue($value)) {

            return null;
        }


        if (
            is_int($value)
            || is_float($value)
        ) {

            return (float) $value;
        }


        $value =
            trim(
                (string) $value
            );


        /*
        |--------------------------------------------------------------------------
        | SUPPRIMER LES ESPACES
        |--------------------------------------------------------------------------
        */

        $value =
            str_replace(
                [
                    "\xc2\xa0",
                    ' ',
                ],
                '',
                $value
            );


        /*
        |--------------------------------------------------------------------------
        | VIRGULE DÉCIMALE
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($value, ',')
            && !str_contains($value, '.')
        ) {

            /*
            | Exemple :
            | 10,50
            */

            $value =
                str_replace(
                    ',',
                    '.',
                    $value
                );

        } elseif (
            str_contains($value, ',')
            && str_contains($value, '.')
        ) {

            /*
            |--------------------------------------------------------------------------
            | FORMAT EUROPÉEN
            |--------------------------------------------------------------------------
            |
            | Exemple :
            | 1.250,50
            |
            */

            if (
                strrpos($value, ',')
                >
                strrpos($value, '.')
            ) {

                $value =
                    str_replace(
                        '.',
                        '',
                        $value
                    );

                $value =
                    str_replace(
                        ',',
                        '.',
                        $value
                    );

            } else {

                /*
                |--------------------------------------------------------------------------
                | FORMAT ANGLAIS
                |--------------------------------------------------------------------------
                |
                | Exemple :
                | 1,250.50
                |
                */

                $value =
                    str_replace(
                        ',',
                        '',
                        $value
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | VÉRIFICATION
        |--------------------------------------------------------------------------
        */

        if (!is_numeric($value)) {

            return null;
        }


        return (float) $value;
    }


    /*
    |--------------------------------------------------------------------------
    | NOMBRE DE LIGNES IMPORTÉES
    |--------------------------------------------------------------------------
    */

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }


    /*
    |--------------------------------------------------------------------------
    | PRODUITS TROUVÉS
    |--------------------------------------------------------------------------
    */

    public function getMatchedProductsCount(): int
    {
        return $this->matchedProductsCount;
    }


    /*
    |--------------------------------------------------------------------------
    | PRODUITS NON TROUVÉS
    |--------------------------------------------------------------------------
    */

    public function getUnmatchedProductsCount(): int
    {
        return $this->unmatchedProductsCount;
    }


    /*
    |--------------------------------------------------------------------------
    | LIGNES INVALIDES
    |--------------------------------------------------------------------------
    */

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }


    /*
    |--------------------------------------------------------------------------
    | ERREURS
    |--------------------------------------------------------------------------
    */

    public function getErrors(): array
    {
        return $this->errors;
    }


    /*
    |--------------------------------------------------------------------------
    | IMPORT VALIDE ?
    |--------------------------------------------------------------------------
    */

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}
