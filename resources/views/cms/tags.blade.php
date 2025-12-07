@extends('cms.layout')

@section('title', 'Marcações · MEW')
@section('page', 'tags')

@section('content')
    <div class="section__header" style="margin-top:0;">
        <p class="section__subtitle" style="color: var(--color-elegancia);">Marcações</p>
        <h1 class="section__title" style="color: var(--color-elegancia);">Organize seus convidados</h1>
        <p style="color: #2b2b2b;">Defina padrinhos, madrinhas, relacionamentos e de qual parte cada convidado é.</p>
    </div>

    <div class="grid" style="gap: 1rem; margin-bottom: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
        <div class="card card--surface">
            <p class="section__subtitle" style="color: var(--color-elegancia); margin-bottom: 0.25rem;">Padrinhos</p>
            <h3 class="service-card__title" style="color: var(--color-elegancia); font-size: 2rem; margin: 0;" id="stat-padrinhos">--</h3>
        </div>
        <div class="card card--surface">
            <p class="section__subtitle" style="color: var(--color-elegancia); margin-bottom: 0.25rem;">Madrinhas</p>
            <h3 class="service-card__title" style="color: var(--color-elegancia); font-size: 2rem; margin: 0;" id="stat-madrinhas">--</h3>
        </div>
        <div class="card card--surface">
            <p class="section__subtitle" style="color: var(--color-elegancia); margin-bottom: 0.25rem;">Família</p>
            <h3 class="service-card__title" style="color: var(--color-elegancia); font-size: 2rem; margin: 0;" id="stat-familia">--</h3>
        </div>
        <div class="card card--surface">
            <p class="section__subtitle" style="color: var(--color-elegancia); margin-bottom: 0.25rem;">Amigos</p>
            <h3 class="service-card__title" style="color: var(--color-elegancia); font-size: 2rem; margin: 0;" id="stat-amigos">--</h3>
        </div>
    </div>

    <div class="card card--surface">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
            <h3 class="service-card__title" style="color: var(--color-elegancia); margin: 0;">Convidados</h3>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <select class="select" id="filter-relationship" style="min-width: 140px;">
                    <option value="">Todos os relacionamentos</option>
                    <option value="mae">Mãe</option>
                    <option value="pai">Pai</option>
                    <option value="familia">Família</option>
                    <option value="amigos">Amigos</option>
                    <option value="trabalho">Trabalho</option>
                    <option value="outros">Outros</option>
                </select>
                <select class="select" id="filter-godparent" style="min-width: 140px;">
                    <option value="">Todos</option>
                    <option value="padrinho">Padrinhos</option>
                    <option value="madrinha">Madrinhas</option>
                    <option value="none">Sem marcação</option>
                </select>
            </div>
        </div>
        <div id="tags-table"></div>
    </div>

    {{-- Modal de edição --}}
    <div id="tag-modal-overlay" class="mew-overlay">
        <div class="mew-modal" style="width: min(500px, calc(100% - 32px));">
            <h3 id="tag-modal-title">Editar marcações</h3>
            <form id="tag-form" style="display: grid; gap: 0.75rem; margin-top: 1rem;">
                <input type="hidden" name="guest_id" id="tag-guest-id">
                
                <div>
                    <label style="display: block; color: #2b2b2b; font-weight: 600; margin-bottom: 0.25rem;">Padrinho/Madrinha</label>
                    <select class="select" name="godparent_role" id="tag-godparent-role">
                        <option value="">Não é padrinho/madrinha</option>
                        <option value="padrinho">Padrinho</option>
                        <option value="madrinha">Madrinha</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; color: #2b2b2b; font-weight: 600; margin-bottom: 0.25rem;">Relacionamento</label>
                    <select class="select" name="relationship" id="tag-relationship">
                        <option value="">Não definido</option>
                        <option value="mae">Mãe</option>
                        <option value="pai">Pai</option>
                        <option value="familia">Família</option>
                        <option value="amigos">Amigos</option>
                        <option value="trabalho">Trabalho</option>
                        <option value="outros">Outros</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; color: #2b2b2b; font-weight: 600; margin-bottom: 0.25rem;">Convidado de qual parte?</label>
                    <select class="select" name="belongs_to_user_id" id="tag-belongs-to">
                        <option value="">Não definido</option>
                        {{-- Opções serão populadas via JS --}}
                    </select>
                </div>

                <div class="mew-actions" style="margin-top: 0.5rem;">
                    <button type="button" id="tag-modal-cancel" class="btn btn--outline">Cancelar</button>
                    <button type="submit" class="btn btn--primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
@endsection
