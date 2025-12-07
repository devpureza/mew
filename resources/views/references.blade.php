<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Referências · MEW</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0b0d11] text-white">
    <div class="max-w-4xl mx-auto px-6 py-10">
        <header class="flex items-center justify-between mb-8">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-white/60">Library</p>
                <h1 class="text-3xl font-semibold">Referências de estilo</h1>
                <p class="text-white/60 mt-2">Use esta página para guardar moodboards, paletas e arquivos de apoio.</p>
            </div>
            <a href="/" class="px-4 py-2 rounded-full border border-white/20 hover:border-white/50 transition text-sm">Voltar</a>
        </header>

        <section class="grid md:grid-cols-2 gap-6">
            <div class="p-5 rounded-2xl bg-white/5 border border-white/10">
                <h2 class="text-xl font-semibold mb-2">Uploads rápidos</h2>
                <p class="text-white/60 mb-4 text-sm">Coloque aqui imagens de referência, logos, PDFs de identidade visual ou playlists.</p>
                <div class="border border-dashed border-white/20 rounded-xl p-6 text-center text-white/60">
                    Arraste e solte arquivos na pasta <code>storage/app/references</code> e sirva-os via <code>public/storage</code>.
                </div>
            </div>
            <div class="p-5 rounded-2xl bg-white/5 border border-white/10">
                <h2 class="text-xl font-semibold mb-2">Anotações</h2>
                <ul class="space-y-2 text-white/70 text-sm list-disc list-inside">
                    <li>Paleta sugerida: coral, areia, verde-água e preto fosco.</li>
                    <li>Fontes sugestivas: Manrope, Sora, Playfair para títulos cerimoniais.</li>
                    <li>Texturas: papel artesanal, onda suave, luz baixa.</li>
                    <li>Clipes: entrada dos noivos, discursos, festa.</li>
                </ul>
            </div>
        </section>
    </div>
</body>
</html>
