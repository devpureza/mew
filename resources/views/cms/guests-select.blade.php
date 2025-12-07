@extends('cms.layout')

@section('title', 'Selecionar Casamento · MEW')
@section('page', 'guests-select')

@section('content')
    <div class="section__header" style="margin-top:0;">
        <p class="section__subtitle" style="color: var(--color-elegancia);">Convidados</p>
        <h1 class="section__title" style="color: var(--color-elegancia);">Selecione um casamento</h1>
        <p style="color: #2b2b2b;">Escolha o casamento para gerenciar seus convidados.</p>
    </div>

    <div id="weddings-grid" class="grid" style="gap: 1rem; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
        {{-- Cards serão renderizados via JS --}}
        <div class="card card--surface" style="padding: 2rem; text-align: center;">
            <p style="color: #666;">Carregando casamentos...</p>
        </div>
    </div>
@endsection
