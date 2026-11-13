<?php

/*
|--------------------------------------------------------------------------
| Paramètres du Magasin
|--------------------------------------------------------------------------
*/

return [

    // V6 : majoration d'une vente directe / d'un proforma fait à une assurance,
    // quand l'assurance n'a pas de taux propre (même défaut que l'Atelier : 35 %)
    'taux_majoration_assurance' => (float) env('TAUX_MAJORATION_ASSURANCE', 35),

    // V7 : un bon de commande fournisseur qui attend une signature (chef
    // magasinier ou admin) depuis plus de N jours ressort « À relancer »
    'relance_bc_jours' => (int) env('RELANCE_BC_JOURS', 3),

];
