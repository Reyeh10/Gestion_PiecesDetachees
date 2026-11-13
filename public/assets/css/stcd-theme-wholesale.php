/* ==========================================================================
   STCD MOTORS — DESIGN « WHOLESALE » (V6)
   --------------------------------------------------------------------------
   Reproduit l'apparence du modèle « Wholesale – Inventory Control » (captures
   officielles) : menu gris anthracite avec le profil sous le logo, accents
   verts, barre du haut blanche, bandeau de titre avec icône verte et fil
   d'Ariane, cartes blanches aux angles droits avec le coin vert, tableaux
   encadrés, boutons plats.

   Écrit pour ce projet (aucun fichier du modèle n'est repris). Chargé APRÈS
   stcd-theme.css et actif seulement avec la classe « stcd-wholesale » sur
   <body> (layouts/layoutMaster) : retirer la classe = retour au design Atelier.
   ========================================================================== */

/* --------------------------------------------------------------------------
   1. COULEURS
   -------------------------------------------------------------------------- */
body.stcd-wholesale {
    --stcd-primary: #37a000;
    --stcd-primary-hover: #2e8600;
    --stcd-primary-active: #266f00;
    --stcd-primary-light: #5cbf2a;
    --stcd-primary-rgb: 55, 160, 0;
    --stcd-primary-50: #eef8e8;
    --stcd-primary-100: #d8efcb;

    --ws-menu: #2c3238;
    --ws-menu-logo: #3a4047;
    --ws-menu-actif: #22272c;
    --ws-menu-sous: #1f2327;
    --ws-menu-texte: #c3c8cd;
    --ws-menu-texte-2: #9aa0a6;
    --ws-bleu: #3c8dbc;
    --ws-bleu-hover: #317aa5;
    --ws-info: #5bc0de;
    --ws-jaune: #f7b50c;
    --ws-rouge: #e8303c;
    --ws-bord: #e3e6ea;

    --stcd-bg: #f1f3f6;
    --stcd-border: #e3e6ea;
    --stcd-text: #333a40;
    --stcd-text-2: #4a5056;
    --stcd-font: 'Open Sans', 'Segoe UI', Roboto, Arial, sans-serif;

    --bs-primary: var(--stcd-primary);
    --bs-primary-rgb: var(--stcd-primary-rgb);
    --bs-link-color: var(--stcd-primary);
    --bs-link-hover-color: var(--stcd-primary-hover);

    font-family: var(--stcd-font) !important;
}

html.dark body.stcd-wholesale {
    --stcd-bg: #15191d;
    --stcd-surface: #22272c;
    --stcd-surface-2: #1b1f23;
    --stcd-border: #343b42;
    --ws-bord: #343b42;
    --stcd-text: #e6e8ea;
    --stcd-text-2: #cfd3d6;
    --stcd-muted: #98a0a7;
}

html body.stcd-wholesale,
html body.stcd-wholesale .layout-page,
html body.stcd-wholesale .content-wrapper {
    background: var(--stcd-bg) !important;
}

html body.stcd-wholesale h1, html body.stcd-wholesale h2, html body.stcd-wholesale h3,
html body.stcd-wholesale h4, html body.stcd-wholesale h5, html body.stcd-wholesale h6,
html body.stcd-wholesale .btn, html body.stcd-wholesale input, html body.stcd-wholesale select,
html body.stcd-wholesale textarea, html body.stcd-wholesale table {
    font-family: var(--stcd-font) !important;
}

/* --------------------------------------------------------------------------
   2. MENU LATÉRAL
   -------------------------------------------------------------------------- */
html body.stcd-wholesale #layout-menu#layout-menu {
    background: var(--ws-menu) !important;
    box-shadow: 2px 0 6px rgba(0, 0, 0, .12) !important;
}

/* bandeau du logo, même hauteur que la barre du haut */
html body.stcd-wholesale #layout-menu#layout-menu .stcd-sidebar-brand,
html body.stcd-wholesale #layout-menu#layout-menu .app-brand {
    height: 56px !important;
    min-height: 56px !important;
    padding: 0 18px !important;
    background: var(--ws-menu-logo) !important;
    border-bottom: 0 !important;
}

html body.stcd-wholesale #layout-menu#layout-menu .stcd-brand-name {
    color: #fff !important;
    font-size: 17px !important;
    font-weight: 800 !important;
    letter-spacing: .02em;
    text-transform: uppercase;
}

html body.stcd-wholesale #layout-menu#layout-menu .stcd-brand-city { color: var(--ws-menu-texte-2) !important; }

/* profil sous le logo */
.stcd-ws-profil { display: none; }

html body.stcd-wholesale #layout-menu#layout-menu .stcd-ws-profil {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
    padding: 20px 12px 16px;
    border-bottom: 1px solid rgba(255, 255, 255, .06);
}

html body.stcd-wholesale .stcd-ws-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 74px;
    height: 74px;
    color: #fff;
    background: linear-gradient(135deg, #4a5259, #353b41);
    border: 3px solid #4b535a;
    border-radius: 50%;
    box-shadow: 0 0 0 3px rgba(0, 0, 0, .15);
    font-size: 24px;
    font-weight: 700;
}

html body.stcd-wholesale .stcd-ws-nom {
    margin-top: 12px;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .04em;
    text-align: center;
}

html body.stcd-wholesale .stcd-ws-statut {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 3px;
    color: var(--stcd-primary-light);
    font-size: 11px;
}

html body.stcd-wholesale .stcd-ws-point {
    width: 8px;
    height: 8px;
    background: var(--stcd-primary-light);
    border-radius: 50%;
}

/* liste des liens */
html body.stcd-wholesale #layout-menu#layout-menu > .menu-inner {
    padding: 8px 0 !important;
}

html body.stcd-wholesale #layout-menu#layout-menu .menu-inner > .menu-item + .menu-item { margin-top: 0 !important; }

html body.stcd-wholesale #layout-menu#layout-menu .menu-inner > .menu-header {
    margin: 14px 0 4px !important;
    padding: 0 20px !important;
}

html body.stcd-wholesale #layout-menu#layout-menu .menu-inner > .menu-header .menu-header-text {
    color: #7d848b !important;
    font-size: 11px !important;
    letter-spacing: .06em !important;
}

html body.stcd-wholesale #layout-menu#layout-menu .menu-inner > .menu-item > .menu-link {
    min-height: 44px !important;
    margin: 0 !important;
    padding: 11px 20px !important;
    color: var(--ws-menu-texte) !important;
    background: transparent !important;
    border-left: 3px solid transparent !important;
    border-radius: 0 !important;
    font-size: 14px !important;
    font-weight: 400 !important;
}

html body.stcd-wholesale #layout-menu#layout-menu .menu-icon {
    color: var(--ws-menu-texte) !important;
    opacity: .9;
}

html body.stcd-wholesale #layout-menu#layout-menu .menu-inner > .menu-item > .menu-link:hover,
html body.stcd-wholesale #layout-menu#layout-menu .menu-inner > .menu-item.open:not(.active) > .menu-link {
    color: #fff !important;
    background: var(--ws-menu-actif) !important;
}

html body.stcd-wholesale #layout-menu#layout-menu .menu-inner > .menu-item.active > .menu-link,
html body.stcd-wholesale #layout-menu#layout-menu .menu-inner > .menu-item.open > .menu-link {
    color: #fff !important;
    background: var(--ws-menu-actif) !important;
    border-left-color: var(--stcd-primary) !important;
    box-shadow: none !important;
    font-weight: 600 !important;
}

html body.stcd-wholesale #layout-menu#layout-menu .menu-inner > .menu-item.active > .menu-link .menu-icon,
html body.stcd-wholesale #layout-menu#layout-menu .menu-inner > .menu-item.open > .menu-link .menu-icon {
    color: #fff !important;
}

/* sous-menus : fond plus sombre, sans trait, texte simple */
html body.stcd-wholesale #layout-menu#layout-menu .menu-sub {
    margin: 0 !important;
    padding: 6px 0 !important;
    background: var(--ws-menu-sous) !important;
    border: 0 !important;
}

html body.stcd-wholesale #layout-menu#layout-menu .menu-sub > .menu-item > .menu-link {
    min-height: 0 !important;
    margin: 0 !important;
    padding: 7px 20px 7px 50px !important;
    color: var(--ws-menu-texte-2) !important;
    background: transparent !important;
    border-radius: 0 !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    line-height: 18px !important;
}

html body.stcd-wholesale #layout-menu#layout-menu .menu-sub > .menu-item > .menu-link > div {
    font-size: 13px !important;
    font-weight: inherit !important;
    line-height: 18px !important;
}

html body.stcd-wholesale #layout-menu#layout-menu .menu-sub > .menu-item > .menu-link::before { display: none !important; }

html body.stcd-wholesale #layout-menu#layout-menu .menu-sub > .menu-item > .menu-link:hover { color: #fff !important; background: transparent !important; }

html body.stcd-wholesale #layout-menu#layout-menu .menu-sub > .menu-item.active > .menu-link {
    color: var(--stcd-primary-light) !important;
    background: transparent !important;
    font-weight: 600 !important;
}

html body.stcd-wholesale #layout-menu#layout-menu .menu-sub .menu-header {
    padding-left: 50px !important;
}

/* bas du menu : nom déjà affiché en haut, on garde jour/nuit, sortie, version */
html body.stcd-wholesale #layout-menu#layout-menu .stcd-sidebar-footer {
    background: var(--ws-menu-logo) !important;
    border-top: 0 !important;
}

html body.stcd-wholesale #layout-menu#layout-menu .stcd-sidebar-user { visibility: hidden; }

/* menu replié par le bouton grille (ordinateur) */
@media (min-width: 1200px) {
    html body.stcd-wholesale.stcd-menu-ferme #layout-menu#layout-menu {
        transform: translateX(-100%) !important;
    }

    html body.stcd-wholesale.stcd-menu-ferme .layout-page {
        width: 100% !important;
        max-width: 100% !important;
        margin-left: 0 !important;
    }

    html body.stcd-wholesale.stcd-menu-ferme .layout-page > .layout-navbar { left: 0 !important; }

    html body.stcd-wholesale .layout-page,
    html body.stcd-wholesale .layout-page > .layout-navbar,
    html body.stcd-wholesale #layout-menu#layout-menu {
        transition: margin-left .25s ease, left .25s ease, width .25s ease, transform .25s ease !important;
    }
}

/* --------------------------------------------------------------------------
   3. BARRE DU HAUT + BANDEAU DU TITRE
   -------------------------------------------------------------------------- */
html body.stcd-wholesale .layout-page > .layout-navbar {
    flex-wrap: wrap !important;
    height: auto !important;
    min-height: 0 !important;
    padding: 0 !important;
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
    backdrop-filter: none !important;
}

/* ligne 1 : bouton grille à gauche, cloche à droite, fond blanc 56 px */
html body.stcd-wholesale .layout-page > .layout-navbar::before {
    content: "";
    position: absolute;
    inset: 0 0 auto 0;
    z-index: -1;
    height: 56px;
    background: #fff;
    border-bottom: 1px solid var(--ws-bord);
}

html.dark body.stcd-wholesale .layout-page > .layout-navbar::before { background: var(--stcd-surface); }

html body.stcd-wholesale .layout-navbar .layout-menu-toggle {
    display: flex !important;
    order: 0;
    align-items: center;
    height: 56px;
    margin: 0 !important;
    padding: 0 18px !important;
    border-right: 1px solid var(--ws-bord);
}

html body.stcd-wholesale .layout-navbar .stcd-ws-grille {
    color: #8a9097 !important;
}

html body.stcd-wholesale .layout-navbar .stcd-ws-grille:hover { color: var(--stcd-primary) !important; }

html body.stcd-wholesale .layout-navbar > .navbar-nav.ms-auto,
html body.stcd-wholesale .layout-navbar > ul.navbar-nav {
    order: 1;
    height: 56px;
    margin-left: auto !important;
    padding-right: 16px;
}

html body.stcd-wholesale .stcd-notification-button {
    width: 38px !important;
    height: 38px !important;
    background: #f4f5f7 !important;
    border: 1px solid var(--ws-bord) !important;
    border-radius: 3px !important;
}

html.dark body.stcd-wholesale .stcd-notification-button { background: var(--stcd-surface-2) !important; }

/* ligne 2 : bandeau blanc du titre */
html body.stcd-wholesale .layout-navbar .stcd-ws-bandeau {
    order: 2;
    flex: 0 0 100%;
    gap: 16px;
    min-height: 74px;
    margin: 0 !important;
    padding: 12px 24px;
    background: #fff;
    border-bottom: 1px solid var(--ws-bord);
}

html.dark body.stcd-wholesale .layout-navbar .stcd-ws-bandeau { background: var(--stcd-surface); }

.stcd-ws-titre-icone,
.stcd-ws-fil { display: none !important; }

html body.stcd-wholesale .stcd-ws-titre-icone {
    display: flex !important;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    color: var(--stcd-primary);
}

html body.stcd-wholesale .stcd-ws-titre-icone svg { width: 44px; height: 44px; }

html body.stcd-wholesale #stcd-topbar-title {
    margin: 0 !important;
    color: var(--stcd-text) !important;
    font-size: 21px !important;
    font-weight: 400 !important;
    line-height: 1.25 !important;
}

html body.stcd-wholesale #stcd-topbar-subtitle {
    margin-top: 2px;
    color: var(--stcd-muted) !important;
    font-size: 13px !important;
}

html body.stcd-wholesale .stcd-ws-fil {
    display: flex !important;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    margin-left: auto;
    padding: 6px 10px;
    color: var(--stcd-text-2);
    background: #fff;
    border: 1px solid var(--ws-bord);
    border-radius: 2px;
    font-size: 13px;
    white-space: nowrap;
}

html.dark body.stcd-wholesale .stcd-ws-fil { background: var(--stcd-surface-2); }

html body.stcd-wholesale .stcd-ws-fil a {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--stcd-text-2) !important;
}

html body.stcd-wholesale .stcd-ws-fil-sep { color: #b3b8bd; }

html body.stcd-wholesale .stcd-ws-fil .stcd-ws-fil-actif {
    color: var(--stcd-primary) !important;
    font-weight: 600;
}

@media (max-width: 767.98px) {
    html body.stcd-wholesale .stcd-ws-fil { display: none !important; }
}

/* le contenu commence sous les deux lignes */
html body.stcd-wholesale .layout-page > .content-wrapper {
    padding-top: 131px !important;
}

/* --------------------------------------------------------------------------
   4. CARTES : angles droits, bord fin, coin vert en haut à gauche
   -------------------------------------------------------------------------- */
html body.stcd-wholesale .content-wrapper .card,
html body.stcd-wholesale .content-wrapper [class$="-card"],
html body.stcd-wholesale .content-wrapper [class*="-card "] {
    position: relative;
    border: 1px solid var(--ws-bord) !important;
    border-radius: 3px !important;
    box-shadow: 0 1px 2px rgba(0, 0, 0, .04) !important;
}

html body.stcd-wholesale .content-wrapper .card::before,
html body.stcd-wholesale .content-wrapper [class$="-card"]::before,
html body.stcd-wholesale .content-wrapper [class*="-card "]::before {
    content: "";
    position: absolute;
    top: -1px;
    left: -1px;
    z-index: 2;
    width: 0;
    height: 0;
    border-top: 12px solid var(--stcd-primary);
    border-right: 12px solid transparent;
    pointer-events: none;
}

html body.stcd-wholesale .content-wrapper td .card::before,
html body.stcd-wholesale .content-wrapper .modal .card::before { display: none; }

html body.stcd-wholesale .content-wrapper .card-header {
    background: transparent !important;
    border-bottom: 1px solid var(--ws-bord) !important;
}

html body.stcd-wholesale .content-wrapper .card-header h5,
html body.stcd-wholesale .content-wrapper .card-header .card-title {
    color: var(--stcd-text) !important;
    font-size: 16px !important;
    font-weight: 500 !important;
}

/* --------------------------------------------------------------------------
   5. TABLEAUX encadrés, en-têtes en gras (sans majuscules), lignes zébrées
   -------------------------------------------------------------------------- */
html body.stcd-wholesale .content-wrapper table.table,
html body.stcd-wholesale .content-wrapper table[class*="-table"] {
    border: 1px solid var(--ws-bord) !important;
    border-collapse: collapse !important;
}

html body.stcd-wholesale .content-wrapper table.table > thead > tr > th,
html body.stcd-wholesale .content-wrapper table[class*="-table"] > thead > tr > th {
    padding: 11px 10px !important;
    color: var(--stcd-text) !important;
    background: #fff !important;
    border: 1px solid var(--ws-bord) !important;
    border-bottom: 2px solid #d9dde1 !important;
    font-size: 13.5px !important;
    font-weight: 700 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
}

html.dark body.stcd-wholesale .content-wrapper table.table > thead > tr > th,
html.dark body.stcd-wholesale .content-wrapper table[class*="-table"] > thead > tr > th {
    background: var(--stcd-surface-2) !important;
    border-bottom-color: #3d454c !important;
}

html body.stcd-wholesale .content-wrapper table.table > tbody > tr > td,
html body.stcd-wholesale .content-wrapper table[class*="-table"] > tbody > tr > td {
    border: 1px solid var(--ws-bord) !important;
}

html body.stcd-wholesale .content-wrapper table.table > tbody > tr:nth-child(odd) > td,
html body.stcd-wholesale .content-wrapper table[class*="-table"] > tbody > tr:nth-child(odd) > td {
    background-color: #f9fafb;
}

html.dark body.stcd-wholesale .content-wrapper table.table > tbody > tr:nth-child(odd) > td,
html.dark body.stcd-wholesale .content-wrapper table[class*="-table"] > tbody > tr:nth-child(odd) > td {
    background-color: rgba(255, 255, 255, .025);
}

html body.stcd-wholesale .content-wrapper table.table > tbody > tr:hover > td,
html body.stcd-wholesale .content-wrapper table[class*="-table"] > tbody > tr:hover > td {
    background-color: var(--stcd-primary-50);
}

html.dark body.stcd-wholesale .content-wrapper table.table > tbody > tr:hover > td,
html.dark body.stcd-wholesale .content-wrapper table[class*="-table"] > tbody > tr:hover > td {
    background-color: rgba(55, 160, 0, .10);
}

/* liens des tableaux en vert, comme le modèle */
html body.stcd-wholesale .content-wrapper td a:not(.btn) { color: var(--stcd-primary) !important; }

/* --------------------------------------------------------------------------
   6. BOUTONS plats, angles légèrement arrondis
   -------------------------------------------------------------------------- */
html body.stcd-wholesale .content-wrapper .btn,
html body.stcd-wholesale .modal .btn {
    border-radius: 3px !important;
    box-shadow: none !important;
    font-weight: 600;
}

html body.stcd-wholesale .btn-primary {
    --bs-btn-bg: var(--ws-bleu);
    --bs-btn-border-color: var(--ws-bleu);
    --bs-btn-hover-bg: var(--ws-bleu-hover);
    --bs-btn-hover-border-color: var(--ws-bleu-hover);
    color: #fff !important;
    background-color: var(--ws-bleu) !important;
    border-color: var(--ws-bleu) !important;
}

html body.stcd-wholesale .btn-primary:hover,
html body.stcd-wholesale .btn-primary:focus {
    background-color: var(--ws-bleu-hover) !important;
    border-color: var(--ws-bleu-hover) !important;
}

html body.stcd-wholesale .btn-success {
    color: #fff !important;
    background-color: var(--stcd-primary) !important;
    border-color: var(--stcd-primary) !important;
}

html body.stcd-wholesale .btn-success:hover { background-color: var(--stcd-primary-hover) !important; }

html body.stcd-wholesale .btn-info {
    color: #fff !important;
    background-color: var(--ws-info) !important;
    border-color: var(--ws-info) !important;
}

html body.stcd-wholesale .btn-warning {
    color: #fff !important;
    background-color: var(--ws-jaune) !important;
    border-color: var(--ws-jaune) !important;
}

html body.stcd-wholesale .btn-danger {
    color: #fff !important;
    background-color: var(--ws-rouge) !important;
    border-color: var(--ws-rouge) !important;
}

html body.stcd-wholesale .btn-secondary,
html body.stcd-wholesale .btn-light,
html body.stcd-wholesale .btn-outline-secondary {
    color: var(--stcd-text-2) !important;
    background-color: #fff !important;
    border-color: #ccd1d6 !important;
}

html.dark body.stcd-wholesale .btn-secondary,
html.dark body.stcd-wholesale .btn-light,
html.dark body.stcd-wholesale .btn-outline-secondary {
    background-color: var(--stcd-surface-2) !important;
    border-color: var(--stcd-border) !important;
}

/* --------------------------------------------------------------------------
   7. CHAMPS DE FORMULAIRE
   -------------------------------------------------------------------------- */
html body.stcd-wholesale .content-wrapper .form-control,
html body.stcd-wholesale .content-wrapper .form-select,
html body.stcd-wholesale .modal .form-control,
html body.stcd-wholesale .modal .form-select {
    border-color: #ccd1d6 !important;
    border-radius: 3px !important;
}

html body.stcd-wholesale .content-wrapper .form-control:focus,
html body.stcd-wholesale .content-wrapper .form-select:focus,
html body.stcd-wholesale .modal .form-control:focus,
html body.stcd-wholesale .modal .form-select:focus {
    border-color: var(--stcd-primary) !important;
    box-shadow: 0 0 0 2px rgba(var(--stcd-primary-rgb), .15) !important;
}

html.dark body.stcd-wholesale .content-wrapper .form-control,
html.dark body.stcd-wholesale .content-wrapper .form-select {
    border-color: var(--stcd-border) !important;
}

/* --------------------------------------------------------------------------
   8. BOUTON ROND « HAUT DE PAGE »
   -------------------------------------------------------------------------- */
.stcd-ws-haut { display: none; }

body.stcd-wholesale .stcd-ws-haut {
    position: fixed;
    right: 22px;
    bottom: 22px;
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    color: #fff;
    background: var(--stcd-primary-light);
    border: 0;
    border-radius: 50%;
    box-shadow: 0 3px 10px rgba(0, 0, 0, .2);
    opacity: 0;
    pointer-events: none;
    transform: translateY(10px);
    transition: opacity .2s, transform .2s;
    cursor: pointer;
}

body.stcd-wholesale .stcd-ws-haut.visible {
    opacity: 1;
    pointer-events: auto;
    transform: none;
}

body.stcd-wholesale .stcd-ws-haut:hover { background: var(--stcd-primary); }

/* les fenêtres de notification passent au-dessus du bouton */
body.stcd-wholesale #stcd-notif-fenetres { bottom: 76px !important; }

/* une référence ne se coupe jamais en plein milieu */
html body.stcd-wholesale .content-wrapper td.reference-cell,
html body.stcd-wholesale .content-wrapper td.reference-cell * {
    overflow-wrap: normal !important;
    word-break: keep-all !important;
    white-space: nowrap !important;
}
