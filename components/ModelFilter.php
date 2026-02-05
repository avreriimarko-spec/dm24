<?php

/**
 * Renders a single filter dropdown section for a specific taxonomy.
 * 
 * @param string $taxonomy The taxonomy slug.
 * @param string $title The display title for the section.
 * @return string HTML output of the filter section.
 */
function render_filter_section(string $taxonomy, string $title): string
{
    $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    if (empty($terms) || is_wp_error($terms)) {
        return '';
    }

    ob_start(); ?>
    <div class="relative mf-dropdown-container">
        <div class="text-center mb-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-neutral-900"><?= esc_html($title) ?></span>
        </div>
        
        <button type="button" 
            class="mf-dropdown-trigger w-full h-10 px-2 flex items-center justify-between border border-neutral-200 rounded-md bg-white hover:border-neutral-400 transition-colors text-left"
            data-taxonomy="<?= esc_attr($taxonomy) ?>">
            <span class="text-[14px] text-neutral-400 truncate mf-trigger-label">Не выбрано</span>
            <svg class="w-5 h-5 text-neutral-300 pointer-events-none flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>

        <div class="mf-dropdown-content absolute left-0 right-0 top-full mt-1 z-[70] hidden bg-white border border-neutral-200 rounded-md shadow-xl max-h-60 overflow-y-auto space-y-1">
            <?php foreach ($terms as $term): ?>
                <label class="mf-dropdown-item flex items-center px-2 py-2 rounded-md cursor-pointer transition-all duration-200 hover:bg-neutral-50 group">
                    <input type="checkbox" class="filter-checkbox sr-only" name="<?= esc_attr($taxonomy) ?>[]" value="<?= esc_attr($term->slug) ?>">
                    <span class="text-[11px] font-bold text-neutral-700 group-hover:text-neutral-900 transition-colors"><?= esc_html($term->name) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Renders the main model filter bar.
 * 
 * @return string HTML output of the filter bar and its associated scripts/styles.
 */
function render_model_filter(): string
{
    $current_base_tax = get_query_var('base_tax');
    $base_tax_taxonomy = !empty($current_base_tax['taxonomy']) ? $current_base_tax['taxonomy'] : '';
    $base_tax_terms = !empty($current_base_tax['terms']) ? implode(',', (array)$current_base_tax['terms']) : '';

    $taxonomies = [
        'price_tax'        => 'Цена',
        'vozrast_tax'      => 'Возраст',
        'nationalnost_tax' => 'Национальность',
        'ves_tax'          => 'Вес',
        'rayonu_tax'       => 'Районы',
        'metro_tax'        => 'Метро',
        'cvet-volos_tax'   => 'Цвет волос',
        'rost_tax'         => 'Рост',
        'grud_tax'         => 'Грудь',
    ];

    ob_start(); ?>

    <div id="mf-static-container" class="w-full bg-white rounded-xl shadow-sm border border-neutral-100 p-4">
        <form id="models-filter" class="flex flex-wrap" style="gap: 7px; align-items: end;">
            <input type="hidden" name="action" value="site_filter_models">
            <input type="hidden" name="nonce" value="<?= esc_attr(wp_create_nonce('site_filter_nonce')) ?>">
            <input type="hidden" name="has_active_filters" value="0">
            <input type="hidden" name="paged" value="1">
            <input type="hidden" name="per_page" value="48">
            
            <input type="hidden" name="base_tax_taxonomy" value="<?= esc_attr($base_tax_taxonomy) ?>">
            <input type="hidden" name="base_tax_terms" value="<?= esc_attr($base_tax_terms) ?>">

            <div class="flex-1 flex flex-wrap" style="gap: 10px;">
                <?php foreach ($taxonomies as $slug => $label) {
                    echo render_filter_section($slug, $label);
                } ?>
            </div>

            <div class="w-full md:w-auto shrink-0 flex flex-nowrap items-center" style="gap: 7px;">
                <button type="button" id="mf-reset" 
                    class="flex-1 md:flex-none h-10 px-2 rounded-md font-bold transition uppercase text-xs tracking-wide whitespace-nowrap text-center">
                    Сбросить
                </button>
                <button type="button" id="mf-apply" 
                    class="flex-1 md:flex-none h-10 px-2 rounded-md font-bold transition uppercase text-xs tracking-wide whitespace-nowrap text-center">
                    Применить
                </button>
            </div>
        </form>
    </div>

    <style>
        /* Dropdown Container Width */
        .mf-dropdown-container {
            width: 100%;
        }
        @media (min-width: 768px) {
            .mf-dropdown-container {
                width: 117px;
            }
        }

        /* Dropdown Selection States */
        .mf-dropdown-item:has(.filter-checkbox:checked),
        .mf-dropdown-item.is-active {
            background: linear-gradient(135deg, #ff2d72 0%, #ff4d88 100%);
        }
        .mf-dropdown-item:has(.filter-checkbox:checked) span,
        .mf-dropdown-item.is-active span {
            color: white;
            font-weight: 600;
        }
        .mf-dropdown-item:has(.filter-checkbox:checked):hover,
        .mf-dropdown-item.is-active:hover {
            background: linear-gradient(135deg, #e01b5d 0%, #ff2d72 100%);
        }

        /* Action button styles */
        #mf-reset {
            background-color: #5579afff;
            color: white;
        }
        #mf-reset:hover {
            background-color: #3d5b77ff;
        }
        #mf-apply {
            background-color: #ff2d72;
            color: white;
        }
        #mf-apply:hover {
            background-color: #ff4d88;
        }

        /* Utilities */
        .mf-dropdown-content {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .mf-dropdown-item input.filter-checkbox {
            display: none;
        }
    </style>

    <?php
    $script = <<<'JS'
    (function () {
        if (window.MF_INIT) return; 
        window.MF_INIT = true;

        document.addEventListener('DOMContentLoaded', function () {
            const config = {
                ajaxUrl: (window.SiteModelsFilter && SiteModelsFilter.ajaxUrl) ? SiteModelsFilter.ajaxUrl : '/wp-admin/admin-ajax.php'
            };

            const ui = {
                form: document.getElementById('models-filter'),
                wrap: document.getElementById('ajax-models'),
                applyBtn: document.getElementById('mf-apply'),
                resetBtn: document.getElementById('mf-reset'),
                sortInput: document.getElementById('mf-sort'), // Hidden input
                sortTrigger: document.getElementById('mf-sort-trigger'),
                sortContainer: document.getElementById('mf-sort-container')
            };

            if (!ui.form || !ui.wrap) return;

            const state = {
                currentPage: 1,
                isLoading: false,
                hasMore: true,
                observer: null
            };

            // --- Methods ---

            const methods = {
                updateTriggerLabel(trigger) {
                    const container = trigger.closest('.mf-dropdown-container');
                    const label = container.querySelector('.mf-trigger-label');
                    const count = container.querySelectorAll('.filter-checkbox:checked').length;
                    
                    if (count > 0) {
                        label.textContent = `Выбрано: ${count}`;
                        label.classList.remove('text-neutral-400');
                        label.classList.add('text-black', 'font-medium');
                    } else {
                        label.textContent = 'Не выбрано';
                        label.classList.remove('text-black', 'font-medium');
                        label.classList.add('text-neutral-400');
                    }
                },

                closeAllDropdowns() {
                    document.querySelectorAll('.mf-dropdown-content').forEach(d => d.classList.add('hidden'));
                },

                setHasActiveFilters() {
                    const active = Array.from(ui.form.querySelectorAll('.filter-checkbox')).some(c => c.checked);
                    const hidden = ui.form.querySelector('input[name="has_active_filters"]');
                    if (hidden) hidden.value = active ? '1' : '0';
                },

                getFormData(page, append) {
                    const fd = new FormData(ui.form);
                    fd.set('paged', String(page));
                    fd.set('append', append ? '1' : '0');
                    if (ui.sortInput) fd.set('sort', ui.sortInput.value);
                    
                    if (append) {
                        fd.set('use_offset', '1');
                        const ids = Array.from(ui.wrap.querySelectorAll('.mf-item[data-id]'))
                                        .map(el => el.dataset.id)
                                        .filter(Boolean);
                        if (ids.length) fd.set('exclude_ids', ids.join(','));
                    } else {
                        fd.set('use_offset', '0');
                    }
                    return fd;
                },

                async fetchResults(page, replace = false) {
                    if (state.isLoading || (!state.hasMore && !replace)) return;
                    
                    state.isLoading = true;
                    try {
                        const fd = methods.getFormData(page, !replace);
                        const res = await fetch(config.ajaxUrl, { method: 'POST', body: fd });
                        const data = await res.json();
                        const html = data?.data?.html || '';
                        
                        if (replace) {
                            ui.wrap.innerHTML = html;
                            state.currentPage = 1;
                        } else {
                            const list = ui.wrap.querySelector('#mf-list') || ui.wrap;
                            const temp = document.createElement('div');
                            temp.innerHTML = html;
                            temp.querySelectorAll('li.mf-item').forEach(li => list.appendChild(li));
                        }
                        
                        state.hasMore = data?.data?.has_more ?? (html.trim().length > 0);
                    } catch (e) {
                        console.error('MF Error:', e);
                        state.hasMore = false;
                    } finally {
                        state.isLoading = false;
                        methods.initInfiniteScroll();
                    }
                },

                initInfiniteScroll() {
                    if (state.observer) state.observer.disconnect();
                    
                    let sentinel = document.getElementById('mf-sentinel');
                    if (!sentinel) {
                        sentinel = document.createElement('div');
                        sentinel.id = 'mf-sentinel';
                        sentinel.style.height = '1px';
                        ui.wrap.appendChild(sentinel);
                    }

                    state.observer = new IntersectionObserver((entries) => {
                        if (entries[0].isIntersecting && !state.isLoading && state.hasMore) {
                            state.currentPage++;
                            methods.fetchResults(state.currentPage, false);
                        }
                    }, { rootMargin: '400px' });
                    state.observer.observe(sentinel);
                }
            };

            // --- Event Handlers ---

            // General dropdown toggle (Delegation)
            document.addEventListener('click', (e) => {
                const trigger = e.target.closest('.mf-dropdown-trigger');
                if (trigger) {
                    const content = trigger.nextElementSibling;
                    const isHidden = content.classList.contains('hidden');
                    methods.closeAllDropdowns();
                    
                    if (isHidden) {
                        content.classList.remove('hidden');
                        const rect = trigger.getBoundingClientRect();
                        const spaceBelow = window.innerHeight - rect.bottom - 20;
                        const spaceAbove = rect.top - 20;
                        
                        // Adjust vertical position
                        if (spaceBelow < 100 && spaceAbove > spaceBelow) {
                            content.classList.add('bottom-full', 'mb-1');
                            content.classList.remove('top-full', 'mt-1');
                            content.style.maxHeight = Math.min(spaceAbove, 400) + 'px';
                        } else {
                            content.classList.add('top-full', 'mt-1');
                            content.classList.remove('bottom-full', 'mb-1');
                            content.style.maxHeight = Math.min(spaceBelow, 400) + 'px';
                        }
                    }
                    e.stopPropagation();
                    return;
                }
                
                // Sorting Item Selection
                const sortItem = e.target.closest('.mf-sort-item');
                if (sortItem) {
                    const value = sortItem.dataset.value;
                    const label = sortItem.querySelector('span').textContent;
                    
                    if (ui.sortInput) ui.sortInput.value = value;
                    if (ui.sortTrigger) {
                        ui.sortTrigger.querySelector('.mf-trigger-label').textContent = label;
                    }

                    // Update active class
                    sortItem.closest('.mf-dropdown-content')
                            .querySelectorAll('.mf-sort-item')
                            .forEach(el => el.classList.remove('is-active'));
                    sortItem.classList.add('is-active');
                    
                    methods.closeAllDropdowns();
                    methods.fetchResults(1, true);
                    e.stopPropagation();
                    return;
                }

                // Filter Item Selection
                const filterItem = e.target.closest('.mf-dropdown-item');
                if (filterItem) {
                    const checkbox = filterItem.querySelector('.filter-checkbox');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        methods.updateTriggerLabel(filterItem.closest('.mf-dropdown-container').querySelector('.mf-dropdown-trigger'));
                        methods.setHasActiveFilters();
                        e.stopPropagation();
                    }
                    return;
                }

                // Close when clicking outside
                if (!e.target.closest('.mf-dropdown-content')) {
                    methods.closeAllDropdowns();
                }
            });

            ui.applyBtn.addEventListener('click', () => methods.fetchResults(1, true));
            
            ui.resetBtn.addEventListener('click', () => {
                ui.form.reset();
                ui.form.querySelectorAll('.filter-checkbox').forEach(c => c.checked = false);
                ui.form.querySelectorAll('.mf-dropdown-trigger').forEach(methods.updateTriggerLabel);
                
                // Also reset sorting trigger label if needed, or keep it as is
                // For now, let's keep it consistent
                methods.fetchResults(1, true);
            });

            // --- Init ---
            methods.initInfiniteScroll();
        });
    })();
    JS;

    wp_register_script('models-filter-drawer', '', [], null, true);
    wp_add_inline_script('models-filter-drawer', $script);
    wp_enqueue_script('models-filter-drawer');

    return ob_get_clean();
}
