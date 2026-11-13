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
            'supprimer_produits'     => 'Supprimer un produit',
            'supprimer_categories'   => 'Supprimer une catégorie / famille',
        ],
        'Fournisseurs' => [
            'voir_fournisseurs'     => 'Voir les fournisseurs',
            'creer_fournisseurs'    => 'Créer un fournisseur',
            'modifier_fournisseurs' => 'Modifier un fournisseur',
            'supprimer_fournisseurs' => 'Supprimer un fournisseur',
        ],
        'Stock & dépôts' => [
            'voir_stock'           => 'Voir les dépôts, le stock par dépôt, les transferts, mouvements et ajustements',
            'gerer_depots'          => 'Créer / modifier les dépôts',
            'supprimer_depots'      => 'Supprimer un dépôt',
            'gerer_transferts'      => 'Créer / modifier les transferts entre dépôts',
            'supprimer_transferts'  => 'Supprimer un transfert entre dépôts',
            'gerer_ajustements'     => 'Saisir, importer et corriger des ajustements d\'inventaire (fiches d\'inventaire comprises)',
            'valider_ajustements'   => 'Valider / refuser les ajustements d\'inventaire (le stock ne change qu\'à la validation)',
            'modifier_mouvements'   => 'Modifier un mouvement de stock',
            'supprimer_mouvements'  => 'Supprimer un mouvement de stock',
        ],
        'Commandes garage & bons de transfert' => [
            'voir_commandes_garage'    => 'Voir les commandes reçues du garage (app Atelier)',
            'traiter_commandes_garage' => 'Identifier les pièces et choisir les dépôts (« Tout valider »)',
            'voir_bons_transfert'      => 'Voir, imprimer et télécharger les bons de transfert',
            'gerer_bons_transfert'     => 'Créer / mettre à jour les bons de transfert et les envoyer au garage',
            'gerer_bt_bus'             => 'Bus leasing / transport : sorties de pièces (écran, import Excel), modifier / supprimer leurs BT',
            'gerer_consommations_atelier' => '« + BT » direct : consommation atelier (pont, compresseur…), vente intersociété, équipe commerciale, autre usage interne ; modifier / supprimer',
        ],
        'Réapprovisionnement' => [
            'voir_demandes_pieces'             => 'Voir les demandes de pièces (suivi des pièces)',
            'gerer_demandes_pieces'            => 'Créer / modifier / importer des demandes de pièces, changer leur statut',
            'supprimer_demandes_pieces'        => 'Supprimer une demande de pièce',
            'voir_commandes_fournisseurs'      => 'Voir les bons de commande fournisseurs',
            'gerer_commandes_fournisseurs'     => 'Créer, envoyer, réceptionner, solder et annuler des bons de commande fournisseurs',
            'valider_commandes_fournisseurs'   => 'Valider (signer) les bons de commande fournisseurs avant l\'admin — chef magasinier',
            'approuver_commandes_fournisseurs' => 'Signer / approuver / rejeter les bons de commande fournisseurs',
        ],
        'Clients & véhicules' => [
            'voir_clients'        => 'Voir les clients et leur historique',
            'creer_clients'       => 'Créer un client',
            'modifier_clients'    => 'Modifier un client',
            'supprimer_clients'   => 'Supprimer un client',
            'voir_vehicules'      => 'Voir les véhicules et leur historique',
            'gerer_vehicules'     => 'Créer / modifier des véhicules',
            'supprimer_vehicules' => 'Supprimer un véhicule',
        ],
        'Proformas & ventes' => [
            'voir_proformas'   => 'Voir les proformas',
            'gerer_proformas'  => 'Créer / annuler des proformas et les convertir en vente',
            'supprimer_proformas' => 'Supprimer un proforma',
            'voir_ventes'      => 'Voir les ventes et les factures (affichage, PDF)',
            'creer_ventes'     => 'Créer / modifier des ventes',
            'encaisser_ventes' => 'Enregistrer les paiements (encaisser les factures)',
            'annuler_ventes'   => 'Annuler une facture par un avoir (remet le stock)',
            'voir_avoirs'      => 'Voir et imprimer les avoirs',
            'rembourser_avoirs' => 'Enregistrer le remboursement d\'un avoir au client',
            'gerer_bons_livraison' => 'Créer des bons de livraison (après la facture)',
        ],
        'Utilisateurs' => [
            'gerer_utilisateurs' => 'Créer / modifier / désactiver les utilisateurs et leurs permissions (hors comptes administrateur)',
            'supprimer_utilisateurs' => 'Supprimer un utilisateur (hors comptes administrateur)',
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
            'voir_produits', 'creer_produits', 'modifier_produits', 'gerer_categories',
            'supprimer_produits', 'supprimer_categories',
            'voir_fournisseurs', 'creer_fournisseurs', 'modifier_fournisseurs', 'supprimer_fournisseurs',
            'voir_stock', 'gerer_depots', 'supprimer_depots', 'gerer_transferts', 'supprimer_transferts',
            'gerer_ajustements', 'valider_ajustements', 'modifier_mouvements', 'supprimer_mouvements',
            'voir_commandes_garage', 'traiter_commandes_garage', 'voir_bons_transfert', 'gerer_bons_transfert', 'gerer_bt_bus', 'gerer_consommations_atelier',
            'voir_demandes_pieces', 'gerer_demandes_pieces', 'voir_commandes_fournisseurs', 'gerer_commandes_fournisseurs', 'valider_commandes_fournisseurs',
            'voir_clients', 'creer_clients', 'modifier_clients', 'supprimer_clients', 'voir_vehicules', 'gerer_vehicules',
            'voir_proformas', 'gerer_proformas', 'supprimer_proformas', 'voir_ventes', 'creer_ventes', 'encaisser_ventes', 'voir_avoirs', 'rembourser_avoirs',
            'gerer_bons_livraison',
            'gerer_utilisateurs', 'supprimer_utilisateurs',
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
            'voir_commandes_garage', 'traiter_commandes_garage', 'voir_bons_transfert', 'gerer_bons_transfert', 'gerer_bt_bus', 'gerer_consommations_atelier',
            'voir_clients', 'creer_clients', 'voir_vehicules',
            'voir_proformas', 'gerer_proformas', 'voir_ventes', 'creer_ventes', 'gerer_bons_livraison',
            'voir_demandes_pieces', 'gerer_demandes_pieces', 'voir_commandes_fournisseurs',
        ],
        'caissier' => [
            'voir_produits',
            'voir_clients', 'voir_vehicules',
            'voir_proformas', 'voir_ventes', 'encaisser_ventes', 'voir_avoirs', 'rembourser_avoirs', 'gerer_bons_livraison',
        ],
    ];

    /**
     * Permission requise pour chaque page (nom de route ; `*` autorisé en fin,
     * le motif le plus précis l'emporte). Les routes absentes de cette liste
     * sont accessibles à tout utilisateur connecté (voir $libres).
     */
    public static array $routes = [
        // Suppressions : chacune sa permission (V6), jamais incluse dans « modifier ».
        'products.destroy' => 'supprimer_produits',
        'categories.destroy' => 'supprimer_categories',
        'families.destroy' => 'supprimer_categories',
        'suppliers.destroy' => 'supprimer_fournisseurs',
        'customers.destroy' => 'supprimer_clients',
        'depots.destroy' => 'supprimer_depots',
        'depot-transfers.destroy' => 'supprimer_transferts',
        'stock-movements.destroy' => 'supprimer_mouvements',
        'proformas.destroy' => 'supprimer_proformas',
        'users.destroy' => 'supprimer_utilisateurs',

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

        'categories.index' => 'voir_produits', 'categories.show' => 'voir_produits',
        'families.show' => 'voir_produits',
        'categories.create' => 'gerer_categories', 'categories.store' => 'gerer_categories',
        'categories.edit' => 'gerer_categories', 'categories.update' => 'gerer_categories',
        'families.create' => 'gerer_categories', 'families.store' => 'gerer_categories',
        'families.edit' => 'gerer_categories', 'families.update' => 'gerer_categories',

        // Fournisseurs
        'suppliers.index' => 'voir_fournisseurs', 'suppliers.show' => 'voir_fournisseurs',
        'suppliers.create' => 'creer_fournisseurs', 'suppliers.store' => 'creer_fournisseurs',
        'suppliers.edit' => 'modifier_fournisseurs', 'suppliers.update' => 'modifier_fournisseurs',

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
        'inventory-adjustments.valider' => 'valider_ajustements', 'inventory-adjustments.valider-lot' => 'valider_ajustements',
        'inventory-adjustments.refuser' => 'valider_ajustements',
        'inventory-adjustments.*' => 'gerer_ajustements', // corriger, annuler (contrôle fin dans le service)
        // V6 : fiches d'inventaire (comptage par rayon)
        'inventaires.index' => 'voir_stock', 'inventaires.show' => 'voir_stock',
        'inventaires.pdf' => 'voir_stock', 'inventaires.imprimer' => 'voir_stock', 'inventaires.export' => 'voir_stock',
        'inventaires.*' => 'gerer_ajustements',
        'stock-movements.*' => 'modifier_mouvements',

        // Commandes garage & bons de transfert
        'fournisseur-commandes.index' => 'voir_commandes_garage', 'fournisseur-commandes.show' => 'voir_commandes_garage',
        'fournisseur-commandes.lignes.update' => 'traiter_commandes_garage',
        'fournisseur-commandes.lignes.update-all' => 'traiter_commandes_garage',
        'fournisseur-commandes.creer-bon-transfert' => 'gerer_bons_transfert', 'fournisseur-commandes.creer-bon-transfert-reste' => 'gerer_bons_transfert', 'fournisseur-commandes.commander-fournisseur' => 'gerer_commandes_fournisseurs',
        'fournisseur-commandes.mettre-a-jour-bon-transfert' => 'gerer_bons_transfert',
        'bons-transfert.index' => 'voir_bons_transfert', 'bons-transfert.show' => 'voir_bons_transfert',
        'bons-transfert.pdf' => 'voir_bons_transfert', 'bons-transfert.imprimer' => 'voir_bons_transfert',
        'bons-transfert.envoyer' => 'gerer_bons_transfert',
        'bus.index' => 'voir_bons_transfert', 'bus.*' => 'gerer_bt_bus',
        'bt-direct.*' => 'gerer_consommations_atelier',
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
        'supplier-orders.validate' => 'valider_commandes_fournisseurs',
        // V7 : demandes de pièces (plusieurs pièces, BC automatique par fournisseur)
        'demandes.index' => 'voir_demandes_pieces', 'demandes.show' => 'voir_demandes_pieces',
        'demandes.*' => 'gerer_demandes_pieces',
        // V7 : le chef magasinier rejette aussi (contrôle fin : SupplierOrderController::peutRejeter)
        'supplier-orders.reject' => 'voir_commandes_fournisseurs',
        'supplier-orders.signature' => 'voir_commandes_fournisseurs',
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
        'sales.index' => 'voir_ventes', 'sales.show' => 'voir_ventes', 'sales.export' => 'voir_ventes',
        'sales.invoice' => 'voir_ventes', 'sales.invoice.download' => 'voir_ventes',
        'sales.payment' => 'encaisser_ventes',
        'sales.cancel' => 'annuler_ventes',
        'sales.*' => 'creer_ventes',
        'avoirs.rembourser' => 'rembourser_avoirs',
        'bons-livraison.create' => 'gerer_bons_livraison', 'bons-livraison.store' => 'gerer_bons_livraison',
        'bons-livraison.*' => 'voir_ventes',
        'avoirs.*' => 'voir_avoirs',

        // Utilisateurs
        'users.*' => 'gerer_utilisateurs',
    ];

    /** Pages de tout utilisateur connecté (pas de permission). */
    public static array $libres = [
        'dashboard', 'logout', 'profile.*', 'password.*', 'notifications.*', 'verification.*', 'session.ping', 'recherche',
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
