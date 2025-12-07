<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · MEW</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Manrope:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css', 'resources/js/app.js'])
</head>
<body class="bg-light cms-body" style="padding-top: 140px;">
    <header class="header header--cms">
        <div class="container header__container">
            <a href="/" class="logo">mew</a>
            <nav class="nav">
                <ul class="nav__list">
                    <li class="nav__item"><a href="/" class="nav__link">Início</a></li>
                    <li class="nav__item"><a href="/confirmacao" class="nav__link btn btn--outline">Confirmar convite</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container" style="max-width: 520px; padding: 0 1.5rem 4rem;">
        <div class="section__header cms-hero" style="margin-top: 0;">
            <p class="section__subtitle" style="color: var(--color-elegancia);">Acesso ao CMS</p>
            <h1 class="section__title" style="color: var(--color-elegancia);">Login</h1>
            <p style="color: #3b3b3b;">Use o usuário Superadmin para entrar no CMS.</p>
        </div>

        @if ($errors->any())
            <div class="card card--error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" class="card card--surface">
            @csrf
            <label class="label">Email
                <input class="input" name="email" type="email" required autocomplete="email" placeholder="superadmin@mew.test" value="{{ old('email') }}"/>
            </label>
            <label class="label">Senha
                <input class="input" name="password" type="password" required autocomplete="current-password" placeholder="password"/>
            </label>
            <label style="display:flex; align-items:center; gap: 0.5rem; color: #3b3b3b; margin-bottom: 1.25rem; font-weight: 600;">
                <input type="checkbox" name="remember" value="1" style="width: 16px; height: 16px; accent-color: var(--accent-color);">
                Manter conectado
            </label>
            <button type="submit" class="btn btn--primary" style="width:100%;">Entrar</button>
        </form>

        <p style="margin-top: 1rem; text-align: center; color: #3b3b3b;">Superadmin seed: superadmin@mew.test / password.</p>
    </main>
</body>
</html>
