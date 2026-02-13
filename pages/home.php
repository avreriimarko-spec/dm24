<?php
/*
Template Name: Универсальный для страниц моделей/ главной
*/
/* Template Post Type: page, tsena, vozrast, nacionalnost, rajon, metro, rost, grud, ves, tsvet-volos, uslugi */

if (!defined('ABSPATH')) exit;

require_once get_template_directory() . '/components/ModelFilter.php';
require_once get_template_directory() . '/components/ModelGrid.php';
require_once get_template_directory() . '/components/auto-text.php';

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

// Для taxonomy-URL (services/slug и т.п.) поля должны браться из связанной CPT-записи.
$qo = get_queried_object();
if ($qo instanceof WP_Term && !empty($qo->slug)) {
    $tax_to_post_type = [
        'uslugi_tax'       => 'uslugi',
        'price_tax'        => 'tsena',
        'vozrast_tax'      => 'vozrast',
        'nationalnost_tax' => 'nacionalnost',
        'rayonu_tax'       => 'rajon',
        'metro_tax'        => 'metro',
        'rost_tax'         => 'rost',
        'grud_tax'         => 'grud',
        'ves_tax'          => 'ves',
        'cvet-volos_tax'   => 'tsvet-volos',
    ];

    $taxonomy = (string) $qo->taxonomy;
    if (isset($tax_to_post_type[$taxonomy])) {
        $linked_post = get_page_by_path((string) $qo->slug, OBJECT, $tax_to_post_type[$taxonomy]);
        if ($linked_post instanceof WP_Post && !empty($linked_post->ID)) {
            $post_id = (int) $linked_post->ID;
        }
    }
}
set_query_var('landing_source_post_id', $post_id);

/**
 * 2) ACF-поля и контент
 */
$p_after_h1_manual = function_exists('get_field') ? (get_field('p_atc', $post_id) ?: '') : '';
$p_after_h1 = $p_after_h1_manual;
$p_under_h2 = function_exists('get_field') ? (get_field('p_title', $post_id) ?: '') : '';
$content    = function_exists('get_field') ? (get_field('content', $post_id) ?: '') : '';
$text_block = function_exists('get_field') ? (get_field('text_block', $post_id) ?: '') : '';
$p_after_h1_is_auto = false;
$auto_links_block = '';
$district_h1_override = '';

if (function_exists('kyzdarki_generate_landing_auto_text')) {
    $auto_text = kyzdarki_generate_landing_auto_text([
        'post_id' => $post_id,
        'post_type' => (string) get_post_type($post_id),
        'page_slug' => $post_id ? (string) get_post_field('post_name', $post_id) : '',
        'taxonomy' => ($qo instanceof WP_Term) ? (string) $qo->taxonomy : '',
        'base_tax' => $base_tax,
        'city' => 'Алматы',
    ]);

    if ($p_after_h1 === '' && !empty($auto_text['p_after_h1'])) {
        $p_after_h1 = (string) $auto_text['p_after_h1'];
        $p_after_h1_is_auto = true;
    }
    if ($p_under_h2 === '' && !empty($auto_text['p_under_h2'])) {
        $p_under_h2 = (string) $auto_text['p_under_h2'];
    }
    if ($content === '' && !empty($auto_text['content'])) {
        $content = (string) $auto_text['content'];
    }
    if ($text_block === '' && !empty($auto_text['text_block'])) {
        $text_block = (string) $auto_text['text_block'];
    }
}

if ($paged === 1 && $p_after_h1_manual === '' && function_exists('kyzdarki_generate_landing_links_block')) {
    $auto_links_block = kyzdarki_generate_landing_links_block([
        'post_id' => $post_id,
        'post_type' => (string) get_post_type($post_id),
        'page_slug' => $post_id ? (string) get_post_field('post_name', $post_id) : '',
        'taxonomy' => ($qo instanceof WP_Term) ? (string) $qo->taxonomy : '',
        'base_tax' => $base_tax,
        'city' => 'Алматы',
    ]);
}

$is_district_context = (($base_tax['taxonomy'] ?? '') === 'rayonu_tax' && !empty($base_tax['terms']));
if ($is_district_context) {
    $district_term_id = (int) ((array) $base_tax['terms'])[0];
    $district_term = get_term($district_term_id, 'rayonu_tax');

    if ($district_term instanceof WP_Term && !is_wp_error($district_term)) {
        $district_name = function_exists('kyzdarki_auto_text_clean')
            ? kyzdarki_auto_text_clean((string) $district_term->name)
            : trim(wp_strip_all_tags((string) $district_term->name));

        if ($district_name !== '') {
            $district_name_safe = esc_html($district_name);
            $individualki_url = home_url('/individualki-almaty');
            $individualki_link = '<a href="' . esc_url($individualki_url) . '">индивидуалки</a>';

            $district_h1 = "Проститутки в районе {$district_name}: цены на интим услуги и фото";
            $district_h1_override = $district_h1;
            set_query_var('auto_h1', $district_h1);
            $GLOBALS['auto_h1'] = $district_h1;
            set_query_var('auto_h2', 'Анкеты проституток');
            $GLOBALS['auto_h2'] = 'Анкеты проституток';

            $models_count = isset($auto_text['models_count']) ? (int) $auto_text['models_count'] : 0;
            if ($models_count <= 0 && function_exists('kyzdarki_auto_text_count_models')) {
                $models_count = kyzdarki_auto_text_count_models($base_tax);
            }
            $models_count_text = number_format_i18n(max(0, $models_count));

            $model_ids_at_district = [];
            if (function_exists('kyzdarki_auto_text_get_model_ids_by_terms')) {
                $model_ids_at_district = kyzdarki_auto_text_get_model_ids_by_terms('rayonu_tax', [$district_term_id], 420);
            }

            $station_terms = [];
            if (
                !empty($model_ids_at_district)
                && function_exists('kyzdarki_auto_text_get_term_rows_by_models')
                && function_exists('kyzdarki_auto_text_terms_from_rows')
            ) {
                $metro_rows = kyzdarki_auto_text_get_term_rows_by_models($model_ids_at_district, 'metro_tax', [], 3);
                $station_terms = kyzdarki_auto_text_terms_from_rows($metro_rows);
            }

            if (count($station_terms) < 3) {
                $fallback_stations = get_terms([
                    'taxonomy' => 'metro_tax',
                    'hide_empty' => true,
                    'number' => 8,
                ]);
                if (!is_wp_error($fallback_stations) && !empty($fallback_stations)) {
                    $known_station_ids = [];
                    foreach ($station_terms as $station_term) {
                        if ($station_term instanceof WP_Term) {
                            $known_station_ids[(int) $station_term->term_id] = true;
                        }
                    }
                    foreach ($fallback_stations as $fallback_station) {
                        if (!$fallback_station instanceof WP_Term) {
                            continue;
                        }
                        $fallback_station_id = (int) $fallback_station->term_id;
                        if (isset($known_station_ids[$fallback_station_id])) {
                            continue;
                        }
                        $station_terms[] = $fallback_station;
                        $known_station_ids[$fallback_station_id] = true;
                        if (count($station_terms) >= 3) {
                            break;
                        }
                    }
                }
            }

            $station_items = [];
            foreach ($station_terms as $station_term) {
                if (!$station_term instanceof WP_Term) {
                    continue;
                }
                $name = function_exists('kyzdarki_auto_text_clean')
                    ? kyzdarki_auto_text_clean((string) $station_term->name)
                    : trim(wp_strip_all_tags((string) $station_term->name));
                if ($name === '') {
                    continue;
                }
                $station_url = get_term_link($station_term);
                $station_items[] = (is_string($station_url) && $station_url !== '' && !is_wp_error($station_url))
                    ? '<a href="' . esc_url($station_url) . '">' . esc_html($name) . '</a>'
                    : esc_html($name);
                if (count($station_items) >= 3) {
                    break;
                }
            }
            $station_fallback_labels = ['центральных станций', 'транспортных узлов', 'пересадочных станций'];
            while (count($station_items) < 3) {
                $station_items[] = esc_html($station_fallback_labels[count($station_items)]);
            }

            $resolve_min_price = static function (string $meta_key, int $term_id = 0): int {
                $args = [
                    'post_type' => 'models',
                    'post_status' => 'publish',
                    'posts_per_page' => 1,
                    'fields' => 'ids',
                    'no_found_rows' => true,
                    'orderby' => 'meta_value_num',
                    'order' => 'ASC',
                    'meta_key' => $meta_key,
                    'meta_type' => 'NUMERIC',
                    'meta_query' => [[
                        'key' => $meta_key,
                        'value' => 0,
                        'type' => 'NUMERIC',
                        'compare' => '>',
                    ]],
                ];
                if ($term_id > 0) {
                    $args['tax_query'] = [[
                        'taxonomy' => 'rayonu_tax',
                        'field' => 'term_id',
                        'terms' => [$term_id],
                        'operator' => 'IN',
                    ]];
                }

                $q = new WP_Query($args);

                $price = 0;
                if (!empty($q->posts)) {
                    $pid = (int) $q->posts[0];
                    $price = (int) get_post_meta($pid, $meta_key, true);
                }
                wp_reset_postdata();
                return max(0, $price);
            };

            $min_price_outcall = $resolve_min_price('price_outcall', $district_term_id);
            $min_price_incall = $resolve_min_price('price', $district_term_id);
            $price_pool = array_filter([$min_price_outcall, $min_price_incall], static function (int $price): bool {
                return $price > 0;
            });
            $min_price = !empty($price_pool) ? min($price_pool) : 0;
            if ($min_price <= 0) {
                $global_min_price_outcall = $resolve_min_price('price_outcall');
                $global_min_price_incall = $resolve_min_price('price');
                $global_price_pool = array_filter([$global_min_price_outcall, $global_min_price_incall], static function (int $price): bool {
                    return $price > 0;
                });
                $min_price = !empty($global_price_pool) ? min($global_price_pool) : 0;
            }
            if ($min_price <= 0 && function_exists('_seo_min_price_label_by_term')) {
                $min_price_label_raw = (string) _seo_min_price_label_by_term($district_term, 'rayonu_tax');
                $min_price = (int) preg_replace('~\D+~', '', $min_price_label_raw);
            }
            $min_price_text = number_format_i18n(max(1, $min_price));

            $neighbor_terms = [];
            if (
                !empty($station_terms)
                && function_exists('kyzdarki_auto_text_get_model_ids_by_terms')
                && function_exists('kyzdarki_auto_text_get_term_rows_by_models')
                && function_exists('kyzdarki_auto_text_terms_from_rows')
            ) {
                $station_ids = [];
                foreach ($station_terms as $station_term) {
                    if ($station_term instanceof WP_Term) {
                        $station_ids[] = (int) $station_term->term_id;
                    }
                }
                if (!empty($station_ids)) {
                    $model_ids_by_stations = kyzdarki_auto_text_get_model_ids_by_terms('metro_tax', $station_ids, 560);
                    $neighbor_rows = kyzdarki_auto_text_get_term_rows_by_models(
                        $model_ids_by_stations,
                        'rayonu_tax',
                        [$district_term_id],
                        3
                    );
                    $neighbor_terms = kyzdarki_auto_text_terms_from_rows($neighbor_rows);
                }
            }

            if (count($neighbor_terms) < 3) {
                $fallback_neighbors = get_terms([
                    'taxonomy' => 'rayonu_tax',
                    'hide_empty' => true,
                    'exclude' => [$district_term_id],
                    'number' => 8,
                ]);
                if (!is_wp_error($fallback_neighbors) && !empty($fallback_neighbors)) {
                    $known_neighbor_ids = [];
                    foreach ($neighbor_terms as $neighbor_term) {
                        if ($neighbor_term instanceof WP_Term) {
                            $known_neighbor_ids[(int) $neighbor_term->term_id] = true;
                        }
                    }
                    foreach ($fallback_neighbors as $fallback_neighbor) {
                        if (!$fallback_neighbor instanceof WP_Term) {
                            continue;
                        }
                        $fallback_neighbor_id = (int) $fallback_neighbor->term_id;
                        if (isset($known_neighbor_ids[$fallback_neighbor_id])) {
                            continue;
                        }
                        $neighbor_terms[] = $fallback_neighbor;
                        $known_neighbor_ids[$fallback_neighbor_id] = true;
                        if (count($neighbor_terms) >= 3) {
                            break;
                        }
                    }
                }
            }

            $neighbor_items = [];
            foreach ($neighbor_terms as $neighbor_term) {
                if (!$neighbor_term instanceof WP_Term) {
                    continue;
                }
                $neighbor_name = function_exists('kyzdarki_auto_text_clean')
                    ? kyzdarki_auto_text_clean((string) $neighbor_term->name)
                    : trim(wp_strip_all_tags((string) $neighbor_term->name));
                if ($neighbor_name === '') {
                    continue;
                }
                $neighbor_url = get_term_link($neighbor_term);
                $neighbor_items[] = '<li>' . (
                    is_string($neighbor_url) && $neighbor_url !== '' && !is_wp_error($neighbor_url)
                        ? '<a href="' . esc_url($neighbor_url) . '">' . esc_html($neighbor_name) . '</a>'
                        : esc_html($neighbor_name)
                ) . '</li>';
                if (count($neighbor_items) >= 3) {
                    break;
                }
            }
            while (count($neighbor_items) < 3) {
                $neighbor_items[] = '<li>Соседний район</li>';
            }
            $neighbor_list = implode('', $neighbor_items);

            $p_after_h1 = '<p>В этом разделе представлен актуальный список проверенных анкет проституток, предлагающих интимный досуг в границах района ' . $district_name_safe . '. Если вы ищете качественный секс отдых без посредников, здесь собраны профили ' . esc_html($models_count_text) . ' индивидуалок, готовых к встрече в ближайшее время. Благодаря удобному расположению в ' . $district_name_safe . ', вы можете организовать свидание с девушкой в течение 15-20 минут.</p>';
            $p_after_h1_is_auto = true;

            $p_under_h2 = '';
            $auto_links_block = '';
            $content = '<h2>Интимный отдых и услуги секса в районе ' . $district_name_safe . '</h2>'
                . '<p>Выбор ' . $individualki_link . ' в ' . $district_name_safe . ' гарантирует вам полную анонимность и большой выбор программ. Девушки из нашего каталога работают в частном секторе и современных ЖК, обеспечивая комфортный интим сервис в шаговой доступности от ключевых точек района.</p>'
                . '<ul>'
                . '<li>Локация и метро: Основная концентрация анкет сосредоточена возле станций ' . $station_items[0] . ', ' . $station_items[1] . ' и ' . $station_items[2] . '.</li>'
                . '<li>Стоимость услуг: Цены на интим в районе ' . $district_name_safe . ' начинаются от ' . esc_html($min_price_text) . ' рублей за час.</li>'
                . '<li>Реальные фото: Все анкеты девушек проходят верификацию. Пометка «Проверено» подтверждает, что снимки в профиле на 100% соответствуют реальности.</li>'
                . '<li>Выезд и прием: Большинство мастеров предлагают как прием в своих апартаментах, так и выезд проституток по любому адресу в пределах ' . $district_name_safe . '.</li>'
                . '</ul>'
                . '<h2>Стоимость индивидуалок и подбор анкет поблизости</h2>'
                . '<p>Если вы не нашли подходящий вариант для досуга непосредственно в ' . $district_name_safe . ', рекомендуем расширить географию поиска. Вы можете найти дешевых проституток или VIP-моделей в соседних локациях:</p>'
                . '<ol>' . $neighbor_list . '</ol>'
                . '<p>Такая навигация позволит вам быстро забронировать интим услуги у проверенной леди в радиусе 10-15 минут на авто или такси.</p>';
            $text_block = '';
        }
    }
}


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
            if ($district_h1_override !== '') {
                $h1 = $district_h1_override;
            } else {
                $h1 = get_query_var('auto_h1');
                if (empty($h1) && !empty($GLOBALS['auto_h1'])) {
                    $h1 = $GLOBALS['auto_h1'];
                }
                if (empty($h1)) {
                    $h1 = function_exists('get_field') ? (get_field('h1_atc', $post_id) ?: '') : '';
                }
                if (empty($h1)) {
                    $h1 = get_the_title($post_id);
                }
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
            $text_html = $p_after_h1_is_auto
                ? wp_kses_post($p_after_h1)
                : wp_kses_post(apply_filters('the_content', $p_after_h1));
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
                <?php echo render_model_filter(); ?>
                
                <div class="title-and-sorting flex flex-col justify-between gap-4">
                    <?php 
                        $h2_models = get_query_var('auto_h2') ?: ($GLOBALS['auto_h2'] ?? '');
                        if ($h2_models === '') {
                            $h2_models = function_exists('get_field') ? (string) (get_field('h2_title', $post_id) ?: '') : '';
                        }
                        if ($h2_models === '') {
                            $h2_models = 'Проститутки Москвы';
                        }
                        if (!empty($h2_models)): ?>
                            <h2 class="text-2xl md:text-3xl font-bold tracking-tight break-words [hyphens:auto]">
                                <?= esc_html($h2_models) ?>
                            </h2>
                        <?php endif; ?>
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
                <style>.title-and-sorting { @media (min-width: 768px) { flex-direction: row } }</style>
            </div>

            <?php if (!empty($p_under_h2) && $paged === 1) : ?>
                <div class="content mt-4 text-neutral-700">
                    <?= wp_kses_post($p_under_h2) ?>
                </div>
            <?php endif; ?>

            <div id="ajax-models" class="mt-8">
                <?php echo render_model_grid_with_filters(); ?>
            </div>

        </div>
    </section>

    <script>
        window.pageContext = {
            post_type: '<?= esc_js(get_post_type($post_id)); ?>',
            post_slug: '<?= esc_js((string) get_post_field('post_name', $post_id)); ?>',
            is_singular: <?= is_singular() ? 'true' : 'false'; ?>,
            is_tax: <?= is_tax() ? 'true' : 'false'; ?>,
            taxonomy: '<?= is_tax() ? esc_js(get_queried_object()->taxonomy ?? '') : ''; ?>',
            term_slug: '<?= is_tax() ? esc_js(get_queried_object()->slug ?? '') : ''; ?>'
        };
    </script>


    <?php
    $has_bottom_seo = $paged === 1 && (!empty($auto_links_block) || !empty($content) || !empty($text_block));
    $clean_bottom_seo_html = static function ($html) {
        $html = wp_kses_post($html);
        $html = preg_replace('/\s(?:class|style)=(["\']).*?\1/i', '', $html);

        return preg_replace_callback('/<(p|ul|ol)\b[^>]*>/i', static function ($m) {
            $tag = preg_replace('/\s{2,}/', ' ', $m[0]);
            $tag = str_replace(' >', '>', $tag);
            $name = strtolower($m[1]);

            if ($name === 'p') {
                return str_replace('<p', '<p style="margin:0;padding:0"', $tag);
            }

            return str_replace('<' . $name, '<' . $name . ' style="margin:0;padding:0;list-style-position:inside"', $tag);
        }, $html);
    };
    ?>
    <?php if ($has_bottom_seo) : ?>
        <section class="mx-auto max-w-[1280px] 2xl:max-w-[1400px] px-4 mb-6">
            <div class="content bg-neutral-50 text-neutral-800 border border-neutral-200 rounded-sm px-4 py-5 md:py-6">
                <?php if (!empty($auto_links_block)) : ?>
                    <div class="[&_a]:underline [&_a]:underline-offset-4 [&_a]:hover:opacity-80">
                        <?= $clean_bottom_seo_html($auto_links_block) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($content)) : ?>
                    <div class="<?= !empty($auto_links_block) ? 'mt-6' : '' ?> overflow-x-auto md:overflow-x-visible">
                        <?= $clean_bottom_seo_html($content) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($text_block)) : ?>
                    <div class="<?= (!empty($auto_links_block) || !empty($content)) ? 'mt-6' : '' ?>">
                        <?= $clean_bottom_seo_html($text_block) ?>
                    </div>
                <?php endif; ?>
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

</main>

<?php get_footer(); ?>
