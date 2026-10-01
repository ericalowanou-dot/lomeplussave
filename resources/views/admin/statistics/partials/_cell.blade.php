{{-- Une cellule de tableau de détail, selon son format. Variables : $value, $format. --}}
@switch($format)
    @case('annonce')
        <td>
            <div class="st-person">
                @if($value['photo'] ?? null)<img class="st-thumb" src="{{ $value['photo'] }}" alt="" loading="lazy">@endif
                <div>
                    @if($value['fiche'] ?? null)
                        <a href="{{ $value['fiche'] }}">{{ \Illuminate\Support\Str::limit($value['titre'], 60) }}</a>
                    @else
                        {{ $value['titre'] ?? '—' }}
                    @endif
                    <small>
                        {{ $value['sous'] ?? '' }}
                        @if($value['url'] ?? null) · <a href="{{ $value['url'] }}" target="_blank" class="fw-normal" title="Voir l'annonce sur le site">voir <i class="fas fa-external-link-alt" style="font-size:.65rem"></i></a>@endif
                    </small>
                </div>
            </div>
        </td>
        @break

    @case('annonce_mini')
        <td class="st-best">
            @if($value)
                <div class="st-person">
                    @if($value['photo'] ?? null)<img class="st-thumb" src="{{ $value['photo'] }}" alt="" loading="lazy">@endif
                    <div>
                        @if($value['fiche'] ?? null)<a href="{{ $value['fiche'] }}">{{ \Illuminate\Support\Str::limit($value['titre'], 40) }}</a>@else{{ $value['titre'] }}@endif
                        <small>{{ $value['note'] ?? '' }}</small>
                    </div>
                </div>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        @break

    @case('personne')
        <td>
            <div class="st-person">
                <img src="{{ $value['photo'] ?? \App\Models\User::defaultProfilPhotoUrl() }}" alt="" loading="lazy">
                <div>
                    @if($value['fiche'] ?? null)<a href="{{ $value['fiche'] }}">{{ $value['nom'] }}</a>@else{{ $value['nom'] ?? '—' }}@endif
                    @if($value['certifie'] ?? false)<i class="fas fa-circle-check badge-cert" title="Certifié"></i>@endif
                    <small>{{ $value['sous'] ?? '' }}</small>
                </div>
            </div>
        </td>
        @break

    @case('texte_fort')
        <td><strong>{{ $value ?? '—' }}</strong></td>
        @break

    @case('num')
        <td class="num">{{ number_format((float) $value, 0, ',', ' ') }}</td>
        @break

    @case('num_alerte')
        <td class="num">@if((int) $value === 0)<span class="text-danger fw-semibold">0</span>@else{{ number_format((float) $value, 0, ',', ' ') }}@endif</td>
        @break

    @case('money')
        <td class="num">{{ $value !== null ? number_format((float) $value, 0, ',', ' ') . ' F' : '—' }}</td>
        @break

    @case('pct')
        <td class="num">{{ number_format((float) $value, 1, ',', ' ') }} %</td>
        @break

    @case('barre')
        <td style="min-width:140px">
            <div class="d-flex align-items-center gap-2">
                <div class="st-bar flex-grow-1"><span style="width: {{ min(100, (float) $value) }}%"></span></div>
                <small class="text-muted">{{ number_format((float) $value, 1, ',', ' ') }} %</small>
            </div>
        </td>
        @break

    @case('date')
    @case('datetime')
        <td style="white-space:nowrap">
            @if($value)
                {{ \Carbon\Carbon::parse($value)->format($format === 'date' ? 'd/m/Y' : 'd/m/Y H:i') }}
            @else
                <span class="text-muted" title="Enregistré avant la datation automatique">date inconnue</span>
            @endif
        </td>
        @break

    @case('statut')
        <td><span class="st-badge {{ $value }}">{{ \App\Services\StatisticsDetails::statutLabel($value) }}</span></td>
        @break

    @case('bool')
        <td><span class="st-badge {{ $value ? 'yes' : 'no' }}">{{ $value ? 'oui' : 'non' }}</span></td>
        @break

    @default
        <td>{{ $value ?? '—' }}</td>
@endswitch
