@extends('cms.layout')

@section('title', 'Meu casamento · MEW')
@section('page', 'couple')

@section('content')
    <div class="section__header cms-hero" style="margin-top:0;">
        <p class="section__subtitle" style="color: var(--color-elegancia);">Casal</p>
        <h1 class="section__title" style="color: var(--color-elegancia);">Meu casamento e convidados</h1>
        <p style="color: #2b2b2b;">Você pode ver seu casamento e adicionar convidados ao evento.</p>
    </div>

    <div class="card card--surface" style="margin-bottom: 1.5rem;">
        <h3 class="service-card__title" style="color: var(--color-elegancia);">Adicionar convidado</h3>
        <form id="guest-form" style="margin-top: 1rem; display: grid; gap: 0.75rem;">
            <select class="select" name="wedding_id" required id="guest-wedding-select">
                <option value="">Selecione o casamento</option>
            </select>
            <input class="input" required name="name" placeholder="Nome do convidado">
            <input class="input" name="cpf" placeholder="CPF (para responsável)" maxlength="11">
            <input class="input" name="email" type="email" placeholder="Email (opcional)">
            <input class="input" name="phone" placeholder="Telefone (opcional)">
            <select class="select" name="parent_guest_id" id="parent-guest-select">
                <option value="">Responsável (pai/mãe)</option>
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
