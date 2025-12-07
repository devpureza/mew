@extends('cms.layout')

@section('title', 'Usuários · MEW')
@section('page', 'users')

@section('content')
    <div class="section__header" style="margin-top:0;">
        <p class="section__subtitle" style="color: var(--color-elegancia);">Usuários</p>
        <h1 class="section__title" style="color: var(--color-elegancia);">Gerencie papéis e CPFs</h1>
        <p style="color: #2b2b2b;">CRUD para SUPERADMIN, ADMIN, COUPLE e GUEST.</p>
    </div>

    <div class="card card--surface" style="margin-bottom: 1.5rem;">
        <h3 class="service-card__title" style="color: var(--color-elegancia);">Criar/editar</h3>
        <form id="user-form" style="margin-top: 1rem; display: grid; gap: 0.75rem;">
            <input type="hidden" name="id" />
            <input class="input" required name="name" placeholder="Nome">
            <input class="input" required name="email" placeholder="Email" type="email">
            <input class="input" required name="cpf" placeholder="CPF (11 dígitos)" maxlength="11" minlength="11">
            <select class="select" name="role" required>
                <option value="superadmin">SUPERADMIN</option>
                <option value="admin">ADMIN</option>
                <option value="couple">COUPLE</option>
                <option value="guest">GUEST</option>
            </select>
            <input class="input" name="birth_date" type="date" placeholder="Data de nascimento">
            <input class="input" name="address_line" placeholder="Endereço (opcional)">
            <input class="input" required name="password" type="password" placeholder="Senha (mín. 8)" minlength="8">
            <button type="submit" class="btn btn--primary" style="width:100%;">Salvar usuário</button>
            <span id="user-form-status" style="color: #2b2b2b; font-size: 0.9rem;"></span>
        </form>
    </div>

    <div class="card card--surface">
        <h3 class="service-card__title" style="color: var(--color-elegancia);">Lista</h3>
        <div id="users-table"></div>
    </div>
@endsection
