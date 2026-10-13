@extends('layouts.layoutMaster')

@section('title', 'Permissions — ' . $cible->name)

@section('content')

<style>
    .perm-legende { display: inline-flex; align-items: center; gap: 6px; }
    .perm-point { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
    .perm-point.defaut { background: #c7d7fe; border: 2px solid #155EEF; }
    .perm-point.perso { background: var(--stcd-primary, #FE9F43); }
    .perm-ligne { display: flex; align-items: center; gap: 14px; padding: 12px 20px; margin: 0; cursor: pointer; }
    .perm-ligne + .perm-ligne { border-top: 1px solid var(--stcd-border, #E6EAED); }
    .perm-ligne:hover { background: var(--stcd-surface-2, #F9FAFB); }
    .perm-ligne .form-check-input { width: 18px; height: 18px; margin: 0; flex-shrink: 0; cursor: pointer; }
    .perm-cle { font-family: ui-monospace, Consolas, monospace; font-size: 11.5px; color: var(--stcd-muted, #8a9198); }
    .perm-badge { font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 999px; white-space: nowrap; }
    .perm-badge.defaut { color: #155EEF; background: #eff4ff; border: 1px solid #c7d7fe; }
    .perm-badge.perso { color: #c26a12; background: #fff4e8; border: 1px solid #ffd7ad; }
    .perm-avatar {
        width: 48px; height: 48px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: var(--stcd-primary, #FE9F43); color: #fff; font-size: 18px; font-weight: 800;
    }
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h4 class="mb-1">Permissions de {{ $cible->name }}</h4>
        <div class="text-muted">{{ $cible->getRoleLabel() }} · {{ $cible->email }}</div>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back"></i> Retour
    </a>
</div>

<form method="POST" action="{{ route('users.permissions.update', $cible) }}">
    @csrf
    @method('PUT')

    {{-- Utilisateur --}}
    <div class="card mb-4">
        <div class="card-body d-flex align-items-center gap-3 flex-wrap">
            <div class="perm-avatar">{{ mb_strtoupper(mb_substr($cible->name, 0, 1)) }}</div>
            <div>
                <div class="fw-bold">{{ $cible->name }}</div>
                <div class="text-muted small">{{ $cible->email }}</div>
            </div>
            <span class="badge bg-label-primary ms-auto">{{ $cible->getRoleLabel() }}</span>
        </div>
    </div>

    {{-- Légende --}}
    <div class="card mb-4">
        <div class="card-body py-3 d-flex flex-wrap gap-4 small text-muted">
            <span class="perm-legende"><span class="perm-point defaut"></span> Permission par défaut du rôle « {{ $cible->getRoleLabel() }} »</span>
            <span class="perm-legende"><span class="perm-point perso"></span> Permission personnalisée pour cet utilisateur (ajoutée ou retirée)</span>
        </div>
    </div>

    {{-- Groupes de permissions --}}
    @foreach($groups as $nomGroupe => $permissions)
        <div class="card mb-4 overflow-hidden">
            <div class="card-header py-3">
                <h6 class="mb-0 text-uppercase small fw-bold">{{ $nomGroupe }}</h6>
            </div>

            @foreach($permissions as $cle => $libelle)
                @php
                    $parDefaut = in_array($cle, $defaults, true);
                    $estPerso  = $cible->userPermissions->firstWhere('permission_key', $cle) !== null;
                    $accordee  = $cible->hasPermission($cle);
                @endphp

                <label class="perm-ligne">
                    <input type="checkbox" class="form-check-input" name="permissions[]" value="{{ $cle }}" @checked($accordee)>

                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold">{{ $libelle }}</div>
                        <div class="perm-cle">{{ $cle }}</div>
                    </div>

                    <div class="d-flex gap-2 flex-shrink-0">
                        @if($parDefaut)
                            <span class="perm-badge defaut">Par défaut</span>
                        @endif
                        @if($estPerso)
                            <span class="perm-badge perso">Personnalisé</span>
                        @endif
                    </div>
                </label>
            @endforeach
        </div>
    @endforeach

    {{-- Actions --}}
    <div class="d-flex justify-content-between flex-wrap gap-2 pb-4">
        <button type="button" class="btn btn-outline-secondary" onclick="reinitialiserPermissions()">
            Réinitialiser aux défauts du rôle
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save"></i> Enregistrer les permissions
        </button>
    </div>
</form>

<script>
    function reinitialiserPermissions() {
        var defauts = @json($defaults);

        document.querySelectorAll('input[name="permissions[]"]').forEach(function (caseACocher) {
            caseACocher.checked = defauts.indexOf(caseACocher.value) !== -1;
        });
    }
</script>

@endsection
