<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmar convite · MEW</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Manrope:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css', 'resources/js/app.js'])
</head>
<body class="bg-light cms-body">
    <header class="header header--cms">
        <div class="container header__container">
            <a href="/" class="logo">mew</a>
            <nav class="nav">
                <ul class="nav__list">
                    <li class="nav__item"><a href="/" class="nav__link">Início</a></li>
                    <li class="nav__item"><a href="/login" class="nav__link btn btn--outline">Área de login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="section" style="padding-top: 200px;">
        <div class="container" style="max-width: 920px;">
            <div class="section__header cms-hero">
                <p class="section__subtitle" style="color: var(--color-elegancia);">Confirmação de presença</p>
                <h1 class="section__title" style="color: var(--color-elegancia);">Busque por CPF ou código</h1>
                <p style="color: #2b2b2b;">Digite o CPF do responsável ou o código do convite (nome+casa+4 dígitos) para ver a família e confirmar cada pessoa.</p>
            </div>

            @if (session('status'))
                <div class="card card--success">
                    {{ session('status') }}
                </div>
            @endif

            <form id="confirm-form" method="POST" action="{{ route('invitations.confirm.web') }}" class="card card--surface">
                @csrf
                <label class="label">CPF ou código do convite
                    <input class="input" id="identifier" name="identifier" required placeholder="CPF (11 dígitos) ou código" style="margin-top: 0.5rem;" />
                </label>
                @error('identifier')
                    <p style="color: #b84e00; font-size: 0.9rem; margin-top: -0.5rem; margin-bottom: 1rem;">{{ $message }}</p>
                @enderror

                <div style="display: flex; gap: 0.75rem; align-items: center; margin-bottom: 1.25rem;">
                    <button type="button" id="btn-lookup" class="btn btn--primary" style="min-width: 180px;">Buscar</button>
                    <span id="lookup-status" style="color: #2b2b2b; font-size: 0.95rem;"></span>
                </div>

                <div id="guest-list" class="grid" style="display:none; gap: 1rem; flex-wrap: wrap;"></div>

                <button type="submit" id="submit-btn" class="btn btn--primary" style="width:100%; margin-top: 1.25rem; display:none;">Registrar presenças selecionadas</button>
            </form>

            <p style="text-align: center; margin-top: 1.5rem; color: #2b2b2b;">Dúvidas? Fale com o casal ou o admin MEW.</p>
        </div>
    </main>

    <script>
        (function() {
            const lookupBtn = document.getElementById('btn-lookup');
            const guestList = document.getElementById('guest-list');
            const statusEl = document.getElementById('lookup-status');
            const submitBtn = document.getElementById('submit-btn');
            const form = document.getElementById('confirm-form');
            const identifierInput = document.getElementById('identifier');

            function renderGuests(guest) {
                guestList.innerHTML = '';
                const guests = [guest, ...(guest.children || [])];

                guests.forEach((g) => {
                    const card = document.createElement('div');
                    card.className = 'card card--surface guest-card';

                    const header = document.createElement('div');
                    header.style.display = 'flex';
                    header.style.justifyContent = 'space-between';
                    header.style.alignItems = 'center';

                    const title = document.createElement('h3');
                    title.className = 'service-card__title';
                    title.innerText = g.name + (g.parent_guest_id ? ' · dependente' : ' · responsável');
                    header.appendChild(title);
                    card.appendChild(header);

                    const actions = document.createElement('div');
                    actions.style.display = 'flex';
                    actions.style.gap = '0.5rem';
                    actions.style.flexWrap = 'wrap';

                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = `status[${g.id}]`;
                    hidden.value = g.status || 'pending';
                    card.appendChild(hidden);

                    const statuses = [
                        { value: 'accepted', label: 'Aceitar' },
                        { value: 'rejected', label: 'Recusar' },
                        { value: 'pending', label: 'Em análise' },
                    ];

                    statuses.forEach(({ value, label }) => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'status-pill';
                        btn.dataset.status = value;
                        btn.innerText = label;
                        if (value === hidden.value) btn.classList.add('active');

                        btn.addEventListener('click', () => {
                            hidden.value = value;
                            actions.querySelectorAll('.status-pill').forEach((b) => b.classList.remove('active'));
                            btn.classList.add('active');
                        });

                        actions.appendChild(btn);
                    });

                    card.appendChild(actions);
                    guestList.appendChild(card);
                });

                guestList.style.display = 'grid';
                submitBtn.style.display = 'inline-block';
            }

            lookupBtn?.addEventListener('click', async (e) => {
                e.preventDefault();
                const identifier = identifierInput.value.trim();
                if (!identifier) {
                    statusEl.innerText = 'Digite um CPF (11 dígitos) ou código de convite.';
                    return;
                }

                statusEl.innerText = 'Buscando...';
                guestList.style.display = 'none';
                submitBtn.style.display = 'none';

                try {
                    const res = await fetch(`/api/v1/invitations/lookup?identifier=${encodeURIComponent(identifier)}`);
                    if (!res.ok) throw new Error('Não encontrado');
                    const data = await res.json();
                    statusEl.innerText = 'Convidados encontrados. Clique em Aceitar/Recusar/Em análise para cada um.';
                    renderGuests(data);
                } catch (err) {
                    statusEl.innerText = 'Convidado não encontrado. Confira CPF/código e tente novamente.';
                }
            });

            form?.addEventListener('submit', (e) => {
                if (guestList.children.length === 0) {
                    e.preventDefault();
                    statusEl.innerText = 'Busque o CPF ou código antes de confirmar.';
                }
            });
        })();
    </script>
</body>
</html>
