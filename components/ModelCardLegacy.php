<?php
// Получаем данные. Приоритет у query_var (так быстрее всего работает наш цикл)
$model = get_query_var('model', []);

// Если вдруг вызвали не из нашего цикла (фолбэк на $args)
if (empty($model) && isset($args['model'])) {
    $model = $args['model'];
}

// Если данных нет вообще — выходим
if (empty($model)) {
    return;
}

/** БАЗА */
$post_id     = (int)($model['ID'] ?? $model['id'] ?? get_the_ID());
$name        = trim((string)($model['name'] ?? get_the_title($post_id)));
$profile_url = $model['uri'] ?? get_permalink($post_id);

/* -------------------- ОПТИМИЗАЦИЯ: НОВАЯ МОДЕЛЬ -------------------- */
if (isset($model['is_new'])) {
    $is_new = (bool)$model['is_new'];
} else {
    static $counter = 0; // Статическая переменная сохраняет значение между вызовами файла
    $is_new = ($counter < 60);
    $counter++;
}

/* -------------------- ОПТИМИЗАЦИЯ: ОТЗЫВЫ -------------------- */
if (isset($model['reviews_count'])) {
    $comments_count = (int)$model['reviews_count'];
} else {
    // Оптимизация: берем стандартное кол-во комментариев WP (кэшируется), вместо WP_Query
    $comments_count = get_comments_number($post_id);
}

/** ПАРАМЕТРЫ */
$age    = $model['age'] ?? '';
$height = $model['height'] ?? '';
$weight = $model['weight'] ?? '';
$bust   = $model['bust'] ?? '';

/** Местоположение */
$district = $model['district'] ?? '';
if (is_array($district)) $district = implode(', ', $district);

/** Услуги */
$services = $model['services'] ?? [];
if (empty($services)) {
    // Используем get_the_terms (кэшируется), а не wp_get_post_terms
    $service_terms = get_the_terms($post_id, 'uslugi_tax');
    if ($service_terms && !is_wp_error($service_terms)) {
        $services = wp_list_pluck($service_terms, 'name');
    }
}

/** Цены */
$currency      = 'RUB';
$to_int        = static fn($v) => (int)preg_replace('~\D+~', '', (string)$v);
$price_1_hour  = $to_int($model['price_outcall'] ?? '');
$price_2_hours = $to_int($model['price_2_hours'] ?? ($price_1_hour * 2));
$price_night   = $to_int($model['price_night'] ?? '');

/** Пути иконок */
$icon_dir = get_stylesheet_directory_uri() . '/assets/icons/card/';

/* -------------------- ОПТИМИЗАЦИЯ ФОТО -------------------- */
$gallery = $model['modelGalleryThumbnail'] ?? [];
$img_src = '';
$img_w   = 340;
$img_h   = 500;

// Проверка LCP приоритета (передается из родительского цикла)
// Проверяем и переменную query_var, и ключ в массиве $model
$is_priority = get_query_var('is_lcp_priority', false) || !empty($model['is_lcp_priority']);

if (!empty($model['image_url'])) {
    $img_src = $model['image_url'];
} elseif (!empty($gallery)) {
    $first = is_array($gallery) ? ($gallery[0] ?? null) : $gallery;

    // Если это массив ACF
    if (is_array($first) && !empty($first['sizes']['model_card'])) {
        $img_src = $first['sizes']['medium_large'];
        $img_w   = $first['sizes']['medium_large-width'] ?? 600;
        $img_h   = $first['sizes']['medium_large-height'] ?? 900;
    }
    // Если это ID
    elseif (is_numeric($first) || (is_array($first) && !empty($first['ID']))) {
        $att_id = is_array($first) ? $first['ID'] : $first;
        $img_data = wp_get_attachment_image_src((int)$att_id, 'model_card');
        if ($img_data) {
            $img_src = $img_data[0];
            $img_w   = $img_data[1];
            $img_h   = $img_data[2];
        }
    }
}

// Fallback
if (!$img_src) {
    $img_src = get_stylesheet_directory_uri() . '/assets/images/placeholder-thumbs.webp';
}

/** ИКОНКИ И МЕТА */
$is_verified    = has_term('', 'drygie_tax', $post_id);
$has_video      = !empty($model['video']);
$is_recommended = !empty($model['recommended']) ? $model['recommended'] : get_post_meta($post_id, 'recommended', true);

if (empty($name) || empty($img_src)) return;

/* ========================================================= */
?>

<li class="mf-item list-none w-full sm:w-[336px] lg:mb-4" data-post-id="<?= esc_attr($post_id) ?>">
    <article
        class="group relative overflow-hidden rounded-xl bg-white shadow-md border border-slate-200 hover:shadow-lg transition-all duration-300 cursor-pointer"
        onclick="(function(e, url) {
            if (e.target.closest('button')) return;
            e.metaKey || e.ctrlKey ? window.open(url,'_blank') : window.location.href = url;
        })(event, '<?= esc_js($profile_url) ?>')">

        <figure class="relative m-0">
            <img
                src="<?= esc_url($img_src) ?>"
                alt="<?= esc_attr($name) ?>"
                <?php if ($is_priority): ?>
                loading="eager"
                fetchpriority="high"
                <?php else: ?>
                loading="lazy"
                decoding="async"
                <?php endif; ?>
                width="<?= esc_attr($img_w) ?>"
                height="<?= esc_attr($img_h) ?>"
                class="w-full h-[600px] lg:h-[500px] object-cover transition-transform duration-500 group-hover:scale-[1.03]" />

            <a href="<?= esc_url($profile_url) ?>"
                class="absolute inset-0 z-10 block"
                aria-label="Профиль <?= esc_attr($name) ?>"></a>

            <?php if ($is_new): ?>
                <div class="absolute top-0 right-0 z-40 w-[90px] h-[90px] overflow-hidden pointer-events-none select-none">
                    <div class="absolute top-[12px] right-[-40px] w-[140px] rotate-45 bg-[#e865a0] text-white text-[12px] font-bold text-center py-1 shadow-md flex items-center justify-center gap-1">
                        <img src="<?= esc_url($icon_dir) ?>new.png" alt="Новая" class="w-4 h-4">
                        Новая
                    </div>
                </div>
            <?php endif; ?>

            <div class="absolute top-3 left-3 flex flex-wrap gap-2 z-30 pointer-events-none">
                <?php if ($is_verified): ?>
                    <img src="<?= esc_url($icon_dir) ?>shield.png" alt="Проверенная" class="w-8 h-8 drop-shadow-lg">
                <?php endif; ?>

                <?php if ($has_video): ?>
                    <img src="<?= esc_url($icon_dir) ?>play-button.png" alt="Видео" class="w-8 h-8 drop-shadow-lg">
                <?php endif; ?>

                <?php if ($comments_count > 0): ?>
                    <div class="relative">
                        <img src="<?= esc_url($icon_dir) ?>ratings.png" alt="Отзывы" class="w-8 h-8 drop-shadow-lg">
                        <span class="absolute -top-1 -right-1 bg-green-500 text-white text-[11px] font-bold rounded-full px-[5px] py-[1px]"><?= $comments_count ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($is_recommended): ?>
                <div class="absolute top-3 right-3 z-30 pointer-events-none">
                    <span class="px-2.5 py-1 rounded-md bg-amber-500/95 text-white font-bold shadow">Рекомендовано</span>
                </div>
            <?php endif; ?>

            <figcaption class="absolute inset-x-0 bottom-0 z-30 text-white pointer-events-none bg-black/70 transition-all duration-300 group-hover:bg-black/80">
                <div class="relative z-10 pointer-events-auto">

                    <div class="flex justify-between items-center px-4 pt-6">
                        <p class="text-lg text-white font-extrabold uppercase tracking-wide truncate m-0">
                            <?= esc_html($name) ?>
                        </p>

                        <button type="button"
                            class="sm:hidden w-8 h-8 rounded-full bg-black/50 text-white flex items-center justify-center transition-all hover:bg-black/70 active:scale-95 toggle-params-btn"
                            aria-expanded="false"
                            aria-label="Показать параметры"
                            onclick="(function(e){
                                    e.stopPropagation();
                                    const btn=e.currentTarget;
                                    const wrap=btn.closest('article').querySelector('.params');
                                    const arrow=btn.querySelector('svg');
                                    const open=btn.getAttribute('aria-expanded')==='true';
                                    btn.setAttribute('aria-expanded', !open);
                                    arrow.classList.toggle('rotate-180', !open);
                                    wrap.classList.toggle('open', !open);
                                })(event)">
                            <svg class="w-4 h-4 transition-transform" viewBox='0 0 24 24' fill='none' stroke='currentColor'>
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M19 9l-7 7-7-7' />
                            </svg>
                        </button>
                    </div>

                    <?php if ($age !== '' || $district !== ''): ?>
                        <p class="px-4 text-sm font-semibold m-0 mt-1">
                            <?= $age !== '' ? esc_html($age) . ' лет' : '' ?>
                            <?= $age !== '' && $district !== '' ? ', ' : '' ?>
                            <?= $district !== '' ? esc_html($district) : '' ?>
                        </p>
                    <?php endif; ?>

                    <div class="mt-3 px-4 flex justify-between items-center text-sm">
                        <span class="font-semibold">Цена</span>
                        <span class="font-extrabold text-base">
                            <?= $price_1_hour ? number_format((int)$price_1_hour, 0, ',', ' ') . ' ' . $currency . ' / 1 час' : '—' ?>
                        </span>
                    </div>

                    <div class="params px-4 mt-3 border-t border-white/20 pt-3 text-white/95
                        max-h-0 overflow-hidden opacity-0 transition-all duration-500
                        sm:group-hover:max-h-[300px] sm:group-hover:opacity-100">

                        <ul class="text-sm space-y-1.5 font-semibold">
                            <?php if ($age): ?>
                                <li class="flex justify-between"><span class="text-white/80">Возраст:</span><span><?= esc_html($age) ?> лет</span></li>
                            <?php endif; ?>

                            <?php if ($height): ?>
                                <li class="flex justify-between"><span class="text-white/80">Рост:</span><span><?= esc_html($height) ?> см</span></li>
                            <?php endif; ?>

                            <?php if ($weight): ?>
                                <li class="flex justify-between"><span class="text-white/80">Вес:</span><span><?= esc_html($weight) ?> кг</span></li>
                            <?php endif; ?>

                            <?php if ($bust): ?>
                                <li class="flex justify-between"><span class="text-white/80">Грудь:</span><span><?= esc_html($bust) ?></span></li>
                            <?php endif; ?>

                            <?php if (!empty($services)): ?>
                                <li class="pt-1">
                                    <span class="text-white/80 block mb-1">Услуги:</span>
                                    <span class="text-xs leading-relaxed"><?= esc_html(implode(', ', array_slice($services, 0, 4))) ?></span>
                                </li>
                            <?php endif; ?>
                        </ul>

                        <?php
                        $description = strip_tags($model['description'] ?? get_the_excerpt($post_id) ?? '');
                        $short_desc  = mb_substr($description, 0, 200) . (mb_strlen($description) > 200 ? '…' : '');
                        ?>

                        <?php if (!empty($short_desc)): ?>
                            <p class="mt-3 text-sm text-white/90 leading-snug">
                                <?= esc_html($short_desc) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </figcaption>
        </figure>
    </article>
</li>
