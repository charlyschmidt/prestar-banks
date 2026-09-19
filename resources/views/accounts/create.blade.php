@extends('layouts.app')


@section('content')


<div class="page-container">


    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

        <div>

            <h1>
                Nueva cuenta
            </h1>

            <p>
                Agregá un banco, billetera o efectivo
            </p>

        </div>

    </div>



    @if ($errors->any())

        <div class="alert alert-danger mb-4">

            @foreach ($errors->all() as $error)

                <div>
                    {{ $error }}
                </div>

            @endforeach

        </div>

    @endif



    <div class="form-card account-form-card">


        <form
            method="POST"
            action="{{ route('accounts.store') }}"
            enctype="multipart/form-data"
        >

            @csrf



            <div class="account-edit-grid">


                {{-- =====================================================
                    COLUMNA IZQUIERDA
                ====================================================== --}}

                <div class="account-edit-main">


                    {{-- NOMBRE --}}

                    <div class="form-group">

                        <label>
                            Nombre de la cuenta
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Ej: Banco Galicia"
                            class="dark-input"
                            required
                        >

                    </div>



                    {{-- TIPO --}}

                    <div class="form-group">

                        <label>
                            Tipo de cuenta
                        </label>

                        <select
                            name="type"
                            class="dark-input"
                            required
                        >

                            <option
                                value="bank"
                                {{ old('type', 'bank') === 'bank' ? 'selected' : '' }}
                            >
                                Banco
                            </option>

                            <option
                                value="wallet"
                                {{ old('type') === 'wallet' ? 'selected' : '' }}
                            >
                                Billetera virtual
                            </option>

                            <option
                                value="cash"
                                {{ old('type') === 'cash' ? 'selected' : '' }}
                            >
                                Efectivo
                            </option>

                        </select>

                    </div>



                    {{-- LOGO --}}

                    <div class="form-group">

                        <label>
                            Logo de la cuenta
                        </label>


                        <div class="account-logo-editor">


                            <div class="account-logo-preview account-logo-empty">

                                <i class="bi bi-bank"></i>

                            </div>


                            <div class="account-logo-upload">

                                <input
                                    type="file"
                                    name="logo"
                                    class="dark-input"
                                    accept=".png,.jpg,.jpeg,.webp"
                                >

                                <small>
                                    PNG, JPG o WEBP recomendado
                                </small>

                            </div>


                        </div>

                    </div>


                </div>



                {{-- =====================================================
                    COLUMNA DERECHA — MONEDAS
                ====================================================== --}}

                <div class="account-edit-currencies">


                    <div class="account-currencies-header">

                        <label>
                            Monedas de la cuenta
                        </label>

                        <small>
                            Seleccioná las monedas que vas a operar en esta cuenta.
                        </small>

                    </div>



                    <div class="currency-selector">

                        @foreach ($currencies as $code => $name)

                            <label class="currency-option">


                                <input
                                    type="checkbox"
                                    name="currencies[]"
                                    value="{{ $code }}"
                                    {{ in_array(
                                        $code,
                                        old('currencies', [])
                                    ) ? 'checked' : '' }}
                                >


                                <span class="currency-option-content">


                                    <span class="currency-option-info">

                                        <strong class="currency-option-code">
                                            {{ $code }}
                                        </strong>

                                        <span class="currency-option-name">
                                            {{ $name }}
                                        </span>

                                    </span>


                                    <span class="currency-option-check">

                                        <i class="bi bi-check-lg"></i>

                                    </span>


                                </span>


                            </label>

                        @endforeach

                    </div>



                    @error('currencies')

                        <small class="text-danger d-block mt-2">
                            {{ $message }}
                        </small>

                    @enderror


                </div>


            </div>



            {{-- =====================================================
                ACCIONES
            ====================================================== --}}

            <div class="form-actions d-flex flex-column flex-md-row justify-content-end gap-2 mt-4">

                <a
                    href="{{ route('accounts.index') }}"
                    class="secondary-button text-center"
                >
                    Cancelar
                </a>


                <button
                    type="submit"
                    class="primary-action-button"
                >

                    <i class="bi bi-check-lg"></i>

                    Guardar cuenta

                </button>

            </div>


        </form>


    </div>


</div>


@endsection