<?php

/**
 * Template Name: Отзывы (все отзывы по моделям)
 * Description: Список опубликованных отзывов с миниатюрой модели, ссылкой вида /slug-id/ и пагинацией.
 */
if (!defined('ABSPATH')) exit;

get_header();

/** ===== Пагинация ===== */
$paged = max(1, get_query_var('paged') ?: get_query_var('page') ?: 1);
$ppp   = 12;

/** ===== Иконки рейтинга ===== */
if (!function_exists('mr_starline')) {
    function mr_starline($r)
    {
        $r = max(0, min(5, (int)$r));
        $out = '<div class="flex items-center gap-0.5" aria-label="Рейтинг ' . $r . ' из 5">';
        for ($i = 1; $i <= 5; $i++) {
            $fill = $i <= $r ? 'currentColor' : 'none';
            $cls  = $i <= $r ? 'text-[#e865a0]' : 'text-neutral-300';
            $out .= '<svg class="w-4 h-4 ' . $cls . '" viewBox="0 0 24 24" fill="' . $fill . '" stroke="currentColor" stroke-width="1.5"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.25l-7.19-.61L12 2 9.19 8.64 2 9.25l5.46 4.72L5.82 21z"/></svg>';
        }
        return $out . '</div>';
    }
}

/** ===== Фото модели ===== */
if (!function_exists('mr_get_model_photo_url')) {
    function mr_get_model_photo_url(int $model_id, $size = 'medium_large'): string
    {
        if (!$model_id) return '';
        if (function_exists('get_field')) {
            $photo = get_field('photo', $model_id);
            if (is_numeric($photo)) {
                $img = wp_get_attachment_image_src((int)$photo, $size);
                if ($img) return $img[0];
            } elseif (is_array($photo)) {
                if (isset($photo['url']) || isset($photo['ID'])) {
                    if (!empty($photo['sizes'][$size])) return $photo['sizes'][$size];
                    if (!empty($photo['ID'])) {
                        $img = wp_get_attachment_image_src((int)$photo['ID'], $size);
                        if ($img) return $img[0];
                    }
                    if (!empty($photo['url'])) return $photo['url'];
                } else {
                    foreach ($photo as $im) {
                        if (is_array($im)) {
                            if (!empty($im['sizes'][$size])) return $im['sizes'][$size];
                            if (!empty($im['ID'])) {
                                $img = wp_get_attachment_image_src((int)$im['ID'], $size);
                                if ($img) return $img[0];
                            }
                            if (!empty($im['url'])) return $im['url'];
                        } elseif (is_numeric($im)) {
                            $img = wp_get_attachment_image_src((int)$im, $size);
                            if ($img) return $img[0];
                        } elseif (is_string($im) && $im !== '') {
                            return $im;
                        }
                    }
                }
            } elseif (is_string($photo) && $photo !== '') {
                return $photo;
            }
        }
        $thumb_id = get_post_thumbnail_id($model_id);
        if ($thumb_id) {
            $img = wp_get_attachment_image_src($thumb_id, $size);
            if ($img) return $img[0];
        }
        return '';
    }
}

/**
 * ===== Ссылка на модель в формате /slug-id/ =====
 * - Берём post_name, чистим __trashed.
 * - Если пусто — пробуем _wp_old_slug, потом генерим из post_title.
 * - Строим абсолютный URL: https://site/slug-id/ (закрывающий слеш обязателен).
 * - Специально игнорируем ACF 'uri', по требованию.
 */
if (!function_exists('mr_get_model_link_slug_id')) {
    function mr_get_model_link_slug_id(int $model_id): string
    {
        if (!$model_id) return '';

        // Получаем чистый слаг
        $slug = (string) get_post_field('post_name', $model_id);
        $slug = preg_replace('/__trashed(?:-\d+)?$/', '', $slug ?? '');
        $slug = sanitize_title($slug);

        if ($slug === '') {
            // Пробуем _wp_old_slug
            $old = get_post_meta($model_id, '_wp_old_slug');
            if (is_array($old) && !empty($old)) {
                $last = end($old);
                if (is_string($last) && $last !== '') {
                    $slug = sanitize_title(preg_replace('/__trashed(?:-\d+)?$/', '', $last));
                }
            }
        }
        if ($slug === '') {
            // Из заголовка
            $title = (string) get_the_title($model_id);
            $slug  = sanitize_title($title);
        }

        // Формат slug-id/
        $final_path = $slug . '-' . (int)$model_id;

        // Абсолютный URL с закрывающим слешом
        return esc_url_raw(user_trailingslashit(home_url('/' . $final_path)));
    }
}

/** ===== Запрос отзывов ===== */
$q = new WP_Query([
    'post_type'      => 'model_review',
    'post_status'    => 'publish',
    'posts_per_page' => $ppp,
    'paged'          => $paged,
    'no_found_rows'  => false,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);

?>
<main class="px-4 py-10 bg-white text-black">
    <div class="max-w-[1100px] mx-auto">

        <header class="mb-8 text-center">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Отзывы клиентов</h1>
            <p class="text-neutral-700 mt-2">Все опубликованные отзывы о моделях.</p>
        </header>

        <?php if ($q->have_posts()): ?>
            <ul class="space-y-6">
                <?php while ($q->have_posts()): $q->the_post();
                    $rid      = get_the_ID();
                    $mid      = (int) get_post_meta($rid, '_mr_model_id', true);
                    $name     = get_post_meta($rid, '_mr_name', true) ?: 'Гость';
                    $rating   = (int) get_post_meta($rid, '_mr_rating', true);
                    $date     = get_the_date('d.m.Y');
                    $date_iso = get_the_date('c');

                    $m_is_model = ($mid && get_post_type($mid) === 'models');
                    $m_title    = $m_is_model ? get_the_title($mid) : 'Модель';
                    $m_link = '';
                    if ($m_is_model) {
                        $raw_link = get_permalink($mid);
                        // Если slug содержит суффикс -123, убираем его для «чистой» ссылки
                        $parsed   = wp_parse_url($raw_link);
                        $path     = isset($parsed['path']) ? $parsed['path'] : '';
                        $path     = preg_replace('~-(\d+)(/)?$~', '$2', $path);
                        $m_link   = home_url($path ?: '/');
                    }
                    $m_img      = $m_is_model ? mr_get_model_photo_url($mid, 'medium_large') : '';
                ?>
                    <li class="rounded-2xl border border-[rgba(232,101,160,.18)] bg-white p-4 md:p-5 shadow-[0_2px_18px_rgba(0,0,0,.04)] hover:shadow-[0_6px_24px_rgba(0,0,0,.06)] transition">
                        <div class="flex gap-4">
                            <?php if ($m_img): ?>
                                <a href="<?php echo esc_url($m_link ?: '#'); ?>"
                                    class="block w-32 h-24 md:w-40 md:h-28 flex-shrink-0 overflow-hidden rounded-xl ring-1 ring-[rgba(232,101,160,.2)]">
                                    <img src="<?php echo esc_url($m_img); ?>"
                                        alt="<?php echo esc_attr($m_title); ?>"
                                        class="w-full h-full object-cover" loading="lazy" decoding="async">
                                </a>
                            <?php endif; ?>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="text-sm text-neutral-500">Автор</div>
                                        <div class="font-semibold text-black leading-tight truncate"><?php echo esc_html($name); ?></div>

                                        <div class="mt-1 text-sm">
                                            <?php if ($m_link): ?>
                                                <a class="text-[#e865a0] hover:text-black underline decoration-1 underline-offset-2"
                                                    href="<?php echo esc_url($m_link); ?>">
                                                    <?php echo esc_html($m_title); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-neutral-700"><?php echo esc_html($m_title); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0">
                                        <?php echo mr_starline($rating); ?>
                                        <time class="text-sm text-neutral-500" datetime="<?php echo esc_attr($date_iso); ?>">
                                            <?php echo esc_html($date); ?>
                                        </time>
                                    </div>
                                </div>

                                <div class="mt-3 content">
                                    <?php echo wp_kses_post(get_the_content()); ?>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php endwhile;
                wp_reset_postdata(); ?>
            </ul>

            <?php
            // Пагинация
            $links = paginate_links([
                'base'      => untrailingslashit(get_pagenum_link(1)) . '/%_%',
                'format'    => 'page/%#%',
                'current'   => $paged,
                'total'     => $q->max_num_pages,
                'type'      => 'array',
                'prev_text' => '‹',
                'next_text' => '›',
                'add_args'  => false,
            ]);
            if ($links): ?>
                <nav class="mt-10 flex justify-center">
                    <ul class="flex flex-wrap gap-2">
                        <?php foreach ($links as $l):
                            $is_cur = strpos($l, 'current') !== false;
                            $text   = wp_strip_all_tags($l);
                            preg_match('~href=["\']([^"\']+)~', $l, $m);
                            $href = $m[1] ?? '#';
                        ?>
                            <li>
                                <?php if ($is_cur): ?>
                                    <span class="px-3 py-2 rounded-xl bg-[#e865a0] text-white shadow-sm"><?php echo esc_html($text); ?></span>
                                <?php else: ?>
                                    <a class="px-3 py-2 rounded-xl border border-[rgba(232,101,160,.25)] text-black hover:bg-[#e865a0] hover:text-white hover:border-[#e865a0] transition"
                                        href="<?php echo esc_url($href); ?>"><?php echo esc_html($text); ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <p class="text-neutral-600">Пока нет опубликованных отзывов.</p>
        <?php endif; ?>
    </div>
</main>

<?php get_footer();
