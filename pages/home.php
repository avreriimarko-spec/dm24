<?php
/*
Template Name: Универсальный для страниц моделей/ главной
*/
/* Template Post Type: page, tsena, vozrast, nacionalnost, rajon, metro, rost, grud, ves, tsvet-volos, uslugi */

if (!defined('ABSPATH')) exit;

require_once get_template_directory() . '/components/ModelFilter.php';
require_once get_template_directory() . '/components/ModelGrid.php';

/* ============================================================
 * 1) БАЗОВЫЙ ФИЛЬТР + ПРЕДВАРИТЕЛЬНЫЙ СПИСОК ДЛЯ JSON-LD
 * ============================================================ */
$ALLOWED_TAX = [
    'price_tax',          // Цена
    'vozrast_tax',        // Возраст
    'rayonu_tax',         // Районы
    'metro_tax',          // Метро
    'rost_tax',           // Рост
    'ves_tax',            // Вес
    'cvet-volos_tax',     // Цвет волос
    'nationalnost_tax',   // Национальность
    'grud_tax',           // Грудь
    'drygie_tax',
    'uslugi_tax'       // Другие
];

/** 1.1 Получаем base_tax для текущего урла */
$base_tax = [];

// a) Архив таксономии
if (is_tax() || is_tag() || is_category()) {
    $qo = get_queried_object();
    if ($qo instanceof WP_Term && in_array($qo->taxonomy, $ALLOWED_TAX, true)) {
        $base_tax = ['taxonomy' => $qo->taxonomy, 'terms' => [(int)$qo->term_id]];
    }
}

// b) Статическая страница: слаг совпадает с термом одной из разрешённых такс
if (empty($base_tax) && is_page()) {
    $page_id   = get_queried_object_id();
    $page_slug = $page_id ? (string) get_post_field('post_name', $page_id) : '';
    if ($page_slug !== '') {
        foreach ($ALLOWED_TAX as $tx) {
            $t = get_term_by('slug', $page_slug, $tx);
            if ($t && !is_wp_error($t)) {
                $base_tax = ['taxonomy' => $tx, 'terms' => [(int)$t->term_id]];
                break;
            }
        }
    }
}

// c) Посадочные CPT
if (empty($base_tax)) {
    $qo        = get_queried_object();
    $post_type = ($qo instanceof WP_Post) ? $qo->post_type : '';
    $page_slug = ($qo instanceof WP_Post && !empty($qo->post_name)) ? (string) $qo->post_name : '';

    $CPT_TAX_MAP = [
        'tsena'         => 'price_tax',
        'vozrast'       => 'vozrast_tax',
        'nacionalnost'  => 'nationalnost_tax',
        'rajon'         => 'rayonu_tax',
        'metro'         => 'metro_tax',
        'rost'          => 'rost_tax',
        'grud'          => 'grud_tax',
        'ves'           => 'ves_tax',
        'tsvet-volos'   => 'cvet-volos_tax',
        'uslugi'        => 'uslugi_tax',
    ];

    if ($page_slug !== '' && isset($CPT_TAX_MAP[$post_type])) {
        $tx = $CPT_TAX_MAP[$post_type];
        $t  = get_term_by('slug', $page_slug, $tx);
        if ($t && !is_wp_error($t)) {
            $base_tax = ['taxonomy' => $tx, 'terms' => [(int)$t->term_id]];
        }
    }
}

/** 1.2 Готовим лёгкий список моделей для JSON-LD */
$ld_models = [];
$args = [
    'post_type'           => 'models',
    'post_status'         => 'publish',
    'posts_per_page'      => 9,
    'no_found_rows'       => true,
    'orderby'             => 'date',
    'order'               => 'DESC',
    'fields'              => 'ids',
    'suppress_filters'    => false,
    'ignore_sticky_posts' => true,
];
if (!empty($base_tax)) {
    $args['tax_query'] = [[
        'taxonomy' => $base_tax['taxonomy'],
        'field'    => 'term_id',
        'terms'    => array_map('intval', (array)$base_tax['terms']),
        'operator' => 'IN',
    ]];
}
$ids = get_posts($args);

$ph = get_stylesheet_directory_uri() . '/assets/images/placeholder-thumbs.webp';
foreach ((array)$ids as $pid) {
    $name = get_the_title($pid);
    $uri  = get_permalink($pid);
    if (!$name || !$uri) continue;
    $img  = get_the_post_thumbnail_url($pid, 'medium') ?: $ph;
    $ld_models[] = ['name' => $name, 'uri' => $uri, 'image' => $img];
}

/** 1.3 Прокинем base_tax диспетчеру JSON-LD и во фронт */
set_query_var('base_tax', $base_tax);

/* ===== ШАПКА ===== */
get_header();

$paged   = max(1, (int)(get_query_var('paged') ?: get_query_var('page') ?: 1));
$post_id = get_queried_object_id();

/**
 * 2) ACF-поля и контент
 */
$p_after_h1 = function_exists('get_field') ? (get_field('p_atc', $post_id) ?: '') : '';
$content    = function_exists('get_field') ? (get_field('content', $post_id) ?: '') : '';
$text_block = function_exists('get_field') ? (get_field('text_block', $post_id) ?: '') : '';


/* 3) Локализация JS */
wp_register_script('models-filter-app', false, [], null, true);
wp_enqueue_script('models-filter-app');
wp_localize_script('models-filter-app', 'SiteModelsFilter', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce'   => wp_create_nonce('site_filter_nonce'),
    'baseTax' => $base_tax,
    'perPage' => 48,
]);
?>

<main class="bg-white text-black">

    <section>
        <?php
        // Автоматический H1 (компонент)
        $auto_h1_component = get_theme_file_path('components/h1-auto.php');
        if (file_exists($auto_h1_component)) {
            require $auto_h1_component;
        }
        ?>

        <h1 class="max-w-[1280px] 2xl:max-w-[1400px] mx-auto mt-2 p-4 text-3xl md:text-5xl font-extrabold tracking-tight leading-tight text-center">
            <?php
            $h1 = get_query_var('auto_h1');
            if (empty($h1) && !empty($GLOBALS['auto_h1'])) {
                $h1 = $GLOBALS['auto_h1'];
            }
            if (empty($h1)) {
                $h1 = get_field('h1_atc') ?: get_the_title();
            }
            if ($paged > 1) {
                $h1 = trim($h1) . ' — страница ' . $paged;
            }
            echo esc_html($h1);
            ?>
        </h1>


        <?php if ($p_after_h1 && $paged === 1):
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $is_bot = (bool) preg_match('/bot|crawl|spider|slurp|mediapartners-google|bingpreview|duckduckbot|baiduspider|yandex|ahrefs|semrush|screaming\s?frog|facebookexternalhit|telegrambot/i', $ua);
            $text_html = wp_kses_post(apply_filters('the_content', $p_after_h1));
            $uid = uniqid('ah1_'); 
        ?>
            <div class="content mx-auto max-w-[1280px] 2xl:max-w-[1400px] px-4 mt-4 md:mt-5 text-base md:text-lg leading-relaxed space-y-4
            [&_p]:text-justify [&_li]:text-justify [&_p]:[hyphens:auto] [&_li]:[hyphens:auto]">

                <div id="<?= $uid ?>_box"
                    class="relative overflow-hidden transition-[max-height] duration-300 ease-in-out"
                    style="<?= $is_bot ? 'max-height:none' : 'max-height:14rem' ?>">
                    <?= $text_html ?>
                    <div id="<?= $uid ?>_fade"
                        class="pointer-events-none absolute left-0 right-0 bottom-0 h-16"
                        style="<?= $is_bot ? 'display:none' : 'background:linear-gradient(to bottom, rgba(255,255,255,0), #fff 70%)' ?>"></div>
                </div>

                <button id="<?= $uid ?>_btn"
                    class="mt-3 inline-flex items-center gap-2 text-[#e865a0] font-semibold hover:opacity-90 transition"
                    aria-expanded="<?= $is_bot ? 'true' : 'false' ?>"
                    <?= $is_bot ? 'hidden' : '' ?>>
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M6 9l6 6 6-6" stroke-width="2" />
                    </svg>
                    <span data-label><?= $is_bot ? 'Свернуть' : 'Показать ещё' ?></span>
                </button>

            </div>
            <script>
                (function() {
                    var box = document.getElementById('<?= $uid ?>_box');
                    var fade = document.getElementById('<?= $uid ?>_fade');
                    var btn = document.getElementById('<?= $uid ?>_btn');
                    if (!box || !btn) return;

                    var collapsedMax = 224; 
                    if (box.scrollHeight <= collapsedMax + 5) {
                        box.style.maxHeight = 'none';
                        if (fade) fade.style.display = 'none';
                        btn.style.display = 'none';
                        return;
                    }

                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        var opened = btn.getAttribute('aria-expanded') === 'true';

                        if (opened) {
                            box.style.maxHeight = collapsedMax + 'px';
                            if (fade) fade.style.display = '';
                            btn.setAttribute('aria-expanded', 'false');
                            btn.querySelector('[data-label]').textContent = 'Показать ещё';
                        } else {
                            box.style.maxHeight = box.scrollHeight + 'px';
                            setTimeout(() => { box.style.maxHeight = 'none'; }, 250);
                            if (fade) fade.style.display = 'none';
                            btn.setAttribute('aria-expanded', 'true');
                            btn.querySelector('[data-label]').textContent = 'Свернуть';
                        }
                    });
                })();
            </script>
        <?php endif; ?>
    </section>

    <!-- Секция видео-сторис (только на спец. странице) -->
    <?php 
    if (is_page('s-video')) {
        require_once get_template_directory() . '/components/stories-modal.php'; // Исправлено: был лишний слэш
    }
    ?>

    <!-- Секция моделей -->
    <section class="mx-auto max-w-[1280px] 2xl:max-w-[1400px] px-4 flex flex-col items-center gap-8 mt-8">

        <div class="w-full flex-1">

            <div id="filter-sorting-area" class="w-full flex flex-col gap-6">
                <?php
                    $h2_models = get_query_var('auto_h2') ?: ($GLOBALS['auto_h2'] ?? '');
                    if (!empty($h2_models)): ?>
                        <h2 class="text-2xl md:text-3xl font-bold tracking-tight break-words [hyphens:auto]">
                            <?= esc_html($h2_models) ?>
                        </h2>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>

                <?php echo render_model_filter(); ?>
                
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3 self-end md:self-auto">
                        <label for="mf-sort-trigger" class="text-sm font-bold uppercase tracking-wide text-black-500">Сортировка:</label>
                        
                        <div class="relative mf-dropdown-container" id="mf-sort-container" style="width: auto;">
                            <!-- Dropdown trigger -->
                            <button type="button" id="mf-sort-trigger"
                                class="mf-dropdown-trigger h-10 px-2 flex items-center justify-between border border-neutral-200 rounded-md bg-white hover:border-neutral-400 transition-colors text-left font-bold"
                                style="min-width: 260px;">
                                <span class="text-[14px] text-black font-medium truncate mf-trigger-label">Дата добавления — новые</span>
                                <svg class="w-5 h-5 text-neutral-300 pointer-events-none flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <!-- Dropdown content -->
                            <div class="mf-dropdown-content absolute left-0 right-0 top-full mt-1 z-[70] hidden bg-white border border-neutral-200 rounded-md shadow-xl max-h-60 overflow-y-auto p-1 space-y-1">
                                <div class="mf-sort-item mf-dropdown-item is-active flex items-center px-2 py-2 rounded-md cursor-pointer transition-all duration-200 hover:bg-neutral-50 group" data-value="date_desc">
                                    <span class="text-[11px] font-bold text-neutral-700 group-hover:text-neutral-900 transition-colors">Дата добавления — новые</span>
                                </div>
                                <div class="mf-sort-item mf-dropdown-item flex items-center px-2 py-2 rounded-md cursor-pointer transition-all duration-200 hover:bg-neutral-50 group" data-value="date_asc">
                                    <span class="text-[11px] font-bold text-neutral-700 group-hover:text-neutral-900 transition-colors">Дата добавления — старые</span>
                                </div>
                                <div class="mf-sort-item mf-dropdown-item flex items-center px-2 py-2 rounded-md cursor-pointer transition-all duration-200 hover:bg-neutral-50 group" data-value="price_asc">
                                    <span class="text-[11px] font-bold text-neutral-700 group-hover:text-neutral-900 transition-colors">Цена — дешёвые</span>
                                </div>
                                <div class="mf-sort-item mf-dropdown-item flex items-center px-2 py-2 rounded-md cursor-pointer transition-all duration-200 hover:bg-neutral-50 group" data-value="price_desc">
                                    <span class="text-[11px] font-bold text-neutral-700 group-hover:text-neutral-900 transition-colors">Цена — дорогие</span>
                                </div>
                            </div>
                            <input type="hidden" id="mf-sort" name="sort" value="date_desc">
                        </div>
                    </div>
                </div>
            </div>

            <div id="ajax-models" class="mt-8">
                <?php echo render_model_grid_with_filters(); ?>
            </div>
        </div>
    </section>

    <script>
        window.pageContext = {
            post_type: '<?= esc_js(get_post_type()); ?>',
            post_slug: '<?= esc_js(get_post_field('post_name', get_queried_object_id())); ?>',
            is_singular: <?= is_singular() ? 'true' : 'false'; ?>,
            is_tax: <?= is_tax() ? 'true' : 'false'; ?>,
            taxonomy: '<?= is_tax() ? esc_js(get_queried_object()->taxonomy ?? '') : ''; ?>',
            term_slug: '<?= is_tax() ? esc_js(get_queried_object()->slug ?? '') : ''; ?>'
        };
    </script>


    <?php if (!empty($content) && $paged === 1) : ?>
        <section>
            <div class="content mx-auto max-w-[1280px] 2xl:max-w-[1400px]
              mt-6 border border-neutral-200 rounded-sm bg-neutral-50 text-neutral-800
              overflow-x-auto -mx-4 px-4 pb-1
              md:overflow-x-visible md:mx-auto md:px-4">
                <?= wp_kses_post($content) ?>
            </div>
        </section>
    <?php endif; ?>


    <!-- FAQ Section -->
    <?php require_once get_template_directory() . '/components/faq-accordion.php'; ?>


    <!-- Responsive Table Scroll Wrapper -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.content table').forEach(function(t) {
                if (t.closest('.table-scroll') || t.closest('figure.wp-block-table') || t.classList.contains('responsive')) return;
                var w = document.createElement('div');
                w.className = 'table-scroll';
                t.parentNode.insertBefore(w, t);
                w.appendChild(t);
            });
        });
    </script>

    <?php if (!empty($text_block) && $paged === 1) : ?>
        <section>
            <div class="content mx-auto max-w-[1280px] 2xl:max-w-[1400px] px-4 mt-6
                bg-neutral-50 text-neutral-800 border border-neutral-200 rounded-sm">
                <?= wp_kses_post($text_block) ?>
            </div>
        </section>
    <?php endif; ?>

</main>

<?php get_footer(); ?>
