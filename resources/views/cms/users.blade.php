@extends('cms.layout')

@section('title', 'Usuarios · MEW')
@section('page', 'users')

@section('content')
    <div class="section__header" style="margin-top:0;">
        <p class="section__subtitle" style="color: var(--color-elegancia);">Casais</p>
        <h1 class="section__title" style="color: var(--color-elegancia);">Adicionar casal</h1>
        <p style="color: #2b2b2b;">Adicione casais para vincular a casamentos</p>
    </div>

    <div class="card card--surface" style="margin-bottom: 1.5rem;">
        <h3 class="service-card__title" style="color: var(--color-elegancia);">Adicionar</h3>
        <form id="user-form" style="margin-top: 1rem; display: grid; gap: 0.75rem;">
            <input type="hidden" name="id" />
            <input type="hidden" name="role" value="couple" />
            <input class="input" required name="name" placeholder="Nome">
            <input class="input" required name="email" placeholder="Email" type="email">
            <input class="input" required name="cpf" placeholder="CPF (11 digitos)" maxlength="11" minlength="11">
            <input class="input" name="birth_date" type="date" placeholder="Data de nascimento">
            <input class="input" name="address_line" placeholder="Endereco (opcional)">
            <input class="input" required name="password" type="password" placeholder="Senha (min. 8)" minlength="8">
            <button type="submit" class="btn btn--primary" style="width:100%;">Salvar</button>
            <span id="user-form-status" style="color: #2b2b2b; font-size: 0.9rem;"></span>
        </form>
    </div>

    <div class="card card--surface">
        <h3 class="service-card__title" style="color: var(--color-elegancia);">Lista</h3>
        <div id="users-table"></div>
    </div>
@endsection
