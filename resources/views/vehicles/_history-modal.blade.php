{{-- ============================================================= --}}
{{-- POPUP GLOBAL : TRAÇABILITÉ PAR IMMATRICULATION                  --}}
{{-- La route existante applique toujours ses contrôles d'accès.    --}}
{{-- Seuls la carte et ses styles sont repris, sans second layout.  --}}
{{-- ============================================================= --}}
{{-- La barre latérale utilise z-index: 2000 : placer le popup au-dessus. --}}
<style>
    #vehicleHistoryModal { z-index: 2100 !important; }
    .modal-backdrop.vehicle-history-backdrop { z-index: 2090 !important; }

    /* Fenêtre centrée, bords arrondis et largeur adaptée à l'écran. */
    #vehicleHistoryModal .modal-dialog {
        width: calc(100% - 40px); max-width: 1240px;
        margin: 20px auto; min-height: calc(100% - 40px);
    }
    #vehicleHistoryModal .modal-content {
        max-height: calc(100dvh - 40px); border: 0; border-radius: 18px;
        overflow: hidden; box-shadow: 0 24px 80px rgba(15,23,42,.25);
        font-family: inherit;
    }
    #vehicleHistoryModal .modal-header {
        padding: 20px 24px; gap: 16px; border-bottom: 1px solid #e9edf5;
        background: linear-gradient(120deg, #f0efff, #f8faff);
    }
    #vehicleHistoryModal .history-modal-heading { display: flex; align-items: center; gap: 12px; min-width: 0; }
    #vehicleHistoryModal .history-modal-icon {
        display: flex; align-items: center; justify-content: center;
        width: 44px; height: 44px; flex: 0 0 44px; border-radius: 12px;
        color: #fff; background: #6965db; font-size: 24px;
    }
    #vehicleHistoryModal .modal-title { font-family: inherit; font-size: 1.1rem; font-weight: 700; color: #27324b; margin: 0; }
    #vehicleHistoryModal .history-modal-subtitle { margin: 4px 0 0; color: #69758b; font-size: .82rem; }
    #vehicleHistoryModal .btn-close { position: static; margin: 0; flex-shrink: 0; }
    #vehicleHistoryModal .modal-body { padding: 22px; overflow-y: auto; background: #fff; }
    #vehicleHistoryModal .modal-footer { padding: 12px 24px; background: #f8f9fc; border-top: 1px solid #e9edf5; }
    #vehicleHistoryModal .modal-footer .btn { border-radius: 8px; padding: 8px 20px; }
    #vehicleHistoryModal #vehicle-history-page { box-shadow: none !important; border: 0; }
    #vehicleHistoryModal #vehicle-history-page > .card-body { padding: 0; }

    /* Quatre champs réguliers ; les actions ont leur propre ligne. */
    #vehicleHistoryModal #vehicle-history-page .history-filters {
        display: grid; grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px 14px; margin: 0 0 18px; padding: 20px;
        border: 1px solid #e5e9f2; border-radius: 12px; background: #fafbfe;
    }
    #vehicleHistoryModal #vehicle-history-page .form-label {
        color: #47536b; font-size: .78rem; font-weight: 600; margin-bottom: 8px;
        text-transform: none; letter-spacing: 0;
    }
    #vehicleHistoryModal #vehicle-history-page .form-control,
    #vehicleHistoryModal #vehicle-history-page .form-select {
        height: 44px; min-height: 44px; border-radius: 8px; border-color: #d9dfeb;
        font-size: .86rem; padding-left: 12px; background-color: #fff;
    }
    #vehicleHistoryModal #vehicle-history-page .form-control:focus,
    #vehicleHistoryModal #vehicle-history-page .form-select:focus {
        border-color: #8179ed; box-shadow: 0 0 0 3px rgba(105,101,219,.12);
    }
    #vehicleHistoryModal #vehicle-history-page .history-filter-buttons {
        grid-column: 1 / -1; padding-top: 14px; border-top: 1px solid #e7eaf2;
    }
    #vehicleHistoryModal #vehicle-history-page .history-filter-actions {
        display: flex; justify-content: flex-end; gap: 10px;
    }
    #vehicleHistoryModal #vehicle-history-page .history-filter-actions .btn {
        width: 150px; flex: 0 0 150px; height: 40px; border-radius: 8px; font-size: .82rem;
    }
    #vehicleHistoryModal #vehicle-history-page .history-filter-actions .btn-primary {
        background: #6965db; border-color: #6965db; box-shadow: 0 3px 8px rgba(105,101,219,.18);
    }
    #vehicleHistoryModal #vehicle-history-page .history-filter-actions .btn-primary:hover { background: #5652c3; }

    /* Deux champs par ligne sur tablette, puis un seul sur téléphone. */
    @media (max-width: 850px) {
        #vehicleHistoryModal #vehicle-history-page .history-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 575.98px) {
        #vehicleHistoryModal .modal-dialog { width: calc(100% - 16px); margin: 8px auto; min-height: calc(100% - 16px); }
        #vehicleHistoryModal .modal-content { max-height: calc(100dvh - 16px); border-radius: 12px; }
        #vehicleHistoryModal .modal-header { padding: 16px; }
        #vehicleHistoryModal .modal-body { padding: 12px; }
        #vehicleHistoryModal .modal-title { font-size: .95rem; }
        #vehicleHistoryModal .history-modal-subtitle { font-size: .75rem; }
        #vehicleHistoryModal #vehicle-history-page .history-filters { grid-template-columns: minmax(0, 1fr); padding: 14px; gap: 14px; }
        #vehicleHistoryModal #vehicle-history-page .history-filter-actions .btn { width: auto; min-width: 0; flex: 1 1 0; padding: 6px; font-size: .75rem; }
    }
</style>

<div class="modal fade" id="vehicleHistoryModal" tabindex="-1" aria-labelledby="vehicleHistoryModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="history-modal-heading">
                    <span class="history-modal-icon" aria-hidden="true"><i class="bx bx-car"></i></span>
                    <div>
                        <h5 class="modal-title" id="vehicleHistoryModalTitle">Traçabilité par immatriculation</h5>
                        <p class="history-modal-subtitle">Retrouvez les pièces vendues et les factures du véhicule.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>

            <div class="modal-body" id="vehicleHistoryModalBody" aria-live="polite"></div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const modalElement = document.getElementById('vehicleHistoryModal');
    const content = document.getElementById('vehicleHistoryModalBody');
    const historyUrl = @js(route('vehicles.history'));
    let pendingRequest = null;

    // Charger la vue Laravel existante : aucun calcul n'est recopié en JavaScript.
    async function loadHistory(url) {
        if (pendingRequest) pendingRequest.abort();
        const controller = new AbortController();
        pendingRequest = controller;
        content.setAttribute('aria-busy', 'true');
        let loading = content.querySelector('[data-history-loading]');
        if (!loading) {
            loading = document.createElement('div');
            loading.dataset.historyLoading = 'true';
            loading.className = 'alert alert-info py-2';
            loading.textContent = 'Chargement de la traçabilité…';
            content.prepend(loading);
        }

        try {
            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: { 'Accept': 'text/html' },
                signal: controller.signal
            });
            if (!response.ok) throw new Error('Réponse indisponible');

            const documentResponse = new DOMParser().parseFromString(await response.text(), 'text/html');
            const card = documentResponse.getElementById('vehicle-history-page');
            if (!card) throw new Error('Page indisponible ou session expirée');

            // Ne pas réexécuter les scripts du menu et du layout reçus dans la réponse.
            card.querySelectorAll('script').forEach(script => script.remove());
            // Le titre est déjà présent dans l'en-tête du popup.
            card.querySelector(':scope > .card-header')?.remove();
            const fragment = document.createDocumentFragment();
            documentResponse.querySelectorAll('style').forEach(style => {
                if (style.textContent.includes('#vehicle-history-page')) fragment.append(style.cloneNode(true));
            });
            fragment.append(card);
            content.replaceChildren(fragment);
            content.scrollTop = 0;
            card.querySelector('[name="plate"]')?.focus();
        } catch (error) {
            if (error.name === 'AbortError') return;
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger';
            alert.setAttribute('role', 'alert');
            alert.textContent = 'Impossible de charger la traçabilité. Vérifiez votre connexion ou votre session. ';
            const fallback = document.createElement('a');
            fallback.href = url;
            fallback.textContent = 'Ouvrir la page de traçabilité';
            alert.append(fallback);
            content.prepend(alert);
        } finally {
            if (pendingRequest === controller) {
                pendingRequest = null;
                content.removeAttribute('aria-busy');
                content.querySelector('[data-history-loading]')?.remove();
            }
        }
    }

    // Intercepter uniquement le lien explicitement marqué dans le sidebar.
    // Ctrl/clic et l'ouverture dans un nouvel onglet restent disponibles.
    document.addEventListener('click', function (event) {
        const link = event.target.closest('a[data-vehicle-history-popup]');
        if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
        if (!window.bootstrap || !window.bootstrap.Modal) return;
        event.preventDefault();
        content.replaceChildren();
        window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
        loadHistory(link.href);
    });

    // La recherche reste dans la modale et conserve tous les filtres GET.
    content.addEventListener('submit', function (event) {
        const form = event.target;
        if (!form.matches('form.history-filters')) return;
        event.preventDefault();
        const url = new URL(form.action, location.href);
        url.search = new URLSearchParams(new FormData(form)).toString();
        loadHistory(url.href);
    });

    // Réinitialiser et paginer dans la modale ; Voir facture reste un lien normal.
    content.addEventListener('click', function (event) {
        const link = event.target.closest('a[href]');
        if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const url = new URL(link.href, location.href);
        const base = new URL(historyUrl, location.href);
        if (url.origin !== base.origin || url.pathname !== base.pathname) return;
        event.preventDefault();
        loadHistory(url.href);
    });

    // Assombrir également la barre latérale, sans modifier les autres modales.
    modalElement.addEventListener('shown.bs.modal', function () {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops[backdrops.length - 1]?.classList.add('vehicle-history-backdrop');
    });

    // Arrêter le chargement à la fermeture sans modifier la page en arrière-plan.
    modalElement.addEventListener('hidden.bs.modal', function () {
        if (pendingRequest) pendingRequest.abort();
        content.replaceChildren();
    });
})();
</script>
