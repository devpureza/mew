import './bootstrap';

const state = {
    users: [],
    weddings: [],
    guests: [],
};

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
};

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
                <button data-user-id="${u.id}" class="btn-delete-user" style="padding:6px 10px; border:1px solid rgba(0,0,0,0.15); border-radius:8px; background:#fff;">Excluir</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    container.appendChild(table);

    document.querySelectorAll('.btn-delete-user').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-user-id');
            if (!id) return;
            await axios.delete(`/api/v1/users/${id}`);
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
            role: fd.get('role'),
            birth_date: fd.get('birth_date') || null,
            address_line: fd.get('address_line') || null,
            password: fd.get('password'),
        };
        try {
            await axios.post('/api/v1/users', payload);
            setStatus('#user-form-status', 'Usuário salvo.', 'green');
            form.reset();
            await fetchUsers(true);
        } catch (err) {
            setStatus('#user-form-status', 'Erro ao salvar (CPF/email únicos?).', '#b84e00');
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
        <th style="text-align:left; padding: 6px;">Título</th>
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
                <button data-wedding-id="${w.id}" class="btn-delete-wedding" style="padding:6px 10px; border:1px solid rgba(0,0,0,0.15); border-radius:8px; background:#fff;">Excluir</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    container.appendChild(table);

    document.querySelectorAll('.btn-delete-wedding').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-wedding-id');
            if (!id) return;
            await axios.delete(`/api/v1/weddings/${id}`);
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
async function fetchGuests(render = true) {
    const { data } = await axios.get('/api/v1/guests');
    state.guests = data.data ?? data;
    if (render) {
        renderGuestsTable();
        populateParentGuestSelect();
    }
    populateWeddingSelect();
}

function populateWeddingSelect() {
    const select = helpers.qs('#guest-wedding-select');
    if (!select) return;
    const current = select.value;
    select.innerHTML = '<option value="">Selecione um casamento</option>';
    state.weddings.forEach((w) => {
        const opt = helpers.create('option');
        opt.value = w.id;
        opt.textContent = `${w.title} (${helpers.formatDate(w.event_date)})`;
        if (current && current === String(w.id)) opt.selected = true;
        select.appendChild(opt);
    });
}

function populateParentGuestSelect() {
    const select = helpers.qs('#parent-guest-select');
    if (!select) return;
    const current = select.value;
    select.innerHTML = '<option value="">Responsável (pai/mãe)</option>';
    state.guests
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

    const table = helpers.create('table');
    table.style.width = '100%';
    table.style.borderCollapse = 'collapse';
    const thead = helpers.create('thead');
    thead.innerHTML = `<tr>
        <th style="text-align:left; padding: 6px;">Nome</th>
        <th style="text-align:left; padding: 6px;">CPF</th>
        <th style="text-align:left; padding: 6px;">Código</th>
        <th style="text-align:left; padding: 6px;">Status</th>
        <th style="text-align:left; padding: 6px;">Casamento</th>
        <th style="text-align:left; padding: 6px;">Pai/Responsável</th>
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
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05);">${g.wedding?.title ?? ''}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05);">${g.parent_guest?.name ?? '-'}</td>
            <td style="padding:6px; border-top:1px solid rgba(0,0,0,0.05); text-align:right;">
                <button data-guest-id="${g.id}" class="btn-delete-guest" style="padding:6px 10px; border:1px solid rgba(0,0,0,0.15); border-radius:8px; background:#fff;">Excluir</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    container.appendChild(table);

    document.querySelectorAll('.btn-delete-guest').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-guest-id');
            if (!id) return;
            await axios.delete(`/api/v1/guests/${id}`);
            await fetchGuests(true);
        });
    });
}

function bindGuestForm() {
    const form = helpers.qs('#guest-form');
    const addDependentBtn = helpers.qs('#add-dependent');
    const dependentsContainer = helpers.qs('#dependents-container');

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
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
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
            form.reset();
            dependentsContainer.innerHTML = '';
            await fetchGuests(true);
        } catch (err) {
            setStatus('#guest-form-status', 'Erro ao salvar convidado. Verifique casamento/pai e CPF único.', '#b84e00');
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

    if (page === 'guests') {
        bindGuestForm();
        await fetchWeddings(false);
        await fetchGuests(true);
        populateWeddingSelect();
        populateParentGuestSelect();
    }

    if (page === 'dashboard') {
        await loadMetrics();
    }

    if (page === 'couple') {
        bindGuestForm();
        await fetchWeddings(false);
        await fetchGuests(true);
        populateWeddingSelect();
        populateParentGuestSelect();
    }

});
