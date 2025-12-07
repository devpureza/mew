@extends('cms.layout')

@section('title', 'Meu casamento · MEW')
@section('page', 'couple')

@section('content')
    <div class="section__header cms-hero" style="margin-top:0;">
        <p class="section__subtitle" style="color: var(--color-elegancia);">Casal</p>
        <h1 class="section__title" style="color: var(--color-elegancia);">Meu casamento e convidados</h1>
        <p style="color: #2b2b2b;">Você pode ver seu casamento e adicionar convidados ao evento.</p>
    </div>

    <div class="grid" style="gap: 1rem; margin-bottom: 1rem; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="card card--surface">
            <p class="section__subtitle" style="color: var(--color-elegancia); margin-bottom: 0.4rem;">Seu casamento</p>
            <h3 class="service-card__title" id="summary-wedding-title" style="color: var(--color-elegancia); margin-bottom: 0.4rem;">--</h3>
            <p style="color: #2b2b2b; margin: 0.15rem 0;">Data: <span id="summary-wedding-date">--</span></p>
            <p style="color: #2b2b2b; margin: 0.15rem 0;">Local: <span id="summary-wedding-location">--</span></p>
        </div>
        <div class="card card--surface">
            <p class="section__subtitle" style="color: var(--color-elegancia); margin-bottom: 0.25rem;">Total</p>
            <h3 class="service-card__title" style="color: var(--color-elegancia); font-size: 2rem; margin: 0;" id="stat-total">--</h3>
        </div>
        <div class="card card--surface">
            <p class="section__subtitle" style="color: var(--color-elegancia); margin-bottom: 0.25rem;">Confirmados</p>
            <h3 class="service-card__title" style="color: #2d8a5f; font-size: 2rem; margin: 0;" id="stat-accepted">--</h3>
        </div>
        <div class="card card--surface">
            <p class="section__subtitle" style="color: var(--color-elegancia); margin-bottom: 0.25rem;">Pendentes</p>
            <h3 class="service-card__title" style="color: #b4860b; font-size: 2rem; margin: 0;" id="stat-pending">--</h3>
        </div>
        <div class="card card--surface">
            <p class="section__subtitle" style="color: var(--color-elegancia); margin-bottom: 0.25rem;">Recusados</p>
            <h3 class="service-card__title" style="color: #b4332b; font-size: 2rem; margin: 0;" id="stat-rejected">--</h3>
        </div>
    </div>

    <div class="card card--surface" style="margin-bottom: 1.5rem;">
        <h3 class="service-card__title" style="color: var(--color-elegancia);">Adicionar convidado</h3>
        <form id="guest-form" style="margin-top: 1rem; display: grid; gap: 0.75rem;">
            <input type="hidden" name="wedding_id" id="guest-wedding-select" value="">
            <input class="input" required name="name" placeholder="Nome do convidado">
            <input class="input" name="cpf" placeholder="CPF (para responsável)" maxlength="11">
            <input class="input" name="email" type="email" placeholder="Email (opcional)">
            <input class="input" name="phone" placeholder="Telefone (opcional)">
            <select class="select" name="parent_guest_id" id="parent-guest-select">
                <option value="">Responsável (deixe vazio se for o responsável)</option>
            </select>
            <select class="select" name="status">
                <option value="pending">Pendente</option>
                <option value="accepted">Aceito</option>
                <option value="rejected">Recusado</option>
            </select>
            <textarea class="textarea" name="notes" placeholder="Notas"></textarea>

            <div style="border:1px dashed rgba(0,48,44,0.2); border-radius: 12px; padding: 0.75rem 0.9rem;">
                <div style="display:flex; justify-content: space-between; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <p style="color: #2b2b2b; font-weight: 700;">Dependentes deste responsável</p>
                    <button type="button" id="add-dependent" class="btn btn--outline" style="padding: 0.5rem 0.9rem;">+ Adicionar</button>
                </div>
                <p style="color: #2b2b2b; font-size: 0.9rem; margin-bottom: 0.5rem;">Use quando este convidado for o responsável. Não é necessário CPF para dependentes.</p>
                <div id="dependents-container" style="display: grid; gap: 0.5rem;"></div>
            </div>

            <button type="submit" class="btn btn--primary" style="width:100%;">Salvar convidado</button>
            <span id="guest-form-status" style="color: #2b2b2b; font-size: 0.9rem;"></span>
        </form>
    </div>

    <div class="card card--surface">
        <h3 class="service-card__title" style="color: var(--color-elegancia);">Meus convidados</h3>
        <div id="guests-table"></div>
    </div>
@endsection
