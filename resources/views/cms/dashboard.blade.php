@extends('cms.layout')

@section('title', 'Dashboard · MEW')
@section('page', 'dashboard')

@section('content')
    <div class="section__header cms-hero" style="margin-top:0;">
        <p class="section__subtitle" style="color: var(--color-elegancia);">Acesso Superadmin</p>
        <h1 class="section__title" style="color: var(--color-elegancia);">CMS MEW</h1>
        <p style="color: #2b2b2b;">Gerencie usuários, casamentos e convidados. Use o menu para navegar.</p>
    </div>

    <div class="grid services__grid cms-cards" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
        <div class="card card--surface">
            <p class="section__subtitle" style="color: var(--color-elegancia);">Casamentos</p>
            <h2 class="section__title" style="font-size: 2.4rem; color: var(--color-elegancia);" id="metric-weddings">--</h2>
            <p class="service-card__text" style="color: #2b2b2b;">Datas, local e casais vinculados.</p>
            <a href="{{ route('cms.weddings') }}" class="chip tag-muted" style="margin-top: 0.75rem; display: inline-block;">Ver casamentos</a>
        </div>
        <div class="card card--surface">
            <p class="section__subtitle" style="color: var(--color-elegancia);">Convidados</p>
            <h2 class="section__title" style="font-size: 2.4rem; color: var(--color-elegancia);" id="metric-guests">--</h2>
            <p class="service-card__text" style="color: #2b2b2b;">Pais/filhos, status e confirmação por CPF/código.</p>
            <a href="{{ route('cms.guests') }}" class="chip tag-muted" style="margin-top: 0.75rem; display: inline-block;">Ver convidados</a>
        </div>
    </div>

    <div class="card card--surface" style="margin-top: 1.5rem;">
        <div class="section__header" style="margin-bottom: 1rem;">
            <p class="section__subtitle" style="color: var(--color-elegancia);">Distribuição por casamento</p>
            <h3 class="section__title" style="font-size: 1.8rem; color: var(--color-elegancia);">Convidados por evento</h3>
        </div>
        <div id="metrics-weddings-list" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;"></div>
    </div>
@endsection
