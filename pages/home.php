<?php
/*
Template Name: Универсальный для страниц моделей/ главной
*/
/* Template Post Type: page, tsena, vozrast, nacionalnost, rajon, metro, rost, grud, ves, tsvet-volos, uslugi */

if (!defined('ABSPATH')) exit;

require_once get_template_directory() . '/components/ModelFilter.php';
require_once get_template_directory() . '/components/ModelGrid.php';

/* ============================================================
 * 1) БАЗОВЫЙ ФИЛЬТР + ПРЕДВАРИТЕЛЬНЫЙ СПИСОК ДЛЯ JSON-LD (до get_header)
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

// c) Посадочные CPT (tsena, vozrast, tsvet-volos и т.д.), где slug совпадает с термином таксы
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

/** 1.2 Готовим лёгкий список моделей для JSON-LD с учётом base_tax */
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

/** 1.3 Прокидываем список в мост для JSON-LD диспетчера (если он есть) */

/** 1.4 Прокинем base_tax диспетчеру JSON-LD и во фронт */
set_query_var('base_tax', $base_tax);

/* ===== ТОЛЬКО ТЕПЕРЬ ШАПКА ===== */
get_header();
$paged = max(1, (int)(get_query_var('paged') ?: get_query_var('page') ?: 1));

/* =======================
 * 2) ACF-поля страницы
 * ======================= */
$post_id = get_queried_object_id();

/**
 * Заголовок H1 страницы:
 * 1) ACF поле 'h1_atc' — если заполнено
 * 2) Для архивов таксономий — имя термина
 * 3) Иначе — заголовок страницы
 */
if (!function_exists('site_build_h1')) {
    function site_build_h1(int $post_id): string
    {
        $acf_h1 = function_exists('get_field') ? (string)(get_field('h1_atc', $post_id) ?: '') : '';
        if ($acf_h1 !== '') return $acf_h1;

        if (is_tax()) {
            $qo = get_queried_object();
            if ($qo instanceof WP_Term && !empty($qo->name)) {
                return (string)$qo->name;
            }
        }

        $title = get_the_title($post_id);
        return $title ?: 'Каталог моделей';
    }
}

/**
 * Дополнительный заголовок H2 над сеткой (при необходимости).
 * По умолчанию — берёт ACF 'h2_title', иначе формирует общий фолбэк.
 */

$p_after_h1  = function_exists('get_field') ? (get_field('p_atc', $post_id) ?: '') : '';
$p_models    = function_exists('get_field') ? (get_field('p_title', $post_id) ?: '') : '';

$banner_html      = function_exists('get_field') ? (get_field('banner-html',      $post_id) ?: '') : '';
$descr_html       = function_exists('get_field') ? (get_field('descr-html',       $post_id) ?: '') : '';
$background_image = function_exists('get_field') ? (get_field('background-image', $post_id) ?: '') : '';
$content          = function_exists('get_field') ? (get_field('content',          $post_id) ?: '') : '';
$text_block       = function_exists('get_field') ? (get_field('text_block',       $post_id) ?: '') : '';

$faq_h1 = function_exists('get_field') ? (get_field('faq_h1', $post_id) ?: '') : '';
$faq_p  = function_exists('get_field') ? (get_field('faq_p',  $post_id) ?: '') : '';

$number = get_theme_mod('contact_number');

/* =======================
 * 3) Локализация JS (передаём baseTax)
 * ======================= */
wp_register_script('models-filter-app', false, [], null, true);
wp_enqueue_script('models-filter-app');
wp_localize_script('models-filter-app', 'SiteModelsFilter', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce'   => wp_create_nonce('site_filter_nonce'),
    'baseTax' => $base_tax, // найденный термин(ы) по странице/архиву
    'perPage' => 48,
]);
?>

<main class="bg-white text-black">

    <section>
        <?php
        // Перед выводом H1
        $auto_h1_component = get_theme_file_path('components/h1-auto.php');
        if (file_exists($auto_h1_component)) {
            require $auto_h1_component;
        }
        ?>

        <h1 class="max-w-[1280px] 2xl:max-w-[1400px] mx-auto mt-2 p-4 text-3xl md:text-5xl font-extrabold tracking-tight leading-tight text-center">
            <?php
            // Пытаемся взять из auto_h1 (query_var / globals)
            $h1 = get_query_var('auto_h1');

            if (empty($h1) && !empty($GLOBALS['auto_h1'])) {
                $h1 = $GLOBALS['auto_h1'];
            }

            // На всякий случай жёсткий фолбэк, если компонент не отработал
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
            $uid = uniqid('ah1_'); // уникальный суффикс
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

                    var collapsedMax = 14 * 16; // 14rem ~= 224px

                    // если текста мало — показываем всё и прячем кнопку
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
                            // плавное раскрытие
                            box.style.maxHeight = box.scrollHeight + 'px';
                            setTimeout(function() {
                                box.style.maxHeight = 'none';
                            }, 250);
                            if (fade) fade.style.display = 'none';
                            btn.setAttribute('aria-expanded', 'true');
                            btn.querySelector('[data-label]').textContent = 'Свернуть';
                        }
                    });
                })();
            </script>
        <?php endif; ?>
    </section>


<?php if (is_page('s-video')): ?>
<?php
/* ==========================================================
   PHP: ПОЛУЧЕНИЕ ДАННЫХ
=========================================================== */
$models_with_video = get_posts([
    'post_type'      => 'models',
    'posts_per_page' => 30,
    'fields'         => 'ids',
    'no_found_rows'  => true,
    'meta_query'     => [['key' => 'video', 'value' => '', 'compare' => '!=']],
]);

$extract_video = static function ($post_id) {
    $raw = get_post_meta($post_id, 'video', true);
    if (is_array($raw)) {
        if (!empty($raw['url'])) return trim($raw['url']);
        if (!empty($raw[0])) {
            $f = $raw[0];
            if (is_array($f) && !empty($f['url'])) return trim($f['url']);
            if (is_string($f)) return trim($f);
        }
    } elseif (is_numeric($raw)) return wp_get_attachment_url((int)$raw);
    elseif (is_string($raw)) {
        $p = preg_split('~[\s,;]+~u', $raw, -1, PREG_SPLIT_NO_EMPTY);
        if (!empty($p[0])) return trim($p[0]);
    }
    return '';
};

$get_story_thumb = static function ($post_id) {
    $placeholder = get_stylesheet_directory_uri() . '/assets/images/placeholder-thumbs.webp';
    $photo = get_post_meta($post_id, 'photo', true);
    if (is_array($photo)) {
        $first = $photo[0] ?? null;
        if (is_array($first) && !empty($first['ID'])) {
            $img = wp_get_attachment_image_src((int)$first['ID'], 'thumbnail');
            if ($img) return ['src' => $img[0], 'width' => $img[1], 'height' => $img[2]];
        }
        if (is_numeric($first)) {
            $img = wp_get_attachment_image_src((int)$first, 'thumbnail');
            if ($img) return ['src' => $img[0], 'width' => $img[1], 'height' => $img[2]];
        }
        if (is_array($first) && !empty($first['url'])) return ['src' => esc_url($first['url']), 'width' => 96, 'height' => 96];
    }
    $thumb_id = get_post_thumbnail_id($post_id);
    if ($thumb_id) {
        $img = wp_get_attachment_image_src($thumb_id, 'thumbnail');
        if ($img) return ['src' => $img[0], 'width' => $img[1], 'height' => $img[2]];
    }
    return ['src' => esc_url($placeholder), 'width' => 96, 'height' => 96];
};
?>

<?php if ($models_with_video): ?>
<section id="stories-section" class="my-10 px-4">
    <div class="mx-auto max-w-[84rem] relative group">
        
        <div id="stories-container" class="flex gap-4 overflow-x-auto no-scrollbar py-4 px-1 cursor-grab select-none active:cursor-grabbing">
            <?php foreach ($models_with_video as $model_id):
                $video = $extract_video($model_id);
                if (!$video) continue;
                $name  = esc_html(get_post_meta($model_id, 'name', true) ?: get_the_title($model_id));
                $thumb = $get_story_thumb($model_id);
                $age    = get_field("age", $model_id);
                $height = get_field("height", $model_id);
                $weight = get_field("weight", $model_id);
                $bust   = get_field("bust", $model_id);
                $price  = get_field("price", $model_id);
            ?>
            <button class="story-btn story-ig flex-shrink-0 w-20 h-20 rounded-full p-[4px] relative transition-transform hover:scale-105"
                data-video="<?= esc_url($video) ?>"
                data-id="<?= $model_id ?>"
                data-name="<?= esc_attr($name) ?>"
                data-link="<?= esc_url(get_permalink($model_id)) ?>"
                data-age="<?= esc_attr($age) ?>"
                data-height="<?= esc_attr($height) ?>"
                data-weight="<?= esc_attr($weight) ?>"
                data-bust="<?= esc_attr($bust) ?>"
                data-price="<?= esc_attr($price) ?>">
                <span class="relative block w-full h-full rounded-full overflow-hidden ring-2 ring-white/50 pointer-events-none">
                    <img src="<?= esc_url($thumb['src']) ?>" alt="<?= $name ?>" width="<?= $thumb['width'] ?>" height="<?= $thumb['height'] ?>" class="w-full h-full object-cover rounded-full">
                </span>
            </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="video-modal" class="hidden fixed inset-0 bg-black/90 flex items-center justify-center z-[999999]">
        
        <div id="video-wrapper" class="relative w-full max-w-3xl bg-black rounded-xl overflow-hidden h-full md:h-auto md:aspect-[9/16] max-h-[90vh]">

            <iframe id="video-iframe" class="w-full h-full hidden" allowfullscreen></iframe>
            <video id="video-player" class="w-full h-full hidden rounded-xl bg-black object-contain" playsinline preload="metadata"></video>

            <button id="close-video" class="absolute top-4 right-4 text-white text-5xl font-bold z-50 hover:opacity-70 transition-opacity">&times;</button>
            <button id="story-prev" class="absolute left-2 top-1/2 -translate-y-1/2 text-white text-7xl opacity-80 hover:opacity-100 z-50">‹</button>
            <button id="story-next" class="absolute right-2 top-1/2 -translate-y-1/2 text-white text-7xl opacity-80 hover:opacity-100 z-50">›</button>

            <div class="story-bottom-bar absolute left-0 right-0 px-6 z-50 flex justify-between items-center" style="bottom: 30px;">
                <a id="story-name" href="#" class="text-white text-xl font-semibold underline-offset-2 hover:text-gray-300 transition-colors"></a>
                
                <button id="story-more" class="text-white text-lg px-4 py-2 bg-white/20 rounded-lg backdrop-blur hover:bg-white/30 transition-all active:scale-95 z-50">
                    Параметры
                </button>

                <button id="story-fav" class="text-white text-4xl select-none hover:scale-110 transition-transform cursor-pointer z-50">♡</button>
            </div>

            <div id="fav-toast" class="hidden absolute top-10 left-1/2 -translate-x-1/2 bg-white text-black px-6 py-2 rounded-full shadow-xl text-lg font-semibold z-[70] text-center whitespace-nowrap"></div>

        </div> 

        <div id="story-panel" 
             class="absolute top-1/2 left-1/2 w-[90%] max-w-sm 
                    bg-black/95 text-white p-6 rounded-2xl 
                    backdrop-blur-sm border border-white/10 shadow-2xl z-[80]">
            
            <button id="story-panel-close" class="absolute top-2 right-4 text-3xl text-gray-400 hover:text-white cursor-pointer p-2">&times;</button>
            <div id="story-panel-content" class="space-y-3 text-lg mt-4"></div>
        </div>

    </div>
</section>

<style>
/* ОСНОВНОЕ: ПЕРЕКРЫТИЕ ВСЕГО САЙТА */
#video-modal {
    z-index: 2147483647 !important; /* Максимальный Z-Index в браузере */
}

#stories-container { user-select:none; cursor:grab; }
#stories-container.active { cursor:grabbing; }

/* === ПАНЕЛЬ ПАРАМЕТРОВ === */
#story-panel {
    transform: translate(-50%, -50%) scale(0.9);
    opacity: 0;
    pointer-events: none;
    transition: all 0.2s ease-out;
}
#story-panel.open {
    transform: translate(-50%, -50%) scale(1) !important;
    opacity: 1 !important;
    pointer-events: auto !important;
}
@media (max-width:640px) { .story-bottom-bar { bottom: 80px !important; } }
#video-player::-webkit-media-controls-panel { display:none!important; }
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const KEYS = ["favModels","favModelsV1","favorites","favoritesModels"];
    
    // Элементы
    const modal   = document.getElementById("video-modal");
    const iframe  = document.getElementById("video-iframe");
    const video   = document.getElementById("video-player");
    const wrapper = document.getElementById("video-wrapper");
    const btnPrev = document.getElementById("story-prev");
    const btnNext = document.getElementById("story-next");
    const btnFav  = document.getElementById("story-fav");
    const btnMore = document.getElementById("story-more");
    const btnClose= document.getElementById("close-video");
    const nameEl  = document.getElementById("story-name");
    const panel   = document.getElementById("story-panel");
    const panelContent = document.getElementById("story-panel-content");
    const panelClose = document.getElementById("story-panel-close");
    const toast   = document.getElementById("fav-toast");

    let stories = [...document.querySelectorAll(".story-btn")].map(btn => ({
        id: parseInt(btn.dataset.id), name: btn.dataset.name, link: btn.dataset.link, video: btn.dataset.video,
        age: btn.dataset.age, height: btn.dataset.height, weight: btn.dataset.weight, bust: btn.dataset.bust, price: btn.dataset.price
    }));
    let current = 0;
    let toastTimer = null;

    // === ФУНКЦИЯ ИЗБРАННОГО ===
    function toggleFavorite(id) {
        let isAdded = false;
        KEYS.forEach(key => {
            let list = []; try { list = JSON.parse(localStorage.getItem(key) || "[]"); } catch(e) {}
            if (!Array.isArray(list)) list = [];
            const index = list.indexOf(id);
            if (index > -1) {
                list.splice(index, 1); isAdded = false;
            } else {
                list.push(id); isAdded = true;
            }
            localStorage.setItem(key, JSON.stringify(list));
        });
        return isAdded;
    }

    function checkIsFav(id) {
        let list = []; try { list = JSON.parse(localStorage.getItem("favorites") || "[]"); } catch(e) {}
        return list.includes(id);
    }

    // === ЗАЩИТА ОТ ВСПЛЫТИЯ СОБЫТИЙ ===
    function killEvent(e) { e.stopPropagation(); }
    const controls = [btnMore, btnFav, btnClose, panel, panelClose];
    controls.forEach(el => {
        if(!el) return;
        el.addEventListener("touchstart", killEvent, {passive: false});
        el.addEventListener("touchend", killEvent);
        el.addEventListener("mousedown", killEvent);
        el.addEventListener("mouseup", killEvent);
        el.addEventListener("click", killEvent);
    });

    // === ОТКРЫТИЕ ===
    function openStory(i){
        current = i;
        const s = stories[i];
        panel.classList.remove("open");
        modal.classList.remove("hidden");
        nameEl.textContent = s.name;
        nameEl.href = s.link;

        const isFav = checkIsFav(s.id);
        updateFavBtn(isFav);

        if (s.video.includes("youtu")) {
            video.classList.add("hidden"); iframe.classList.remove("hidden");
            iframe.src = s.video + "?autoplay=1";
        } else {
            iframe.src = ""; iframe.classList.add("hidden");
            video.src = s.video; video.classList.remove("hidden");
            video.play();
        }
    }

    function updateFavBtn(isFav) {
        if (isFav) {
            btnFav.textContent = "♥"; btnFav.classList.add("text-red-500");
        } else {
            btnFav.textContent = "♡"; btnFav.classList.remove("text-red-500");
        }
    }

    function next(){ if (current < stories.length-1) openStory(current+1); }
    function prev(){ if (current > 0) openStory(current-1); }

    document.querySelectorAll(".story-btn").forEach((btn,i)=> btn.addEventListener("click", ()=>openStory(i)));

    // === КНОПКИ ===
    btnNext.onclick = (e) => { killEvent(e); next(); }
    btnPrev.onclick = (e) => { killEvent(e); prev(); }
    btnClose.onclick = (e) => {
        killEvent(e);
        modal.classList.add("hidden");
        iframe.src=""; video.pause();
        panel.classList.remove("open");
    };

    // === ПАРАМЕТРЫ ===
    btnMore.onclick = (e) => {
        killEvent(e);
        if (!panel.classList.contains("open")) {
            const s = stories[current];
            panelContent.innerHTML = "";
            const fields = [["Возраст", s.age], ["Рост", s.height], ["Вес", s.weight], ["Грудь", s.bust], ["Цена", s.price]];
            let hasData = false;
            fields.forEach(([label,val])=>{
                if (!val) return; hasData = true;
                panelContent.innerHTML += `<div class="flex justify-between border-b border-white/20 py-2 last:border-0"><span class="opacity-70">${label}</span><span class="font-bold text-xl">${val}</span></div>`;
            });
            if(!hasData) panelContent.innerHTML = '<div class="text-center opacity-60">Нет данных</div>';
        }
        panel.classList.toggle("open");
    };
    panelClose.onclick = (e) => { killEvent(e); panel.classList.remove("open"); };
    wrapper.onclick = (e) => { if(panel.classList.contains("open")) { panel.classList.remove("open"); e.stopPropagation(); } };

    // === ЛАЙК ===
    btnFav.onclick = (e) => {
        killEvent(e);
        const s = stories[current];
        const isNowFav = toggleFavorite(s.id);
        updateFavBtn(isNowFav);
        toast.textContent = isNowFav ? `${s.name} добавлена в избранное` : `${s.name} удалена из избранного`;
        toast.classList.remove("hidden");
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(()=>toast.classList.add("hidden"), 2000);
    };

    // === СВАЙПЫ ===
    let startX = 0, swiping=false;
    function start(e){
        if (e.target.closest('button') || e.target.closest('#story-panel') || e.target.closest('#story-more')) return;
        startX = e.touches? e.touches[0].clientX : e.clientX; swiping=false;
    }
    function move(e){
        if (panel.classList.contains("open")) return;
        let dx = (e.touches? e.touches[0].clientX : e.clientX) - startX;
        if (Math.abs(dx)>15) swiping=true;
    }
    function end(e){
        if (!swiping) return; if (panel.classList.contains("open")) return;
        let dx = (e.changedTouches? e.changedTouches[0].clientX : e.clientX) - startX;
        if (dx>50) prev(); if (dx<-50) next();
    }
    
    [modal, wrapper].forEach(el=>{
        el.addEventListener("touchstart",start); el.addEventListener("touchmove",move); el.addEventListener("touchend",end);
        el.addEventListener("mousedown",start); el.addEventListener("mousemove",move); el.addEventListener("mouseup",end);
    });
});
</script>
<?php endif; ?>
<?php endif; ?>








    <!-- Секция моделей -->
    <section class="mx-auto max-w-[1280px] 2xl:max-w-[1400px] px-4 flex flex-row justify-between items-start gap-8">

        <!-- Модели + заголовок/описание справа -->
        <div class="flex-1 min-w-0">

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
                            <button type="button" id="mf-sort-trigger"
                                class="mf-dropdown-trigger h-10 px-2 flex items-center justify-between border border-neutral-200 rounded-md bg-white hover:border-neutral-400 transition-colors text-left font-bold"
                                style="min-width: 260px;">
                                <span class="text-[14px] text-black font-medium truncate mf-trigger-label">Дата добавления — новые</span>
                                <svg class="w-5 h-5 text-neutral-300 pointer-events-none flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>

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
              /* дорожка-скролл на мобилке */
              overflow-x-auto -mx-4 px-4 pb-1
              md:overflow-x-visible md:mx-auto md:px-4">
                <?= wp_kses_post($content) ?>
            </div>
        </section>
    <?php endif; ?>


    <?php
    // Собираем только реально заполненные элементы FAQ
    $faq_rows_raw = function_exists('get_field') ? (get_field('faq', $post_id) ?: []) : [];
    $faq_rows = [];
    if (is_array($faq_rows_raw)) {
        foreach ($faq_rows_raw as $row) {
            $b = $row['faq_block'] ?? $row;
            $q = trim((string)($b['question'] ?? $row['question'] ?? ''));
            $a_raw = (string)($b['answer'] ?? $row['answer'] ?? '');
            $a_check = trim(wp_strip_all_tags(do_shortcode($a_raw)));
            if ($q !== '' && $a_check !== '') $faq_rows[] = ['q' => $q, 'a' => $a_raw];
        }
    }

    if (!empty($faq_rows)) : ?>
        <section id="faq" class="py-12 mx-auto max-w-[1280px] 2xl:max-w-[1400px] px-4">
            <?php if (!empty($faq_h1)) : ?>
                <h2 class="text-[28px] md:text-[34px] font-extrabold text-center tracking-tight">
                    <?= esc_html($faq_h1) ?>
                </h2>
            <?php endif; ?>

            <?php if (!empty($faq_p)) : ?>
                <p class="text-neutral-700 text-center mt-2 mb-8 max-w-[820px] mx-auto"><?= esc_html($faq_p) ?></p>
            <?php endif; ?>

            <div class="space-y-4">
                <?php foreach ($faq_rows as $i => $item) :
                    $pid = 'faq-panel-' . ($i + 1);
                    $q   = $item['q'];
                    $a   = $item['a'];
                    $is_open = ($i === 0);
                ?>
                    <div class="faq-item rounded-xl border border-neutral-200 overflow-hidden bg-white shadow-[0_1px_0_rgba(0,0,0,.04)]">
                        <button
                            type="button"
                            class="faq-trigger w-full text-left px-5 py-4 flex items-center justify-between gap-4 bg-white hover:bg-neutral-50 transition-colors border-l-4"
                            style="border-left-color:#e865a0"
                            aria-expanded="<?= $is_open ? 'true' : 'false' ?>"
                            aria-controls="<?= esc_attr($pid) ?>">
                            <span class="pr-6 font-semibold text-[15px] leading-snug"><?= esc_html($q) ?></span>
                            <svg class="chev w-5 h-5 text-neutral-700 transition-transform duration-200" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M7 10l5 5 5-5H7z" />
                            </svg>
                        </button>

                        <!-- тонкая линия-акцент под вопросом -->
                        <div class="h-px bg-neutral-200 relative after:absolute after:left-0 after:top-0 after:h-px after:w-24 after:bg-[#e865a0]"></div>

                        <div id="<?= esc_attr($pid) ?>" class="faq-panel <?= $is_open ? '' : 'is-collapsed' ?>">
                            <div class="px-5 md:px-6 py-4 md:py-5">
                                <div class="prose prose-sm max-w-[820px] mx-auto text-neutral-800 prose-a:text-[#e865a0] prose-a:underline hover:prose-a:no-underline">
                                    <?= wp_kses_post(apply_filters('the_content', $a)) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <script>
                (function() {
                    const root = document.getElementById('faq');
                    if (!root) return;
                    const items = Array.from(root.querySelectorAll('.faq-item'));

                    items.forEach((item) => {
                        const btn = item.querySelector('.faq-trigger');
                        const pan = item.querySelector('.faq-panel');
                        if (!btn || !pan) return;

                        btn.addEventListener('click', () => {
                            const open = btn.getAttribute('aria-expanded') === 'true';
                            btn.setAttribute('aria-expanded', String(!open));
                            pan.classList.toggle('is-collapsed', open);
                        });
                    });
                })();
            </script>
        </section>
    <?php endif; ?>

    <!-- Скрипт для блока с видео -->
    <?php if (is_page('s-video')): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('video-modal');
                const iframe = document.getElementById('video-iframe');
                const player = document.getElementById('video-player');
                const closeBtn = document.getElementById('close-video');

                function openVideo(src) {
                    iframe.classList.add('hidden');
                    player.classList.add('hidden');
                    iframe.src = '';
                    player.src = '';

                    const url = src.trim();
                    const wrapper = document.getElementById('video-modal');
                    wrapper.classList.remove('portrait');

                    // YouTube
                    if (/youtube\.com|youtu\.be/i.test(url)) {
                        const id = url.match(/(?:v=|be\/)([A-Za-z0-9_-]+)/)?.[1];
                        if (id) {
                            iframe.src = `https://www.youtube.com/embed/${id}?autoplay=1&mute=0`;
                            iframe.classList.remove('hidden');
                        }
                    }
                    // Vimeo
                    else if (/vimeo\.com/i.test(url)) {
                        const id = url.match(/vimeo\.com\/(\d+)/)?.[1];
                        if (id) {
                            iframe.src = `https://player.vimeo.com/video/${id}?autoplay=1`;
                            iframe.classList.remove('hidden');
                        }
                    }
                    // MP4 / WebM / MOV
                    else if (/\.(mp4|webm|mov|m4v|m3u8)(\?|$)/i.test(url)) {
                        player.src = url;
                        player.classList.remove('hidden');
                        player.play().catch(() => {});
                        player.addEventListener('loadedmetadata', function checkOrientation() {
                            const isPortrait = player.videoHeight > player.videoWidth;
                            if (isPortrait) wrapper.classList.add('portrait');
                            player.removeEventListener('loadedmetadata', checkOrientation);
                        });
                    }
                    // fallback
                    else {
                        iframe.srcdoc = `
            <div style="color:white;font-family:sans-serif;text-align:center;padding:40px;">
                <p style="font-size:18px;">🎥 Видео не поддерживается напрямую</p>
                <p style="margin-top:10px;font-size:14px;">Попробуйте открыть по ссылке:</p>
                <a href="${url}" target="_blank" style="color:#e865a0;text-decoration:underline;">${url}</a>
            </div>`;
                        iframe.classList.remove('hidden');
                    }

                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }

                document.querySelectorAll('.story-btn').forEach(btn => {
                    btn.addEventListener('click', () => openVideo(btn.dataset.video));
                });

                function closeModal() {
                    modal.classList.add('hidden');
                    iframe.src = '';
                    player.pause();
                    player.src = '';
                    document.body.style.overflow = '';
                }

                closeBtn.addEventListener('click', closeModal);
                modal.addEventListener('click', e => {
                    if (e.target === modal) closeModal();
                });
                document.addEventListener('keydown', e => {
                    if (e.key === 'Escape') closeModal();
                });
            });
        </script>
    <?php endif; ?>

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
