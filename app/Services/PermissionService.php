<?php

namespace App\Services;

/**
 * Permissions du Magasin, sur le modèle de l'app Atelier :
 *
 *   1. chaque rôle a des permissions PAR DÉFAUT ($roleDefaults) ;
 *   2. l'administrateur peut, pour un utilisateur précis, ajouter ou retirer
 *      une permission (table user_permissions, écran « Permissions ») ;
 *   3. l'administrateur a toujours tous les droits.
 *
 * Chaque page (route nommée) est rattachée à UNE permission ($routes).
 * Le middleware CheckRoutePermission bloque la page (403) si l'utilisateur
 * n'a pas cette permission ; le menu et les boutons se basent sur
 * auth()->user()->hasPermission('…').
 */
class PermissionService
{
    /**
     * Pages réservées à l'administrateur : cette « permission » n'est donnée
     * à aucun rôle et n'apparaît pas dans l'écran « Permissions », elle ne
     * peut donc pas être accordée à un autre utilisateur.
     */
    public const RESERVE_ADMIN = 'reserve_administrateur';

    /** Permissions affichées dans l'écran « Permissions », groupées par module. */
    public static array $groups = [
        'Produits & référentiels' => [
            'voir_produits'          => 'Voir les produits, le stock global, les catégories et familles',
            'creer_produits'         => 'Créer / importer des produits (et ajouter marques, modèles, rayons, emplacements)',
            'modifier_produits'      => 'Modifier la fiche d\'un produit',
            'gerer_categories'       => 'Créer / modifier les catégories et familles',
            'supprimer_referentiels' => 'Supprimer des produits, catégories, familles et fournisseurs',
        ],
        'Fournisseurs' => [
            'voir_fournisseurs'     => 'Voir les fournisseurs',
            'creer_fournisseurs'    => 'Créer un fournisseur',
            'modifier_fournisseurs' => 'Modifier un fournisseur',
        ],
        'Stock & dépôts' => [
            'voir_stock'           => 'Voir les dépôts, le stock par dépôt, les transferts, mouvements et ajustements',
            'gerer_depots'         => 'Créer / modifier / supprimer les dépôts',
            'gerer_transferts'     => 'Créer / modifier / supprimer les transferts entre dépôts',
            'gerer_ajustements'    => 'Créer et importer des ajustements d\'inventaire',
            'modifier_ajustements' => 'Modifier / supprimer un ajustement d\'inventaire déjà enregistré',
            'modifier_mouvements'  => 'Modifier / supprimer un mouvement de stock',
        ],
        'Commandes garage & bons de transfert' => [
            'voir_commandes_garage'    => 'Voir les commandes reçues du garage (app Atelier)',
            'traiter_commandes_garage' => 'Identifier les pièces et choisir les dépôts (« Tout valider »)',
            'voir_bons_transfert'      => 'Voir, imprimer et télécharger les bons de transfert',
            'gerer_bons_transfert'     => 'Créer / mettre à jour les bons de transfert et les envoyer au garage',
        ],
        'Réapprovisionnement' => [
            'voir_demandes_pieces'             => 'Voir les demandes de pièces (suivi des pièces)',
            'gerer_demandes_pieces'            => 'Créer / modifier / importer des demandes de pièces, changer leur statut',
            'supprimer_demandes_pieces'        => 'Supprimer une demande de pièce',
            'voir_commandes_fournisseurs'      => 'Voir les bons de commande fournisseurs',
            'gerer_commandes_fournisseurs'     => 'Créer, envoyer et annuler des bons de commande fournisseurs',
            'approuver_commandes_fournisseurs' => 'Signer / approuver / rejeter les bons de commande fournisseurs',
        ],
        'Clients & véhicules' => [
            'voir_clients'        => 'Voir les clients et leur historique',
            'creer_clients'       => 'Créer un client',
            'modifier_clients'    => 'Modifier / supprimer un client',
            'voir_vehicules'      => 'Voir les véhicules et leur historique',
            'gerer_vehicules'     => 'Créer / modifier des véhicules',
            'supprimer_vehicules' => 'Supprimer un véhicule',
        ],
        'Proformas & ventes' => [
            'voir_proformas'   => 'Voir les proformas',
            'gerer_proformas'  => 'Créer / annuler / supprimer des proformas et les convertir en vente',
            'voir_ventes'      => 'Voir les ventes et les factures (affichage, PDF)',
            'creer_ventes'     => 'Créer / modifier des ventes',
            'encaisser_ventes' => 'Enregistrer les paiements (encaisser les factures)',
            'annuler_ventes'   => 'Annuler une facture par un avoir (remet le stock)',
            'voir_avoirs'      => 'Voir et imprimer les avoirs',
            'rembourser_avoirs' => 'Enregistrer le remboursement d\'un avoir au client',
        ],
        'Utilisateurs' => [
            'gerer_utilisateurs' => 'Créer / modifier / désactiver les utilisateurs et leurs permissions (hors comptes administrateur)',
        ],
    ];

    /**
     * Permissions par défaut de chaque rôle (l'admin a tout, il n'est pas listé).
     * Elles reprennent les règles déjà prévues dans les pages (boutons
     * réservés à l'admin / au chef…) ; chaque utilisateur peut ensuite être
     * ajusté à la main dans l'écran « Permissions ».
     */
    public static array $roleDefaults = [
        'chef_magasinier' => [
            'voir_produits', 'creer_produits', 'modifier_produits', 'gerer_categories', 'supprimer_referentiels',
            'voir_fournisseurs', 'creer_fournisseurs', 'modifier_fournisseurs',
            'voir_stock', 'gerer_depots', 'gerer_transferts', 'gerer_ajustements', 'modifier_mouvements',
            'voir_commandes_garage', 'traiter_commandes_garage', 'voir_bons_transfert', 'gerer_bons_transfert',
            'voir_demandes_pieces', 'gerer_demandes_pieces', 'voir_commandes_fournisseurs', 'gerer_commandes_fournisseurs',
            'voir_clients', 'creer_clients', 'modifier_clients', 'voir_vehicules', 'gerer_vehicules',
            'voir_proformas', 'gerer_proformas', 'voir_ventes', 'creer_ventes', 'encaisser_ventes', 'voir_avoirs', 'rembourser_avoirs',
            'gerer_utilisateurs',
        ],
        'magasinier' => [
            'voir_produits', 'creer_produits',
            'voir_fournisseurs', 'creer_fournisseurs',
            'voir_stock',
            'voir_commandes_garage', 'traiter_commandes_garage', 'voir_bons_transfert',
            'voir_demandes_pieces', 'gerer_demandes_pieces', 'voir_commandes_fournisseurs', 'gerer_commandes_fournisseurs',
            'voir_vehicules', 'gerer_vehicules',
        ],
        'vendeur' => [
            'voir_produits',
            'voir_stock',
            'voir_commandes_garage', 'traiter_commandes_garage', 'voir_bons_transfert', 'gerer_bons_transfert',
            'voir_clients', 'creer_clients', 'voir_vehicules',
            'voir_proformas', 'gerer_proformas', 'voir_ventes', 'creer_ventes',
        ],
        'caissier' => [
            'voir_produits',
            'voir_clients', 'voir_vehicules',
            'voir_proformas', 'voir_ventes', 'encaisser_ventes', 'voir_avoirs', 'rembourser_avoirs',
        ],
    ];

    /**
     * Permission requise pour chaque page (nom de route ; `*` autorisé en fin,
     * le motif le plus précis l'emporte). Les routes absentes de cette liste
     * sont accessibles à tout utilisateur connecté (voir $libres).
     */
    public static array $routes = [
        // Produits & référentiels
        'products.index' => 'voir_produits', 'products.show' => 'voir_produits',
        'products.available' => 'voir_produits', 'products.unavailable' => 'voir_produits',
        'products.sold' => 'voir_produits', 'products.to-order' => 'voir_produits', 'products.to-order.export' => 'voir_produits',
        'products.export.excel' => 'voir_produits', 'products.sold.export' => 'voir_produits',
        'products.create' => 'creer_produits', 'products.store' => 'creer_produits',
        'products.import' => 'creer_produits', 'products.preview' => 'creer_produits',
        'products.import.store' => 'creer_produits',
        'product-options.*' => 'creer_produits',
        'products.edit' => 'modifier_produits', 'products.update' => 'modifier_produits',
        'products.destroy' => 'supprimer_referentiels',

        'categories.index' => 'voir_produits', 'categories.show' => 'voir_produits',
        'families.show' => 'voir_produits',
        'categories.create' => 'gerer_categories', 'categories.store' => 'gerer_categories',
        'categories.edit' => 'gerer_categories', 'categories.update' => 'gerer_categories',
        'families.create' => 'gerer_categories', 'families.store' => 'gerer_categories',
        'families.edit' => 'gerer_categories', 'families.update' => 'gerer_categories',
        'categories.destroy' => 'supprimer_referentiels', 'families.destroy' => 'supprimer_referentiels',

        // Fournisseurs
        'suppliers.index' => 'voir_fournisseurs', 'suppliers.show' => 'voir_fournisseurs',
        'suppliers.create' => 'creer_fournisseurs', 'suppliers.store' => 'creer_fournisseurs',
        'suppliers.edit' => 'modifier_fournisseurs', 'suppliers.update' => 'modifier_fournisseurs',
        'suppliers.destroy' => 'supprimer_referentiels',

        // Stock & dépôts
        'depots.index' => 'voir_stock', 'depots.show' => 'voir_stock',
        'depot-transfers.index' => 'voir_stock', 'depot-transfers.show' => 'voir_stock',
        'depot-transfers.stock' => 'voir_stock',
        'stock-movements.index' => 'voir_stock', 'stock-movements.show' => 'voir_stock',
        'stock-movements.entries' => 'voir_stock', 'stock-movements.exits' => 'voir_stock',
        'inventory-adjustments.index' => 'voir_stock', 'inventory-adjustments.show' => 'voir_stock',
        'depots.*' => 'gerer_depots',
        'depot-transfers.*' => 'gerer_transferts',
        'inventory-adjustments.create' => 'gerer_ajustements', 'inventory-adjustments.store' => 'gerer_ajustements',
        'inventory-adjustments.import' => 'gerer_ajustements', 'inventory-adjustments.import.*' => 'gerer_ajustements',
        'inventory-adjustments.*' => 'modifier_ajustements',
        'stock-movements.*' => 'modifier_mouvements',

        // Commandes garage & bons de transfert
        'fournisseur-commandes.index' => 'voir_commandes_garage', 'fournisseur-commandes.show' => 'voir_commandes_garage',
        'fournisseur-commandes.lignes.update' => 'traiter_commandes_garage',
        'fournisseur-commandes.lignes.update-all' => 'traiter_commandes_garage',
        'fournisseur-commandes.creer-bon-transfert' => 'gerer_bons_transfert',
        'fournisseur-commandes.mettre-a-jour-bon-transfert' => 'gerer_bons_transfert',
        'bons-transfert.index' => 'voir_bons_transfert', 'bons-transfert.show' => 'voir_bons_transfert',
        'bons-transfert.pdf' => 'voir_bons_transfert', 'bons-transfert.imprimer' => 'voir_bons_transfert',
        'bons-transfert.envoyer' => 'gerer_bons_transfert',
        // Suppression manuelle : administrateur uniquement (voir RESERVE_ADMIN)
        'fournisseur-commandes.destroy' => self::RESERVE_ADMIN,
        'bons-transfert.destroy' => self::RESERVE_ADMIN,

        // Réapprovisionnement
        'vehicle-part-requests.index' => 'voir_demandes_pieces', 'vehicle-part-requests.show' => 'voir_demandes_pieces',
        'vehicle-part-requests.not-found' => 'voir_demandes_pieces', 'vehicle-part-requests.ordered' => 'voir_demandes_pieces',
        'vehicle-part-requests.received' => 'voir_demandes_pieces', 'vehicle-part-requests.import-template' => 'voir_demandes_pieces',
        'vehicle-part-requests.destroy' => 'supprimer_demandes_pieces',
        'vehicle-part-requests.*' => 'gerer_demandes_pieces',
        'supplier-orders.index' => 'voir_commandes_fournisseurs', 'supplier-orders.show' => 'voir_commandes_fournisseurs',
        'supplier-orders.approve' => 'approuver_commandes_fournisseurs',
        'supplier-orders.reject' => 'approuver_commandes_fournisseurs',
        'supplier-orders.signature' => 'approuver_commandes_fournisseurs',
        'supplier-orders.*' => 'gerer_commandes_fournisseurs',

        // Clients & véhicules
        'customers.index' => 'voir_clients', 'customers.show' => 'voir_clients', 'customers.history' => 'voir_clients',
        'customers.create' => 'creer_clients', 'customers.store' => 'creer_clients',
        'customers.*' => 'modifier_clients',
        'vehicles.index' => 'voir_vehicules', 'vehicles.show' => 'voir_vehicules', 'vehicles.history' => 'voir_vehicules',
        'vehicles.destroy' => 'supprimer_vehicules',
        'vehicles.*' => 'gerer_vehicules',

        // Proformas & ventes
        'proformas.index' => 'voir_proformas', 'proformas.show' => 'voir_proformas', 'proformas.pdf' => 'voir_proformas',
        'proformas.*' => 'gerer_proformas',
        'sales.index' => 'voir_ventes', 'sales.show' => 'voir_ventes',
        'sales.invoice' => 'voir_ventes', 'sales.invoice.download' => 'voir_ventes',
        'sales.payment' => 'encaisser_ventes',
        'sales.cancel' => 'annuler_ventes',
        'sales.*' => 'creer_ventes',
        'avoirs.rembourser' => 'rembourser_avoirs',
        'avoirs.*' => 'voir_avoirs',

        // Utilisateurs
        'users.*' => 'gerer_utilisateurs',
    ];

    /** Pages de tout utilisateur connecté (pas de permission). */
    public static array $libres = [
        'dashboard', 'logout', 'profile.*', 'password.*', 'notifications.*', 'verification.*', 'session.ping',
    ];

    public static function all(): array
    {
        return array_merge(...array_values(static::$groups));
    }

    public static function defaultsForRole(?string $role): array
    {
        return static::$roleDefaults[$role] ?? [];
    }

    /** Permission requise pour une route ; null = pas de restriction. */
    public static function forRoute(?string $routeName): ?string
    {
        if (! $routeName) {
            return null;
        }

        if (isset(static::$routes[$routeName])) {
            return static::$routes[$routeName];
        }

        // Motifs « xxx.* » : le plus long d'abord (le plus précis gagne).
        $motifs = array_filter(array_keys(static::$routes), fn ($m) => str_ends_with($m, '*'));
        usort($motifs, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($motifs as $motif) {
            if (str_starts_with($routeName, substr($motif, 0, -1))) {
                return static::$routes[$motif];
            }
        }

        return null;
    }

    public static function estLibre(?string $routeName): bool
    {
        foreach (static::$libres as $motif) {
            if ($routeName === $motif || (str_ends_with($motif, '*') && str_starts_with((string) $routeName, substr($motif, 0, -1)))) {
                return true;
            }
        }

        return false;
    }
}
