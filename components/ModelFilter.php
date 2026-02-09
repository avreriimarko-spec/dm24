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
                    <span class="text-[12px] font-bold text-neutral-700 group-hover:text-neutral-900 transition-colors"><?= esc_html($term->name) ?></span>
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

    <div id="mf-drawer-backdrop" class="mf-drawer-backdrop" aria-hidden="true"></div>
    <button type="button" id="mf-drawer-toggle" class="mf-drawer-toggle" aria-controls="mf-static-container" aria-expanded="false" aria-label="Открыть фильтры">
        <svg class="mf-drawer-toggle__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M3 6h18M6 12h12M10 18h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
    </button>

    <div id="mf-static-container" class="w-full bg-white rounded-xl shadow-sm border border-neutral-100 p-4" aria-hidden="false">
        <div class="mf-drawer-header">
            <span class="mf-drawer-title">Фильтры</span>
            <button type="button" id="mf-drawer-close" class="mf-drawer-close" aria-label="Закрыть фильтры">
                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
        <form id="models-filter" class="flex flex-wrap" style="gap: 7px; align-items: end;">
            <input type="hidden" name="action" value="site_filter_models">
            <input type="hidden" name="nonce" value="<?= esc_attr(wp_create_nonce('site_filter_nonce')) ?>">
            <input type="hidden" name="has_active_filters" value="0">
            <input type="hidden" name="paged" value="1">
            <input type="hidden" name="per_page" value="48">
            
            <input type="hidden" name="base_tax_taxonomy" value="<?= esc_attr($base_tax_taxonomy) ?>">
            <input type="hidden" name="base_tax_terms" value="<?= esc_attr($base_tax_terms) ?>">

            <div class="mf-fields">
                <div class="flex-1 flex flex-wrap" style="gap: 10px;">
                    <?php foreach ($taxonomies as $slug => $label) {
                        echo render_filter_section($slug, $label);
                    } ?>
                </div>
            </div>

            <div class="mf-actions w-full md:w-auto shrink-0 flex flex-nowrap items-center" style="gap: 7px;">
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
            background: linear-gradient(135deg, #e865a0 0%, #ff4d88 100%);
        }
        .mf-dropdown-item:has(.filter-checkbox:checked) span,
        .mf-dropdown-item.is-active span {
            color: white;
            font-weight: 600;
        }
        .mf-dropdown-item:has(.filter-checkbox:checked):hover,
        .mf-dropdown-item.is-active:hover {
            background: linear-gradient(135deg, #e01b5d 0%, #e865a0 100%);
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
            background-color: #e865a0;
            color: white;
        }
        #mf-apply:hover {
            background-color: #ff4d88;
        }

        /* Utilities */
        .mf-dropdown-content {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow-x: hidden;
        }
        .mf-dropdown-item {
            align-items: flex-start;
        }
        .mf-dropdown-item span {
            display: block;
            min-width: 0;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .mf-dropdown-item input.filter-checkbox {
            display: none;
        }

        /* Mobile drawer */
        .mf-drawer-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
            z-index: 98;
        }

        .mf-drawer-toggle {
            display: none;
            position: fixed;
            right: 16px;
            bottom: 18px;
            z-index: 97;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            background: #ff3ea5;
            color: #fff;
            border: 1px solid #ff3ea5;
            box-shadow: 0 10px 25px rgba(0,0,0,0.35);
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-size: 12px;
            font-weight: 700;
        }

        .mf-drawer-toggle__icon {
            width: 20px;
            height: 20px;
        }

        .mf-drawer-header {
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #1b1b1b;
            margin-bottom: 12px;
        }

        .mf-drawer-title {
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            font-size: 13px;
            color: #e6e6e6;
        }

        .mf-drawer-close {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #1b1b1b;
            background: #0b0b0b;
            color: #e6e6e6;
        }

        .mf-drawer-close svg {
            width: 18px;
            height: 18px;
        }

        @media (max-width: 767px) {
            body.mf-drawer-open {
                overflow: hidden;
            }

            .mf-drawer-title {
                color: #111;
            }

            .mf-drawer-close {
                border-color: #e1e1ea;
                background: #ffffff;
                color: #111;
            }

            #mf-static-container {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                width: min(86vw, 360px);
                max-width: 100%;
                transform: translateX(-110%);
                transition: transform 0.28s ease;
                z-index: 110;
                background: #f7f7fb;
                color: #111;
                border: 1px solid #e6e6ee;
                box-shadow: 0 30px 60px rgba(0,0,0,0.22), 0 0 0 1px rgba(255,62,165,0.08) inset;
                padding: 16px;
                overflow: hidden;
                display: flex;
                flex-direction: column;
            }

            #mf-static-container.is-open {
                transform: translateX(0);
            }

            .mf-drawer-backdrop.is-open {
                opacity: 1;
                pointer-events: auto;
            }

            .mf-drawer-toggle {
                display: inline-flex;
            }

            .mf-drawer-header {
                display: flex;
                width: 100%;
                border-bottom: 1px solid #e6e6ee;
            }

            #models-filter {
                flex-direction: column;
                align-items: stretch !important;
                gap: 12px !important;
                flex-wrap: nowrap !important;
                min-height: 0;
                flex: 1;
            }

            #models-filter .flex-1 {
                width: 100%;
            }

            .mf-fields {
                flex: 1;
                min-height: 0;
                overflow-y: auto;
                padding-right: 2px;
            }

            .mf-actions {
                margin-top: auto;
                background: #ffffff;
                padding-top: 12px;
                padding-bottom: 12px;
                border-top: 1px solid #e6e6ee;
                z-index: 2;
            }

            #mf-static-container .mf-dropdown-container > .text-center {
                text-align: left;
                padding-left: 2px;
            }

            #mf-static-container .mf-dropdown-container > .text-center span {
                color: #ff3ea5;
                letter-spacing: 0.16em;
            }

            #mf-static-container .mf-dropdown-trigger {
                background: #ffffff;
                border-color: #e1e1ea;
                border-left: 2px solid #ff3ea5;
                text-align: left;
            }

            #mf-static-container .mf-dropdown-trigger:hover {
                border-color: #ff3ea5;
                border-left-color: #ff6bbf;
                box-shadow: 0 0 0 2px rgba(255, 62, 165, 0.2);
            }

            #mf-static-container .mf-dropdown-trigger .mf-trigger-label {
                color: #5b5b6a;
                font-weight: 600;
                letter-spacing: 0.02em;
            }

            #mf-static-container .mf-dropdown-trigger .mf-trigger-label.text-neutral-400 {
                color: #8a8a98;
            }

            #mf-static-container .mf-dropdown-trigger .mf-trigger-label.text-black {
                color: #111;
            }

            #mf-static-container .mf-dropdown-trigger .mf-trigger-label.font-medium {
                font-weight: 600;
            }

            #mf-static-container .mf-dropdown-trigger svg {
                color: #9a9aa8;
            }

            #mf-static-container .mf-dropdown-content {
                background: #ffffff;
                border-color: #e1e1ea;
                box-shadow: 0 16px 35px rgba(0,0,0,0.18);
            }

            #mf-static-container .text-neutral-900,
            #mf-static-container .text-black {
                color: #111;
            }

            #mf-static-container .text-neutral-700 {
                color: #555;
            }

            #mf-static-container .mf-dropdown-item {
                border: 1px solid transparent;
                text-align: left;
            }

            #mf-static-container .mf-dropdown-item span {
                color: #222;
                text-align: left;
            }

            #mf-static-container .mf-dropdown-item:hover {
                background: linear-gradient(135deg, rgba(255,62,165,0.12), rgba(232,101,160,0.08));
                border-color: rgba(255,62,165,0.2);
            }

            #mf-reset {
                background: transparent;
                border: 1px solid #ff3ea5;
                color: #ff9ad0;
            }

            #mf-reset:hover {
                background: rgba(255, 62, 165, 0.2);
                color: #ffffff;
            }

            #mf-apply {
                background: linear-gradient(135deg, #ff3ea5 0%, #e865a0 100%);
            }

            #mf-apply:hover {
                background: linear-gradient(135deg, #ff4db0 0%, #ff6bbf 100%);
            }
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
                sortContainer: document.getElementById('mf-sort-container'),
                drawer: document.getElementById('mf-static-container'),
                drawerToggle: document.getElementById('mf-drawer-toggle'),
                drawerClose: document.getElementById('mf-drawer-close'),
                drawerBackdrop: document.getElementById('mf-drawer-backdrop')
            };

            if (!ui.form || !ui.wrap) return;

            const state = {
                currentPage: 1,
                isLoading: false,
                hasMore: true
            };

            // --- Methods ---

            const methods = {
                isMobile() {
                    return window.matchMedia('(max-width: 767px)').matches;
                },

                openDrawer() {
                    if (!ui.drawer || !ui.drawerToggle || !ui.drawerBackdrop) return;
                    if (!methods.isMobile()) return;
                    ui.drawer.classList.add('is-open');
                    ui.drawerBackdrop.classList.add('is-open');
                    ui.drawerToggle.classList.add('is-open');
                    ui.drawerToggle.setAttribute('aria-expanded', 'true');
                    ui.drawer.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('mf-drawer-open');
                },

                closeDrawer() {
                    if (!ui.drawer || !ui.drawerToggle || !ui.drawerBackdrop) return;
                    ui.drawer.classList.remove('is-open');
                    ui.drawerBackdrop.classList.remove('is-open');
                    ui.drawerToggle.classList.remove('is-open');
                    ui.drawerToggle.setAttribute('aria-expanded', 'false');
                    if (methods.isMobile()) {
                        ui.drawer.setAttribute('aria-hidden', 'true');
                    } else {
                        ui.drawer.setAttribute('aria-hidden', 'false');
                    }
                    document.body.classList.remove('mf-drawer-open');
                },

                syncDrawerState() {
                    if (!methods.isMobile()) {
                        if (ui.drawer) ui.drawer.classList.remove('is-open');
                        if (ui.drawerBackdrop) ui.drawerBackdrop.classList.remove('is-open');
                        if (ui.drawerToggle) ui.drawerToggle.classList.remove('is-open');
                        if (ui.drawerToggle) ui.drawerToggle.setAttribute('aria-expanded', 'false');
                        if (ui.drawer) ui.drawer.setAttribute('aria-hidden', 'false');
                        document.body.classList.remove('mf-drawer-open');
                        return;
                    }

                    if (ui.drawer && !ui.drawer.classList.contains('is-open')) {
                        ui.drawer.setAttribute('aria-hidden', 'true');
                    }
                },

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
                    }
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

            ui.applyBtn.addEventListener('click', async () => {
                await methods.fetchResults(1, true);
                if (methods.isMobile()) methods.closeDrawer();
            });
            
            ui.resetBtn.addEventListener('click', () => {
                ui.form.reset();
                ui.form.querySelectorAll('.filter-checkbox').forEach(c => c.checked = false);
                ui.form.querySelectorAll('.mf-dropdown-trigger').forEach(methods.updateTriggerLabel);
                
                // Also reset sorting trigger label if needed, or keep it as is
                // For now, let's keep it consistent
                methods.fetchResults(1, true);
                if (methods.isMobile()) methods.closeDrawer();
            });

            // --- Init ---
            if (ui.drawerToggle) {
                ui.drawerToggle.addEventListener('click', () => methods.openDrawer());
            }
            if (ui.drawerClose) {
                ui.drawerClose.addEventListener('click', () => methods.closeDrawer());
            }
            if (ui.drawerBackdrop) {
                ui.drawerBackdrop.addEventListener('click', () => methods.closeDrawer());
            }
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') methods.closeDrawer();
            });
            window.addEventListener('resize', methods.syncDrawerState);
            methods.syncDrawerState();
        });
    })();
    JS;

    wp_register_script('models-filter-drawer', '', [], null, true);
    wp_add_inline_script('models-filter-drawer', $script);
    wp_enqueue_script('models-filter-drawer');

    return ob_get_clean();
}
