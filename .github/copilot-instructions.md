# Instruções Copilot para MEW (Marriage Event Wizard)

## Visão Geral

MEW é um CMS Laravel 12 para gerenciamento de casamentos, casais e convidados. Possui controle de acesso por roles (superadmin, admin, couple, guest) com interface web e API REST.

## Arquitetura

### Models Principais (`app/Models/`)
- **User** → Membros do casal que possuem casamentos (`belongsToMany Wedding` via pivot `wedding_user`)
- **Wedding** → Possui muitos convidados, pertence a muitos casais
- **Guest** → Pertence a um casamento, suporta hierarquia familiar (`parent_guest_id` para dependentes)

### Relacionamentos
```
User ←→ Wedding (many-to-many via wedding_user com pivot role/is_primary)
Wedding → Guest (one-to-many)
Guest → Guest (auto-referência parent/children para famílias)
```

### Enums (`app/Enums/`)
Sempre use enums tipados para campos de status/role:
- `UserRole`: `superadmin`, `admin`, `guest`, `couple`
- `GuestStatus`: `pending`, `accepted`, `rejected`

## Comandos de Desenvolvimento

### Iniciar Desenvolvimento
```bash
composer dev
```
Executa servidor Laravel, queue, logs (pail) e Vite simultaneamente.

### Reset do Banco
```bash
php artisan migrate:fresh --seed
```
Cria dados demo: superadmin (`superadmin@mew.test`), usuários casal, casamento exemplo com convidados.

### Testes
```bash
composer test
```
Usa SQLite em memória (`:memory:`) conforme `phpunit.xml`.

## Convenções

### Rotas API (`routes/api.php`)
- Prefixo: `/api/v1/`
- Controllers de recurso: `users`, `weddings`, `guests`
- Endpoints de convite: `POST invitations/confirm`, `GET invitations/lookup`

### Rotas Web (`routes/web.php`)
- CMS protegido por middleware `auth` + `role:superadmin` ou `role:couple`
- Confirmação de presença: `/confirmacao` (público)

### Controle por Role (`EnsureUserHasRole` middleware)
```php
Route::middleware(['auth', 'role:superadmin'])->group(...);
Route::middleware(['auth', 'role:couple'])->group(...);
```

### Códigos de Convite
Gerados automaticamente em `Guest::booted()` usando nome + título do casamento + dígitos aleatórios.

### Estrutura Familiar
- `is_head_of_family: true` → Convidado responsável com CPF para lookup
- `parent_guest_id` → Liga dependentes ao responsável
- `party_size` → Calculado: quantidade de dependentes + 1

## Frontend

### Stack
- Templates Blade com Tailwind CSS 4 (`@tailwindcss/vite`)
- Vanilla JS para interatividade do CMS (`resources/js/cms-pages.js`)
- Sistema próprio de modal/toast (sem dependências externas)

### Views do CMS (`resources/views/cms/`)
- `layout.blade.php` → Layout base
- Páginas: `dashboard`, `users`, `weddings`, `guests` (superadmin)
- `couple.blade.php` → Gerenciamento de convidados para casais

### Entry Points
- `resources/js/app.js` → Bundle principal
- `resources/js/cms-pages.js` → Lógica do CMS (650+ linhas de operações CRUD)

## Padrões de API

### Autorização em Controllers
Controllers verificam `auth()->user()->role` para filtrar dados:
```php
if ($role === 'couple') {
    $allowedWeddingIds = $user->weddings()->pluck('wedding_id');
    $query->whereIn('wedding_id', $allowedWeddingIds);
}
```

### Lookup de Convite
Resolve por CPF (11 dígitos → família) ou código de convite (individual):
```php
$isCpf = strlen(preg_replace('/\D/', '', $identifier)) === 11;
```

## Referência Rápida de Arquivos

| Caminho | Propósito |
|---------|-----------|
| `app/Http/Controllers/InvitationController.php` | Fluxo de confirmação RSVP |
| `app/Http/Middleware/EnsureUserHasRole.php` | Proteção de rotas por role |
| `database/seeders/DatabaseSeeder.php` | Dados demo com todas as roles |
| `resources/js/cms-pages.js` | Estado do CMS e chamadas API |
| `references/` | Referências estáticas de design HTML |
