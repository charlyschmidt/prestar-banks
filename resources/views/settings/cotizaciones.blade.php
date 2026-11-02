@extends('layouts.app')

@section('content')
    <div class="page-container">
        <div class="page-header">
            <div>
                <h1>Cotizaciones</h1>
                <p>Elegí qué mercados aparecen en la barra debajo del header de {{ $company->name }}.</p>
            </div>
            <a href="{{ route('settings.index') }}" class="primary-action-button">
                <i class="bi bi-arrow-left"></i> Volver a configuración
            </a>
        </div>

        <div class="form-card fx-settings-card">
            <div class="settings-section-header">
                <div class="setting-icon"><i class="bi bi-currency-exchange"></i></div>
                <div>
                    <h3>Mercados visibles</h3>
                    <p>Activá o desactivá las cotizaciones que querés mostrar. La preferencia se comparte con todos los usuarios de esta empresa.</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert-error" role="alert">
                    Revisá las cotizaciones seleccionadas e intentá nuevamente.
                </div>
            @endif

            @php
                $marked = old('markets', $selectedMarkets);
                $marked = is_array($marked) ? $marked : $selectedMarkets;
            @endphp

            <form action="{{ route('settings.cotizaciones.update') }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="fx-settings-grid">
                    @foreach ($groups as $groupName => $markets)
                        <section class="fx-settings-group" aria-label="Cotizaciones {{ $groupName }}">
                            <h4>{{ $groupName }}</h4>
                            <div class="fx-settings-market-list">
                                @foreach ($markets as $code => $details)
                                    @php
                                        $quote = $currentRates['rates'][$code] ?? null;
                                        $buy = is_array($quote) ? ($quote['buy'] ?? null) : null;
                                        $sell = is_array($quote) ? ($quote['sell'] ?? null) : null;
                                    @endphp
                                    <label class="fx-settings-market" for="fx-market-{{ strtolower($code) }}">
                                        <span class="fx-settings-market-description">
                                            <strong>{{ $details['name'] }}</strong>
                                            <small>{{ $details['description'] }}</small>
                                            <span class="fx-settings-quote">
                                                <span><b class="fx-settings-buy">C</b> {{ is_numeric($buy) ? '$' . number_format((float) $buy, 2, ',', '.') : '—' }}</span>
                                                <span><b class="fx-settings-sell">V</b> {{ is_numeric($sell) ? '$' . number_format((float) $sell, 2, ',', '.') : '—' }}</span>
                                            </span>
                                        </span>
                                        <input
                                            id="fx-market-{{ strtolower($code) }}"
                                            class="fx-settings-toggle"
                                            type="checkbox"
                                            name="markets[]"
                                            value="{{ $code }}"
                                            @checked(in_array($code, $marked, true))
                                        >
                                    </label>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>

                <div class="fx-settings-actions">
                    <p>Los cambios se aplican al recargar la página. Si desactivás todos los mercados, se ocultará la barra completa.</p>
                    <button type="submit" class="primary-action-button">
                        <i class="bi bi-check2"></i> Guardar cotizaciones
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
