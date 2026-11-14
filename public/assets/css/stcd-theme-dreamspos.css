/* ==========================================================================
   STCD MOTORS — DESIGN « DREAMSPOS » (V6)
   --------------------------------------------------------------------------
   Reprend l'apparence du template DreamsPOS (démo publique) : menu latéral
   blanc, texte bleu marine #092C4C, entrée active sur fond orange très léger
   avec texte orange #FE9F43, sous-menus à petits points, fond gris clair
   #F7F7F7, cartes blanches à bordure fine sans ombre, en-têtes de tableau
   gris clair, boutons orange, police Nunito, cartes du tableau de bord en
   couleurs (orange, bleu marine, vert d'eau, bleu).

   Écrit pour ce projet : aucun code ni image du template n'est repris,
   seulement les couleurs, la police et les proportions.
   Chargé APRÈS stcd-theme.css, actif avec la classe « stcd-dreamspos » sur
   <body> (choix du design : $stcdDesign dans layouts/layoutMaster).
   ========================================================================== */

/* --------------------------------------------------------------------------
   1. COULEURS
   -------------------------------------------------------------------------- */
body.stcd-dreamspos {
    --stcd-primary: #FE9F43;
    --stcd-primary-hover: #e88a2f;
    --stcd-primary-active: #d97a1f;
    --stcd-primary-light: #ffb46b;
    --stcd-primary-rgb: 254, 159, 67;
    --stcd-primary-50: #fff6ed;
    --stcd-primary-100: #ffe7cf;

    --dp-navy: #092C4C;
    --dp-gris: #646B72;
    --dp-bord: #E6EAED;
    --dp-teal: #0E9384;
    --dp-bleu: #155EEF;
    --dp-vert: #3EB780;
    --dp-rouge: #FF4D4F;
    --dp-jaune: #FFCA18;
    --dp-actif: rgba(254, 159, 67, .10);

    --stcd-bg: #F7F7F7;
    --stcd-surface: #ffffff;
    --stcd-surface-2: #F9FAFB;
    --stcd-border: #E6EAED;
    --stcd-text: #212B36;
    --stcd-text-2: #646B72;
    --stcd-muted: #8a9198;
    --stcd-font: 'Nunito', 'Segoe UI', Roboto, Arial, sans-serif;

    --bs-primary: var(--stcd-primary);
    --bs-primary-rgb: var(--stcd-primary-rgb);
    --bs-link-color: var(--stcd-primary);
    --bs-link-hover-color: var(--stcd-primary-hover);

    font-family: var(--stcd-font) !important;
}

html.dark body.stcd-dreamspos {
    --dp-navy: #D8DFEE;
    --dp-gris: #8a9198;
    --dp-bord: #2a2d33;
    --dp-actif: rgba(254, 159, 67, .14);
    --stcd-bg: #0D0D0D;
    --stcd-surface: #1d1d1d;
    --stcd-surface-2: #141414;
    --stcd-border: #2a2d33;
    --stcd-text: #f1f3f5;
    --stcd-text-2: #c3c8ce;
    --stcd-muted: #7A8086;
}

html body.stcd-dreamspos,
html body.stcd-dreamspos .layout-page,
html body.stcd-dreamspos .content-wrapper {
    background: var(--stcd-bg) !important;
}

html body.stcd-dreamspos h1, html body.stcd-dreamspos h2, html body.stcd-dreamspos h3,
html body.stcd-dreamspos h4, html body.stcd-dreamspos h5, html body.stcd-dreamspos h6,
html body.stcd-dreamspos .btn, html body.stcd-dreamspos input, html body.stcd-dreamspos select,
html body.stcd-dreamspos textarea, html body.stcd-dreamspos table {
    font-family: var(--stcd-font) !important;
}

/* --------------------------------------------------------------------------
   2. MENU LATÉRAL BLANC
   -------------------------------------------------------------------------- */
html body.stcd-dreamspos #layout-menu#layout-menu {
    background: var(--stcd-surface) !important;
    border-right: 1px solid var(--dp-bord) !important;
    box-shadow: none !important;
}

html body.stcd-dreamspos #layout-menu#layout-menu .stcd-sidebar-brand,
html body.stcd-dreamspos #layout-menu#layout-menu .app-brand {
    height: 64px !important;
    min-height: 64px !important;
    padding: 0 20px !important;
    background: var(--stcd-surface) !important;
    border-bottom: 1px solid var(--dp-bord) !important;
}

html body.stcd-dreamspos #layout-menu#layout-menu .stcd-brand-name {
    color: var(--dp-navy) !important;
    font-size: 17px !important;
    font-weight: 800 !important;
}

html body.stcd-dreamspos #layout-menu#layout-menu .stcd-brand-city { color: var(--dp-gris) !important; }

html body.stcd-dreamspos #layout-menu#layout-menu > .menu-inner { padding: 12px 0 !important; }

html body.stcd-dreamspos #layout-menu#layout-menu > .menu-inner::-webkit-scrollbar-thumb { background: #d5dade !important; }

/* titres de section : « Inventaire », « Ventes »… */
html body.stcd-dreamspos #layout-menu#layout-menu .menu-inner > .menu-header {
    margin: 16px 0 6px !important;
    padding: 0 22px !important;
}

html body.stcd-dreamspos #layout-menu#layout-menu .menu-inner > .menu-header .menu-header-text {
    color: var(--dp-navy) !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
}

/* liens */
html body.stcd-dreamspos #layout-menu#layout-menu .menu-inner > .menu-item > .menu-link {
    min-height: 40px !important;
    margin: 2px 14px !important;
    padding: 8px 12px !important;
    color: var(--dp-navy) !important;
    background: transparent !important;
    border-radius: 5px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
}

html body.stcd-dreamspos #layout-menu#layout-menu .menu-inner > .menu-item > .menu-link > div {
    font-size: 14px !important;
    font-weight: 600 !important;
}

html body.stcd-dreamspos #layout-menu#layout-menu .menu-icon {
    color: var(--dp-navy) !important;
    opacity: .85;
}

html body.stcd-dreamspos #layout-menu#layout-menu .menu-inner > .menu-item > .menu-link:hover,
html body.stcd-dreamspos #layout-menu#layout-menu .menu-inner > .menu-item.open > .menu-link,
html body.stcd-dreamspos #layout-menu#layout-menu .menu-inner > .menu-item.active > .menu-link {
    color: var(--stcd-primary) !important;
    background: var(--dp-actif) !important;
    box-shadow: none !important;
}

html body.stcd-dreamspos #layout-menu#layout-menu .menu-inner > .menu-item > .menu-link:hover .menu-icon,
html body.stcd-dreamspos #layout-menu#layout-menu .menu-inner > .menu-item.open > .menu-link .menu-icon,
html body.stcd-dreamspos #layout-menu#layout-menu .menu-inner > .menu-item.active > .menu-link .menu-icon {
    color: var(--stcd-primary) !important;
    opacity: 1;
}

html body.stcd-dreamspos #layout-menu#layout-menu .menu-link small,
html body.stcd-dreamspos #layout-menu#layout-menu .menu-link .menu-subtitle { color: var(--dp-gris) !important; }

/* sous-menus : petits points gris, actif en bleu marine avec point orange cerclé */
html body.stcd-dreamspos #layout-menu#layout-menu .menu-sub {
    margin: 2px 0 4px 0 !important;
    padding: 0 !important;
    background: transparent !important;
    border: 0 !important;
}

html body.stcd-dreamspos #layout-menu#layout-menu .menu-sub > .menu-item > .menu-link {
    margin: 0 14px !important;
    padding: 7px 12px 7px 42px !important;
    color: var(--dp-gris) !important;
    background: transparent !important;
    border-radius: 5px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    line-height: 18px !important;
}

html body.stcd-dreamspos #layout-menu#layout-menu .menu-sub > .menu-item > .menu-link > div {
    font-size: 14px !important;
    font-weight: inherit !important;
    line-height: 18px !important;
}

html body.stcd-dreamspos #layout-menu#layout-menu .menu-sub > .menu-item > .menu-link::before {
    left: 24px !important;
    width: 5px !important;
    height: 5px !important;
    background: #9aa1a7 !important;
    box-shadow: none !important;
}

html body.stcd-dreamspos #layout-menu#layout-menu .menu-sub > .menu-item > .menu-link:hover {
    color: var(--stcd-primary) !important;
    background: transparent !important;
}

html body.stcd-dreamspos #layout-menu#layout-menu .menu-sub > .menu-item.active > .menu-link {
    color: var(--dp-navy) !important;
    background: transparent !important;
    font-weight: 700 !important;
}

html body.stcd-dreamspos #layout-menu#layout-menu .menu-sub > .menu-item.active > .menu-link::before {
    width: 7px !important;
    height: 7px !important;
    background: var(--stcd-primary) !important;
    box-shadow: 0 0 0 3px rgba(254, 159, 67, .25) !important;
}

html body.stcd-dreamspos #layout-menu#layout-menu .menu-sub .menu-header { padding-left: 42px !important; }

/* bas du menu */
html body.stcd-dreamspos #layout-menu#layout-menu .stcd-sidebar-footer {
    background: var(--stcd-surface) !important;
    border-top: 1px solid var(--dp-bord) !important;
}

html body.stcd-dreamspos #layout-menu#layout-menu .stcd-sidebar-user-name { color: var(--dp-navy) !important; }
html body.stcd-dreamspos #layout-menu#layout-menu .stcd-sidebar-user-role,
html body.stcd-dreamspos #layout-menu#layout-menu .stcd-sidebar-version { color: var(--dp-gris) !important; }

html body.stcd-dreamspos #layout-menu#layout-menu .stcd-sidebar-avatar {
    color: #fff !important;
    background: var(--stcd-primary) !important;
}

html body.stcd-dreamspos #layout-menu#layout-menu .stcd-sidebar-icon-btn {
    color: var(--dp-gris) !important;
    border: 1px solid var(--dp-bord) !important;
    border-radius: 8px !important;
}

html body.stcd-dreamspos #layout-menu#layout-menu .stcd-sidebar-icon-btn:hover {
    color: var(--stcd-primary) !important;
    background: var(--dp-actif) !important;
}

/* menu réduit aux icônes par le bouton grille (ordinateur), comme DreamsPOS :
   76 px d'icônes ; il se déplie par-dessus la page au survol. */
@media (min-width: 1200px) {
    html body.stcd-dreamspos.stcd-menu-ferme #layout-menu#layout-menu {
        width: 76px !important;
        min-width: 76px !important;
        max-width: 76px !important;
        transform: none !important;
        overflow: hidden !important;
        z-index: 1100 !important;
    }

    html body.stcd-dreamspos.stcd-menu-ferme #layout-menu#layout-menu:hover {
        width: 256px !important;
        min-width: 256px !important;
        max-width: 256px !important;
        box-shadow: 8px 0 30px rgba(9, 44, 76, .12) !important;
    }

    html body.stcd-dreamspos.stcd-menu-ferme .layout-page {
        width: calc(100% - 76px) !important;
        max-width: calc(100% - 76px) !important;
        margin-left: 76px !important;
    }

    html body.stcd-dreamspos.stcd-menu-ferme .layout-page > .layout-navbar { left: 76px !important; }

    /* réduit : on ne garde que les icônes */
    html body.stcd-dreamspos.stcd-menu-ferme #layout-menu#layout-menu:not(:hover) :is(
        .stcd-brand-text,
        .menu-inner > .menu-item > .menu-link > div,
        .menu-inner > .menu-item > .menu-link > .badge,
        .menu-header,
        .menu-sub,
        .stcd-sidebar-footer
    ) { display: none !important; }

    html body.stcd-dreamspos.stcd-menu-ferme #layout-menu#layout-menu:not(:hover) .menu-link.menu-toggle::after { display: none !important; }

    html body.stcd-dreamspos.stcd-menu-ferme #layout-menu#layout-menu:not(:hover) .menu-inner > .menu-item > .menu-link {
        justify-content: center !important;
        margin: 3px 12px !important;
        padding: 10px 0 !important;
    }

    html body.stcd-dreamspos.stcd-menu-ferme #layout-menu#layout-menu:not(:hover) .stcd-sidebar-brand {
        justify-content: center !important;
        padding: 0 !important;
    }

    html body.stcd-dreamspos .layout-page,
    html body.stcd-dreamspos .layout-page > .layout-navbar,
    html body.stcd-dreamspos #layout-menu#layout-menu {
        transition: margin-left .25s ease, left .25s ease, width .25s ease, min-width .25s ease, max-width .25s ease, box-shadow .25s ease !important;
    }
}

/* --------------------------------------------------------------------------
   3. BARRE DU HAUT
   -------------------------------------------------------------------------- */
html body.stcd-dreamspos .layout-page > .layout-navbar {
    min-height: 64px !important;
    padding: 10px 24px !important;
    background: var(--stcd-surface) !important;
    border-bottom: 1px solid var(--dp-bord) !important;
    box-shadow: none !important;
}

html body.stcd-dreamspos .layout-navbar .layout-menu-toggle {
    display: flex !important;
    align-items: center;
    margin-right: 16px !important;
}

html body.stcd-dreamspos .layout-navbar .stcd-ws-grille {
    display: flex !important;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    padding: 0 !important;
    color: var(--dp-gris) !important;
    border: 1px solid var(--dp-bord);
    border-radius: 8px;
}

html body.stcd-dreamspos .layout-navbar .stcd-ws-grille svg { width: 16px; height: 16px; }

html body.stcd-dreamspos .layout-navbar .stcd-ws-grille:hover {
    color: var(--stcd-primary) !important;
    border-color: var(--stcd-primary);
}

html body.stcd-dreamspos #stcd-topbar-title {
    color: var(--dp-navy) !important;
    font-size: 18px !important;
    font-weight: 800 !important;
}

html body.stcd-dreamspos #stcd-topbar-subtitle {
    color: var(--dp-gris) !important;
    font-size: 13px !important;
}

html body.stcd-dreamspos .stcd-notification-button {
    width: 36px !important;
    height: 36px !important;
    color: var(--dp-gris) !important;
    background: var(--stcd-surface) !important;
    border: 1px solid var(--dp-bord) !important;
    border-radius: 8px !important;
}

html body.stcd-dreamspos .stcd-notification-button:hover {
    color: var(--stcd-primary) !important;
    border-color: var(--stcd-primary) !important;
}

html body.stcd-dreamspos .stcd-notification-badge {
    background: var(--stcd-primary) !important;
    color: #fff !important;
}

/* --------------------------------------------------------------------------
   4. CARTES : blanches, bordure fine, sans ombre, coins 8 px
   -------------------------------------------------------------------------- */
html body.stcd-dreamspos .content-wrapper .card,
html body.stcd-dreamspos .content-wrapper [class$="-card"],
html body.stcd-dreamspos .content-wrapper [class*="-card "] {
    border: 1px solid var(--dp-bord) !important;
    border-radius: 8px !important;
    box-shadow: none !important;
}

html body.stcd-dreamspos .content-wrapper .card-header {
    background: transparent !important;
    border-bottom: 1px solid var(--dp-bord) !important;
}

html body.stcd-dreamspos .content-wrapper .card-header h5,
html body.stcd-dreamspos .content-wrapper .card-header .card-title {
    color: var(--dp-navy) !important;
    font-size: 16px !important;
    font-weight: 700 !important;
}

/* tableau de bord : les 4 premières cartes en couleurs, comme DreamsPOS */
html body.stcd-dreamspos .dashboard-section:first-of-type .row > div:nth-child(-n+4) .dashboard-card {
    border: 0 !important;
    color: #fff !important;
}

html body.stcd-dreamspos .dashboard-section:first-of-type .row > div:nth-child(1) .dashboard-card { background: var(--stcd-primary) !important; }
html body.stcd-dreamspos .dashboard-section:first-of-type .row > div:nth-child(2) .dashboard-card { background: #092C4C !important; }
html body.stcd-dreamspos .dashboard-section:first-of-type .row > div:nth-child(3) .dashboard-card { background: var(--dp-teal) !important; }
html body.stcd-dreamspos .dashboard-section:first-of-type .row > div:nth-child(4) .dashboard-card { background: var(--dp-bleu) !important; }

html body.stcd-dreamspos .dashboard-section:first-of-type .row > div:nth-child(-n+4) .dashboard-card :is(.dashboard-label, .dashboard-value, .dashboard-helper) {
    color: #fff !important;
}

html body.stcd-dreamspos .dashboard-section:first-of-type .row > div:nth-child(-n+4) .dashboard-card .dashboard-icon {
    color: #fff !important;
    background: rgba(255, 255, 255, .18) !important;
}

html body.stcd-dreamspos .dashboard-section:first-of-type .row > div:nth-child(-n+4) .dashboard-card .dashboard-icon i { color: #fff !important; }

/* --------------------------------------------------------------------------
   5. TABLEAUX : en-tête gris clair, lignes séparées, sans traits verticaux
   -------------------------------------------------------------------------- */
html body.stcd-dreamspos .content-wrapper table.table > thead > tr > th,
html body.stcd-dreamspos .content-wrapper table[class*="-table"] > thead > tr > th {
    padding: 12px 12px !important;
    color: #212B36 !important;
    background: #E6EAED !important;
    border: 0 !important;
    font-size: 13.5px !important;
    font-weight: 700 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
}

html.dark body.stcd-dreamspos .content-wrapper table.table > thead > tr > th,
html.dark body.stcd-dreamspos .content-wrapper table[class*="-table"] > thead > tr > th {
    color: #f1f3f5 !important;
    background: #262626 !important;
}

html body.stcd-dreamspos .content-wrapper table.table > tbody > tr > td,
html body.stcd-dreamspos .content-wrapper table[class*="-table"] > tbody > tr > td {
    border-right: 0 !important;
    border-left: 0 !important;
    border-bottom: 1px solid var(--dp-bord) !important;
}

html body.stcd-dreamspos .content-wrapper table.table > tbody > tr:hover > td,
html body.stcd-dreamspos .content-wrapper table[class*="-table"] > tbody > tr:hover > td {
    background-color: var(--stcd-surface-2);
}

html.dark body.stcd-dreamspos .content-wrapper table.table > tbody > tr:hover > td,
html.dark body.stcd-dreamspos .content-wrapper table[class*="-table"] > tbody > tr:hover > td {
    background-color: rgba(255, 255, 255, .03);
}

/* --------------------------------------------------------------------------
   6. BOUTONS
   -------------------------------------------------------------------------- */
html body.stcd-dreamspos .content-wrapper .btn,
html body.stcd-dreamspos .modal .btn {
    border-radius: 5px !important;
    box-shadow: none !important;
    font-weight: 700;
}

html body.stcd-dreamspos .btn-primary {
    --bs-btn-bg: var(--stcd-primary);
    --bs-btn-border-color: var(--stcd-primary);
    --bs-btn-hover-bg: var(--stcd-primary-hover);
    --bs-btn-hover-border-color: var(--stcd-primary-hover);
    color: #fff !important;
    background-color: var(--stcd-primary) !important;
    border-color: var(--stcd-primary) !important;
}

html body.stcd-dreamspos .btn-primary:hover,
html body.stcd-dreamspos .btn-primary:focus {
    background-color: var(--stcd-primary-hover) !important;
    border-color: var(--stcd-primary-hover) !important;
}

html body.stcd-dreamspos .btn-dark,
html body.stcd-dreamspos .btn-secondary:not(.btn-outline-secondary) {
    color: #fff !important;
    background-color: #092C4C !important;
    border-color: #092C4C !important;
}

html body.stcd-dreamspos .btn-success {
    color: #fff !important;
    background-color: var(--dp-vert) !important;
    border-color: var(--dp-vert) !important;
}

html body.stcd-dreamspos .btn-info {
    color: #fff !important;
    background-color: var(--dp-bleu) !important;
    border-color: var(--dp-bleu) !important;
}

html body.stcd-dreamspos .btn-warning {
    color: #212B36 !important;
    background-color: var(--dp-jaune) !important;
    border-color: var(--dp-jaune) !important;
}

html body.stcd-dreamspos .btn-danger {
    color: #fff !important;
    background-color: var(--dp-rouge) !important;
    border-color: var(--dp-rouge) !important;
}

html body.stcd-dreamspos .btn-light,
html body.stcd-dreamspos .btn-outline-secondary {
    color: var(--dp-navy) !important;
    background-color: var(--stcd-surface) !important;
    border-color: var(--dp-bord) !important;
}

/* --------------------------------------------------------------------------
   7. CHAMPS
   -------------------------------------------------------------------------- */
html body.stcd-dreamspos .content-wrapper .form-control,
html body.stcd-dreamspos .content-wrapper .form-select,
html body.stcd-dreamspos .modal .form-control,
html body.stcd-dreamspos .modal .form-select {
    border-color: var(--dp-bord) !important;
    border-radius: 5px !important;
}

html body.stcd-dreamspos .content-wrapper .form-control:focus,
html body.stcd-dreamspos .content-wrapper .form-select:focus,
html body.stcd-dreamspos .modal .form-control:focus,
html body.stcd-dreamspos .modal .form-select:focus {
    border-color: var(--stcd-primary) !important;
    box-shadow: 0 0 0 2px rgba(var(--stcd-primary-rgb), .15) !important;
}

html body.stcd-dreamspos .content-wrapper label,
html body.stcd-dreamspos .content-wrapper .form-label {
    color: #212B36;
    font-weight: 600;
}

html.dark body.stcd-dreamspos .content-wrapper label,
html.dark body.stcd-dreamspos .content-wrapper .form-label { color: var(--stcd-text); }

/* --------------------------------------------------------------------------
   8. BADGES et liens
   -------------------------------------------------------------------------- */
html body.stcd-dreamspos .content-wrapper td a:not(.btn) { color: var(--stcd-primary) !important; font-weight: 600; }

html body.stcd-dreamspos .badge.bg-primary { background-color: var(--stcd-primary) !important; }

html body.stcd-dreamspos .content-wrapper td.reference-cell,
html body.stcd-dreamspos .content-wrapper td.reference-cell * {
    overflow-wrap: normal !important;
    word-break: keep-all !important;
    white-space: nowrap !important;
}

/* ==========================================================================
   9. ANIMATIONS (moderne, discret)
   Coupées si l'ordinateur demande moins d'animations (voir fin du fichier).
   ========================================================================== */
@keyframes dp-monter {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: none; }
}

@keyframes dp-fondu {
    from { opacity: 0; }
    to   { opacity: 1; }
}

@keyframes dp-zoom {
    from { opacity: 0; transform: scale(.94) translateY(8px); }
    to   { opacity: 1; transform: none; }
}

@keyframes dp-pulse {
    0%   { box-shadow: 0 0 0 0 rgba(254, 159, 67, .55); }
    70%  { box-shadow: 0 0 0 8px rgba(254, 159, 67, 0); }
    100% { box-shadow: 0 0 0 0 rgba(254, 159, 67, 0); }
}

@keyframes dp-onde {
    to { opacity: 0; transform: scale(2.6); }
}

/* contenu de la page : apparition en cascade */
html body.stcd-dreamspos .content-wrapper > .container-xxl > * {
    animation: dp-monter .5s cubic-bezier(.2, .7, .2, 1) both;
}

html body.stcd-dreamspos .content-wrapper > .container-xxl > *:nth-child(2) { animation-delay: .06s; }
html body.stcd-dreamspos .content-wrapper > .container-xxl > *:nth-child(3) { animation-delay: .12s; }
html body.stcd-dreamspos .content-wrapper > .container-xxl > *:nth-child(n+4) { animation-delay: .18s; }

/* cartes en grille (tableau de bord…) : une par une */
html body.stcd-dreamspos .content-wrapper .row > [class*="col"] {
    animation: dp-monter .5s cubic-bezier(.2, .7, .2, 1) both;
}

html body.stcd-dreamspos .content-wrapper .row > [class*="col"]:nth-child(2) { animation-delay: .05s; }
html body.stcd-dreamspos .content-wrapper .row > [class*="col"]:nth-child(3) { animation-delay: .10s; }
html body.stcd-dreamspos .content-wrapper .row > [class*="col"]:nth-child(4) { animation-delay: .15s; }
html body.stcd-dreamspos .content-wrapper .row > [class*="col"]:nth-child(n+5) { animation-delay: .20s; }

/* lignes des tableaux : fondu léger */
html body.stcd-dreamspos .content-wrapper table > tbody > tr {
    animation: dp-fondu .4s ease both;
}

html body.stcd-dreamspos .content-wrapper table > tbody > tr:nth-child(n+2)  { animation-delay: .03s; }
html body.stcd-dreamspos .content-wrapper table > tbody > tr:nth-child(n+4)  { animation-delay: .06s; }
html body.stcd-dreamspos .content-wrapper table > tbody > tr:nth-child(n+7)  { animation-delay: .09s; }
html body.stcd-dreamspos .content-wrapper table > tbody > tr:nth-child(n+10) { animation-delay: .12s; }

/* cartes : se soulèvent au survol */
html body.stcd-dreamspos .content-wrapper .row > [class*="col"] > .card,
html body.stcd-dreamspos .dashboard-card {
    transition: transform .25s cubic-bezier(.2, .7, .2, 1), box-shadow .25s ease !important;
}

html body.stcd-dreamspos .content-wrapper .row > [class*="col"] > .card:hover,
html body.stcd-dreamspos .dashboard-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(9, 44, 76, .10) !important;
}

html body.stcd-dreamspos .dashboard-card .dashboard-icon {
    transition: transform .3s cubic-bezier(.2, .7, .2, 1);
}

html body.stcd-dreamspos .dashboard-card:hover .dashboard-icon { transform: scale(1.1) rotate(-6deg); }

/* boutons : léger relief au survol, enfoncement au clic, onde */
html body.stcd-dreamspos .btn {
    position: relative;
    overflow: hidden;
    transition: transform .15s ease, box-shadow .2s ease, background-color .2s ease, color .2s ease !important;
}

html body.stcd-dreamspos .btn:not(:disabled):hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(9, 44, 76, .12) !important;
}

html body.stcd-dreamspos .btn:not(:disabled):active { transform: translateY(0) scale(.97); }

.dp-onde {
    position: absolute;
    width: 40px;
    height: 40px;
    margin: -20px 0 0 -20px;
    background: rgba(255, 255, 255, .45);
    border-radius: 50%;
    pointer-events: none;
    animation: dp-onde .55s ease-out forwards;
}

/* menu : transitions douces, l'icône glisse au survol */
html body.stcd-dreamspos #layout-menu#layout-menu .menu-link,
html body.stcd-dreamspos #layout-menu#layout-menu .menu-icon {
    transition: color .2s ease, background-color .2s ease, transform .2s ease !important;
}

html body.stcd-dreamspos #layout-menu#layout-menu .menu-inner > .menu-item > .menu-link:hover .menu-icon {
    transform: translateX(3px);
}

html body.stcd-dreamspos #layout-menu#layout-menu .menu-sub > .menu-item > .menu-link:hover {
    transform: translateX(3px);
}

/* barre du haut légèrement translucide */
html body.stcd-dreamspos .layout-page > .layout-navbar {
    background: rgba(255, 255, 255, .88) !important;
    backdrop-filter: saturate(160%) blur(10px) !important;
}

html.dark body.stcd-dreamspos .layout-page > .layout-navbar { background: rgba(29, 29, 29, .88) !important; }

/* cloche : pulse quand il y a des notifications non lues */
html body.stcd-dreamspos .stcd-notification-badge:not(.est-vide) {
    animation: dp-pulse 2s infinite;
}

/* champs : halo orange qui apparaît en douceur */
html body.stcd-dreamspos .form-control,
html body.stcd-dreamspos .form-select {
    transition: border-color .2s ease, box-shadow .2s ease !important;
}

/* fenêtres (modales, menus déroulants) : ouverture en zoom */
html body.stcd-dreamspos .modal.show .modal-dialog { animation: dp-zoom .28s cubic-bezier(.2, .7, .2, 1); }
html body.stcd-dreamspos .dropdown-menu.show { animation: dp-zoom .2s ease; }

/* liens des tableaux : soulignement animé */
html body.stcd-dreamspos .content-wrapper td a:not(.btn) {
    background-image: linear-gradient(currentColor, currentColor);
    background-position: 0 100%;
    background-repeat: no-repeat;
    background-size: 0 1px;
    transition: background-size .25s ease;
}

html body.stcd-dreamspos .content-wrapper td a:not(.btn):hover { background-size: 100% 1px; }

/* moins d'animations demandé par l'ordinateur : tout est coupé */
@media (prefers-reduced-motion: reduce) {
    html body.stcd-dreamspos *,
    html body.stcd-dreamspos *::before,
    html body.stcd-dreamspos *::after {
        animation: none !important;
        transition: none !important;
    }
}

/* ==========================================================================
   10. BARRE DU HAUT RESTRUCTURÉE (recherche, + Ajouter, plein écran, profil)
   ========================================================================== */
html body.stcd-dreamspos .layout-page > .layout-navbar {
    gap: 12px;
    min-height: 64px !important;
    padding: 0 24px !important;
}

/* recherche globale */
html body.stcd-dreamspos .dp-recherche {
    position: relative;
    display: flex;
    flex: 0 1 420px;
    align-items: center;
    gap: 8px;
    height: 40px;
    padding: 0 12px;
    color: var(--dp-gris);
    background: var(--stcd-surface-2);
    border: 1px solid var(--dp-bord);
    border-radius: 8px;
    transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
}

html body.stcd-dreamspos .dp-recherche:focus-within {
    background: var(--stcd-surface);
    border-color: var(--stcd-primary);
    box-shadow: 0 0 0 3px rgba(var(--stcd-primary-rgb), .15);
}

html body.stcd-dreamspos .dp-recherche input {
    flex: 1;
    min-width: 0;
    height: 100%;
    color: var(--stcd-text);
    background: transparent;
    border: 0;
    outline: 0;
    font-size: 14px;
}

html body.stcd-dreamspos .dp-recherche input::-webkit-search-cancel-button { cursor: pointer; }

html body.stcd-dreamspos .dp-recherche kbd {
    padding: 2px 6px;
    color: var(--dp-gris);
    background: var(--stcd-surface);
    border: 1px solid var(--dp-bord);
    border-radius: 4px;
    box-shadow: none;
    font-family: var(--stcd-font);
    font-size: 11px;
    white-space: nowrap;
}

html body.stcd-dreamspos .dp-recherche-resultats {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    z-index: 1100;
    width: max(100%, 440px);

    max-height: 70vh;
    overflow-y: auto;
    padding: 6px;
    background: var(--stcd-surface);
    border: 1px solid var(--dp-bord);
    border-radius: 10px;
    box-shadow: 0 18px 40px rgba(9, 44, 76, .14);
    animation: dp-zoom .18s ease;
}

html body.stcd-dreamspos .dp-recherche-groupe {
    padding: 10px 10px 4px;
    color: var(--dp-gris);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .05em;
    text-transform: uppercase;
}

html body.stcd-dreamspos a.dp-resultat {
    display: flex;
    flex-direction: column;
    padding: 8px 10px;
    color: var(--stcd-text) !important;
    border-radius: 6px;
    transition: background-color .15s ease;
}

html body.stcd-dreamspos a.dp-resultat strong { font-size: 14px; }
html body.stcd-dreamspos a.dp-resultat small { color: var(--dp-gris); font-size: 12px; }

html body.stcd-dreamspos a.dp-resultat:hover,
html body.stcd-dreamspos a.dp-resultat.actif { background: var(--dp-actif); }

html body.stcd-dreamspos a.dp-resultat.actif strong { color: var(--stcd-primary); }

html body.stcd-dreamspos .dp-recherche-info {
    padding: 14px 10px;
    color: var(--dp-gris);
    font-size: 13px;
}

/* bouton orange + Ajouter */
html body.stcd-dreamspos .dp-btn-ajouter {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 38px;
    padding: 0 14px;
    color: #fff !important;
    background: var(--stcd-primary);
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    transition: background-color .2s ease, transform .15s ease, box-shadow .2s ease;
}

html body.stcd-dreamspos .dp-btn-ajouter:hover {
    background: var(--stcd-primary-hover);
    box-shadow: 0 6px 14px rgba(var(--stcd-primary-rgb), .35);
    transform: translateY(-1px);
}

html body.stcd-dreamspos .dp-ajouter-menu {
    display: none;
    width: 340px;
    padding: 12px;
    border: 1px solid var(--dp-bord) !important;
    border-radius: 12px !important;
    box-shadow: 0 18px 40px rgba(9, 44, 76, .14) !important;
}

html body.stcd-dreamspos .dp-ajouter-menu.show {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
}

html body.stcd-dreamspos .dp-ajouter-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 12px 6px;
    color: var(--dp-navy) !important;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 600;
    text-align: center;
    transition: background-color .15s ease, transform .15s ease;
}

html body.stcd-dreamspos .dp-ajouter-item:hover {
    background: var(--dp-actif);
    transform: translateY(-2px);
}

html body.stcd-dreamspos .dp-ajouter-icone {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    color: var(--stcd-primary);
    background: var(--dp-actif);
    border-radius: 10px;
}

/* boutons ronds-carrés (plein écran) */
html body.stcd-dreamspos .dp-icone-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    color: var(--dp-gris) !important;
    border: 1px solid var(--dp-bord);
    border-radius: 8px;
    transition: color .2s ease, border-color .2s ease;
}

html body.stcd-dreamspos .dp-icone-btn:hover {
    color: var(--stcd-primary) !important;
    border-color: var(--stcd-primary);
}

/* profil */
html body.stcd-dreamspos .dp-profil-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 4px 6px 4px 4px;
    border-radius: 10px;
    transition: background-color .15s ease;
}

html body.stcd-dreamspos .dp-profil-btn:hover { background: var(--stcd-surface-2); }

html body.stcd-dreamspos .dp-avatar {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    color: #fff;
    background: linear-gradient(135deg, #FE9F43, #ff7a45);
    border-radius: 50%;
    font-size: 13px;
    font-weight: 800;
}

html body.stcd-dreamspos .dp-avatar-grand { width: 44px; height: 44px; font-size: 15px; }

html body.stcd-dreamspos .dp-profil-texte {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}

html body.stcd-dreamspos .dp-profil-texte strong { color: var(--dp-navy); font-size: 13.5px; }
html body.stcd-dreamspos .dp-profil-texte small { color: var(--dp-gris); font-size: 12px; }

html body.stcd-dreamspos .dp-profil-menu {
    width: 250px;
    padding: 8px !important;
    border: 1px solid var(--dp-bord) !important;
    border-radius: 12px !important;
    box-shadow: 0 18px 40px rgba(9, 44, 76, .14) !important;
}

html body.stcd-dreamspos .dp-profil-entete {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 8px 12px;
    margin-bottom: 6px;
    border-bottom: 1px solid var(--dp-bord);
}

html body.stcd-dreamspos .dp-profil-entete div { display: flex; flex-direction: column; min-width: 0; }
html body.stcd-dreamspos .dp-profil-entete strong { color: var(--dp-navy); font-size: 14px; }
html body.stcd-dreamspos .dp-profil-entete small { overflow: hidden; color: var(--dp-gris); font-size: 12px; text-overflow: ellipsis; }

html body.stcd-dreamspos .dp-profil-menu .dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    color: var(--dp-navy);
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
}

html body.stcd-dreamspos .dp-profil-menu .dropdown-item:hover { color: var(--stcd-primary); background: var(--dp-actif); }
html body.stcd-dreamspos .dp-profil-menu .dp-deconnexion { color: var(--dp-rouge); }

/* le menu latéral n'a plus besoin du bloc utilisateur (il est en haut) */
html body.stcd-dreamspos #layout-menu#layout-menu .stcd-sidebar-user,
html body.stcd-dreamspos #layout-menu#layout-menu .stcd-sidebar-icon-btn { display: none !important; }

html body.stcd-dreamspos #layout-menu#layout-menu .stcd-sidebar-footer {
    justify-content: center !important;
    padding: 10px !important;
}

/* petits écrans : on garde l'essentiel */
@media (max-width: 991.98px) {
    html body.stcd-dreamspos .dp-recherche kbd,
    html body.stcd-dreamspos .dp-profil-texte,
    html body.stcd-dreamspos .dp-btn-ajouter span,
    html body.stcd-dreamspos .dp-plein-ecran { display: none !important; }
}

@media (max-width: 575.98px) {
    html body.stcd-dreamspos .dp-recherche { display: none !important; }
}

/* ==========================================================================
   11. EN-TÊTE DE PAGE dans le contenu (titre, sous-titre, fil d'Ariane)
   Le bloc du titre est déplacé de la barre du haut vers le haut du contenu
   (layoutMaster) : la barre du haut garde la recherche.
   ========================================================================== */
html body.stcd-dreamspos .dp-entete-page {
    display: flex !important;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px 16px;
    margin: 0 0 22px !important;
    padding: 0 !important;
}

html body.stcd-dreamspos .dp-entete-page .stcd-ws-titre-icone { display: none !important; }

html body.stcd-dreamspos .dp-entete-page #stcd-topbar-title {
    margin: 0 !important;
    color: var(--dp-navy) !important;
    font-size: 22px !important;
    font-weight: 800 !important;
    line-height: 1.25 !important;
}

html body.stcd-dreamspos .dp-entete-page #stcd-topbar-subtitle {
    margin-top: 2px;
    color: var(--dp-gris) !important;
    font-size: 14px !important;
}

html body.stcd-dreamspos .dp-entete-page .stcd-ws-fil {
    display: flex !important;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    margin-left: auto;
    color: var(--dp-gris);
    font-size: 13px;
}

html body.stcd-dreamspos .dp-entete-page .stcd-ws-fil a {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--dp-gris) !important;
}

html body.stcd-dreamspos .dp-entete-page .stcd-ws-fil a:hover { color: var(--stcd-primary) !important; }
html body.stcd-dreamspos .dp-entete-page .stcd-ws-fil-sep { color: #b8bec4; }
html body.stcd-dreamspos .dp-entete-page .stcd-ws-fil-actif { color: var(--stcd-primary) !important; font-weight: 700; }

@media (max-width: 767.98px) {
    html body.stcd-dreamspos .dp-entete-page .stcd-ws-fil { display: none !important; }
}

/* le contenu commence juste sous la barre du haut (64 px) */
html body.stcd-dreamspos .layout-page > .content-wrapper { padding-top: 64px !important; }
