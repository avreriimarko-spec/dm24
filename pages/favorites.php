<?php
/* Template Name: Избранные модели */
get_header();
?>
<main class="mx-auto w-full lg:w-[1200px] px-4 py-8">
    <div class="flex items-center flex-col lg:flex-row justify-between gap-3 mb-6">
        <h1 class="text-3xl font-bold">Избранные модели</h1>

        <!-- Панель действий -->
        <div class="flex items-center gap-3">
            <span id="fav-count" class="hidden text-sm text-neutral-500"></span>
            <button id="fav-clear"
                type="button"
                class="hidden inline-flex items-center gap-2 h-9 px-3 rounded-md
                     border border-neutral-300 bg-white text-black
                     hover:bg-neutral-50 active:translate-y-px transition"
                aria-label="Очистить избранные модели">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="1.8" stroke-linecap="round" d="M3 6h18M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" />
                    <path stroke-width="1.8" d="M6 6h12l-1 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L6 6Z" />
                </svg>
                Очистить избранное
            </button>
        </div>
    </div>

    <div id="fav-root" class="min-h-[120px]">
        <p class="text-neutral-600">Загружаем избранное…</p>
    </div>
</main>

<script>
    (function() {
        const KEYS = ['favModels', 'favModelsV1', 'favorites', 'favoritesModels'];

        function collectIds() {
            const out = new Set();
            for (const k of KEYS) {
                const raw = localStorage.getItem(k);
                if (!raw) continue;
                try {
                    const parsed = JSON.parse(raw);
                    if (Array.isArray(parsed)) {
                        for (const it of parsed) {
                            if (typeof it === 'object' && it && 'id' in it) {
                                const id = parseInt(it.id, 10);
                                if (id) out.add(id);
                            } else {
                                const id = parseInt(it, 10);
                                if (id) out.add(id);
                            }
                        }
                    } else {
                        const id = parseInt(parsed, 10);
                        if (id) out.add(id);
                    }
                } catch (e) {
                    (raw + '').split(/[\s,]+/).map(x => parseInt(x, 10)).filter(Boolean).forEach(id => out.add(id));
                }
            }
            const usp = new URLSearchParams(location.search);
            if (usp.has('ids')) {
                (usp.get('ids') || '').split(/[\s,]+/).map(x => parseInt(x, 10)).filter(Boolean).forEach(id => out.add(id));
            }
            return Array.from(out);
        }

        function renderEmpty() {
            const root = document.getElementById('fav-root');
            root.innerHTML = '<p class="text-neutral-600">У вас пока нет избранных моделей.</p>';
            document.getElementById('fav-count').classList.add('hidden');
            document.getElementById('fav-clear').classList.add('hidden');
            document.dispatchEvent(new CustomEvent('favorites:rendered', {
                detail: {
                    ids: []
                }
            }));
        }

        function clearFavoritesStorage() {
            for (const k of KEYS) localStorage.removeItem(k);
        }

        const root = document.getElementById('fav-root');
        const countEl = document.getElementById('fav-count');
        const btnClear = document.getElementById('fav-clear');

        let ids = collectIds();

        function updatePanel() {
            if (ids.length > 0) {
                countEl.textContent = `Всего: ${ids.length}`;
                countEl.classList.remove('hidden');
                btnClear.classList.remove('hidden');
            } else {
                countEl.classList.add('hidden');
                btnClear.classList.add('hidden');
            }
        }

        btnClear.addEventListener('click', function() {
            if (!ids.length) return;
            if (!confirm('Очистить все избранные модели?')) return;
            clearFavoritesStorage();
            ids = [];
            updatePanel();
            renderEmpty();
        });

        if (!ids.length) {
            renderEmpty();
            return;
        }
        updatePanel();

        const fd = new FormData();
        fd.append('action', 'site_render_favorites');
        fd.append('ids', ids.join(','));

        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin'
            })
            .then(r => r.json())
            .then(j => {
                if (j && j.success && j.data) {
                    const html = (j.data.html || '').trim();
                    if (html) {
                        root.innerHTML = html; // просто вставляем то, что вернул сервер
                        document.dispatchEvent(new CustomEvent('favorites:rendered', {
                            detail: {
                                ids
                            }
                        }));
                    } else {
                        // сервер вернул пусто (например, все ID удалены из БД)
                        renderEmpty();
                    }
                } else {
                    root.innerHTML = '<p class="text-red-600">Не удалось загрузить избранное.</p>';
                    console.warn('Favorites response:', j);
                }
            })
            .catch(err => {
                root.innerHTML = '<p class="text-red-600">Сетевая ошибка. Попробуйте позже.</p>';
                console.error(err);
            });
    })();
</script>

<?php get_footer(); ?>