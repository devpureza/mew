import './bootstrap';

const state = {
    users: [],
    weddings: [],
    guests: [],
};

// Lightweight "sweet alert" style UI (custom, no deps)
const modalUi = (() => {
    const ensureBase = () => {
        if (document.getElementById('mew-alert-style')) return;

        const style = document.createElement('style');
        style.id = 'mew-alert-style';
        style.textContent = `
        .mew-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); display: none; align-items: center; justify-content: center; z-index: 9999; }
        .mew-overlay.is-open { display: flex; animation: mew-fade-in 120ms ease-out; }
        .mew-modal { background: #fff; border-radius: 16px; padding: 24px; width: min(420px, calc(100% - 32px)); box-shadow: 0 20px 45px rgba(0,0,0,0.12); font-family: 'Manrope', system-ui, -apple-system, sans-serif; }
        .mew-modal h3 { margin: 0 0 8px; font-size: 1.2rem; color: #0b1e20; }
        .mew-modal p { margin: 0 0 16px; color: #2b2b2b; line-height: 1.5; }
        .mew-modal .mew-actions { display: flex; gap: 10px; justify-content: flex-end; }
        .mew-btn { border: none; border-radius: 10px; padding: 10px 14px; font-weight: 600; cursor: pointer; transition: transform 80ms ease, box-shadow 120ms ease; }
        .mew-btn:active { transform: translateY(1px); }
        .mew-btn-outline { background: #fff; border: 1px solid rgba(0,0,0,0.12); color: #0b1e20; }
        .mew-btn-primary { background: linear-gradient(135deg, #0d423c, #0b302c); color: #fff; }
        .mew-btn-danger { background: #b4332b; color: #fff; }
        .mew-toast-stack { position: fixed; top: 18px; right: 18px; display: grid; gap: 10px; z-index: 10000; width: min(320px, calc(100% - 36px)); }
        .mew-toast { padding: 12px 14px; border-radius: 12px; color: #0b1e20; background: #fff; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 15px 30px rgba(0,0,0,0.12); animation: mew-slide-in 150ms ease-out; }
        .mew-toast.success { border-color: #2d8a5f; }
        .mew-toast.error { border-color: #b4332b; }
        .mew-toast.info { border-color: #0b302c; }
        @keyframes mew-slide-in { from { transform: translateY(-8px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes mew-fade-in { from { opacity: 0; } to { opacity: 1; } }
        `;
        document.head.appendChild(style);

        const overlay = document.createElement('div');
        overlay.id = 'mew-overlay';
        overlay.className = 'mew-overlay';
        overlay.innerHTML = `
            <div class="mew-modal" role="dialog" aria-modal="true" aria-labelledby="mew-modal-title">
                <h3 id="mew-modal-title"></h3>
                <p id="mew-modal-text"></p>
                <div class="mew-actions">
                    <button type="button" id="mew-cancel" class="mew-btn mew-btn-outline">Cancelar</button>
                    <button type="button" id="mew-confirm" class="mew-btn mew-btn-primary">OK</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);

        const toastStack = document.createElement('div');
        toastStack.id = 'mew-toast-stack';
        toastStack.className = 'mew-toast-stack';
        document.body.appendChild(toastStack);
    };

    const show = ({ title, text, confirmText = 'OK', cancelText = null, tone = 'info', confirmVariant = 'primary' }) =>
        new Promise((resolve) => {
            ensureBase();
            const overlay = document.getElementById('mew-overlay');
            const titleEl = document.getElementById('mew-modal-title');
            const textEl = document.getElementById('mew-modal-text');
            const cancelBtn = document.getElementById('mew-cancel');
            const confirmBtn = document.getElementById('mew-confirm');

            titleEl.textContent = title || '';
            textEl.textContent = text || '';
            confirmBtn.textContent = confirmText;

            confirmBtn.classList.remove('mew-btn-primary', 'mew-btn-danger');
            if (confirmVariant === 'danger') {
                confirmBtn.classList.add('mew-btn-danger');
            } else {
                confirmBtn.classList.add('mew-btn-primary');
            }

            if (cancelText) {
                cancelBtn.style.display = 'inline-flex';
                cancelBtn.textContent = cancelText;
            } else {
                cancelBtn.style.display = 'none';
            }

            const close = (result) => {
                overlay.classList.remove('is-open');
                resolve(result);
            };

            cancelBtn.onclick = () => close(false);
            confirmBtn.onclick = () => close(true);
            overlay.onclick = (e) => {
                if (e.target === overlay && cancelText) close(false);
            };

            overlay.classList.add('is-open');
        });

    const toast = (message, tone = 'info', timeout = 3800) => {
        ensureBase();
        const stack = document.getElementById('mew-toast-stack');
        const el = document.createElement('div');
        el.className = `mew-toast ${tone}`;
        el.textContent = message;
        stack.appendChild(el);
        setTimeout(() => el.remove(), timeout);
    };

    return { show, toast, ensureBase };
})();

const helpers = {
    qs: (sel) => document.querySelector(sel),
    create(tag, className) {
        const el = document.createElement(tag);
        if (className) el.className = className;
        return el;
    },
    formatDate(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        if (Number.isNaN(date.getTime())) return dateStr;
        return new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: 'long', year: 'numeric' }).format(date);
    },
    statusLabel(status) {
        const map = {
            pending: 'Pendente',
            accepted: 'Aceito',
            rejected: 'Recusado',
        };
        return map[status] ?? status ?? '-';
    },
};

function updateCoupleSummary() {
    const titleEl = helpers.qs('#summary-wedding-title');
    if (!titleEl) return;
    const dateEl = helpers.qs('#summary-wedding-date');
    const locEl = helpers.qs('#summary-wedding-location');
    const select = helpers.qs('#guest-wedding-select');
    const selectedId = select?.value;

    let wedding = null;
    if (selectedId) {
        wedding = state.weddings.find((w) => String(w.id) === String(selectedId));
    }
    if (!wedding && state.weddings.length > 0) {
        wedding = state.weddings[0];
    }

    if (!wedding) {
        titleEl.textContent = '--';
        if (dateEl) dateEl.textContent = '--';
        if (locEl) locEl.textContent = '--';
        return;
    }

    titleEl.textContent = wedding.title ?? 'Casamento';
    if (dateEl) dateEl.textContent = helpers.formatDate(wedding.event_date);
    if (locEl) locEl.textContent = wedding.location ?? '--';
}

function setStatus(selector, message, color = '#2b2b2b') {
    const el = helpers.qs(selector);
    if (el) {
        el.innerText = message;
        el.style.color = color;
    }
}

function bootstrapCsrf() {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    }
}

async function loadMetrics() {
    const res = await axios.get('/api/v1/metrics');
    const { counts, weddings } = res.data;

    const weddingsEl = document.getElementById('metric-weddings');
    const guestsEl = document.getElementById('metric-guests');
    const listEl = document.getElementById('metrics-weddings-list');

    if (weddingsEl) weddingsEl.textContent = counts.weddings ?? '--';
    if (guestsEl) guestsEl.textContent = counts.guests ?? '--';

    if (listEl) {
        listEl.innerHTML = '';
        weddings.forEach((w) => {
            const card = helpers.create('div', 'card');
            card.classList.add('card--surface');
            card.innerHTML = `
                <p class="section__subtitle" style="color: var(--color-elegancia);">${w.title}</p>
                <h4 class="section__title" style="font-size: 1.5rem; color: var(--color-elegancia);">${w.guests_count ?? 0} convidados</h4>
                <p class="service-card__text" style="color: #2b2b2b; font-weight: 700;">${w.guests_accepted_count ?? 0} confirmados</p>
                <p class="service-card__text" style="color: #2b2b2b;">Data: ${helpers.formatDate(w.event_date)}</p>
            `;
            listEl.appendChild(card);
        });
    }
}

// USERS
async function fetchUsers(render = true) {
    const { data } = await axios.get('/api/v1/users');
    state.users = data.data ?? data;
    if (render) renderUsersTable();
}

function renderUsersTable() {
    const container = helpers.qs('#users-table');
    if (!container) return;
    container.innerHTML = '';

    const table = helpers.create('table');
    table.style.width = '100%';
    table.style.borderCollapse = 'collapse';
    const thead = helpers.create('thead');
    thead.innerHTML = `<tr>
        <th style="text-align:left; padding: 6px;">Nome</th>
        <th style="text-align:left; padding: 6px;">Email</th>
        <th style="text-align:left; padding: 6px;">CPF</th>
        <th style="text-align:left; padding: 6px;">Papel</th>
        <th style="padding: 6px;">Ações</th>
    </tr>`;
    table.appendChild(thead);

    const tbody = helpers.create('tbody');
    state.users.forEach((u) => {
        const tr = helpers.create('tr');
        tr.innerHTML = `
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05);">${u.name}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05);">${u.email}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05);">${u.cpf}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05); text-transform: uppercase;">${u.role}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05); text-align:right;">
                <button data-user-id="${u.id}" data-user-name="${u.name}" class="btn-delete-user" style="padding:6px 10px; border:1px solid rgba(0,0,0,0.15); border-radius:8px; background:#fff;">Excluir</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    container.appendChild(table);

    document.querySelectorAll('.btn-delete-user').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-user-id');
            const name = btn.getAttribute('data-user-name') || 'usuario';
            if (!id) return;
            const confirmed = await modalUi.show({
                title: 'Excluir usuario?',
                text: `Essa acao remove ${name}.`,
                cancelText: 'Cancelar',
                confirmText: 'Excluir',
                confirmVariant: 'danger',
            });
            if (!confirmed) return;
            await axios.delete(`/api/v1/users/${id}`);
            modalUi.toast('Usuario excluido.', 'success');
            await fetchUsers(true);
        });
    });
}

function bindUserForm() {
    const form = helpers.qs('#user-form');
    if (!form) return;
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        const payload = {
            name: fd.get('name'),
            email: fd.get('email'),
            cpf: fd.get('cpf'),
            role: 'couple',
            birth_date: fd.get('birth_date') || null,
            address_line: fd.get('address_line') || null,
            password: fd.get('password'),
        };
        try {
            await axios.post('/api/v1/users', payload);
            setStatus('#user-form-status', 'Casal salvo.', 'green');
            modalUi.toast('Casal salvo.', 'success');
            form.reset();
            await fetchUsers(true);
        } catch (err) {
            setStatus('#user-form-status', 'Erro ao salvar (CPF/email unicos?).', '#b84e00');
            modalUi.toast('Erro ao salvar (CPF/email unicos?).', 'error');
        }
    });
}

// WEDDINGS
async function fetchWeddings(render = true) {
    const { data } = await axios.get('/api/v1/weddings');
    state.weddings = data.data ?? data;
    if (render) renderWeddingsTable();
}

function populateCoupleOptions() {
    const wrapper = helpers.qs('#couple-options');
    if (!wrapper) return;
    wrapper.innerHTML = '';
    const couples = (state.users || []).filter((u) => u.role === 'couple');
    couples.forEach((c) => {
        const label = helpers.create('label');
        label.style.display = 'flex';
        label.style.alignItems = 'center';
        label.style.gap = '8px';
        label.style.color = '#2b2b2b';
        const input = helpers.create('input');
        input.type = 'checkbox';
        input.value = c.id;
        input.name = 'couple_ids[]';
        label.appendChild(input);
        const span = helpers.create('span');
        span.innerText = `${c.name} (${c.email})`;
        label.appendChild(span);
        wrapper.appendChild(label);
    });
}

function renderWeddingsTable() {
    const container = helpers.qs('#weddings-table');
    if (!container) return;
    container.innerHTML = '';

    const table = helpers.create('table');
    table.style.width = '100%';
    table.style.borderCollapse = 'collapse';
    const thead = helpers.create('thead');
    thead.innerHTML = `<tr>
        <th style="text-align:left; padding: 6px;">TÃ­tulo</th>
        <th style="text-align:left; padding: 6px;">Data</th>
        <th style="text-align:left; padding: 6px;">Local</th>
        <th style="text-align:left; padding: 6px;">Casais</th>
        <th style="padding:6px;">Ações</th>
    </tr>`;
    table.appendChild(thead);

    const tbody = helpers.create('tbody');
    state.weddings.forEach((w) => {
        const couples = (w.couples || []).map((c) => c.name).join(', ');
        const tr = helpers.create('tr');
        tr.innerHTML = `
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05);">${w.title}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05);">${helpers.formatDate(w.event_date)}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05);">${w.location ?? ''}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05);">${couples}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05); text-align:right;">
                <button data-wedding-id="${w.id}" data-wedding-title="${w.title}" class="btn-delete-wedding" style="padding:6px 10px; border:1px solid rgba(0,0,0,0.15); border-radius:8px; background:#fff;">Excluir</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    container.appendChild(table);

    document.querySelectorAll('.btn-delete-wedding').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-wedding-id');
            const title = btn.getAttribute('data-wedding-title') || 'casamento';
            if (!id) return;
            const confirmed = await modalUi.show({
                title: 'Excluir casamento?',
                text: `Essa acao remove ${title} e seus relacionamentos.`,
                cancelText: 'Cancelar',
                confirmText: 'Excluir',
                confirmVariant: 'danger',
            });
            if (!confirmed) return;
            await axios.delete(`/api/v1/weddings/${id}`);
            modalUi.toast('Casamento excluido.', 'success');
            await fetchWeddings(true);
            await fetchGuests(true);
        });
    });
}

function bindWeddingForm() {
    const form = helpers.qs('#wedding-form');
    if (!form) return;
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        const coupleIds = fd.getAll('couple_ids[]').map(Number).filter(Boolean);
        const payload = {
            title: fd.get('title'),
            event_date: fd.get('event_date'),
            location: fd.get('location') || null,
            notes: fd.get('notes') || null,
            couple_ids: coupleIds,
        };
        try {
            await axios.post('/api/v1/weddings', payload);
            setStatus('#wedding-form-status', 'Casamento salvo.', 'green');
            form.reset();
            await fetchWeddings(true);
            await fetchGuests(true);
        } catch (err) {
            setStatus('#wedding-form-status', 'Erro ao salvar casamento.', '#b84e00');
        }
    });
}

// GUESTS
async function fetchGuests(render = true, weddingId = null) {
    const { data } = await axios.get('/api/v1/guests');
    let guests = data.data ?? data;
    
    // Filtra por casamento se especificado (para páginas que mostram guests de um wedding específico)
    // Nota: Para couple, a API já retorna filtrado, mas filtramos novamente para garantir
    if (weddingId) {
        guests = guests.filter((g) => String(g.wedding_id) === String(weddingId));
    }
    
    state.guests = guests;
    
    if (render) {
        renderGuestsTable();
        populateParentGuestSelect(weddingId);
    }
    populateWeddingSelect();
    updateGuestStats();
}

function updateGuestStats() {
    const totalEl = helpers.qs('#stat-total');
    const acceptedEl = helpers.qs('#stat-accepted');
    const pendingEl = helpers.qs('#stat-pending');
    const rejectedEl = helpers.qs('#stat-rejected');
    
    // Se nenhum elemento de stats existe, não faz nada
    if (!totalEl && !acceptedEl && !pendingEl && !rejectedEl) return;
    
    const total = state.guests.length;
    const accepted = state.guests.filter((g) => g.status === 'accepted').length;
    const pending = state.guests.filter((g) => g.status === 'pending').length;
    const rejected = state.guests.filter((g) => g.status === 'rejected').length;
    
    if (totalEl) totalEl.textContent = total;
    if (acceptedEl) acceptedEl.textContent = accepted;
    if (pendingEl) pendingEl.textContent = pending;
    if (rejectedEl) rejectedEl.textContent = rejected;
}

function populateWeddingSelect() {
    const select = helpers.qs('#guest-wedding-select');
    if (!select) return;
    
    // Se for input hidden (página de guests com wedding específico), não popular
    if (select.type === 'hidden') return;
    
    const current = select.value;
    select.innerHTML = '<option value="">Selecione um casamento</option>';
    state.weddings.forEach((w) => {
        const opt = helpers.create('option');
        opt.value = w.id;
        opt.textContent = `${w.title} (${helpers.formatDate(w.event_date)})`;
        if (current && current === String(w.id)) opt.selected = true;
        select.appendChild(opt);
    });
    if (!select.value && state.weddings[0]) {
        select.value = String(state.weddings[0].id);
    }
    updateCoupleSummary();
}

function populateParentGuestSelect(weddingId = null) {
    const select = helpers.qs('#parent-guest-select');
    if (!select) return;
    const current = select.value;
    select.innerHTML = '<option value="">Responsável (deixe vazio se for o responsável)</option>';
    
    let guests = state.guests;
    if (weddingId) {
        guests = guests.filter((g) => String(g.wedding_id) === String(weddingId));
    }
    
    guests
        .filter((g) => g.is_head_of_family || g.cpf)
        .forEach((g) => {
            const opt = helpers.create('option');
            opt.value = g.id;
            opt.textContent = `${g.name} ${g.cpf ? `(${g.cpf})` : ''}`;
            if (current && current === String(g.id)) opt.selected = true;
            select.appendChild(opt);
        });
}

function renderGuestsTable() {
    const container = helpers.qs('#guests-table');
    if (!container) return;
    container.innerHTML = '';
    
    const currentWeddingId = helpers.qs('#current-wedding-id')?.value || null;

    if (state.guests.length === 0) {
        container.innerHTML = '<p style="color: #666; padding: 1rem 0;">Nenhum convidado cadastrado neste casamento.</p>';
        return;
    }

    const table = helpers.create('table');
    table.style.width = '100%';
    table.style.borderCollapse = 'collapse';
    const thead = helpers.create('thead');
    
    // Se estamos na página de um casamento específico, não mostra coluna de casamento
    const showWeddingColumn = !currentWeddingId;
    
    thead.innerHTML = `<tr>
        <th style="text-align:left; padding: 6px;">Nome</th>
        <th style="text-align:left; padding: 6px;">CPF</th>
        <th style="text-align:left; padding: 6px;">Código</th>
        <th style="text-align:left; padding: 6px;">Status</th>
        ${showWeddingColumn ? '<th style="text-align:left; padding: 6px;">Casamento</th>' : ''}
        <th style="text-align:left; padding: 6px;">Responsável</th>
        <th style="padding:6px;">Ações</th>
    </tr>`;
    table.appendChild(thead);

    const tbody = helpers.create('tbody');
    state.guests.forEach((g) => {
        const tr = helpers.create('tr');
        tr.innerHTML = `
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05);">${g.name}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05);">${g.cpf ?? '-'}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05); font-family: monospace;">${g.invitation_code ?? '-'}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05); text-transform: uppercase;">${helpers.statusLabel(g.status)}</td>
            ${showWeddingColumn ? `<td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05);">${g.wedding?.title ?? ''}</td>` : ''}
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05);">${g.parent_guest?.name ?? '-'}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05); text-align:right;">
                <button data-guest-id="${g.id}" data-guest-name="${g.name}" class="btn-delete-guest" style="padding:6px 10px; border:1px solid rgba(0,0,0,0.15); border-radius:8px; background:#fff;">Excluir</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    container.appendChild(table);

    document.querySelectorAll('.btn-delete-guest').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-guest-id');
            const name = btn.getAttribute('data-guest-name') || 'convidado';
            if (!id) return;
            const confirmed = await modalUi.show({
                title: 'Excluir convidado?',
                text: `Essa ação remove ${name}.`,
                cancelText: 'Cancelar',
                confirmText: 'Excluir',
                confirmVariant: 'danger',
            });
            if (!confirmed) return;
            await axios.delete(`/api/v1/guests/${id}`);
            modalUi.toast('Convidado excluído.', 'success');
            await fetchGuests(true, currentWeddingId);
        });
    });
}

function bindGuestForm() {
    const form = helpers.qs('#guest-form');
    const addDependentBtn = helpers.qs('#add-dependent');
    const dependentsContainer = helpers.qs('#dependents-container');
    const weddingSelect = helpers.qs('#guest-wedding-select');
    
    // Só adiciona listener de change se não for input hidden
    if (weddingSelect && weddingSelect.type !== 'hidden') {
        weddingSelect.addEventListener('change', updateCoupleSummary);
    }

    function addDependentRow() {
        if (!dependentsContainer) return;
        const row = helpers.create('div', 'dependent-row');
        row.style.display = 'grid';
        row.style.gridTemplateColumns = '1fr 160px 60px';
        row.style.gap = '0.5rem';
        row.style.alignItems = 'center';

        const nameInput = helpers.create('input');
        nameInput.name = 'dependent_name[]';
        nameInput.placeholder = 'Nome do dependente';
        nameInput.style.padding = '0.7rem 0.8rem';
        nameInput.style.border = '1px solid rgba(0,48,44,0.2)';
        nameInput.style.borderRadius = '10px';

        const statusSelect = helpers.create('select');
        statusSelect.name = 'dependent_status[]';
        statusSelect.style.padding = '0.7rem 0.8rem';
        statusSelect.style.border = '1px solid rgba(0,48,44,0.2)';
        statusSelect.style.borderRadius = '10px';
        [
            { value: 'pending', label: 'Pendente' },
            { value: 'accepted', label: 'Aceito' },
            { value: 'rejected', label: 'Recusado' },
        ].forEach(({ value, label }) => {
            const opt = helpers.create('option');
            opt.value = value;
            opt.textContent = label;
            statusSelect.appendChild(opt);
        });

        const removeBtn = helpers.create('button');
        removeBtn.type = 'button';
        removeBtn.textContent = 'X';
        removeBtn.style.border = '1px solid rgba(0,0,0,0.15)';
        removeBtn.style.background = '#fff';
        removeBtn.style.borderRadius = '8px';
        removeBtn.style.padding = '0.6rem';
        removeBtn.addEventListener('click', () => row.remove());

        row.appendChild(nameInput);
        row.appendChild(statusSelect);
        row.appendChild(removeBtn);
        dependentsContainer.appendChild(row);
    }

    addDependentBtn?.addEventListener('click', addDependentRow);

    if (!form) return;
    
    // Pega o wedding_id do input hidden (pode ser #current-wedding-id ou #guest-wedding-select quando hidden)
    const getWeddingId = () => {
        return helpers.qs('#current-wedding-id')?.value || helpers.qs('#guest-wedding-select')?.value || null;
    };
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const currentWeddingId = getWeddingId();
        const fd = new FormData(form);
        const dependents = [];
        dependentsContainer?.querySelectorAll('.dependent-row').forEach((row) => {
            const name = row.querySelector('input[name="dependent_name[]"]')?.value?.trim();
            const status = row.querySelector('select[name="dependent_status[]"]')?.value || 'pending';
            if (name) {
                dependents.push({ name, status });
            }
        });
        const payload = {
            wedding_id: fd.get('wedding_id'),
            name: fd.get('name'),
            cpf: fd.get('cpf') || null,
            email: fd.get('email') || null,
            phone: fd.get('phone') || null,
            parent_guest_id: fd.get('parent_guest_id') || null,
            status: fd.get('status') || 'pending',
            notes: fd.get('notes') || null,
            dependents,
        };
        if (payload.parent_guest_id === '') payload.parent_guest_id = null;
        try {
            await axios.post('/api/v1/guests', payload);
            setStatus('#guest-form-status', 'Convidado salvo.', 'green');
            modalUi.toast('Convidado salvo com sucesso!', 'success');
            form.reset();
            // Restaura o wedding_id no input hidden
            if (currentWeddingId) {
                helpers.qs('#guest-wedding-select').value = currentWeddingId;
            }
            if (dependentsContainer) dependentsContainer.innerHTML = '';
            await fetchGuests(true, currentWeddingId);
            populateParentGuestSelect(currentWeddingId);
        } catch (err) {
            setStatus('#guest-form-status', 'Erro ao salvar convidado. Verifique casamento/pai e CPF único.', '#b84e00');
            modalUi.toast('Erro ao salvar convidado.', 'error');
        }
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    bootstrapCsrf();
    const page = document.body.dataset.page;

    if (page === 'users') {
        bindUserForm();
        await fetchUsers(true);
    }

    if (page === 'weddings') {
        bindWeddingForm();
        await fetchUsers(false); // load couples
        populateCoupleOptions();
        await fetchWeddings(true);
    }

    if (page === 'guests-select') {
        await fetchWeddings(false);
        renderWeddingsSelectGrid();
    }

    if (page === 'guests') {
        const currentWeddingId = helpers.qs('#current-wedding-id')?.value;
        bindGuestForm();
        await fetchWeddings(false);
        await fetchGuests(true, currentWeddingId);
        updateWeddingPageHeader(currentWeddingId);
    }

    if (page === 'dashboard') {
        await loadMetrics();
    }

    if (page === 'couple') {
        await fetchWeddings(false);
        
        // Para couple, pega automaticamente o primeiro (e único) casamento
        const coupleWeddingId = state.weddings[0]?.id || null;
        
        if (coupleWeddingId) {
            // Seta o wedding_id no input hidden
            const weddingInput = helpers.qs('#guest-wedding-select');
            if (weddingInput) {
                weddingInput.value = coupleWeddingId;
            }
            
            // Atualiza o resumo do casamento
            updateCoupleSummary();
        }
        
        bindGuestForm();
        await fetchGuests(true, coupleWeddingId);
        populateParentGuestSelect(coupleWeddingId);
    }

    if (page === 'tags') {
        await fetchWeddings(false);
        await fetchGuests(false);
        initTagsPage();
    }

    if (page === 'import') {
        await initImportPage();
    }

});

function renderWeddingsSelectGrid() {
    const container = helpers.qs('#weddings-grid');
    if (!container) return;
    container.innerHTML = '';

    if (state.weddings.length === 0) {
        const emptyCard = helpers.create('div', 'card card--surface');
        emptyCard.style.padding = '2rem';
        emptyCard.style.textAlign = 'center';
        emptyCard.style.gridColumn = '1 / -1';
        emptyCard.innerHTML = `
            <p style="color: #666; margin-bottom: 1rem;">Nenhum casamento cadastrado.</p>
            <a href="/cms/casamentos" class="btn btn--primary">Criar casamento</a>
        `;
        container.appendChild(emptyCard);
        return;
    }

    state.weddings.forEach((w) => {
        const card = helpers.create('a', 'card card--surface');
        card.href = `/cms/convidados/${w.id}`;
        card.style.textDecoration = 'none';
        card.style.display = 'block';
        card.style.transition = 'transform 120ms ease, box-shadow 120ms ease';
        card.style.cursor = 'pointer';
        
        const couples = (w.couples || []).map((c) => c.name).join(' & ') || 'Sem casal definido';
        
        card.innerHTML = `
            <p class="section__subtitle" style="color: var(--color-elegancia); margin-bottom: 0.25rem;">${helpers.formatDate(w.event_date)}</p>
            <h3 class="service-card__title" style="color: var(--color-elegancia); margin-bottom: 0.5rem;">${w.title}</h3>
            <p style="color: #2b2b2b; margin: 0.25rem 0; font-size: 0.95rem;">${couples}</p>
            <p style="color: #666; margin: 0.25rem 0; font-size: 0.9rem;">${w.location || 'Local não definido'}</p>
            <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid rgba(0,48,44,0.1);">
                <span style="color: var(--color-elegancia); font-weight: 600; font-size: 0.9rem;">Ver convidados →</span>
            </div>
        `;
        
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-2px)';
            card.style.boxShadow = '0 8px 24px rgba(0,0,0,0.12)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = '';
        });
        
        container.appendChild(card);
    });
}

function updateWeddingPageHeader(weddingId) {
    const titleEl = helpers.qs('#page-wedding-title');
    const infoEl = helpers.qs('#page-wedding-info');
    
    if (!titleEl || !weddingId) return;
    
    const wedding = state.weddings.find((w) => String(w.id) === String(weddingId));
    
    if (!wedding) {
        titleEl.textContent = 'Casamento não encontrado';
        infoEl.textContent = '';
        return;
    }
    
    const couples = (wedding.couples || []).map((c) => c.name).join(' & ') || '';
    
    titleEl.textContent = wedding.title;
    infoEl.textContent = `${helpers.formatDate(wedding.event_date)}${wedding.location ? ` · ${wedding.location}` : ''}${couples ? ` · ${couples}` : ''}`;
}

// ========== TAGS PAGE ==========

const tagsHelpers = {
    relationshipLabel(value) {
        const map = {
            mae: 'Mãe',
            pai: 'Pai',
            familia: 'Família',
            amigos: 'Amigos',
            trabalho: 'Trabalho',
            outros: 'Outros',
        };
        return map[value] ?? '-';
    },
    godparentLabel(value) {
        const map = {
            padrinho: 'Padrinho',
            madrinha: 'Madrinha',
        };
        return map[value] ?? '-';
    },
};

function initTagsPage() {
    // Garante que os estilos do modal estão carregados
    modalUi.ensureBase();
    
    populateBelongsToSelect();
    renderTagsTable();
    updateTagsStats();
    bindTagsFilters();
    bindTagModal();
}

function populateBelongsToSelect() {
    const select = helpers.qs('#tag-belongs-to');
    if (!select) return;
    
    // Pega os casais do casamento
    const wedding = state.weddings[0];
    if (!wedding || !wedding.couples) return;
    
    wedding.couples.forEach((couple) => {
        const opt = helpers.create('option');
        opt.value = couple.id;
        opt.textContent = couple.name;
        select.appendChild(opt);
    });
}

function updateTagsStats() {
    const padrinhos = state.guests.filter((g) => g.godparent_role === 'padrinho').length;
    const madrinhas = state.guests.filter((g) => g.godparent_role === 'madrinha').length;
    const familia = state.guests.filter((g) => g.relationship === 'familia' || g.relationship === 'mae' || g.relationship === 'pai').length;
    const amigos = state.guests.filter((g) => g.relationship === 'amigos').length;
    
    const padEl = helpers.qs('#stat-padrinhos');
    const madEl = helpers.qs('#stat-madrinhas');
    const famEl = helpers.qs('#stat-familia');
    const amiEl = helpers.qs('#stat-amigos');
    
    if (padEl) padEl.textContent = padrinhos;
    if (madEl) madEl.textContent = madrinhas;
    if (famEl) famEl.textContent = familia;
    if (amiEl) amiEl.textContent = amigos;
}

function getFilteredTagsGuests() {
    const relationshipFilter = helpers.qs('#filter-relationship')?.value || '';
    const godparentFilter = helpers.qs('#filter-godparent')?.value || '';
    
    return state.guests.filter((g) => {
        // Filtro de relacionamento
        if (relationshipFilter && g.relationship !== relationshipFilter) {
            return false;
        }
        
        // Filtro de padrinho/madrinha
        if (godparentFilter) {
            if (godparentFilter === 'none') {
                if (g.godparent_role) return false;
            } else {
                if (g.godparent_role !== godparentFilter) return false;
            }
        }
        
        return true;
    });
}

function renderTagsTable() {
    const container = helpers.qs('#tags-table');
    if (!container) return;
    container.innerHTML = '';
    
    const guests = getFilteredTagsGuests();
    
    if (guests.length === 0) {
        container.innerHTML = '<p style="color: #666; padding: 1rem 0;">Nenhum convidado encontrado com os filtros selecionados.</p>';
        return;
    }
    
    const wedding = state.weddings[0];
    const couplesMap = {};
    if (wedding?.couples) {
        wedding.couples.forEach((c) => { couplesMap[c.id] = c.name; });
    }

    const table = helpers.create('table');
    table.style.width = '100%';
    table.style.borderCollapse = 'collapse';
    const thead = helpers.create('thead');
    thead.innerHTML = `<tr>
        <th style="text-align:left; padding: 6px;">Nome</th>
        <th style="text-align:left; padding: 6px;">Padrinho/Madrinha</th>
        <th style="text-align:left; padding: 6px;">Relacionamento</th>
        <th style="text-align:left; padding: 6px;">Parte de</th>
        <th style="text-align:left; padding: 6px;">Status</th>
        <th style="padding:6px;">Ações</th>
    </tr>`;
    table.appendChild(thead);

    const tbody = helpers.create('tbody');
    guests.forEach((g) => {
        const belongsToName = g.belongs_to_user_id ? (couplesMap[g.belongs_to_user_id] || '-') : '-';
        const godparentBadge = g.godparent_role 
            ? `<span style="background: ${g.godparent_role === 'padrinho' ? '#0d423c' : '#8b5a7c'}; color: #fff; padding: 2px 8px; border-radius: 6px; font-size: 0.8rem;">${tagsHelpers.godparentLabel(g.godparent_role)}</span>`
            : '-';
        
        const tr = helpers.create('tr');
        tr.innerHTML = `
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05);">${g.name}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05);">${godparentBadge}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05);">${tagsHelpers.relationshipLabel(g.relationship)}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05);">${belongsToName}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05);">${helpers.statusLabel(g.status)}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05); text-align:right;">
                <button data-guest-id="${g.id}" class="btn-edit-tag" style="padding:6px 10px; border:1px solid rgba(0,0,0,0.15); border-radius:8px; background:#fff; cursor: pointer;">Editar</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    container.appendChild(table);

    // Bind edit buttons
    document.querySelectorAll('.btn-edit-tag').forEach((btn) => {
        btn.addEventListener('click', () => {
            const guestId = btn.getAttribute('data-guest-id');
            openTagModal(guestId);
        });
    });
}

function bindTagsFilters() {
    const relationshipFilter = helpers.qs('#filter-relationship');
    const godparentFilter = helpers.qs('#filter-godparent');
    
    relationshipFilter?.addEventListener('change', renderTagsTable);
    godparentFilter?.addEventListener('change', renderTagsTable);
}

function openTagModal(guestId) {
    const guest = state.guests.find((g) => String(g.id) === String(guestId));
    if (!guest) return;
    
    const overlay = helpers.qs('#tag-modal-overlay');
    const titleEl = helpers.qs('#tag-modal-title');
    const guestIdInput = helpers.qs('#tag-guest-id');
    const godparentSelect = helpers.qs('#tag-godparent-role');
    const relationshipSelect = helpers.qs('#tag-relationship');
    const belongsToSelect = helpers.qs('#tag-belongs-to');
    
    titleEl.textContent = `Marcações: ${guest.name}`;
    guestIdInput.value = guest.id;
    godparentSelect.value = guest.godparent_role || '';
    relationshipSelect.value = guest.relationship || '';
    belongsToSelect.value = guest.belongs_to_user_id || '';
    
    overlay.classList.add('is-open');
}

function closeTagModal() {
    const overlay = helpers.qs('#tag-modal-overlay');
    overlay.classList.remove('is-open');
}

function bindTagModal() {
    const cancelBtn = helpers.qs('#tag-modal-cancel');
    const overlay = helpers.qs('#tag-modal-overlay');
    const form = helpers.qs('#tag-form');
    
    cancelBtn?.addEventListener('click', closeTagModal);
    
    overlay?.addEventListener('click', (e) => {
        if (e.target === overlay) closeTagModal();
    });
    
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const guestId = helpers.qs('#tag-guest-id').value;
        const godparentRole = helpers.qs('#tag-godparent-role').value || null;
        const relationship = helpers.qs('#tag-relationship').value || null;
        const belongsToUserId = helpers.qs('#tag-belongs-to').value || null;
        
        try {
            await axios.patch(`/api/v1/guests/${guestId}/tags`, {
                godparent_role: godparentRole,
                relationship: relationship,
                belongs_to_user_id: belongsToUserId,
                is_godparent: !!godparentRole,
            });
            
            
            modalUi.toast('Marcações salvas!', 'success');
            closeTagModal();
            
            // Recarrega os guests
            await fetchGuests(false);
            renderTagsTable();
            updateTagsStats();
        } catch (err) {
            modalUi.toast('Erro ao salvar marcações.', 'error');
        }
    });
}

// ========== IMPORT PAGE ==========

const importState = {
    file: null,
    rawData: [],      // Dados crus da planilha [[row1], [row2], ...]
    headers: [],      // Cabeçalhos detectados ou gerados
    hasHeader: true,
    mapping: {},      // { name: 0, relationship: 1, ... }
    previewData: [],  // Dados mapeados para preview
    couples: [],      // Lista de casais do wedding para o campo "belongs_to"
};

async function initImportPage() {
    modalUi.ensureBase();
    
    // Buscar casais do wedding
    await fetchWeddings(false);
    const wedding = state.weddings[0];
    if (wedding?.couples) {
        importState.couples = wedding.couples;
    }
    
    bindImportDropzone();
    bindImportButtons();
    bindTemplateDownload();
}

function bindTemplateDownload() {
    helpers.qs('#btn-download-xlsx')?.addEventListener('click', () => downloadTemplate('xlsx'));
    helpers.qs('#btn-download-csv')?.addEventListener('click', () => downloadTemplate('csv'));
}

async function downloadTemplate(format) {
    try {
        const XLSX = await import('xlsx');
        
        // Dados do template com exemplos
        const templateData = [
            ['Nome', 'Relacionamento', 'Padrinho/Madrinha', 'CPF', 'E-mail', 'Telefone'],
            ['Maria Silva', 'Família', '', '12345678901', 'maria@email.com', '11999998888'],
            ['João Santos', 'Amigos', 'Padrinho', '', 'joao@email.com', '11988887777'],
            ['Ana Oliveira', 'Família', 'Madrinha', '98765432100', '', '11977776666'],
            ['Pedro Costa', 'Trabalho', '', '', 'pedro@empresa.com', ''],
            ['', '', '', '', '', ''], // Linha vazia para o usuário preencher
        ];
        
        // Criar workbook
        const ws = XLSX.utils.aoa_to_sheet(templateData);
        
        // Ajustar largura das colunas
        ws['!cols'] = [
            { wch: 25 }, // Nome
            { wch: 15 }, // Relacionamento
            { wch: 18 }, // Padrinho/Madrinha
            { wch: 14 }, // CPF
            { wch: 25 }, // E-mail
            { wch: 15 }, // Telefone
        ];
        
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Convidados');
        
        // Gerar arquivo
        if (format === 'xlsx') {
            XLSX.writeFile(wb, 'modelo_convidados_mew.xlsx');
        } else {
            XLSX.writeFile(wb, 'modelo_convidados_mew.csv', { bookType: 'csv' });
        }
        
        modalUi.toast('Template baixado com sucesso!', 'success');
    } catch (err) {
        console.error('Erro ao gerar template:', err);
        modalUi.toast('Erro ao gerar o arquivo.', 'error');
    }
}

function bindImportDropzone() {
    const dropzone = helpers.qs('#dropzone');
    const fileInput = helpers.qs('#file-input');
    
    if (!dropzone || !fileInput) return;
    
    dropzone.addEventListener('click', () => fileInput.click());
    
    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.style.borderColor = 'var(--color-elegancia)';
        dropzone.style.background = 'rgba(13, 66, 60, 0.05)';
    });
    
    dropzone.addEventListener('dragleave', () => {
        dropzone.style.borderColor = 'rgba(0,0,0,0.15)';
        dropzone.style.background = 'transparent';
    });
    
    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.style.borderColor = 'rgba(0,0,0,0.15)';
        dropzone.style.background = 'transparent';
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });
    
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });
    
    helpers.qs('#btn-remove-file')?.addEventListener('click', resetImport);
}

function handleFileSelect(file) {
    const validTypes = [
        'text/csv',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];
    const validExtensions = ['.csv', '.xls', '.xlsx'];
    
    const ext = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();
    
    if (!validExtensions.includes(ext)) {
        modalUi.toast('Formato de arquivo não suportado. Use CSV, XLS ou XLSX.', 'error');
        return;
    }
    
    importState.file = file;
    
    // Mostrar info do arquivo
    helpers.qs('#file-name').textContent = file.name;
    helpers.qs('#file-size').textContent = formatFileSize(file.size);
    helpers.qs('#file-info').style.display = 'block';
    helpers.qs('#dropzone').style.display = 'none';
    
    // Parsear arquivo
    parseFile(file);
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' bytes';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

async function parseFile(file) {
    try {
        const XLSX = await import('xlsx');
        
        const ext = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();
        
        // Para CSV, tentar detectar encoding
        if (ext === '.csv') {
            const textReader = new FileReader();
            textReader.onload = (e) => {
                try {
                    let text = e.target.result;
                    
                    // Tentar corrigir encoding Latin-1 para UTF-8
                    if (text.includes('Ã') || text.includes('Ã©') || text.includes('Ã£')) {
                        // Texto parece estar em UTF-8 mas foi lido errado, tentar decodificar
                        try {
                            const bytes = new Uint8Array(text.split('').map(c => c.charCodeAt(0)));
                            text = new TextDecoder('utf-8').decode(bytes);
                        } catch (decodeErr) {
                            // Ignora erro de decodificação
                        }
                    }
                    
                    const workbook = XLSX.read(text, { type: 'string' });
                    processWorkbook(workbook, XLSX);
                } catch (err) {
                    console.error('Erro ao parsear CSV:', err);
                    // Fallback: tentar como binary
                    parseFileAsBinary(file, XLSX);
                }
            };
            textReader.readAsText(file, 'UTF-8');
        } else {
            // Para XLS/XLSX, ler como array buffer
            parseFileAsBinary(file, XLSX);
        }
    } catch (err) {
        console.error('Erro ao importar XLSX:', err);
        modalUi.toast('Erro ao carregar biblioteca de leitura.', 'error');
    }
}

function parseFileAsBinary(file, XLSX) {
    const reader = new FileReader();
    reader.onload = (e) => {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array', codepage: 65001 }); // UTF-8
            processWorkbook(workbook, XLSX);
        } catch (err) {
            console.error('Erro ao parsear:', err);
            modalUi.toast('Erro ao ler o arquivo. Verifique se está correto.', 'error');
        }
    };
    reader.readAsArrayBuffer(file);
}

function processWorkbook(workbook, XLSX) {
    // Pegar primeira sheet
    const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
    const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1, defval: '', raw: false });
    
    if (jsonData.length === 0) {
        modalUi.toast('A planilha está vazia.', 'error');
        resetImport();
        return;
    }
    
    // Limpar caracteres mal codificados
    const cleanedData = jsonData.map(row => 
        row.map(cell => fixEncoding(String(cell)))
    );
    
    importState.rawData = cleanedData;
    
    // Detectar cabeçalhos
    const firstRow = cleanedData[0];
    if (Array.isArray(firstRow)) {
        importState.headers = firstRow.map((h, i) => String(h || `Coluna ${i + 1}`));
    }
    
    // Mostrar step de mapeamento
    showMappingStep();
}

function fixEncoding(str) {
    if (!str) return str;
    
    // Mapa de caracteres mal codificados (Latin-1 lido como UTF-8)
    const fixes = {
        'Ã©': 'é',
        'Ã¡': 'á',
        'Ã£': 'ã',
        'Ãª': 'ê',
        'Ã­': 'í',
        'Ã³': 'ó',
        'Ãº': 'ú',
        'Ã§': 'ç',
        'Ã': 'Á',
        'Ã‰': 'É',
        'Ã"': 'Ó',
        'Ãš': 'Ú',
        'Ã‡': 'Ç',
        'Ã¢': 'â',
        'Ã´': 'ô',
        'Ã¼': 'ü',
        'Ã±': 'ñ',
        'Ã€': 'À',
        'Ã‚': 'Â',
        'ÃŠ': 'Ê',
        'ÃŽ': 'Î',
        'Ã"': 'Ô',
        'Ã›': 'Û',
        'Ã¨': 'è',
        'Ã¬': 'ì',
        'Ã²': 'ò',
        'Ã¹': 'ù',
    };
    
    let fixed = str;
    for (const [bad, good] of Object.entries(fixes)) {
        fixed = fixed.split(bad).join(good);
    }
    
    return fixed;
}

function showMappingStep() {
    helpers.qs('#step-mapping').style.display = 'block';
    
    // Popular selects de mapeamento
    const selects = ['map-name', 'map-relationship', 'map-godparent', 'map-belongs-to', 'map-cpf', 'map-email', 'map-phone'];
    
    selects.forEach((selectId) => {
        const select = helpers.qs(`#${selectId}`);
        if (!select) return;
        
        // Limpar opções existentes (menos a primeira)
        while (select.options.length > 1) {
            select.remove(1);
        }
        
        // Adicionar colunas
        importState.headers.forEach((header, index) => {
            const opt = document.createElement('option');
            opt.value = index;
            opt.textContent = header;
            select.appendChild(opt);
        });
    });
    
    // Auto-detectar mapeamento
    autoDetectMapping();
}

function autoDetectMapping() {
    const namePatterns = ['nome', 'name', 'convidado', 'guest'];
    const relationshipPatterns = ['relação', 'relacionamento', 'relationship', 'tipo', 'type'];
    const godparentPatterns = ['padrinho', 'madrinha', 'godparent', 'godfather', 'godmother'];
    const belongsPatterns = ['parte', 'lado', 'de quem', 'belongs', 'side'];
    const cpfPatterns = ['cpf', 'documento', 'document'];
    const emailPatterns = ['email', 'e-mail', 'mail'];
    const phonePatterns = ['telefone', 'phone', 'celular', 'tel', 'whatsapp'];
    
    importState.headers.forEach((header, index) => {
        const h = header.toLowerCase();
        
        if (namePatterns.some(p => h.includes(p))) {
            helpers.qs('#map-name').value = index;
        }
        if (relationshipPatterns.some(p => h.includes(p))) {
            helpers.qs('#map-relationship').value = index;
        }
        if (godparentPatterns.some(p => h.includes(p))) {
            helpers.qs('#map-godparent').value = index;
        }
        if (belongsPatterns.some(p => h.includes(p))) {
            helpers.qs('#map-belongs-to').value = index;
        }
        if (cpfPatterns.some(p => h.includes(p))) {
            helpers.qs('#map-cpf').value = index;
        }
        if (emailPatterns.some(p => h.includes(p))) {
            helpers.qs('#map-email').value = index;
        }
        if (phonePatterns.some(p => h.includes(p))) {
            helpers.qs('#map-phone').value = index;
        }
    });
}

function bindImportButtons() {
    helpers.qs('#has-header')?.addEventListener('change', (e) => {
        importState.hasHeader = e.target.checked;
    });
    
    helpers.qs('#btn-apply-mapping')?.addEventListener('click', applyMapping);
    helpers.qs('#btn-back-mapping')?.addEventListener('click', () => {
        helpers.qs('#step-preview').style.display = 'none';
        helpers.qs('#step-mapping').style.display = 'block';
    });
    helpers.qs('#btn-import')?.addEventListener('click', doImport);
    helpers.qs('#btn-new-import')?.addEventListener('click', () => {
        resetImport();
        helpers.qs('#step-success').style.display = 'none';
        helpers.qs('#step-upload').style.display = 'block';
    });
}

function applyMapping() {
    const nameCol = helpers.qs('#map-name').value;
    
    if (nameCol === '') {
        modalUi.toast('Selecione a coluna do nome do convidado.', 'error');
        return;
    }
    
    importState.mapping = {
        name: nameCol !== '' ? parseInt(nameCol) : null,
        relationship: helpers.qs('#map-relationship').value !== '' ? parseInt(helpers.qs('#map-relationship').value) : null,
        godparent_role: helpers.qs('#map-godparent').value !== '' ? parseInt(helpers.qs('#map-godparent').value) : null,
        belongs_to: helpers.qs('#map-belongs-to').value !== '' ? parseInt(helpers.qs('#map-belongs-to').value) : null,
        cpf: helpers.qs('#map-cpf').value !== '' ? parseInt(helpers.qs('#map-cpf').value) : null,
        email: helpers.qs('#map-email').value !== '' ? parseInt(helpers.qs('#map-email').value) : null,
        phone: helpers.qs('#map-phone').value !== '' ? parseInt(helpers.qs('#map-phone').value) : null,
    };
    
    // Processar dados
    const startRow = importState.hasHeader ? 1 : 0;
    importState.previewData = [];
    
    for (let i = startRow; i < importState.rawData.length; i++) {
        const row = importState.rawData[i];
        const name = row[importState.mapping.name];
        
        // Pular linhas sem nome
        if (!name || String(name).trim() === '') continue;
        
        const guest = {
            _rowIndex: i,
            _valid: true,
            _errors: [],
            name: String(name).trim(),
            relationship: importState.mapping.relationship !== null ? String(row[importState.mapping.relationship] || '').trim() : '',
            godparent_role: importState.mapping.godparent_role !== null ? String(row[importState.mapping.godparent_role] || '').trim() : '',
            belongs_to_text: importState.mapping.belongs_to !== null ? String(row[importState.mapping.belongs_to] || '').trim() : '',
            belongs_to_user_id: null,
            cpf: importState.mapping.cpf !== null ? String(row[importState.mapping.cpf] || '').trim() : '',
            email: importState.mapping.email !== null ? String(row[importState.mapping.email] || '').trim() : '',
            phone: importState.mapping.phone !== null ? String(row[importState.mapping.phone] || '').trim() : '',
        };
        
        // Tentar mapear belongs_to para user_id
        if (guest.belongs_to_text && importState.couples.length > 0) {
            const match = importState.couples.find(c => 
                c.name.toLowerCase().includes(guest.belongs_to_text.toLowerCase()) ||
                guest.belongs_to_text.toLowerCase().includes(c.name.toLowerCase())
            );
            if (match) {
                guest.belongs_to_user_id = match.id;
            }
        }
        
        // Validar
        if (guest.name.length < 2) {
            guest._valid = false;
            guest._errors.push('Nome muito curto');
        }
        
        if (guest.email && !guest.email.includes('@')) {
            guest._valid = false;
            guest._errors.push('E-mail inválido');
        }
        
        importState.previewData.push(guest);
    }
    
    // Mostrar preview
    showPreviewStep();
}

function showPreviewStep() {
    helpers.qs('#step-mapping').style.display = 'none';
    helpers.qs('#step-preview').style.display = 'block';
    
    renderPreviewTable();
    updatePreviewCounts();
}

function updatePreviewCounts() {
    const total = importState.previewData.length;
    const valid = importState.previewData.filter(g => g._valid).length;
    
    helpers.qs('#preview-count').textContent = `${total} registro(s) encontrado(s)`;
    helpers.qs('#valid-count').textContent = valid;
}

function renderPreviewTable() {
    const container = helpers.qs('#preview-table-container');
    if (!container) return;
    
    const table = helpers.create('table');
    table.style.width = '100%';
    table.style.borderCollapse = 'collapse';
    table.style.fontSize = '0.9rem';
    
    const thead = helpers.create('thead');
    thead.innerHTML = `<tr style="position: sticky; top: 0; background: #f5f0e8;">
        <th style="text-align:left; padding: 8px; border-bottom: 2px solid rgba(0,0,0,0.1);">#</th>
        <th style="text-align:left; padding: 8px; border-bottom: 2px solid rgba(0,0,0,0.1);">Nome</th>
        <th style="text-align:left; padding: 8px; border-bottom: 2px solid rgba(0,0,0,0.1);">Relacionamento</th>
        <th style="text-align:left; padding: 8px; border-bottom: 2px solid rgba(0,0,0,0.1);">Padrinho/Madrinha</th>
        <th style="text-align:left; padding: 8px; border-bottom: 2px solid rgba(0,0,0,0.1);">CPF</th>
        <th style="text-align:left; padding: 8px; border-bottom: 2px solid rgba(0,0,0,0.1);">E-mail</th>
        <th style="text-align:left; padding: 8px; border-bottom: 2px solid rgba(0,0,0,0.1);">Ações</th>
    </tr>`;
    table.appendChild(thead);
    
    const tbody = helpers.create('tbody');
    
    importState.previewData.forEach((guest, index) => {
        const tr = helpers.create('tr');
        tr.style.background = guest._valid ? 'transparent' : 'rgba(180, 51, 43, 0.08)';
        
        tr.innerHTML = `
            <td style="padding: 8px; border-bottom: 1px solid rgba(0,0,0,0.05);">${index + 1}</td>
            <td style="padding: 8px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                <input type="text" value="${escapeHtml(guest.name)}" data-index="${index}" data-field="name" class="preview-input" style="width: 100%; padding: 4px 8px; border: 1px solid rgba(0,0,0,0.1); border-radius: 4px;">
            </td>
            <td style="padding: 8px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                <select data-index="${index}" data-field="relationship" class="preview-input" style="width: 100%; padding: 4px 8px; border: 1px solid rgba(0,0,0,0.1); border-radius: 4px;">
                    <option value="">-</option>
                    <option value="mae" ${guest.relationship.toLowerCase().includes('mãe') || guest.relationship === 'mae' ? 'selected' : ''}>Mãe</option>
                    <option value="pai" ${guest.relationship.toLowerCase().includes('pai') ? 'selected' : ''}>Pai</option>
                    <option value="familia" ${guest.relationship.toLowerCase().includes('famíl') || guest.relationship.toLowerCase().includes('famil') ? 'selected' : ''}>Família</option>
                    <option value="amigos" ${guest.relationship.toLowerCase().includes('amig') ? 'selected' : ''}>Amigos</option>
                    <option value="trabalho" ${guest.relationship.toLowerCase().includes('trabalh') || guest.relationship.toLowerCase().includes('coleg') ? 'selected' : ''}>Trabalho</option>
                    <option value="outros" ${guest.relationship.toLowerCase().includes('outro') ? 'selected' : ''}>Outros</option>
                </select>
            </td>
            <td style="padding: 8px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                <select data-index="${index}" data-field="godparent_role" class="preview-input" style="width: 100%; padding: 4px 8px; border: 1px solid rgba(0,0,0,0.1); border-radius: 4px;">
                    <option value="">-</option>
                    <option value="padrinho" ${guest.godparent_role.toLowerCase().includes('padrinho') ? 'selected' : ''}>Padrinho</option>
                    <option value="madrinha" ${guest.godparent_role.toLowerCase().includes('madrinha') ? 'selected' : ''}>Madrinha</option>
                </select>
            </td>
            <td style="padding: 8px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                <input type="text" value="${escapeHtml(guest.cpf)}" data-index="${index}" data-field="cpf" class="preview-input" style="width: 100%; padding: 4px 8px; border: 1px solid rgba(0,0,0,0.1); border-radius: 4px;" placeholder="00000000000">
            </td>
            <td style="padding: 8px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                <input type="email" value="${escapeHtml(guest.email)}" data-index="${index}" data-field="email" class="preview-input" style="width: 100%; padding: 4px 8px; border: 1px solid rgba(0,0,0,0.1); border-radius: 4px;">
            </td>
            <td style="padding: 8px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                <button type="button" data-index="${index}" class="btn-remove-row" style="padding: 4px 8px; background: #b4332b; color: #fff; border: none; border-radius: 4px; cursor: pointer;">✕</button>
            </td>
        `;
        
        tbody.appendChild(tr);
    });
    
    table.appendChild(tbody);
    container.innerHTML = '';
    container.appendChild(table);
    
    // Bind inputs
    document.querySelectorAll('.preview-input').forEach(input => {
        input.addEventListener('change', (e) => {
            const index = parseInt(e.target.dataset.index);
            const field = e.target.dataset.field;
            importState.previewData[index][field] = e.target.value;
            
            // Re-validar
            validatePreviewRow(index);
        });
    });
    
    // Bind remove buttons
    document.querySelectorAll('.btn-remove-row').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const index = parseInt(e.target.dataset.index);
            importState.previewData.splice(index, 1);
            renderPreviewTable();
            updatePreviewCounts();
        });
    });
}

function validatePreviewRow(index) {
    const guest = importState.previewData[index];
    guest._valid = true;
    guest._errors = [];
    
    if (!guest.name || guest.name.length < 2) {
        guest._valid = false;
        guest._errors.push('Nome muito curto');
    }
    
    if (guest.email && !guest.email.includes('@')) {
        guest._valid = false;
        guest._errors.push('E-mail inválido');
    }
    
    // Atualizar visual
    const row = document.querySelector(`tr:has([data-index="${index}"])`);
    if (row) {
        row.style.background = guest._valid ? 'transparent' : 'rgba(180, 51, 43, 0.08)';
    }
    
    updatePreviewCounts();
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

async function doImport() {
    const validGuests = importState.previewData.filter(g => g._valid);
    
    if (validGuests.length === 0) {
        modalUi.toast('Nenhum convidado válido para importar.', 'error');
        return;
    }
    
    const weddingId = helpers.qs('#wedding_id')?.value;
    if (!weddingId) {
        modalUi.toast('Erro: casamento não identificado.', 'error');
        return;
    }
    
    const confirmed = await modalUi.show({
        title: 'Confirmar importação',
        text: `Deseja importar ${validGuests.length} convidado(s)?`,
        confirmText: 'Importar',
        cancelText: 'Cancelar',
        tone: 'info',
    });
    
    if (!confirmed) return;
    
    try {
        const payload = {
            wedding_id: parseInt(weddingId),
            guests: validGuests.map(g => ({
                name: g.name,
                relationship: g.relationship || null,
                godparent_role: g.godparent_role || null,
                belongs_to_user_id: g.belongs_to_user_id,
                cpf: g.cpf || null,
                email: g.email || null,
                phone: g.phone || null,
            })),
        };
        
        const res = await axios.post('/api/v1/guests/import', payload);
        
        if (res.data.success) {
            helpers.qs('#step-preview').style.display = 'none';
            helpers.qs('#step-success').style.display = 'block';
            helpers.qs('#success-message').textContent = res.data.message;
            
            if (res.data.errors && res.data.errors.length > 0) {
                helpers.qs('#success-message').textContent += ` (${res.data.errors.length} erro(s))`;
            }
        } else {
            modalUi.toast(res.data.message || 'Erro ao importar.', 'error');
        }
    } catch (err) {
        console.error('Erro na importação:', err);
        modalUi.toast('Erro ao importar convidados.', 'error');
    }
}

function resetImport() {
    importState.file = null;
    importState.rawData = [];
    importState.headers = [];
    importState.mapping = {};
    importState.previewData = [];
    
    helpers.qs('#file-input').value = '';
    helpers.qs('#file-info').style.display = 'none';
    helpers.qs('#dropzone').style.display = 'block';
    helpers.qs('#step-mapping').style.display = 'none';
    helpers.qs('#step-preview').style.display = 'none';
}
