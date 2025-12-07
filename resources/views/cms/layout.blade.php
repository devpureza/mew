<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CMS · MEW')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Manrope:wght@300;400;500;600&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/landing.css', 'resources/js/app.js', 'resources/js/cms-pages.js'])
</head>
<body class="bg-light cms-body" data-page="@yield('page')">
    <header class="header header--cms">
        <div class="container header__container">
            <a href="/" class="logo">mew</a>
            <nav class="nav">
                <ul class="nav__list">
                    @php
                        $role = optional(auth()->user()->role)->value ?? (auth()->user()->role ?? null);
                    @endphp
                    @if($role === 'superadmin')
                        <li class="nav__item"><a href="{{ route('cms') }}" class="nav__link">Dashboard</a></li>
                        <li class="nav__item"><a href="{{ route('cms.users') }}" class="nav__link">Usuários</a></li>
                        <li class="nav__item"><a href="{{ route('cms.weddings') }}" class="nav__link">Casamentos</a></li>
                        <li class="nav__item"><a href="{{ route('cms.guests') }}" class="nav__link">Convidados</a></li>
                    @endif
                    @if($role === 'couple')
                        <li class="nav__item"><a href="{{ route('cms.couple') }}" class="nav__link">Meu casamento</a></li>
                        <li class="nav__item"><a href="{{ route('cms.tags') }}" class="nav__link">Marcações</a></li>
                        <li class="nav__item"><a href="{{ route('cms.import') }}" class="nav__link">Importar</a></li>
                    @endif
                    <li class="nav__item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav__link btn btn--outline" style="border:none; background:transparent; padding:0;">Sair</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="section" style="padding-top: 160px;">
        <div class="container">
            @yield('content')
        </div>
    </main>
</body>
</html>
