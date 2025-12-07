<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MEW - Moments Ever Wanted | Assessoria de Casamentos</title>
    <meta name="description" content="MEW - Moments Ever Wanted. Um novo conceito em casamentos. Assessoria que une propósito, fluidez e sofisticação.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Manrope:wght@300;400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/landing.css', 'resources/js/app.js'])
</head>
<body>
    <header class="header">
        <div class="container header__container">
            <a href="#home" class="logo">mew</a>
            <nav class="nav">
                <ul class="nav__list">
                    <li class="nav__item"><a href="#home" class="nav__link">Início</a></li>
                    <li class="nav__item"><a href="#concept" class="nav__link">Conceito</a></li>
                    <li class="nav__item"><a href="#services" class="nav__link">CMS</a></li>
                    <li class="nav__item"><a href="#portfolio" class="nav__link">Galeria</a></li>
                    <li class="nav__item"><a href="/confirmacao" class="nav__link btn btn--outline">Confirmar convite</a></li>
                    <li class="nav__item"><a href="/login" class="nav__link btn btn--primary">Área de login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section id="home" class="hero">
        <div class="container hero__container">
            <div class="hero__content">
                <h1 class="hero__title">Moments <br>Ever <br><span>Wanted</span></h1>
                <p class="hero__description">MEW - Moments Ever Wanted. Um novo conceito em casamentos. Assessoria que une propósito, fluidez e sofisticação.</p>
                <div class="hero__actions">
                    <a href="/confirmacao" class="btn btn--outline">Confirmar convite</a>
                </div>
            </div>
            <div class="hero__visual">
                <div class="hero__image-container">
                    <img src="{{ asset('assets/img1.png') }}" alt="Casamento MEW" class="hero__image">
                    <img src="{{ asset('assets/decor.png') }}" alt="" class="hero__decor">
                </div>
            </div>
        </div>
    </section>

    <section id="concept" class="section concept">
        <div class="container">
            <p class="section__subtitle">Nossa Essência</p>
            <h2 class="concept__text">"O fio condutor da jornada. Conduzimos os noivos equilibrando emoção, fluidez e profissionalismo, com tecnologia própria para convites e presenças."</h2>
        </div>
    </section>

    <section id="services" class="section services">
        <div class="container">
            <div class="section__header">
                <p class="section__subtitle">O que oferecemos</p>
                <h2 class="section__title">Experiência completa</h2>
            </div>
            <div class="grid services__grid">
                <div class="service-card">
                    <div class="service-card__icon">✦</div>
                    <h3 class="service-card__title">Usuários e papéis</h3>
                    <p class="service-card__text">Cadastro com nome, CPF, nascimento, endereço, foto e papéis SUPERADMIN, ADMIN, COUPLE e GUEST.</p>
                </div>
                <div class="service-card">
                    <div class="service-card__icon">✺</div>
                    <h3 class="service-card__title">Casamentos</h3>
                    <p class="service-card__text">Datas, local e casais vinculados. Notas do evento e pivot para casais principais/secundários.</p>
                </div>
                <div class="service-card">
                    <div class="service-card__icon">✓</div>
                    <h3 class="service-card__title">Convidados</h3>
                    <p class="service-card__text">Pais com CPF, filhos sem CPF, status pending/accepted/rejected e confirmação em cascata via CPF.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="portfolio" class="section portfolio">
        <div class="container">
            <div class="section__header">
                <p class="section__subtitle">Histórias reais</p>
                <h2 class="section__title">Nossos casamentos</h2>
            </div>
            <div class="grid portfolio__grid">
                <div class="portfolio-item">
                    <div class="portfolio-item__image" style="background-image: url('{{ asset('assets/img2.png') }}');"></div>
                </div>
                <div class="portfolio-item">
                    <div class="portfolio-item__image" style="background-image: url('{{ asset('assets/img2.png') }}');"></div>
                </div>
                <div class="portfolio-item">
                    <div class="portfolio-item__image" style="background-image: url('{{ asset('assets/img2.png') }}');"></div>
                </div>
                <div class="portfolio-item">
                    <div class="portfolio-item__image" style="background-image: url('{{ asset('assets/img2.png') }}');"></div>
                </div>
                <div class="portfolio-item">
                    <div class="portfolio-item__image" style="background-image: url('{{ asset('assets/img2.png') }}');"></div>
                </div>
            </div>
        </div>
    </section>

    <footer id="contact" class="footer">
        <div class="container footer__container">
            <div class="footer__brand">
                <a href="#home" class="logo">mew</a>
                <p>Goiânia, Goiás, Brasil · 2025</p>
                <p>Transformando sonhos em momentos eternos.</p>
            </div>
            <div class="footer__contact">
                <a href="mailto:contato@mew.com.br">contato@mew.com.br</a>
                <a href="tel:+5562999999999">+55 (62) 99999-9999</a>
                <div class="social-icons" style="justify-content: flex-end; margin-top: 1rem;">
                    <a href="#" style="font-size: 1rem; margin-left: 1rem;">Instagram</a>
                    <a href="#" style="font-size: 1rem; margin-left: 1rem;">WhatsApp</a>
                </div>
            </div>
        </div>
        <div class="container footer__bottom">
            <p>&copy; 2025 MEW - Moments Ever Wanted.</p>
            <p>Design de referência aplicado.</p>
        </div>
    </footer>
</body>
</html>
