{{-- Badge d'état d'une ligne de bon de commande (identification + dépôt). --}}
@if($ligne->product_id && ! $ligne->depot_id)
    <span class="badge-attente">Pièce identifiée — choisir un dépôt</span>
@elseif($ligne->disponible === true)
    <span class="badge-dispo">Disponible{{ is_null($ligne->reference) ? ' (identifiée manuellement)' : '' }}</span>
@elseif(! is_null($ligne->disponible))
    <span class="badge-indispo">Indisponible</span>
@else
    <span class="badge-attente">Sans référence — à vérifier</span>
@endif

@if($ligne->depot_id && $ligne->depot)
    <span class="text-muted small d-block mt-1">Dépôt : {{ $ligne->depot->name }}</span>
@endif

@if($ligne->note)
    <span class="text-muted small d-block mt-1">{{ $ligne->note }}</span>
@endif
