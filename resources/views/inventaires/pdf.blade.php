@php
    $qte = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ' '), '0'), ',');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche d'inventaire {{ $inventaire->numero }}</title>

    <style>
        @page { margin: 20px 22px; }
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5px;
            color: #263238;
        }

        table { width: 100%; border-collapse: collapse; }

        .titre { font-size: 17px; font-weight: bold; color: #092C4C; }
        .sous-titre { margin-top: 2px; font-size: 10px; color: #475569; }
        .numero { text-align: right; font-size: 13px; font-weight: bold; color: #092C4C; }
        .separator { height: 2px; margin: 10px 0 12px; background: #FE9F43; }

        .info td { padding: 5px 7px; border: 1px solid #e2e8f0; }
        .info-label { font-size: 7.5px; font-weight: bold; text-transform: uppercase; color: #94a3b8; }
        .info-value { font-size: 10px; font-weight: bold; }

        .rayon {
            margin: 12px 0 4px;
            padding: 4px 7px;
            color: #fff;
            background: #092C4C;
            font-size: 10px;
            font-weight: bold;
        }

        .lignes th {
            padding: 5px;
            border: 1px solid #cbd5e1;
            background: #f1f5f9;
            font-size: 7.5px;
            text-align: left;
            text-transform: uppercase;
        }

        .lignes td { height: 22px; padding: 4px 5px; border: 1px solid #cbd5e1; }
        .a-remplir { background: #fffaf0; }
        .text-right { text-align: right; }

        .signatures { margin-top: 18px; }
        .signatures td { width: 33%; height: 60px; padding: 6px 8px; border: 1px solid #cbd5e1; vertical-align: top; }
        .signature-titre { font-size: 8px; font-weight: bold; text-transform: uppercase; color: #334155; }
    </style>
</head>
<body>

<table>
    <tr>
        <td style="width:65%;">
            <div class="titre">FICHE D'INVENTAIRE</div>
            <div class="sous-titre">STCD MOTORS — {{ $inventaire->depot->name }} — {{ $inventaire->perimetre }}</div>
        </td>
        <td class="numero">
            {{ $inventaire->numero }}<br>
            <span style="font-size:9px; color:#64748b;">Imprimée le {{ now()->format('d/m/Y H:i') }}</span>
        </td>
    </tr>
</table>

<div class="separator"></div>

<table class="info">
    <tr>
        <td><div class="info-label">Dépôt</div><div class="info-value">{{ $inventaire->depot->name }}</div></td>
        <td><div class="info-label">Périmètre</div><div class="info-value">{{ $inventaire->perimetre }}</div></td>
        <td><div class="info-label">Pièces à compter</div><div class="info-value">{{ $inventaire->lignes->count() }}</div></td>
        <td><div class="info-label">Compté par</div><div class="info-value">{{ $inventaire->compteur ?: '………………………' }}</div></td>
    </tr>
</table>

@foreach($inventaire->lignes->groupBy(fn ($l) => $l->rayon?->name ?? 'Sans rayon') as $rayon => $lignes)
    <div class="rayon">Rayon {{ $rayon }} — {{ $lignes->count() }} pièce(s)</div>

    <table class="lignes">
        <thead>
            <tr>
                <th style="width:5%;">N°</th>
                <th style="width:12%;">Emplacement</th>
                <th style="width:17%;">Référence</th>
                <th style="width:28%;">Désignation</th>
                <th style="width:9%;" class="text-right">Attendu</th>
                <th style="width:10%;">Compté</th>
                <th style="width:19%;">Remarque / correction</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lignes as $l)
                <tr>
                    <td>{{ $l->ordre }}</td>
                    <td>{{ $l->location?->name ?? '—' }}</td>
                    <td><strong>{{ $l->reference }}</strong></td>
                    <td>{{ \Illuminate\Support\Str::limit($l->designation, 48) }}</td>
                    <td class="text-right">{{ $qte($l->qte_attendue) }}</td>
                    <td class="a-remplir"></td>
                    <td class="a-remplir"></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach

<table class="signatures">
    <tr>
        <td><div class="signature-titre">Compté par</div></td>
        <td><div class="signature-titre">Date et heure</div></td>
        <td><div class="signature-titre">Vérifié par</div></td>
    </tr>
</table>

</body>
</html>
