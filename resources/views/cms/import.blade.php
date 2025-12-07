@extends('cms.layout')

@section('title', 'Importar Convidados · MEW')
@section('page', 'import')

@section('content')
    <input type="hidden" id="wedding_id" value="{{ $wedding->id }}">
    
    <div class="section__header" style="margin-top:0;">
        <p class="section__subtitle" style="color: var(--color-elegancia);">Importação</p>
        <h1 class="section__title" style="color: var(--color-elegancia);">Importar Convidados</h1>
        <p style="color: #2b2b2b;">Faça upload de uma planilha CSV ou Excel com sua lista de convidados.</p>
    </div>

    {{-- Download Template --}}
    <div class="card card--surface" style="margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h4 style="margin: 0 0 0.25rem; color: var(--color-elegancia);">📥 Modelo de Planilha</h4>
            <p style="margin: 0; color: #666; font-size: 0.9rem;">Baixe o modelo com as colunas corretas para facilitar a importação.</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="button" id="btn-download-xlsx" class="btn btn--primary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Baixar XLSX
            </button>
            <button type="button" id="btn-download-csv" class="btn btn--outline" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Baixar CSV
            </button>
        </div>
    </div>

    {{-- Step 1: Upload --}}
    <div id="step-upload" class="card card--surface" style="margin-bottom: 1.5rem;">
        <h3 class="service-card__title" style="color: var(--color-elegancia); margin: 0 0 1rem;">
            <span style="background: var(--color-elegancia); color: #fff; width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 0.5rem;">1</span>
            Selecione o arquivo
        </h3>
        
        <div id="dropzone" style="border: 2px dashed rgba(0,0,0,0.15); border-radius: 12px; padding: 3rem; text-align: center; cursor: pointer; transition: all 150ms ease;">
            <svg style="width: 48px; height: 48px; color: var(--color-elegancia); margin-bottom: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <p style="color: #2b2b2b; margin: 0 0 0.5rem; font-weight: 600;">Arraste o arquivo aqui ou clique para selecionar</p>
            <p style="color: #666; margin: 0; font-size: 0.9rem;">Formatos aceitos: CSV, XLS, XLSX</p>
            <input type="file" id="file-input" accept=".csv,.xls,.xlsx" style="display: none;">
        </div>
        
        <div id="file-info" style="display: none; margin-top: 1rem; padding: 1rem; background: rgba(13, 66, 60, 0.08); border-radius: 8px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <svg style="width: 24px; height: 24px; color: var(--color-elegancia);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <div>
                        <p id="file-name" style="margin: 0; font-weight: 600; color: #0b1e20;"></p>
                        <p id="file-size" style="margin: 0; font-size: 0.85rem; color: #666;"></p>
                    </div>
                </div>
                <button type="button" id="btn-remove-file" style="background: none; border: none; cursor: pointer; color: #b4332b; font-weight: 600;">Remover</button>
            </div>
        </div>
    </div>

    {{-- Step 2: Column Mapping --}}
    <div id="step-mapping" class="card card--surface" style="margin-bottom: 1.5rem; display: none;">
        <h3 class="service-card__title" style="color: var(--color-elegancia); margin: 0 0 1rem;">
            <span style="background: var(--color-elegancia); color: #fff; width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 0.5rem;">2</span>
            Mapeie as colunas
        </h3>
        <p style="color: #666; margin-bottom: 1rem;">Identifique quais colunas da sua planilha correspondem aos campos do sistema.</p>
        
        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <div>
                <label style="display: block; color: #2b2b2b; font-weight: 600; margin-bottom: 0.25rem;">Nome do convidado *</label>
                <select class="select" id="map-name" required>
                    <option value="">Selecione a coluna...</option>
                </select>
            </div>
            <div>
                <label style="display: block; color: #2b2b2b; font-weight: 600; margin-bottom: 0.25rem;">Relacionamento</label>
                <select class="select" id="map-relationship">
                    <option value="">Não importar</option>
                </select>
            </div>
            <div>
                <label style="display: block; color: #2b2b2b; font-weight: 600; margin-bottom: 0.25rem;">Padrinho/Madrinha</label>
                <select class="select" id="map-godparent">
                    <option value="">Não importar</option>
                </select>
            </div>
            <div>
                <label style="display: block; color: #2b2b2b; font-weight: 600; margin-bottom: 0.25rem;">Convidado de</label>
                <select class="select" id="map-belongs-to">
                    <option value="">Não importar</option>
                </select>
            </div>
            <div>
                <label style="display: block; color: #2b2b2b; font-weight: 600; margin-bottom: 0.25rem;">CPF</label>
                <select class="select" id="map-cpf">
                    <option value="">Não importar</option>
                </select>
            </div>
            <div>
                <label style="display: block; color: #2b2b2b; font-weight: 600; margin-bottom: 0.25rem;">E-mail</label>
                <select class="select" id="map-email">
                    <option value="">Não importar</option>
                </select>
            </div>
            <div>
                <label style="display: block; color: #2b2b2b; font-weight: 600; margin-bottom: 0.25rem;">Telefone</label>
                <select class="select" id="map-phone">
                    <option value="">Não importar</option>
                </select>
            </div>
        </div>
        
        <div style="margin-top: 1.5rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" id="has-header" checked style="width: 18px; height: 18px;">
                <span style="color: #2b2b2b;">A primeira linha contém cabeçalhos</span>
            </label>
        </div>
        
        <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
            <button type="button" id="btn-apply-mapping" class="btn btn--primary">Aplicar Mapeamento</button>
        </div>
    </div>

    {{-- Step 3: Preview --}}
    <div id="step-preview" class="card card--surface" style="margin-bottom: 1.5rem; display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
            <h3 class="service-card__title" style="color: var(--color-elegancia); margin: 0;">
                <span style="background: var(--color-elegancia); color: #fff; width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 0.5rem;">3</span>
                Revise os dados
            </h3>
            <span id="preview-count" style="color: #666; font-size: 0.9rem;"></span>
        </div>
        
        <p style="color: #666; margin-bottom: 1rem;">Clique em uma célula para editar. Linhas com erros aparecem em vermelho.</p>
        
        <div id="preview-table-container" style="overflow-x: auto; max-height: 400px; overflow-y: auto;"></div>
        
        <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <button type="button" id="btn-back-mapping" class="btn btn--outline">Voltar ao Mapeamento</button>
            <button type="button" id="btn-import" class="btn btn--primary">
                <svg style="width: 18px; height: 18px; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Importar <span id="valid-count">0</span> Convidados
            </button>
        </div>
    </div>

    {{-- Step 4: Success --}}
    <div id="step-success" class="card card--surface" style="display: none; text-align: center; padding: 3rem;">
        <svg style="width: 64px; height: 64px; color: #2d8a5f; margin-bottom: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="service-card__title" style="color: var(--color-elegancia); margin: 0 0 0.5rem;">Importação Concluída!</h3>
        <p id="success-message" style="color: #666; margin-bottom: 1.5rem;"></p>
        <div style="display: flex; gap: 0.75rem; justify-content: center;">
            <a href="{{ route('cms.couple') }}" class="btn btn--primary">Ver Convidados</a>
            <button type="button" id="btn-new-import" class="btn btn--outline">Nova Importação</button>
        </div>
    </div>
@endsection
