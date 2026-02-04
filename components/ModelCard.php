<?php
// Получаем данные. Приоритет у query_var (так быстрее всего работает наш цикл)
$model = get_query_var('model', []);

// Если вдруг вызвали не из нашего цикла (фолбэк на $args)
if (empty($model) && isset($args['model'])) {
    $model = $args['model'];
}

// Если данных нет вообще — выходим
if (empty($model)) {
    // Опционально: можно вывести пустую заглушку, но лучше просто выйти
    // echo '<li class="list-none"><p>No model data found.</p></li>';
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

$district = $model['district'] ?? '';
$metro = $model['metro'] ?? '';

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
$currency      = '₸';
$to_int        = static fn($v) => (int)preg_replace('~\D+~', '', (string)$v);
$price_incall_1h  = $to_int($model['price'] ?? '');
$price_outcall_1h = $to_int($model['price_outcall'] ?? '');
$price_incall_2h  = $to_int($model['price_2_hours'] ?? ($price_incall_1h ? $price_incall_1h * 2 : 0));
$price_outcall_2h = $to_int($model['price_outcall_2_hours'] ?? ($price_outcall_1h ? $price_outcall_1h * 2 : 0));
$price_incall_night  = $to_int($model['price_night'] ?? '');
$price_outcall_night = $to_int($model['price_outcall_night'] ?? '');

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

<?php
$description = strip_tags($model['description'] ?? get_the_excerpt($post_id) ?? '');
$desc_limit  = (int) get_theme_mod('model_card_desc_length', 220);
$desc_limit  = max(160, min(260, $desc_limit));
$short_desc  = mb_substr($description, 0, $desc_limit) . (mb_strlen($description) > $desc_limit ? '…' : '');
$services_preview = array_slice($services, 0, 6);
$format_price = static function (int $val) use ($currency): string {
    return $val > 0 ? number_format($val, 0, ',', ' ') . ' ' . $currency : '—';
};
$has_outcall_prices = ($price_outcall_1h || $price_outcall_2h || $price_outcall_night);
$has_incall_prices  = ($price_incall_1h || $price_incall_2h || $price_incall_night);
$has_stats = ($age || $height || $weight || $bust);
$has_icons = ($is_verified || $has_video || $comments_count > 0);

$resolve_contacts = static function (bool $is_cheap) {
    static $cache = [
        'cheap' => null,
        'normal' => null,
    ];
    $key = $is_cheap ? 'cheap' : 'normal';
    if ($cache[$key] !== null) return $cache[$key];

    if ($is_cheap) {
        $raw_tg = get_theme_mod('contact_telegram_5');
        $raw_wa = get_theme_mod('contact_whatsapp_5');
    } else {
        $tg_pool = [];
        $wa_pool = [];
        if ($t = get_theme_mod('contact_telegram')) $tg_pool[] = $t;
        if ($w = get_theme_mod('contact_whatsapp')) $wa_pool[] = $w;
        for ($i = 1; $i <= 4; $i++) {
            if ($t = get_theme_mod("contact_telegram_$i")) $tg_pool[] = $t;
            if ($w = get_theme_mod("contact_whatsapp_$i")) $wa_pool[] = $w;
        }
        $raw_tg = !empty($tg_pool) ? $tg_pool[array_rand($tg_pool)] : '';
        $raw_wa = !empty($wa_pool) ? $wa_pool[array_rand($wa_pool)] : '';
    }

    $tg_clean = trim((string)$raw_tg);
    $tg_clean = preg_replace('~^https?://t\.me/~i', '', $tg_clean);
    $tg_clean = ltrim($tg_clean, '@');
    $tg_clean = preg_replace('~[^a-z0-9_]+~i', '', $tg_clean);
    $wa_clean = preg_replace('~\D+~', '', (string)$raw_wa);

    $cache[$key] = [
        'tg' => $tg_clean,
        'wa' => $wa_clean,
    ];

    return $cache[$key];
};

$is_cheap_model = has_term('deshevyye-prostitutki', 'price_tax', $post_id);
$contacts = $resolve_contacts($is_cheap_model);
$tg_handle = $contacts['tg'] ?? '';
$wa_number = $contacts['wa'] ?? '';
?>

<li class="mf-item list-none w-full" data-post-id="<?= esc_attr($post_id) ?>">
    <article
        class="anketa-card"
        onclick="(function(e, url) {
            e.metaKey || e.ctrlKey ? window.open(url,'_blank') : window.location.href = url;
        })(event, '<?= esc_js($profile_url) ?>')">
        <a href="<?= esc_url($profile_url) ?>" class="anketa-card__link" aria-label="Профиль <?= esc_attr($name) ?>"></a>

        <div class="anketa-card__header">
            <div class="anketa-card__title"><?= esc_html($name) ?></div>

            <?php if ($is_new): ?>
                <div class="anketa-card__badge">Новая</div>
            <?php endif; ?>
        </div>

        <div class="anketa-card__main">
            <div class="anketa-card__media-block">
                <div class="anketa-card__img-wrapper">
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
                        class="anketa-card__img" />
                </div>
    
                <?php if ($has_icons): ?>
                    <div class="anketa-card__icons">
                        <?php if ($has_video): ?>
                            <img src="<?= esc_url($icon_dir) ?>play-button.png" alt="Видео">
                        <?php endif; ?>
                        <?php if ($comments_count > 0): ?>
                            <span class="anketa-card__comments">
                                <img src="<?= esc_url($icon_dir) ?>ratings.png" alt="Отзывы">
                                <span><?= (int) $comments_count ?></span>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
    
            <div class="anketa-card__content">
                <!-- <?php if ($district): ?>
                    <div class="anketa-card__district"><?= esc_html($district) ?></div>
                <?php endif; ?> -->
                <?php if ($metro): ?>
                    <div class="anketa-card__metro"><?= esc_html($metro) ?></div>
                <?php endif; ?>

                <?php if ($has_stats || $has_outcall_prices || $has_incall_prices): ?>
                    <div class="anketa-card__stats-and-prices">
                        <?php if ($has_stats): ?>
                            <div class="anketa-card__stats">
                                <?php if ($age): ?>
                                    <div><span>Возраст</span><strong><?= esc_html($age) ?></strong></div>
                                <?php endif; ?>
                                <?php if ($height): ?>
                                    <div><span>Рост</span><strong><?= esc_html($height) ?></strong></div>
                                <?php endif; ?>
                                <?php if ($weight): ?>
                                    <div><span>Вес</span><strong><?= esc_html($weight) ?></strong></div>
                                <?php endif; ?>
                                <?php if ($bust): ?>
                                    <div><span>Грудь</span><strong><?= esc_html($bust) ?></strong></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($has_outcall_prices || $has_incall_prices): ?>
                            <div class="anketa-card__prices">
                                <div><span>1 час</span><strong style="color: #ff2d72;"><?= esc_html($format_price($price_outcall_1h)) ?></strong></div>
                                <div><span>2 часа</span><strong style="color: #ff2d72;"><?= esc_html($format_price($price_outcall_2h)) ?></strong></div>

                                <?php 
                                    if ($format_price($price_outcall_night) === '—') {
                                        $color = 'initial';
                                    } else {
                                        $color = '#ff2d72';
                                    }
                                ?>
                                <div><span>Ночь</span><strong style="color: <?= $color; ?>;"><?= esc_html($format_price($price_outcall_night)) ?></strong></div>
                                
                                <?php if ($has_incall_prices): ?>
                                    <div style="justify-content: start; gap: 5px;">
                                        <img src="<?= esc_url($icon_dir) ?>checked.svg" style="height: 15px;" />
                                        <strong>Выезд</strong>
                                    </div>
                                <?php endif; ?>    
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($is_verified): ?>
                    <div style="display: flex; justify-content: center; align-items: center; gap: 5px;">
                        <img src="<?= esc_url($icon_dir) ?>checked.svg" style="height: 20px;">
                        <strong style="color: #3fbf36">Анкета проверена</strong>
                    </div>
                <?php endif; ?>
    
                <?php if (!empty($services_preview)): ?>
                    <div class="anketa-card__services">
                        <?php
                            foreach ($services_preview as $service) {
                                echo '<div class="anketa-card__service">'.htmlspecialchars($service).'</div>';
                            }
                        ?>
                        
                    </div>
                <?php endif; ?>

                <!-- <?php if (!empty($short_desc)): ?>
                    <div class="anketa-card__about">
                        <span>Обо мне:</span>
                        <p><?= esc_html($short_desc) ?></p>
                    </div>
                <?php endif; ?> -->
            </div>
        </div>
    </article>
</li>
