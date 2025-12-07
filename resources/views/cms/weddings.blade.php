@extends('cms.layout')

@section('title', 'Casamentos · MEW')
@section('page', 'weddings')

@section('content')
    <div class="section__header" style="margin-top:0;">
        <p class="section__subtitle" style="color: var(--color-elegancia);">Casamentos</p>
        <h1 class="section__title" style="color: var(--color-elegancia);">Gerencie eventos e casais</h1>
        <p style="color: #2b2b2b;">Datas, local, notas e vinculação de casais.</p>
    </div>

    <div class="card card--surface" style="margin-bottom: 1.5rem;">
        <h3 class="service-card__title" style="color: var(--color-elegancia);">Criar casamento</h3>
        <form id="wedding-form" style="margin-top: 1rem; display: grid; gap: 0.75rem;">
            <input class="input" required name="title" placeholder="Título do casamento">
            <input class="input" required name="event_date" type="date">
            <input class="input" name="location" placeholder="Local">
            <textarea class="textarea" name="notes" placeholder="Notas"></textarea>
            <div>
                <p style="color:#2b2b2b; font-weight: 600; margin-bottom: 0.3rem;">Casais</p>
                <div id="couple-options" style="display: grid; gap: 0.35rem;"></div>
            </div>
            <button type="submit" class="btn btn--primary" style="width:100%;">Salvar casamento</button>
            <span id="wedding-form-status" style="color: #2b2b2b; font-size: 0.9rem;"></span>
        </form>
    </div>

    <div class="card card--surface">
        <h3 class="service-card__title" style="color: var(--color-elegancia);">Lista</h3>
        <div id="weddings-table"></div>
    </div>
@endsection
