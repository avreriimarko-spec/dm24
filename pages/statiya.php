<?php
/* Template Name: Статья */
/* Template Post Type: blog */

if (!defined('ABSPATH')) exit;

/** === ACF поля статьи === */
$getf           = function_exists('get_field');
$h1             = $getf ? (get_field('h1_statiya') ?: '') : '';
$lead           = $getf ? (get_field('p_statiya')  ?: '') : '';
$seo_block_html = $getf ? (get_field('seo_statiya') ?: '') : '';
$photo          = $getf ? (get_field('photo_statiya') ?: '') : '';

/** === SEO мета (если нужно) === */
$seo_title       = $getf ? (get_field('seo_title') ?: '') : '';
$seo_description = $getf ? (get_field('seo_description') ?: '') : '';
$seo_keywords    = $getf ? (get_field('seo_keywords') ?: '') : '';

/** === Утилита: URL картинки из ACF === */
function blog_img_url_from_acf($photo, $size = 'full')
{
    if (!$photo) return '';
    if (is_numeric($photo)) {
        $img = wp_get_attachment_image_src((int)$photo, $size);
        return $img ? $img[0] : '';
    }
    if (is_array($photo)) {
        if (!empty($photo['url'])) return $photo['url'];
        if (!empty($photo['ID'])) {
            $img = wp_get_attachment_image_src((int)$photo['ID'], $size);
            return $img ? $img[0] : '';
        }
        return '';
    }
    if (is_string($photo)) return $photo;
    return '';
}

/** === Рендер звёзд для рейтинга комментариев === */
if (!function_exists('post_starline')) {
    function post_starline($r)
    {
        $r = max(0, min(5, (int)$r));
        $out = '<div class="flex items-center gap-0.5" aria-label="Рейтинг ' . $r . ' из 5">';
        for ($i = 1; $i <= 5; $i++) {
            $fill = $i <= $r ? 'currentColor' : 'none';
            $cls  = $i <= $r ? 'text-yellow-500' : 'text-neutral-300';
            $out .= '<svg class="w-4 h-4 ' . $cls . '" viewBox="0 0 24 24" fill="' . $fill . '" stroke="currentColor" stroke-width="1.5"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.25l-7.19-.61L12 2 9.19 8.64 2 9.25l5.46 4.72L5.82 21z"/></svg>';
        }
        return $out . '</div>';
    }
}

get_header();

if (have_posts()) : the_post();

    $post_id    = get_the_ID();
    $date_human = date_i18n('j F, Y', get_post_time('U', true));
    $date_iso   = get_post_time('c', true);
    $title_out  = $h1 !== '' ? $h1 : get_the_title();

    $photo_url  = blog_img_url_from_acf($photo, 'full');
    if (!$photo_url) {
        $tid = get_post_thumbnail_id();
        if ($tid) {
            $im = wp_get_attachment_image_src($tid, 'full');
            $photo_url = $im ? $im[0] : '';
        }
    }

    // AJAX безопасность для формы отзывов
    $ajax_url = admin_url('admin-ajax.php');
    $nonce    = wp_create_nonce('blog_review_nonce');
?>
    <main class="px-4 py-10">
        <div class="max-w-[1200px] mx-auto">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

                <!-- ====== ЛЕВАЯ КОЛОНКА (контент статьи) ====== -->
                <section class="lg:col-span-2">

                    <!-- H1 -->
                    <h1 class="text-[40px] leading-tight font-extrabold text-black mb-4">
                        <?php echo esc_html($title_out); ?>
                    </h1>

                    <!-- Дата с линией -->
                    <div class="mb-6">
                        <div class="w-fit">
                            <time class="block text-[13px] font-semibold text-neutral-800" datetime="<?php echo esc_attr($date_iso); ?>">
                                <?php echo esc_html($date_human); ?>
                            </time>
                            <span aria-hidden="true" class="mt-1 block h-[2px] w-full bg-neutral-300"></span>
                        </div>
                    </div>

                    <!-- Обложка -->
                    <?php if ($photo_url): ?>
                        <figure class="rounded-sm overflow-hidden">
                            <img src="<?php echo esc_url($photo_url); ?>"
                                alt="<?php echo esc_attr($title_out); ?>"
                                class="w-full max-h-[320px] object-cover"
                                loading="eager" decoding="async" />
                        </figure>
                        <div class="h-8"></div>
                    <?php endif; ?>

                    <!-- Вступление -->
                    <?php if ($lead): ?>
                        <p class="text-[17px] leading-relaxed text-neutral-800 mb-6 max-w-3xl">
                            <?php echo esc_html($lead); ?>
                        </p>
                    <?php endif; ?>

                    <!-- Основной контент -->
                    <article class="prose prose-neutral max-w-none prose-img:rounded-sm prose-a:text-[#ff2d72]">
                        <?php
                        the_content();
                        wp_link_pages([
                            'before' => '<div class="mt-6 text-sm text-neutral-600">Страницы: ',
                            'after'  => '</div>',
                        ]);
                        ?>
                    </article>

                    <!-- SEO-блок ниже контента -->
                    <?php if ($seo_block_html): ?>
                        <section class="mt-10 prose prose-neutral max-w-none content">
                            <?php echo $seo_block_html; ?>
                        </section>
                    <?php endif; ?>


                    <!-- ====== ФОРМА ОТЗЫВА + СПИСОК ОТЗЫВОВ ====== -->
                    <section id="post-reviews" class="mt-12">
                        <h2 class="text-2xl font-extrabold text-black mb-3">Отзывы к статье</h2>
                        <p class="text-sm text-neutral-700 mb-4">Оставьте отзыв — он появится после модерации. E-mail виден только модератору.</p>

                        <!-- Форма -->
                        <form id="blog-review-form" class="rounded-xl border border-[rgba(255,45,114,.18)] bg-white p-4 md:p-5 space-y-4"
                            method="post" data-ajax="<?php echo esc_url($ajax_url); ?>">
                            <input type="hidden" name="action" value="blog_add_review">
                            <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">
                            <input type="hidden" name="post_id" value="<?php echo (int)$post_id; ?>">
                            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off"> <!-- honeypot -->

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm mb-1">Имя*</label>
                                    <input name="name" type="text" required placeholder="Ваше имя"
                                        class="w-full rounded-lg border border-neutral-300 bg-white text-black placeholder-neutral-400 px-3 py-2 outline-none focus:border-[#ff2d72]">
                                </div>
                                <div>
                                    <label class="block text-sm mb-1">E-mail*</label>
                                    <input name="email" type="email" required placeholder="you@example.com"
                                        class="w-full rounded-lg border border-neutral-300 bg-white text-black placeholder-neutral-400 px-3 py-2 outline-none focus:border-[#ff2d72]">
                                    <p class="mt-1 text-[12px] text-neutral-500">Не публикуется.</p>
                                </div>
                                <div>
                                    <label class="block text-sm mb-1">Оценка*</label>
                                    <select name="rating" required
                                        class="w-full rounded-lg border border-neutral-300 bg-white text-black px-3 py-2 outline-none focus:border-[#ff2d72]">
                                        <option value="">Выберите</option>
                                        <option value="5">5 — Отлично</option>
                                        <option value="4">4 — Хорошо</option>
                                        <option value="3">3 — Нормально</option>
                                        <option value="2">2 — Плохо</option>
                                        <option value="1">1 — Ужасно</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm mb-1">Сообщение*</label>
                                <textarea name="message" rows="4" required placeholder="Ваш отзыв"
                                    class="w-full rounded-lg border border-neutral-300 bg-white text-black placeholder-neutral-400 px-3 py-2 outline-none focus:border-[#ff2d72]"></textarea>
                            </div>

                            <button id="blog-review-submit" type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-[#ff2d72] text-white hover:bg-[#e62967] transition">
                                Отправить на модерацию
                            </button>

                            <div id="blog-review-alert" class="hidden mt-2 text-sm rounded-lg px-3 py-2"></div>
                        </form>

                        <!-- Список опубликованных отзывов -->
                        <?php
                        $comments = get_comments([
                            'post_id' => $post_id,
                            'status'  => 'approve',
                            'type'    => 'comment',
                            'orderby' => 'comment_date_gmt',
                            'order'   => 'DESC',
                            'number'  => 0,
                        ]);
                        ?>
                        <div class="mt-6">
                            <?php if ($comments): ?>
                                <ul class="space-y-4">
                                    <?php foreach ($comments as $c):
                                        $c_name = $c->comment_author ?: 'Гость';
                                        $c_date = date_i18n('d.m.Y', strtotime($c->comment_date_gmt));
                                        $c_iso  = date('c', strtotime($c->comment_date_gmt));
                                        $c_text = wpautop(esc_html($c->comment_content));
                                        $c_rating = (int)get_comment_meta($c->comment_ID, '_rating', true);
                                    ?>
                                        <li class="rounded-xl border border-neutral-200 bg-white p-4">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="font-semibold text-black truncate"><?php echo esc_html($c_name); ?></div>
                                                <div class="flex items-center gap-3 shrink-0">
                                                    <?php echo post_starline($c_rating); ?>
                                                    <time class="text-xs text-neutral-500" datetime="<?php echo esc_attr($c_iso); ?>">
                                                        <?php echo esc_html($c_date); ?>
                                                    </time>
                                                </div>
                                            </div>
                                            <div class="mt-2 text-[15px] leading-relaxed text-neutral-800">
                                                <?php echo $c_text; ?>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-neutral-600 text-sm">Пока нет опубликованных отзывов.</p>
                            <?php endif; ?>
                        </div>
                    </section>
                    <!-- ====== /ФОРМА + ОТЗЫВЫ ====== -->

                </section>

                <!-- ====== ПРАВЫЙ САЙДБАР (компактный «Другие статьи») ====== -->
                <aside class="lg:col-span-1">
                    <div class="lg:sticky lg:top-24">
                        <h2 class="text-lg font-extrabold mb-3">Другие статьи</h2>

                        <?php
                        $side = new WP_Query([
                            'post_type'      => 'blog',
                            'post_status'    => 'publish',
                            'posts_per_page' => 10,
                            'post__not_in'   => [$post_id],
                            'no_found_rows'  => true,
                        ]);
                        ?>

                        <?php if ($side->have_posts()): ?>
                            <ul class="space-y-2" id="side-posts">
                                <?php
                                $i = 0;
                                while ($side->have_posts()): $side->the_post();
                                    $s_title = get_the_title();
                                    $s_link  = get_permalink();
                                    $s_date_human = date_i18n('j F Y', get_post_time('U', true));
                                    $s_date_iso   = get_post_time('c', true);

                                    $s_p    = $getf ? (get_field('p_statiya') ?: '') : '';
                                    $s_seo  = $getf ? (get_field('seo_statiya') ?: '') : '';
                                    $s_desc_src = $s_p !== '' ? $s_p : ($s_seo !== '' ? wp_strip_all_tags($s_seo) : (has_excerpt() ? get_the_excerpt() : wp_strip_all_tags(get_the_content(''))));
                                    $s_desc = wp_trim_words($s_desc_src, 14, '…');

                                    $s_photo = $getf ? (get_field('photo_statiya') ?: '') : '';
                                    $s_img   = blog_img_url_from_acf($s_photo, 'medium');
                                    if (!$s_img) {
                                        $tid = get_post_thumbnail_id();
                                        if ($tid) {
                                            $im = wp_get_attachment_image_src($tid, 'medium');
                                            $s_img = $im ? $im[0] : '';
                                        }
                                    }
                                    $hidden_cls = ($i >= 4) ? 'hidden side-more' : '';
                                ?>
                                    <li class="<?php echo esc_attr($hidden_cls); ?>">
                                        <a href="<?php echo esc_url($s_link); ?>"
                                            class="group grid grid-cols-[80px,1fr] gap-3 items-start rounded-xl border border-[rgba(255,45,114,.18)] bg-white p-2 hover:shadow-[0_6px_16px_rgba(0,0,0,.06)] transition">
                                            <div class="w-[80px] h-[80px] rounded-lg overflow-hidden bg-neutral-100">
                                                <?php if ($s_img): ?>
                                                    <img src="<?php echo esc_url($s_img); ?>" alt="<?php echo esc_attr($s_title); ?>"
                                                        class="w-full h-full object-cover transition group-hover:scale-[1.03]" loading="lazy" decoding="async" />
                                                <?php else: ?>
                                                    <div class="w-full h-full flex items-center justify-center text-neutral-400 text-xs">Нет фото</div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="min-w-0">
                                                <span class="inline-flex items-center rounded-full bg-[rgba(255,45,114,.08)] text-[#ff2d72] px-2 py-[2px] text-[11px] font-semibold">
                                                    <time datetime="<?php echo esc_attr($s_date_iso); ?>"><?php echo esc_html($s_date_human); ?></time>
                                                </span>
                                                <h3 class="mt-1 text-[14px] font-extrabold leading-snug text-black group-hover:underline underline-offset-4 decoration-[#ff2d72]">
                                                    <?php echo esc_html($s_title); ?>
                                                </h3>
                                                <?php if ($s_desc): ?>
                                                    <p class="mt-[2px] text-[12px] text-neutral-700 line-clamp-2"><?php echo esc_html($s_desc); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                    </li>
                                <?php
                                    $i++;
                                endwhile;
                                wp_reset_postdata();
                                ?>
                            </ul>

                            <?php if ($i > 4): ?>
                                <div class="mt-3">
                                    <button type="button" id="side-more-btn"
                                        class="w-full text-center text-[13px] font-semibold px-3 py-2 rounded-full border border-[#ff2d72] text-[#ff2d72] hover:bg-[rgba(255,45,114,.06)] transition">
                                        Показать ещё
                                    </button>
                                </div>
                                <script>
                                    (function() {
                                        const btn = document.getElementById('side-more-btn');
                                        const items = document.querySelectorAll('.side-more');
                                        if (!btn) return;
                                        let open = false;
                                        btn.addEventListener('click', () => {
                                            open = !open;
                                            items.forEach(el => el.classList.toggle('hidden', !open));
                                            btn.textContent = open ? 'Скрыть' : 'Показать ещё';
                                        });
                                    })();
                                </script>
                            <?php endif; ?>

                        <?php else: ?>
                            <p class="text-neutral-500 text-sm">Пока нет других статей.</p>
                        <?php endif; ?>
                    </div>
                </aside>

            </div>
        </div>

        <!-- JS: отправка отзыва -->
        <script>
            (function() {
                const form = document.getElementById('blog-review-form');
                if (!form) return;
                const ajaxUrl = form.dataset.ajax || '/wp-admin/admin-ajax.php';
                const btn = document.getElementById('blog-review-submit');
                const alertBox = document.getElementById('blog-review-alert');

                function showAlert(type, msg) {
                    alertBox.classList.remove('hidden');
                    alertBox.className = 'mt-2 text-sm rounded-lg px-3 py-2 ' +
                        (type === 'ok' ?
                            'bg-green-50 text-green-700 border border-green-200' :
                            'bg-red-50 text-red-700 border border-red-200');
                    alertBox.textContent = msg;
                }

                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    alertBox.classList.add('hidden');

                    // простая фронт-валидация e-mail
                    const email = (form.email?.value || '').trim();
                    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        showAlert('err', 'Пожалуйста, укажите корректный e-mail.');
                        return;
                    }

                    btn.disabled = true;
                    btn.classList.add('opacity-60', 'cursor-not-allowed');

                    try {
                        const fd = new FormData(form);
                        const res = await fetch(ajaxUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            body: fd
                        });
                        const txt = await res.text();
                        let j;
                        try {
                            j = JSON.parse(txt);
                        } catch (_e) {
                            throw new Error('Сбой сети или неверный ответ');
                        }

                        if (!j || !j.success) {
                            throw new Error((j && j.data && j.data.message) || 'Не удалось отправить');
                        }
                        showAlert('ok', (j.data && j.data.message) || 'Спасибо! Отзыв отправлен на модерацию.');
                        form.reset();
                    } catch (err) {
                        showAlert('err', err.message || 'Ошибка. Попробуйте позже.');
                    } finally {
                        btn.disabled = false;
                        btn.classList.remove('opacity-60', 'cursor-not-allowed');
                    }
                });
            })();
        </script>
    </main>

<?php
endif;
get_footer();
