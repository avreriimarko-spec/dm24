<?php
/* Template Name: Model — Media (tabs + sticky left + badges) */
/* Template Post Type: models */

if (!defined('ABSPATH')) exit;
get_header();

$id   = get_the_ID();
$name = function_exists('get_field') ? (get_field('name', $id) ?: get_the_title($id)) : get_the_title($id);
$ACCENT = '#e1315a';
$date_published = get_the_date('d.m.Y', $id);

/* ================== ACF ================== */
$gallery    = (array)(function_exists('get_field') ? (get_field('photo',  $id) ?: []) : []);
$selfies    = (array)(function_exists('get_field') ? (get_field('selfie', $id) ?: []) : []);
$videos_raw =            function_exists('get_field') ?  get_field('video', $id)      : [];

/* videos_raw → список URL */
$videos = [];
if (is_string($videos_raw)) {
    $s = trim($videos_raw);
    if ($s !== '') $videos = preg_split('~[\s,;]+~u', $s, -1, PREG_SPLIT_NO_EMPTY) ?: [$s];
} elseif (is_array($videos_raw)) {
    if (isset($videos_raw['url']) && is_string($videos_raw['url'])) {
        $videos[] = trim((string)$videos_raw['url']);
    } elseif (array_keys($videos_raw) === range(0, count($videos_raw) - 1)) {
        foreach ($videos_raw as $it) {
            if (is_array($it) && !empty($it['url'])) $videos[] = trim((string)$it['url']);
            elseif (is_string($it))                  $videos[] = trim($it);
        }
    }
}
$videos = array_values(array_unique(array_filter($videos)));

/* ================== термы/поля ================== */
$districts = wp_get_post_terms($id, 'rayonu_tax',       ['fields' => 'names']) ?: [];
$hair      = wp_get_post_terms($id, 'cvet-volos_tax',   ['fields' => 'names']) ?: [];
$nation    = wp_get_post_terms($id, 'nationalnost_tax', ['fields' => 'names']) ?: [];
$metro     = wp_get_post_terms($id, 'metro_tax',        ['fields' => 'names']) ?: [];


$age    = trim((string)(function_exists('get_field') ? get_field('age',    $id) : ''));
$height = trim((string)(function_exists('get_field') ? get_field('height', $id) : ''));
$weight = trim((string)(function_exists('get_field') ? get_field('weight', $id) : ''));
$bust   = trim((string)(function_exists('get_field') ? get_field('bust',   $id) : ''));

$price_in_1h  = (float)(function_exists('get_field') ? get_field('price',         $id) : 0);
$price_out_1h = (float)(function_exists('get_field') ? get_field('price_outcall', $id) : 0);

$about     = function_exists('get_field') ? get_field('description', $id) : '';

/* Статусы */
$vip      = function_exists('get_field') ? (bool)get_field('vip', $id)       : false;
$verified = function_exists('get_field') ? (bool)get_field('verified', $id)  : false;
$online   = function_exists('get_field') ? get_field('online', $id)          : '';

/* Контакты */
// --- нормализация
$__mr_norm_tg = function ($v) {
    $v = trim((string)$v);
    if ($v === '') return '';
    $v = preg_replace('~^https?://t\.me/~i', '', $v);
    $v = ltrim($v, '@');
    return preg_replace('~[^a-z0-9_]+~i', '', $v);
};
$__mr_norm_wa = function ($v) {
    return preg_replace('~\D+~', '', (string)$v);
};

// --- собрать варианты
$__tg_variants = [];
$__wa_variants = [];

// старые поля (основные)
$main_tg = get_theme_mod('contact_telegram');
$main_wa = get_theme_mod('contact_whatsapp');
if (!empty($main_tg)) $__tg_variants[] = $__mr_norm_tg($main_tg);
if (!empty($main_wa)) $__wa_variants[] = $__mr_norm_wa($main_wa);

// новые 4 варианта
for ($i = 1; $i <= 4; $i++) {
    $tg = get_theme_mod("contact_telegram_$i");
    $wa = get_theme_mod("contact_whatsapp_$i");
    if (!empty($tg)) $__tg_variants[] = $__mr_norm_tg($tg);
    if (!empty($wa)) $__wa_variants[] = $__mr_norm_wa($wa);
}

// --- выбрать случайные
$__chosen_tg = !empty($__tg_variants) ? $__tg_variants[array_rand($__tg_variants)] : '';
$__chosen_wa = !empty($__wa_variants) ? $__wa_variants[array_rand($__wa_variants)] : '';

// --- финальные значения (не меняем имена переменных!)
$phone = trim((string) get_theme_mod('contact_number'));

// если задано хоть одно значение — заменяем
// 1. Определяем: это "Дешевые"? (Страница или категория модели)
$is_cheap_context = (is_page('deshevyye-prostitutki') || has_term('deshevyye-prostitutki', 'price_tax', get_the_ID()));

// 2. Выбираем "сырые" данные
if ($is_cheap_context) {
    // Логика для дешевых: берем строго 5-й контакт
    $raw_tg = get_theme_mod('contact_telegram_5');
    $raw_wa = get_theme_mod('contact_whatsapp_5');
} else {
    // Логика для остальных: собираем пул и берем рандом
    $tg_pool = [];
    $wa_pool = [];

    // Основной
    if ($t = get_theme_mod('contact_telegram')) $tg_pool[] = $t;
    if ($w = get_theme_mod('contact_whatsapp')) $wa_pool[] = $w;

    // Дополнительные 1-4
    for ($i = 1; $i <= 4; $i++) {
        if ($t = get_theme_mod("contact_telegram_$i")) $tg_pool[] = $t;
        if ($w = get_theme_mod("contact_whatsapp_$i")) $wa_pool[] = $w;
    }

    // Выбираем случайный, если есть из чего
    $raw_tg = !empty($tg_pool) ? $tg_pool[array_rand($tg_pool)] : '';
    $raw_wa = !empty($wa_pool) ? $wa_pool[array_rand($wa_pool)] : '';
}

// 3. Нормализация (чистим мусор, чтобы получить чистый ник/номер)
$tg = trim((string)$raw_tg);
$tg = preg_replace('~^https?://t\.me/~i', '', $tg); // Убираем https://t.me/
$tg = ltrim($tg, '@'); // Убираем @
$tg = preg_replace('~[^a-z0-9_]+~i', '', $tg); // Убираем лишние символы

$wa = preg_replace('~\D+~', '', (string)$raw_wa); // Оставляем только цифры

// 4. Формирование ссылок
$tel_href = (isset($phone) && $phone) ? 'tel:' . preg_replace('~\D+~', '', $phone) : '';
$wa_href  = $wa ? 'https://wa.me/' . $wa : '';
$tg_href  = $tg ? 'https://t.me/' . $tg : '';
// очистка временных
unset($__mr_norm_tg, $__mr_norm_wa, $__tg_variants, $__wa_variants, $__chosen_tg, $__chosen_wa, $tg, $wa, $main_tg, $main_wa, $i);

/* ALT */
$hair_str   = $hair ? implode(', ', $hair) : '';
$nation_str = $nation ? implode(', ', $nation) : '';
$alt_parts = [];
if ($bust   !== '') $alt_parts[] = 'грудь ' . $bust;
if ($height !== '') $alt_parts[] = 'рост ' . $height . ' см';
if ($weight !== '') $alt_parts[] = 'вес ' . $weight . ' кг';
if ($hair_str   !== '') $alt_parts[] = 'цвет волос ' . $hair_str;
if ($nation_str !== '') $alt_parts[] = 'национальность ' . $nation_str;
$alt = 'Проститутка ' . $name . ($alt_parts ? ' - ' . implode(', ', $alt_parts) : '');

/* ================== helpers ================== */
function em_normalize_video_canonical($raw)
{
    if (is_array($raw)) $raw = (string)($raw['url'] ?? '');
    $raw = trim((string)$raw);
    if ($raw === '') return null;

    if (stripos($raw, '<iframe') !== false && preg_match('~src=["\']([^"\']+)~i', $raw, $m)) $raw = $m[1];

    $u = @parse_url($raw);
    $scheme = strtolower($u['scheme'] ?? '');
    $host = strtolower($u['host'] ?? '');
    $path = $u['path'] ?? '';
    $query  = [];
    if (!empty($u['query'])) parse_str($u['query'], $query);

    if ($scheme && preg_match('~(^|\.)youtube\.com$|(^|\.)youtu\.be$~', $host)) {
        $id = '';
        if (preg_match('~^/([A-Za-z0-9_\-]{6,})$~', $path, $m)) $id = $m[1];
        if (!$id && !empty($query['v'])) $id = preg_replace('~[^A-Za-z0-9_\-]~', '', $query['v']);
        if (!$id && preg_match('~^/(?:embed|shorts)/([A-Za-z0-9_\-]{6,})~', $path, $m)) $id = $m[1];
        if ($id) return ['key' => 'yt:' . $id, 'type' => 'embed', 'src' => 'https://www.youtube.com/embed/' . $id, 'poster' => 'https://img.youtube.com/vi/' . $id . '/hqdefault.jpg'];
    }

    if ($scheme && preg_match('~(^|\.)vimeo\.com$~', $host)) {
        if (preg_match('~^/(?:video/)?(\d+)~', $path, $m)) {
            $vid = $m[1];
            return ['key' => 'vimeo:' . $vid, 'type' => 'embed', 'src' => 'https://player.vimeo.com/video/' . $vid, 'poster' => ''];
        }
    }

    if ($scheme && preg_match('~\.mp4$~i', $path)) {
        $hostKey = preg_replace('~^www\.~i', '', $host);
        $key     = strtolower($hostKey . $path);
        return ['key' => 'mp4:' . $key, 'type' => 'mp4', 'src' => $raw, 'poster' => ''];
    }

    if (in_array($scheme, ['http', 'https'], true)) {
        return ['key' => 'embed:' . $host . ($path ?: '/'), 'type' => 'embed', 'src' => $raw, 'poster' => ''];
    }
    return null;
}

function em_img_single_sizes($im)
{
    $id = 0;
    $url = '';
    if (is_array($im)) {
        $id = isset($im['ID']) ? (int)$im['ID'] : 0;
        $url = $im['url'] ?? '';
    } elseif (is_numeric($im)) {
        $id = (int)$im;
    } elseif (is_string($im)) {
        $url = trim($im);
    }

    if ($id) {
        $ml   = wp_get_attachment_image_src($id, 'medium_large');
        $md   = wp_get_attachment_image_src($id, 'medium');
        $th   = wp_get_attachment_image_src($id, 'thumbnail');
        $full = wp_get_attachment_image_src($id, 'full');
        return [
            'ml'    => $ml[0]   ?? ($full[0] ?? ''),
            'ml_w'  => $ml[1]   ?? null,
            'ml_h'  => $ml[2]   ?? null,
            'thumb' => $md[0]   ?? ($th[0] ?? ($ml[0] ?? $full[0] ?? '')),
            'full'  => $full[0] ?? ($ml[0] ?? ''),
        ];
    }
    if ($url !== '') return ['ml' => $url, 'ml_w' => null, 'ml_h' => null, 'thumb' => $url, 'full' => $url];
    return null;
}

/* ================== Фото ================== */
$images = [];
foreach ((array)$gallery as $im) {
    $r = em_img_single_sizes($im);
    if ($r && $r['ml']) {
        $images[] = ['type' => 'image', 'src' => $r['full'], 'ml' => $r['ml'], 'ml_w' => $r['ml_w'], 'ml_h' => $r['ml_h'], 'thumb' => $r['thumb'], 'poster' => ''];
    }
}
$images_count = count($images);

/* фолбэк-постер для видео */
$poster_fallback = $images_count ? ($images[0]['thumb'] ?: $images[0]['src']) : '';

/* ================== Видео (дедуп по key) ================== */
$videos_media_map = [];
foreach ($videos as $vraw) {
    $norm = em_normalize_video_canonical($vraw);
    if (!$norm) continue;
    if ($norm['poster'] === '' && $poster_fallback) $norm['poster'] = $poster_fallback;
    $videos_media_map[$norm['key']] = ['type' => ($norm['type'] === 'mp4' ? 'mp4' : 'video'), 'src' => $norm['src'], 'poster' => $norm['poster']];
}
$videos_media = array_values($videos_media_map);
$videos_count = count($videos_media);

/* ================== Селфи ================== */
$selfies_items = [];
foreach ($selfies as $im) {
    if (is_array($im)) {
        $full  = $im['url'] ?? '';
        $thumb = $im['sizes']['medium'] ?? ($im['url'] ?? '');
    } else {
        $arrF  = wp_get_attachment_image_src((int)$im, 'full');
        $arrM  = wp_get_attachment_image_src((int)$im, 'medium');
        $full  = $arrF[0] ?? '';
        $thumb = $arrM[0] ?? $full;
    }
    if ($full) $selfies_items[] = ['type' => 'image', 'src' => $full, 'thumb' => $thumb, 'poster' => ''];
}
$selfies_count = count($selfies_items);

/* ================== Лайтбокс-поток ================== */
$lb_items = array_merge(
    array_map(fn($m) => [
        'type'   => 'image',
        'src'    => $m['src'],
        'poster' => '',
        'alt'    => $alt,
    ], $images),
    array_map(fn($v) => [
        'type'   => $v['type'],
        'src'    => $v['src'],
        'poster' => $v['poster'],
        'alt'    => $alt, // тот же alt для постеров видео
    ], $videos_media),
    array_map(fn($m) => [
        'type'   => 'image',
        'src'    => $m['src'],
        'poster' => '',
        'alt'    => $alt, // при желании можно 'Селфи ' . $name
    ], $selfies_items)
);

?>
<main class="mx-auto w-full lg:w-[1200px] px-4 bg-white text-black">

    <article class="grid grid-cols-1 lg:grid-cols-12 gap-2 lg:gap-8 py-6">
        <!-- ========== ЛЕВО (5/12): sticky, компактнее ========== -->
        <section class="lg:col-span-5 lg:sticky lg:top-6 lg:self-start" aria-label="Фото и контакты модели">
            <h1 id="model-title" class="text-2xl sm:text-3xl font-bold leading-tight mb-8">
                <?php
                $auto_h1_component = get_theme_file_path('components/h1-auto.php');
                if (file_exists($auto_h1_component)) {
                    require $auto_h1_component;
                }
                $h1 = get_query_var('auto_h1');

                if (empty($h1) && !empty($GLOBALS['auto_h1'])) {
                    $h1 = $GLOBALS['auto_h1'];
                }

                // запасной вариант, если по какой-то причине компонент не отработал
                if (empty($h1)) {
                    $h1 = 'Проститутка ' . $name . ', Алматы';
                }

                echo esc_html($h1);
                ?>
            </h1>    

            <!-- Слайдер фото -->
            <div class="relative rounded-lg overflow-hidden border border-neutral-200">
                <?php
                // Приводим к булеву типу (true/false)
                $on = (bool)$online;

                // Выводим только если переменная существует и $on равно true
                if ($online !== '' && $on) { ?>
                    <span class="absolute top-3 left-3 z-40 px-2 py-1 rounded-full text-xs font-semibold bg-emerald-500/90 text-white backdrop-blur-sm shadow-md select-none">
                        Онлайн
                    </span>
                <?php } ?>

                <div class="w-full aspect-[3/4] bg-neutral-100" style="aspect-ratio:3/4;">
                    <?php if ($images_count) { ?>
                        <div class="swiper js-left-slider h-full">
                            <div class="swiper-wrapper">
                                <?php foreach ($images as $idx => $m) { ?>
                                    <div class="swiper-slide relative">
                                        <img
                                            src="<?php echo esc_url($m['ml'] ?? $m['src']); ?>"
                                            alt="<?php echo esc_attr($alt); ?>"
                                            class="w-full h-full object-cover js-open-lightbox cursor-pointer"
                                            data-idx="<?php echo esc_attr($idx); ?>"
                                            loading="<?php echo $idx === 0 ? 'eager' : 'lazy'; ?>"
                                            fetchpriority="<?php echo $idx === 0 ? 'high' : 'auto'; ?>"
                                            decoding="async">
                                    </div>
                                <?php } ?>
                            </div>

                            <?php if ($images_count > 1) { ?>
                                <!-- Навигация для ПК -->
                                <div class="hidden md:block">
                                    <div class="swiper-button-prev !text-[<?php echo esc_attr($ACCENT); ?>] !w-8 !h-8 !mt-0 !top-1/2 !left-2 after:!text-sm after:!font-bold bg-white/80 backdrop-blur-sm !rounded-full hover:bg-white/90 transition-colors"></div>
                                    <div class="swiper-button-next !text-[<?php echo esc_attr($ACCENT); ?>] !w-8 !h-8 !mt-0 !top-1/2 !right-2 after:!text-sm after:!font-bold bg-white/80 backdrop-blur-sm !rounded-full hover:bg-white/90 transition-colors"></div>
                                    <div class="swiper-pagination !bottom-4"></div>
                                </div>
                            <?php } ?>
                        </div>

                        <!-- 📱 Мобильный UI поверх фото -->
                        <div class="md:hidden absolute bottom-3 left-0 right-0 z-20 flex items-center justify-between px-4">
                            <!-- Счётчик -->
                            <div class="flex items-center gap-1 bg-black/60 backdrop-blur-sm text-white text-xs font-semibold px-3 py-1 rounded-full shadow-md select-none">
                                <span class="js-photo-index">1</span>
                                <span>/</span>
                                <span><?php echo (int)$images_count; ?></span>
                            </div>

                            <!-- Иконка "на весь экран" -->
                            <button
                                type="button"
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-black/60 backdrop-blur-sm text-white hover:bg-black/70 active:scale-95 transition-all shadow-md js-open-lightbox"
                                aria-label="Открыть на весь экран">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="w-5 h-5">
                                    <path d="M4 4h6M4 4v6M20 4h-6M20 4v6M4 20h6M4 20v-6M20 20h-6M20 20v-6" />
                                </svg>
                            </button>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                const interval = setInterval(() => {
                                    const swiperEl = document.querySelector('.js-left-slider');
                                    if (!swiperEl || !swiperEl.swiper) return;

                                    const swiper = swiperEl.swiper;
                                    const indexEl = document.querySelector('.js-photo-index');

                                    if (swiper && indexEl) {
                                        indexEl.textContent = swiper.realIndex + 1;
                                        swiper.on('slideChange', () => {
                                            indexEl.textContent = swiper.realIndex + 1;
                                        });
                                        clearInterval(interval);
                                    }
                                }, 300);
                            });
                        </script>

                    <?php } else { ?>
                        <div class="w-full h-full flex items-center justify-center text-neutral-500">Нет фото</div>
                    <?php } ?>
                </div>
            </div>

            <!-- Мини-превью -->
            <?php if (! wp_is_mobile()): ?>
                <?php if ($images_count > 1) { ?>
                    <div class="mt-3 overflow-x-auto">
                        <div class="flex gap-2 pb-2">
                            <?php foreach ($images as $idx => $m) { ?>
                                <button type="button"
                                    class="shrink-0 w-16 h-16 rounded-md overflow-hidden border-2 border-transparent js-thumb-btn transition-all duration-200 hover:border-[<?php echo esc_attr($ACCENT); ?>]"
                                    data-slide="<?php echo esc_attr($idx); ?>">
                                    <img src="<?php echo esc_url($m['thumb'] ?? $m['src']); ?>" 
                                    alt="<?php echo esc_attr($alt); ?>" 
                                    class="w-full h-full object-cover" 
                                    loading="lazy">
                                </button>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>

                <!-- ЦЕНЫ (В СТОЛБИК) + район -->
                <section class="mt-4 space-y-2">
                    <?php if ($price_in_1h) { ?>
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-1 rounded bg-neutral-900 text-white text-xs font-medium">Апартаменты</span>
                            <span class="font-semibold text-lg"><?php echo esc_html(number_format($price_in_1h, 0, ',', ' ')); ?> ₸</span>
                        </div>
                    <?php } ?>
                    <?php if ($price_out_1h) { ?>
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-1 rounded bg-[#ff2d72] text-white text-xs font-medium">Выезд</span>
                            <span class="font-semibold text-lg"><?php echo esc_html(number_format($price_out_1h, 0, ',', ' ')); ?> ₸</span>
                        </div>
                    <?php } ?>

                    <?php if (!empty($districts)) { ?>
                        <div class="flex items-center gap-2 text-sm text-neutral-700 pt-1">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2a7 7 0 00-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 00-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z" />
                            </svg>
                            <span><?php echo esc_html(implode(', ', $districts)); ?></span>
                        </div>
                    <?php } ?>
                </section>

<div class="mt-5 flex items-center gap-3">
    <?php 
    // === [ЗАЩИТА] Подготовка переменных ===
    // Кодируем ссылки только если они существуют
    // (Предполагаем, что в $tg_href и $wa_href уже лежат готовые ссылки с https://)
    $enc_tg = !empty($tg_href) ? base64_encode($tg_href) : '';
    $enc_wa = !empty($wa_href) ? base64_encode($wa_href) : '';
    ?>

    <?php if (!empty($tg_href)) { ?>
        <a href="javascript:void(0);" 
           data-enc="<?php echo esc_attr($enc_tg); ?>" 
           rel="nofollow noopener"
           class="protected-contact inline-flex items-center gap-2 px-4 py-3 rounded-lg bg-[#229ED9] text-white font-medium hover:bg-[#1e88c7] transition-colors">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M9.9 13.4l-.4 5.6c.6 0 .8-.3 1.1-.6l2.7-2.6 5.6 4.1c1 .6 1.8.3 2.1-.9l3.8-17.7c.3-1.2-.4-1.7-1.4-1.4L1.5 9.6c-1.2.3-1.2 1-.2 1.3l5.6 1.7 12.9-8.1c.6-.4 1.2-.2.7.2" />
            </svg>
            Telegram
        </a>
    <?php } ?>

    <?php if (!empty($wa_href)) { ?>
        <a href="javascript:void(0);" 
           data-enc="<?php echo esc_attr($enc_wa); ?>" 
           rel="nofollow noopener"
           class="protected-contact inline-flex items-center gap-2 px-4 py-3 rounded-lg bg-[#25D366] text-white font-medium hover:bg-[#22c55e] transition-colors">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 3.9A10 10 0 003.7 17.2L3 21l3.9-1A10 10 0 1020 3.9zM8.5 7.8c.2-.5.3-.5.6-.5h.5c.2 0 .4 0 .5.4s.7 2.2.8 2.3c.1.2.1.4 0 .5-.1.1-.2.3-.4.5-.2.2-.3.4-.1.7.2.3.9 1.6 2.1 2.5 1.4 1 1.7.9 2 .8s.9-.3 1-.5c.1-.2.5-.6.7-.5.2 0 1.9.9 2.2 1 .3.1.5.2.5.4 0 .2.1 1.1-.5 1.7-.5.6-1.3.8-2.2.8-1 .1-1.9-.3-3-.9a11.5 11.5 0 01-3.4-3.1 7.8 7.8 0 01-1.4-2.9c-.2-1 .1-1.9.2-2.2z" />
            </svg>
            WhatsApp
        </a>
    <?php } ?>
</div>

            <?php endif; ?>
        </section>


        <!-- ========== ПРАВО (7/12) ========== -->
        <section class="lg:col-span-7" aria-labelledby="model-title">
            <!-- Заголовок + кнопка избранного -->
            <header class="mt-1 flex items-start justify-between gap-4">

                <div class="min-w-0">
                    <!-- район -->
                    <?php if ($districts) { ?>
                        <div class="text-black-600 text-lg">
                            <svg class="inline-block" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 22" width="24" height="24" fill="#000000" style="opacity:1;"><path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10"/><path d="M8 8a2 2 0 1 1 0-4a2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6a3 3 0 0 0 0 6"/></svg>
                            <span>Район:</span> <?php echo esc_html(implode(', ', $districts)); ?>
                        </div>
                    <?php } ?>
                </div>


                <!-- Сердечко -->
                <button
                    type="button"
                    id="fav-toggle"
                    aria-label="В избранное"
                    title="В избранное"
                    class="shrink-0 inline-flex items-center justify-center 
               w-10 h-10 p-2 rounded-full
               lg:w-auto lg:h-auto lg:px-3 lg:py-2 lg:rounded-lg
               border border-neutral-200 text-neutral-700 hover:bg-rose-50 transition-colors"
                    aria-pressed="false"
                    data-id="<?php echo (int)$id; ?>"
                    data-title="<?php echo esc_attr($name); ?>">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 1 0-7.8 7.8l1 1L12 22l7.8-8.6 1-1a5.5 5.5 0 0 0 0-7.8z" />
                    </svg>
                    <span class="hidden lg:inline ml-2">В избранное</span>
                </button>
            </header>

            <?php
                // дата проверки из ACF или post_meta
                $verify_raw = function_exists('get_field')
                    ? get_field('data_verify', get_the_ID())
                    : get_post_meta(get_the_ID(), 'data_verify', true);

                if (!empty($verify_raw)) {
                    $verify_ts = is_numeric($verify_raw) ? (int)$verify_raw : strtotime((string)$verify_raw);
                    $verify_fmt = $verify_ts
                        ? date_i18n(get_option('date_format') ?: 'd.m.Y', $verify_ts)
                        : wp_strip_all_tags((string)$verify_raw);

                    echo '
                    <div class="w-full mt-5 text-sm text-black flex items-center justify-between pr-4" style="padding-right: 10px;">
                        <div class="flex items-center gap-1.5">
                            <svg class="text-[#22c55e]" viewBox="0 0 20 24" width="26" height="22" fill="none" aria-hidden="true">
                                <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="text-xl">Проверено: ' . esc_html($verify_fmt) . '</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15 20" width="26" height="26" fill="#352222ff" style="opacity:1;">
                                <path  d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v1h14V3a1 1 0 0 0-1-1zm13 3H1v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z"/>
                            </svg> 
                            <span class="text-xl">' . esc_html($date_published) . '</span>
                        </div>
                    </div>';
                }

            ?>

            <?php if (!empty($metro)): ?>
                <div class="flex items-center gap-2 mt-6">
                    <?php
                        $is_first = true;
                        $metro_limited = array_slice($metro, 0, 4);

                        foreach ($metro_limited as $m) {
                            $term = get_term_by('name', $m, 'metro_tax');

                            if ($term) {
                                $term_link = home_url($term->slug);
                                $label = $is_first ? 'Метро:' : 'Доп. метро:';
                                $is_first = false;

                                echo '
                                    <div class="flex items-center justify-center flex-col p-2 w-full" style="background-color: #f2f2f2ff;">
                                        <span style="color: #4c4c4c; font-size: 14px;">' . esc_html($label) . '</span>
                                        <a href="' . esc_url($term_link) . '" class="text-[#ff2d72] text-base" style="width: max-content;">'
                                        . esc_html($m) .
                                        '</a>
                                    </div>
                                ';
                            }
                        }
                    ?>
                </div>
            <?php endif; ?>

            <!-- Описание -->
            <section class="mt-8 mb-8" aria-label="Описание модели">
                <div class="w-full" style="background-color: #f2f2f2ff; padding: 30px 20px 20px">
                    <?php if (!empty($about)) { 
                        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
                        $is_bot = (bool) preg_match('/bot|crawl|spider|slurp|mediapartners-google|bingpreview|duckduckbot|baiduspider|yandex|ahrefs|semrush|screaming\s?frog|facebookexternalhit|telegrambot/i', $ua);
                        $uid = uniqid('desc_');
                    ?>
                        <div id="<?= $uid ?>_box" 
                             class="relative overflow-hidden transition-[max-height] duration-300 ease-in-out prose prose-neutral max-w-none text-xl text-center" 
                             style="<?= $is_bot ? 'max-height:none' : 'max-height:10rem' ?>">
                            
                            <div style="color: #4c4c4c; display: flex; justify-content: space-between; align-items: end;">
                                <div style="max-width: 507px; margin-bottom: 1rem">
                                    <?php echo wpautop(wp_kses_post($about)); ?>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="min-width: 60px; height: 46px; fill: #4c4c4c">
                                    <path d="M9.983 3v7.391c0 5.704-3.731 9.57-8.983 10.609l-.995-2.151c2.432-.917 3.995-3.638 3.995-5.849h-4v-10h9.983zm14.017 0v7.391c0 5.704-3.748 9.571-9 10.609l-.996-2.151c2.433-.917 3.996-3.638 3.996-5.849h-3.983v-10h9.983z"/>
                                </svg> 
                            </div>

                            <div id="<?= $uid ?>_fade" class="pointer-events-none absolute left-0 right-0 bottom-0 h-16" style="<?= $is_bot ? 'display:none' : 'background:linear-gradient(to bottom, rgba(242,242,242,0), #f2f2f2 80%)' ?>"></div>
                        </div>

                        <button id="<?= $uid ?>_btn"
                                class="mt-4 mx-auto flex items-center gap-2 text-[#ff2d72] font-semibold hover:opacity-90 transition"
                                aria-expanded="<?= $is_bot ? 'true' : 'false' ?>"
                                <?= $is_bot ? 'hidden' : '' ?>>
                            <svg class="w-4 h-4 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M6 9l6 6 6-6" stroke-width="2" />
                            </svg>
                            <span data-label><?= $is_bot ? 'Свернуть' : 'Показать ещё' ?></span>
                        </button>

                        <script>
                            (function() {
                                var box = document.getElementById('<?= $uid ?>_box');
                                var fade = document.getElementById('<?= $uid ?>_fade');
                                var btn = document.getElementById('<?= $uid ?>_btn');
                                if (!box || !btn) return;

                                var collapsedMax = 10 * 16; // 10rem

                                if (box.scrollHeight <= collapsedMax + 10) {
                                    box.style.maxHeight = 'none';
                                    if (fade) fade.style.display = 'none';
                                    btn.style.display = 'none';
                                    return;
                                }

                                btn.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    var opened = btn.getAttribute('aria-expanded') === 'true';
                                    var arrow = btn.querySelector('svg');

                                    if (opened) {
                                        box.style.maxHeight = collapsedMax + 'px';
                                        if (fade) fade.style.display = '';
                                        btn.setAttribute('aria-expanded', 'false');
                                        btn.querySelector('[data-label]').textContent = 'Показать ещё';
                                        arrow.style.transform = 'rotate(0deg)';
                                    } else {
                                        box.style.maxHeight = box.scrollHeight + 'px';
                                        setTimeout(function() {
                                            box.style.maxHeight = 'none';
                                        }, 350);
                                        if (fade) fade.style.display = 'none';
                                        btn.setAttribute('aria-expanded', 'true');
                                        btn.querySelector('[data-label]').textContent = 'Свернуть';
                                        arrow.style.transform = 'rotate(180deg)';
                                    }
                                });
                            })();
                        </script>
                    <?php } else { ?>
                        <p class="text-neutral-600">Описание пока не добавлено.</p>
                    <?php } ?>
                </div>
            </section>
            

            <!-- Табы -->
            <nav class="border-b mb-6" style="border-color: <?php echo esc_attr($ACCENT); ?>;" aria-label="Разделы анкеты">
                <div class="flex flex-nowrap items-center gap-4 tabs-scroll overflow-x-scroll p-2 whitespace-nowrap w-full"
                    role="tablist" aria-label="Секции медиа и описания">
                    <?php if ($images_count) { ?>
                        <button type="button" role="tab" aria-selected="true" data-tab="photos"
                            class="js-tab inline-flex items-center gap-2 px-1 py-3 -mb-px border-b-2 font-medium text-sm transition-colors"
                            style="border-color: <?php echo esc_attr($ACCENT); ?>; color: <?php echo esc_attr($ACCENT); ?>;">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="6" width="18" height="12" rx="2"></rect>
                                <path d="M8 6l1.5-2h5L16 6"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            Фото
                        </button>
                    <?php } ?>
                    <?php if ($videos_count) { ?>
                        <button type="button" role="tab" aria-selected="false" data-tab="videos"
                            class="js-tab inline-flex items-center gap-2 px-1 py-3 -mb-px border-b-2 border-transparent text-gray-600 hover:text-[<?php echo esc_attr($ACCENT); ?>] hover:border-[<?php echo esc_attr($ACCENT); ?>] font-medium text-sm transition-colors">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="5" width="13" height="14" rx="2"></rect>
                                <path d="M16 9l5-3v12l-5-3z"></path>
                            </svg>
                            Видео
                        </button>
                    <?php } ?>
                    <?php if ($selfies_count) { ?>
                        <button type="button" role="tab" aria-selected="false" data-tab="selfies"
                            class="js-tab inline-flex items-center gap-2 px-1 py-3 -mb-px border-b-2 border-transparent text-gray-600 hover:text-[<?php echo esc_attr($ACCENT); ?>] hover:border-[<?php echo esc_attr($ACCENT); ?>] font-medium text-sm transition-colors">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="3"></circle>
                                <path d="M4 20a8 8 0 0116 0"></path>
                            </svg>
                            Селфи
                        </button>
                    <?php } ?>



                    <?php /* TAB: Параметры */ ?>
                    <button type="button" role="tab" aria-selected="false" data-tab="params"
                        class="js-tab inline-flex items-center gap-2 px-1 py-3 -mb-px border-b-2 border-transparent text-gray-600 hover:text-[<?php echo esc_attr($ACCENT); ?>] hover:border-[<?php echo esc_attr($ACCENT); ?>] font-medium text-sm transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M3 12h18M3 18h18"></path>
                        </svg>
                        Параметры
                    </button>

                    <?php /* TAB: Районы */ ?>
                    <button type="button" role="tab" aria-selected="false" data-tab="districts"
                        class="js-tab inline-flex items-center gap-2 px-1 py-3 -mb-px border-b-2 border-transparent text-gray-600 hover:text-[<?php echo esc_attr($ACCENT); ?>] hover:border-[<?php echo esc_attr($ACCENT); ?>] font-medium text-sm transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 12-9 12S3 17 3 10a9 9 0 1118 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        Районы
                    </button>

                    <!-- TAB: Услуги -->
                    <button type="button" role="tab" aria-selected="false" data-tab="services"
                        class="js-tab inline-flex items-center gap-2 px-1 py-3 -mb-px border-b-2 border-transparent text-gray-600 
           hover:text-[<?php echo esc_attr($ACCENT); ?>] hover:border-[<?php echo esc_attr($ACCENT); ?>] 
           font-medium text-sm transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 3v18m9-9H3" />
                        </svg>
                        Услуги
                    </button>

                </div>
            </nav>


            <!-- Фото -->
            <section class="js-section js-section-photos <?php echo $images_count ? '' : 'hidden'; ?>" aria-label="Галерея фотографий">
                <?php if ($images_count) { ?>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <?php foreach ($images as $idx => $m) { ?>
                            <button type="button"
                                class="group relative aspect-[3/4] rounded-lg overflow-hidden bg-gray-100 js-open-lightbox transition-transform duration-200 hover:scale-105"
                                data-idx="<?php echo esc_attr($idx); ?>">
                                <img src="<?php echo esc_url($m['thumb'] ?? $m['src']); ?>" alt="<?php echo esc_attr($alt); ?>" class="w-full h-full object-cover" loading="lazy">
                            </button>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="flex items-center justify-center h-64 text-gray-500">Нет фото</div>
                <?php } ?>
            </section>

            <!-- Видео -->
            <section class="js-section js-section-videos hidden" aria-label="Галерея видео">
                <?php if ($videos_count) { ?>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <?php foreach ($videos_media as $i => $v) { ?>
                            <button type="button"
                                class="group relative aspect-[3/4] rounded-lg overflow-hidden bg-gray-900 js-open-lightbox transition-transform duration-200 hover:scale-105"
                                data-idx="<?php echo esc_attr($images_count + $i); ?>">
                                <?php if (!empty($v['poster'])) { ?>
                                    <img src="<?php echo esc_url($v['poster']); ?>" alt="<?php echo esc_attr($alt); ?>" class="w-full h-full object-cover">
                                <?php } else { ?>
                                    <div class="w-full h-full bg-gray-900"></div>
                                <?php } ?>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="rounded-full w-12 h-12 bg-black/50 backdrop-blur-sm inline-flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white ml-1" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M8 5v14l11-7-11-7z" />
                                        </svg>
                                    </div>
                                </div>
                            </button>
                        <?php } ?>

                    </div>
                <?php } else { ?>
                    <div class="flex items-center justify-center h-64 text-gray-500">Видео нет</div>
                <?php } ?>
                <div class="mt-6">
                    <a href="/s-video"
                        class="inline-flex items-center gap-2 font-medium hover:underline"
                        style="color: <?php echo esc_attr($ACCENT); ?>;">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 9l5-3v12l-5-3z"></path>
                            <rect x="3" y="5" width="13" height="14" rx="2"></rect>
                        </svg>
                        Другие эскортницы с&nbsp;видео
                    </a>
                </div>
            </section>

            <!-- Селфи -->
            <section class="js-section js-section-selfies hidden" aria-label="Галерея селфи">
                <?php if ($selfies_count) { ?>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <?php foreach ($selfies_items as $i => $im) { ?>
                            <button type="button"
                                class="group relative aspect-[3/4] rounded-lg overflow-hidden bg-gray-100 js-open-lightbox transition-transform duration-200 hover:scale-105"
                                data-idx="<?php echo esc_attr($images_count + $videos_count + $i); ?>">
                               <img src="<?php echo esc_url($im['thumb'] ?? $im['src']); ?>" 
                                class="w-full h-full object-cover" 
                                alt="Селфи <?php echo esc_attr($name); ?>" 
                                loading="lazy">
                            </button>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="flex items-center justify-center h-64 text-gray-500">Селфи нет</div>
                <?php } ?>
            </section>

            <!-- Параметры -->
            <section class="js-section js-section-params hidden" aria-label="Параметры модели">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php
                    // Функция для вывода параметра с правильной семантикой (dt/dd)
                    $row = function ($label, $val, $taxonomy = null) {
                        if ($val === '' || $val === null) return;

                        if ($taxonomy) {
                            $terms = wp_get_post_terms(get_the_ID(), $taxonomy);
                            if (!empty($terms) && !is_wp_error($terms)) {
                                $term = $terms[0];

                                // Ссылка по slug без префикса и вложений
                                $term_link = untrailingslashit(home_url($term->slug));

                                if (!empty($term_link) && !is_wp_error($term_link)) {
                                    $val = '<a href="' . esc_url($term_link) . '" class="text-blue-500 hover:underline">' . esc_html($val) . '</a>';
                                }
                            }
                        }

                        echo '<div class="flex items-center justify-between rounded-md border border-neutral-200 bg-white px-3 py-2">';
                        echo '<dt class="text-neutral-600">' . esc_html($label) . '</dt>';
                        echo '<dd class="font-medium">' . $val . '</dd>';
                        echo '</div>';
                    };

                    // Вывод параметров
                    $row('Возраст',           $age ? $age . ' лет' : '', 'vozrast_tax');
                    $row('Рост',              $height ? $height . ' см' : '', 'rost_tax');
                    $row('Вес',               $weight ? $weight . ' кг' : '', 'ves_tax');
                    $row('Грудь',             $bust, 'grud_tax');
                    $row('Цвет волос',        $hair ? implode(', ', $hair) : '', 'cvet-volos_tax');
                    $row('Национальность',    $nation ? implode(', ', $nation) : '', 'nationalnost_tax');
                    $row('Цена',              $price_in_1h ? number_format($price_in_1h, 0, ',', ' ') . ' ₸' : '', 'price_tax');
                    ?>
                </dl>
            </section>







            <!-- Районы -->
            <section class="js-section js-section-districts hidden" aria-label="Районы">
                <?php if (!empty($districts)) { ?>
                    <div class="prose prose-neutral max-w-none">
                        <p><strong>Районы:</strong></p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($districts as $d) {
                                // Получаем терм для района
                                $term = get_term_by('name', $d, 'rayonu_tax');
                                if ($term) {
                                    // Строим ссылку с нужным сегментом (например, rajon/{slug})
                                    $term_link = home_url($term->slug);
                                    echo '<a href="' . esc_url($term_link) . '" class="px-2 py-1 rounded-full bg-neutral-100 text-neutral-800 text-sm border border-neutral-200 hover:bg-neutral-200 transition-colors">' . esc_html($d) . '</a>';
                                }
                            } ?>
                        </div>
                    </div>
                <?php } else { ?>
                    <p class="text-neutral-600">Районы не указаны.</p>
                <?php } ?>
            </section>

            <!-- Услуги -->
            <section class="js-section js-section-services hidden" aria-label="Услуги">
                <?php
                $service_terms = wp_get_post_terms(get_the_ID(), 'uslugi_tax');
                if (!empty($service_terms) && !is_wp_error($service_terms)) { ?>
                    <div class="prose prose-neutral max-w-none">
                        <p><strong>Услуги:</strong></p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($service_terms as $term) {
                                $term_link = home_url($term->slug);
                            ?>
                                <a href="<?php echo esc_url($term_link); ?>"
                                    class="px-2 py-1 rounded-full bg-neutral-100 text-neutral-800 text-sm border border-neutral-200 
                    hover:bg-neutral-200 transition-colors">
                                    <?php echo esc_html($term->name); ?>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                <?php } else { ?>
                    <p class="text-neutral-600">Услуги не указаны.</p>
                <?php } ?>
            </section>

            <?php if (wp_is_mobile()): ?>
                <!-- ЦЕНЫ (В СТОЛБИК) + район -->
                <section class="mt-4 space-y-2">
                    <?php if ($price_in_1h) { ?>
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-1 rounded bg-neutral-900 text-white text-xs font-medium">Апартаменты</span>
                            <span class="font-semibold text-lg"><?php echo esc_html(number_format($price_in_1h, 0, ',', ' ')); ?> ₸</span>
                        </div>
                    <?php } ?>
                    <?php if ($price_out_1h) { ?>
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-1 rounded bg-[#ff2d72] text-white text-xs font-medium">Выезд</span>
                            <span class="font-semibold text-lg"><?php echo esc_html(number_format($price_out_1h, 0, ',', ' ')); ?> ₸</span>
                        </div>
                    <?php } ?>

                    <?php if (!empty($districts)) { ?>
                        <div class="flex items-center gap-2 text-sm text-neutral-700 pt-1">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2a7 7 0 00-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 00-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z" />
                            </svg>
                            <span><?php echo esc_html(implode(', ', $districts)); ?></span>
                        </div>
                    <?php } ?>
                </section>

                <!-- Контакты под ценой -->
                <div class="mt-5 mb-5 flex items-center gap-3">
                    <?php if (!empty($tg_href)) { ?>
                        <a href="<?php echo esc_url($tg_href); ?>" rel="nofollow noopener"
                            class="inline-flex items-center gap-2 px-4 py-3 rounded-lg bg-[#229ED9] text-white font-medium hover:bg-[#1e88c7] transition-colors">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9.9 13.4l-.4 5.6c.6 0 .8-.3 1.1-.6l2.7-2.6 5.6 4.1c1 .6 1.8.3 2.1-.9l3.8-17.7c.3-1.2-.4-1.7-1.4-1.4L1.5 9.6c-1.2.3-1.2 1-.2 1.3l5.6 1.7 12.9-8.1c.6-.4 1.2-.2.7.2" />
                            </svg>
                            Telegram
                        </a>
                    <?php } ?>
                    <?php if (!empty($wa_href)) { ?>
                        <a href="<?php echo esc_url($wa_href); ?>" rel="nofollow noopener"
                            class="inline-flex items-center gap-2 px-4 py-3 rounded-lg bg-[#25D366] text-white font-medium hover:bg-[#22c55e] transition-colors">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20 3.9A10 10 0 003.7 17.2L3 21l3.9-1A10 10 0 1020 3.9zM8.5 7.8c.2-.5.3-.5.6-.5h.5c.2 0 .4 0 .5.4s.7 2.2.8 2.3c.1.2.1.4 0 .5-.1.1-.2.3-.4.5-.2.2-.3.4-.1.7.2.3.9 1.6 2.1 2.5 1.4 1 1.7.9 2 .8s.9-.3 1-.5c.1-.2.5-.6.7-.5.2 0 1.9.9 2.2 1 .3.1.5.2.5.4 0 .2.1 1.1-.5 1.7-.5.6-1.3.8-2.2.8-1 .1-1.9-.3-3-.9a11.5 11.5 0 01-3.4-3.1 7.8 7.8 0 01-1.4-2.9c-.2-1 .1-1.9.2-2.2z" />
                            </svg>
                            WhatsApp
                        </a>
                    <?php } ?>
                </div>

            <?php endif; ?>

            <!-- Метро и районы -->
            <section class="js-section js-section-areas hidden" aria-label="Метро и районы модели">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Метро</h3>
                        <?php if (!empty($metro)) { ?>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($metro as $m) { ?>
                                    <span class="px-2 py-1 rounded-full bg-rose-50 text-rose-700 text-sm border border-rose-200"><?php echo esc_html($m); ?></span>
                                <?php } ?>
                            </div>
                        <?php } else { ?>
                            <p class="text-neutral-600">Метро не указано.</p>
                        <?php } ?>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-2">Районы</h3>
                        <?php if (!empty($districts)) { ?>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($districts as $d) { ?>
                                    <span class="px-2 py-1 rounded-full bg-neutral-100 text-neutral-800 text-sm border border-neutral-200"><?php echo esc_html($d); ?></span>
                                <?php } ?>
                            </div>
                        <?php } else { ?>
                            <p class="text-neutral-600">Районы не указаны.</p>
                        <?php } ?>
                    </div>
                </div>
            </section>



            <!-- ===== Отзывы ===== -->
            <section class="mt-10" aria-label="Отзывы о модели">
                <?php
                if (function_exists('mr_render_reviews_block')) {
                    // можно переопределить заголовок через фильтр 'mr/reviews_heading'
                    mr_render_reviews_block($id /*, ['class' => ''] */);
                } else {
                    echo '<p class="text-neutral-600">Блок отзывов недоступен.</p>';
                }
                ?>
            </section>

            <!-- ===== Похожие анкеты ===== -->
            <?php
            $nat_ids = wp_get_post_terms($id, 'nationalnost_tax', ['fields' => 'ids']);
            $args = [
                'post_type'           => 'models',
                'post_status'         => 'publish',
                'posts_per_page'      => 4,
                'post__not_in'        => [$id],
                'orderby'             => 'rand',
                'no_found_rows'       => true,
                'ignore_sticky_posts' => true,
            ];
            if (!is_wp_error($nat_ids) && !empty($nat_ids)) {
                $args['tax_query'] = [[
                    'taxonomy' => 'nationalnost_tax',
                    'field'    => 'term_id',
                    'terms'    => array_map('intval', (array)$nat_ids),
                    'operator' => 'IN',
                ]];
            }
            $related = get_posts($args);
            if (empty($related)) {
                unset($args['tax_query']);
                $related = get_posts($args);
            }

            if (!empty($related)) { ?>
                <section class="mt-12" aria-labelledby="similar-title">
                    <h2 id="similar-title" class="text-2xl font-bold mb-6">Другие анкеты похожие на <?php echo $name ?></h2>
                    <ul class="cards-grid cards-grid--models">
                        <?php foreach ($related as $p) {
                            $district_names = wp_get_post_terms($p->ID, 'rayonu_tax', ['fields' => 'names']);
                            $district = (!is_wp_error($district_names) && $district_names)
                                ? implode(', ', $district_names)
                                : (function_exists('get_field') ? (get_field('district', $p->ID) ?: '') : '');
                            $model = [
                                'ID'                    => $p->ID,
                                'name'                  => function_exists('get_field') ? (get_field('name', $p->ID) ?: get_the_title($p->ID)) : get_the_title($p->ID),
                                'uri'                   => get_permalink($p->ID),
                                'modelGalleryThumbnail' => function_exists('get_field') ? get_field('photo', $p->ID) : '',
                                'district'              => $district,
                                'price'                 => function_exists('get_field') ? get_field('price', $p->ID) : '',
                                'price_outcall'         => function_exists('get_field') ? get_field('price_outcall', $p->ID) : '',
                                'online'                => function_exists('get_field') ? get_field('online', $p->ID) : '',
                                'height'                => function_exists('get_field') ? get_field('height', $p->ID) : '',
                                'weight'                => function_exists('get_field') ? get_field('weight', $p->ID) : '',
                                'age'                   => function_exists('get_field') ? get_field('age', $p->ID) : '',
                                'bust'                  => function_exists('get_field') ? get_field('bust', $p->ID) : '',
                                'description'           => function_exists('get_field') ? get_field('description', $p->ID) : '',
                            ];
                            set_query_var('model', $model);
                            get_template_part('components/ModelCardLegacy');
                        } ?>
                    </ul>
                </section>
            <?php } ?>
        </section>
    </article>

    <!-- ===== Лайтбокс ===== -->
    <div id="lb" class="fixed inset-0 z-[100] hidden">
        <div id="lb-overlay" class="absolute inset-0 bg-black/90 z-10"></div>
        <div class="absolute inset-0 z-20 flex items-center justify-center">
            <div class="relative w-full h-full">
                <div class="swiper js-lightbox h-full">
                    <div class="swiper-wrapper"></div>
                    <div class="swiper-button-prev !text-white"></div>
                    <div class="swiper-button-next !text-white"></div>
                    <div class="swiper-pagination"></div>
                </div>
                <button type="button" id="lb-close" aria-label="Закрыть"
                    class="absolute top-3 right-3 z-30 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center">
                    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 6L18 18M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ACCENT = '<?php echo esc_js($ACCENT); ?>';

            /* Tabs */
            var tabs = Array.from(document.querySelectorAll('.js-tab'));
            var sections = {
                photos: document.querySelector('.js-section-photos'),
                videos: document.querySelector('.js-section-videos'),
                selfies: document.querySelector('.js-section-selfies'),
                params: document.querySelector('.js-section-params'),
                reviews: document.querySelector('.js-section-reviews'),
                districts: document.querySelector('.js-section-districts'),
                services: document.querySelector('.js-section-services')
            };

            function applyTabStyles(btn, active) {
                if (active) {
                    btn.setAttribute('aria-selected', 'true');
                    btn.classList.remove('border-transparent', 'text-gray-600');
                    btn.style.borderColor = ACCENT;
                    btn.style.color = ACCENT;
                } else {
                    btn.setAttribute('aria-selected', 'false');
                    btn.classList.add('border-transparent', 'text-gray-600');
                    btn.style.borderColor = 'transparent';
                    btn.style.color = '';
                }
            }

            function openTab(tabName) {
                Object.keys(sections).forEach(function(key) {
                    if (sections[key]) sections[key].classList.toggle('hidden', key !== tabName);
                });
                tabs.forEach(function(b) {
                    applyTabStyles(b, b.getAttribute('data-tab') === tabName);
                });
            }
            tabs.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    openTab(btn.getAttribute('data-tab'));
                });
            });
            if (tabs.length) {
                var order = ['photos', 'videos', 'selfies', 'params', 'services', 'reviews', 'districts'];
                var firstAvailable = order.find(function(key) {
                    var el = sections[key];
                    // для контентных вкладок (about/params/reviews/metro/districts) показываем всегда,
                    // для фото/видео/селфи – только если есть контент (не скрыты)
                    if (!el) return false;
                    var always = ['params', 'reviews', 'districts'];
                    return always.includes(key) || !el.classList.contains('hidden');
                }) || 'about';
                var def = tabs.find(b => b.getAttribute('data-tab') === firstAvailable) || tabs[0];
                openTab(def.getAttribute('data-tab'));
            }

            /* Swiper: левый слайдер и отзывы */
            if (window.Swiper) {
                var leftSliderEl = document.querySelector('.js-left-slider');
                if (leftSliderEl) {
                    var leftSlider = new Swiper('.js-left-slider', {
                        slidesPerView: 1,
                        spaceBetween: 0,
                        loop: <?php echo $images_count > 1 ? 'true' : 'false'; ?>,
                        pagination: <?php echo $images_count > 1 ? "{ el: '.js-left-slider .swiper-pagination', clickable: true }" : 'false'; ?>,
                        navigation: <?php echo $images_count > 1 ? "{ nextEl: '.js-left-slider .swiper-button-next', prevEl: '.js-left-slider .swiper-button-prev' }" : 'false'; ?>
                    });
                    var thumbs = Array.from(document.querySelectorAll('.js-thumb-btn'));
                    thumbs.forEach(function(btn, i) {
                        btn.addEventListener('click', function() {
                            if (leftSlider.slideToLoop) leftSlider.slideToLoop(i);
                            else leftSlider.slideTo(i);
                            thumbs.forEach(b => b.classList.remove('active'));
                            btn.classList.add('active');
                        });
                    });
                    if (thumbs.length) {
                        leftSlider.on('slideChange', function() {
                            var idx = leftSlider.realIndex ?? leftSlider.activeIndex;
                            thumbs.forEach((b, k) => b.classList.toggle('active', k === idx));
                        });
                        thumbs[0].classList.add('active');
                    }
                }
                if (document.querySelector('.js-reviews-slider')) {
                    new Swiper('.js-reviews-slider', {
                        slidesPerView: 1,
                        spaceBetween: 20,
                        loop: true,
                        autoplay: {
                            delay: 5000,
                            disableOnInteraction: false
                        },
                        breakpoints: {
                            768: {
                                slidesPerView: 2,
                                spaceBetween: 24
                            },
                            1024: {
                                slidesPerView: 3,
                                spaceBetween: 30
                            }
                        },
                        navigation: {
                            nextEl: '.js-reviews-slider ~ .swiper-button-next, .js-reviews-slider .swiper-button-next',
                            prevEl: '.js-reviews-slider ~ .swiper-button-prev, .js-reviews-slider .swiper-button-prev'
                        },
                        pagination: {
                            el: '.js-reviews-slider ~ .swiper-pagination, .js-reviews-slider .swiper-pagination',
                            clickable: true
                        }
                    });
                }
            }

            /* Лайтбокс */
            var LB_ITEMS = <?php echo wp_json_encode($lb_items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
            var lb = document.getElementById('lb');
            var lbWrap = document.querySelector('.js-lightbox .swiper-wrapper');

            function ensureVideoFrame(slide) {
                if (slide.querySelector('.lb-video-iframe')) return;
                var src = slide.dataset.src || '';
                if (!src) return;
                var poster = slide.querySelector('.lb-video-poster');
                if (poster) poster.remove();
                var box = document.createElement('div');
                box.className = 'lb-video-iframe';
                if (slide.dataset.type === 'mp4') {
                    box.innerHTML = '<video src="' + src + '" controls autoplay playsinline class="w-screen h-screen max-w-screen max-h-screen object-contain"></video>';
                } else {
                    var url = src + (src.indexOf('?') >= 0 ? '&' : '?') + 'autoplay=1';
                    box.innerHTML = '<iframe src="' + url + '" frameborder="0" allow="autoplay; fullscreen; encrypted-media" allowfullscreen referrerpolicy="no-referrer-when-downgrade" class="w-screen h-screen"></iframe>';
                }
                slide.appendChild(box);
            }

            if (lbWrap && LB_ITEMS.length) {
                LB_ITEMS.forEach(function(item) {
                    var slide = document.createElement('div');
                    slide.className = 'swiper-slide';
                    slide.dataset.type = item.type;
                    slide.dataset.src = item.src || '';
                    slide.dataset.poster = item.poster || '';
                    if (item.type === 'video' || item.type === 'mp4') {
                        slide.innerHTML =
                            '<div class="lb-video-poster">' +
                            (item.poster ? '<img src="' + item.poster + '" alt="' + item.alt + '" class="max-w-screen max-h-screen object-contain">' : '<div class="w-screen h-screen bg-black"></div>') +
                            '<span class="play"><svg width="40" height="40" viewBox="0 0 24 24" fill="#fff"><path d="M8 5v14l11-7-11-7z"/></svg></span>' +
                            '</div>';
                    } else {
                        slide.innerHTML = '<img src="' + (item.src || '') + '" alt="' + item.alt + '" class="max-w-screen max-h-screen object-contain">';
                    }
                    lbWrap.appendChild(slide);
                });
            }

            var lightbox = new Swiper('.js-lightbox', {
                loop: LB_ITEMS.length > 1,
                navigation: {
                    nextEl: '.js-lightbox .swiper-button-next',
                    prevEl: '.js-lightbox .swiper-button-prev'
                },
                pagination: {
                    el: '.js-lightbox .swiper-pagination',
                    clickable: true
                },
                keyboard: {
                    enabled: true
                },
                on: {
                    slideChange: function() {
                        document.querySelectorAll('.js-lightbox .swiper-slide').forEach(function(slide) {
                            if ((slide.dataset.type === 'video' || slide.dataset.type === 'mp4') && !slide.classList.contains('swiper-slide-active')) {
                                var v = slide.querySelector('.lb-video-iframe');
                                if (v) {
                                    v.remove();
                                    if (!slide.querySelector('.lb-video-poster')) {
                                        var poster = slide.dataset.poster || '';
                                        var altTxt = slide.dataset.alt || '';
                                        var wrap = document.createElement('div');
                                        wrap.className = 'lb-video-poster';
                                        wrap.innerHTML = (poster ?
                                                '<img src="' + poster + '" alt="' + altTxt + '" class="max-w-screen max-h-screen object-contain">' :
                                                '<div class="w-screen h-screen bg-black"></div>') +
                                            '<span class="play"><svg width="40" height="40" viewBox="0 0 24 24" fill="#fff"><path d="M8 5v14l11-7-11-7z"/></svg></span>';
                                        slide.appendChild(wrap);

                                    }
                                }
                            }
                        });
                        var active = document.querySelector('.js-lightbox .swiper-slide-active');
                        if (active && (active.dataset.type === 'video' || active.dataset.type === 'mp4')) ensureVideoFrame(active);
                    }
                }
            });

            function openLightbox(index) {
                if (!LB_ITEMS.length) return;
                lb.classList.remove('hidden');
                document.documentElement.classList.add('overflow-hidden');
                var slideIndex = parseInt(index) || 0;
                if (lightbox.slideToLoop) lightbox.slideToLoop(slideIndex, 0);
                else lightbox.slideTo(slideIndex, 0);
                setTimeout(function() {
                    var active = document.querySelector('.js-lightbox .swiper-slide-active');
                    if (active && (active.dataset.type === 'video' || active.dataset.type === 'mp4')) ensureVideoFrame(active);
                }, 100);
            }

            function closeLightbox() {
                lb.classList.add('hidden');
                document.documentElement.classList.remove('overflow-hidden');
                document.querySelectorAll('.lb-video-iframe').forEach(function(n) {
                    n.remove();
                });
            }
            document.querySelectorAll('.js-open-lightbox').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    openLightbox(parseInt(btn.getAttribute('data-idx')) || 0);
                });
            });
            document.getElementById('lb-close')?.addEventListener('click', closeLightbox);
            document.getElementById('lb-overlay')?.addEventListener('click', closeLightbox);
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !lb.classList.contains('hidden')) closeLightbox();
            });

            /* Звёзды */
            var starsBox = document.getElementById('mr-stars');
            if (starsBox) {
                var labels = Array.from(starsBox.querySelectorAll('label.star'));

                function paint(r) {
                    labels.forEach(function(l) {
                        var v = parseInt(l.dataset.val, 10);
                        l.classList.toggle('text-yellow-500', v <= r);
                        l.classList.toggle('text-gray-300', v > r);
                    });
                }
                labels.forEach(function(l) {
                    var v = parseInt(l.dataset.val, 10);
                    l.addEventListener('mouseenter', function() {
                        paint(v);
                    });
                    l.addEventListener('click', function() {
                        paint(v);
                        document.getElementById('mr-star-' + v).checked = true;
                    });
                });
                starsBox.addEventListener('mouseleave', function() {
                    var c = document.querySelector('#model-review-form input[name="rating"]:checked');
                    paint(c ? parseInt(c.value, 10) : 0);
                });
            }

            /* AJAX форма */
            var reviewForm = document.getElementById('model-review-form');
            if (reviewForm) {
                var msgEl = document.getElementById('mr-msg');
                reviewForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    if (msgEl) msgEl.textContent = '';
                    var submitBtn = reviewForm.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-50');
                    }
                    try {
                        var fd = new FormData(reviewForm);
                        var res = await fetch(reviewForm.action, {
                            method: 'POST',
                            body: fd,
                            credentials: 'same-origin'
                        });
                        var out = await res.json();
                        if (out && out.success) {
                            if (msgEl) {
                                msgEl.classList.remove('text-red-600');
                                msgEl.classList.add('text-green-600');
                                msgEl.textContent = (out.data && out.data.message) ? out.data.message : 'Спасибо! Отзыв отправлен на модерацию.';
                            }
                            reviewForm.reset();
                            if (typeof paint === 'function') paint(0);
                        } else {
                            if (msgEl) {
                                msgEl.classList.remove('text-green-600');
                                msgEl.classList.add('text-red-600');
                                msgEl.textContent = (out && out.data && out.data.message) ? out.data.message : 'Ошибка отправки отзыва.';
                            }
                        }
                    } catch (err) {
                        if (msgEl) {
                            msgEl.classList.remove('text-green-600');
                            msgEl.classList.add('text-red-600');
                            msgEl.textContent = 'Сетевая ошибка. Попробуйте позже.';
                        }
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-50');
                    }
                });
            }
        });
    </script>

    <script>
        (function() {
            const KEY = 'favModels'; // единый ключ

            // Нормализация: сделать из чего угодно -> [int,int]
            function normalize(v) {
                let ids = [];
                try {
                    const parsed = typeof v === 'string' ? JSON.parse(v) : v;
                    if (Array.isArray(parsed)) {
                        for (const it of parsed) {
                            if (it && typeof it === 'object' && 'id' in it) {
                                const id = parseInt(it.id, 10);
                                if (id) ids.push(id);
                            } else {
                                const id = parseInt(it, 10);
                                if (id) ids.push(id);
                            }
                        }
                    } else {
                        const id = parseInt(parsed, 10);
                        if (id) ids.push(id);
                    }
                } catch (e) {
                    if (typeof v === 'string') {
                        ids = v.split(/[\s,]+/).map(x => parseInt(x, 10)).filter(Boolean);
                    }
                }
                return Array.from(new Set(ids));
            }

            // Миграция со старых ключей (если были)
            (function migrate() {
                const keys = ['favModels', 'favModelsV1', 'favorites', 'favoritesModels'];
                const collected = new Set();
                for (const k of keys) {
                    const raw = localStorage.getItem(k);
                    if (!raw) continue;
                    for (const id of normalize(raw)) collected.add(id);
                    if (k !== KEY) localStorage.removeItem(k);
                }
                localStorage.setItem(KEY, JSON.stringify(Array.from(collected)));
            })();

            function getList() {
                return normalize(localStorage.getItem(KEY) || '[]');
            }

            function saveList(ids) {
                localStorage.setItem(
                    KEY,
                    JSON.stringify(Array.from(new Set(ids.map(x => parseInt(x, 10)).filter(Boolean))))
                );
                document.dispatchEvent(new CustomEvent('favorites:changed', {
                    detail: {
                        ids: getList()
                    }
                }));
            }

            function has(id) {
                id = parseInt(id, 10);
                return getList().includes(id);
            }

            function add(id) {
                id = parseInt(id, 10);
                const list = getList();
                if (!list.includes(id)) {
                    list.push(id);
                    saveList(list);
                }
            }

            function remove(id) {
                id = parseInt(id, 10);
                saveList(getList().filter(x => x !== id));
            }

            // UI
            const btn = document.getElementById('fav-toggle');
            if (!btn) return; // теперь return внутри функции — ок

            const id = parseInt(btn.dataset.id, 10);

            function paint() {
                const active = has(id);
                btn.setAttribute('aria-pressed', active ? 'true' : 'false');
                btn.classList.toggle('bg-rose-500', active);
                btn.classList.toggle('text-white', active);
                btn.classList.toggle('border-rose-500', active);
                btn.classList.toggle('hover:bg-rose-600', active);
                const span = btn.querySelector('span');
                if (span) span.textContent = active ? 'В избранном' : 'В избранное';
                const svg = btn.querySelector('svg');
                if (svg) svg.setAttribute('fill', active ? 'currentColor' : 'none');
            }

            btn.addEventListener('click', function() {
                if (has(id)) remove(id);
                else add(id);
                paint();
            });

            // Первичная отрисовка состояния
            paint();
        })();
    </script>



</main>
<?php get_footer();
