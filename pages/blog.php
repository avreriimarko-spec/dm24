<?php

/**
 * Template Name: Блог
 */
get_header();

$heading = function_exists('get_field') ? (get_field('h1') ?: get_the_title()) : get_the_title();
$lead    = function_exists('get_field') ? (get_field('p')  ?: '') : '';

$is_ajax = isset($_GET['ajax_blog']) && $_GET['ajax_blog'] == '1';

$paged = max(1, get_query_var('paged') ?: get_query_var('page') ?: 1);
$ppp   = 12;

$q = new WP_Query([
    'post_type'      => 'blog',
    'post_status'    => 'publish',
    'posts_per_page' => $ppp,
    'paged'          => $paged,
    'no_found_rows'  => false,
]);

/** Получить URL картинки из ACF photo_statiya (ID/array/url) */
function blog_get_img_url($photo, $size = 'large')
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
?>
<?php
if ($is_ajax) {
    ob_start();
}
?>

<main class="px-4 py-12 bg-white text-black">
    <div class="max-w-[1200px] mx-auto">

        <?php if (!$is_ajax): ?>
            <!-- Заголовок и лид -->
            <header class="mb-10 text-center">
                <h1 class="text-[32px] md:text-[40px] font-extrabold tracking-tight">
                    <?php echo esc_html($heading); ?>
                </h1>
                <?php if ($lead): ?>
                    <p class="mt-2 text-neutral-700"><?php echo esc_html($lead); ?></p>
                <?php endif; ?>
            </header>
        <?php endif; ?>

        <?php if ($q->have_posts()): ?>
            <!-- Две колонки: слева картинка, справа текст -->
            <section id="blog-list" class="grid grid-cols-1 lg:grid-cols-2 gap-x-10 gap-y-10">
                <?php while ($q->have_posts()): $q->the_post();
                    $acf_h1    = function_exists('get_field') ? (get_field('h1_statiya')   ?: '') : '';
                    $acf_p     = function_exists('get_field') ? (get_field('p_statiya')    ?: '') : '';
                    $acf_seo   = function_exists('get_field') ? (get_field('seo_statiya')  ?: '') : '';
                    $acf_photo = function_exists('get_field') ? (get_field('photo_statiya') ?: '') : '';

                    $permalink = get_permalink();
                    $title     = $acf_h1 !== '' ? $acf_h1 : get_the_title();

                    $desc_source = $acf_p !== '' ? $acf_p
                        : ($acf_seo !== '' ? wp_strip_all_tags($acf_seo)
                            : (has_excerpt() ? get_the_excerpt() : wp_strip_all_tags(get_the_content(''))));
                    $desc = wp_trim_words($desc_source, 28, '…');

                    $img_url = blog_get_img_url($acf_photo, 'large');
                    if (!$img_url) {
                        $thumb_id = get_post_thumbnail_id();
                        $img      = $thumb_id ? wp_get_attachment_image_src($thumb_id, 'large') : null;
                        $img_url  = $img ? $img[0] : '';
                    }

                    $date_human = date_i18n('j F Y', get_the_time('U'));
                    $date_iso   = get_the_date('c');
                ?>
                    <article class="group rounded-2xl border border-[rgba(255,45,114,.18)] bg-white shadow-[0_2px_18px_rgba(0,0,0,.04)] hover:shadow-[0_6px_24px_rgba(0,0,0,.06)] transition overflow-hidden">
                        <a href="<?php echo esc_url($permalink); ?>" class="grid grid-cols-1 md:grid-cols-[320px_1fr] items-stretch gap-0 md:gap-6 h-full">
                            <!-- Медиа слева -->
                            <div class="w-full h-full">
                                <div class="w-full h-full md:aspect-auto aspect-[16/9] bg-neutral-100 overflow-hidden md:rounded-none">
                                    <?php if ($img_url): ?>
                                        <img
                                            src="<?php echo esc_url($img_url); ?>"
                                            alt="<?php echo esc_attr($title); ?>"
                                            class="w-full h-full object-cover transition duration-300 group-hover:scale-[1.02]"
                                            loading="lazy" decoding="async">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-neutral-500 text-sm">Нет изображения</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Текст справа -->
                            <div class="p-5 md:py-5 md:pr-5 md:pl-0 flex flex-col">
                                <div class="mb-3">
                                    <div class="inline-flex items-center gap-2 text-[13px] font-semibold">
                                        <span class="inline-block rounded-full bg-[rgba(255,45,114,.08)] text-[#ff2d72] px-2 py-0.5">
                                            <time datetime="<?php echo esc_attr($date_iso); ?>"><?php echo esc_html($date_human); ?></time>
                                        </span>
                                    </div>
                                </div>

                                <h2 class="text-xl md:text-2xl font-extrabold leading-snug">
                                    <span class="group-hover:underline decoration-2 underline-offset-4 decoration-[#ff2d72]">
                                        <?php echo esc_html($title); ?>
                                    </span>
                                </h2>

                                <?php if ($desc): ?>
                                    <p class="mt-3 text-[15px] leading-6 text-neutral-700 line-clamp-3">
                                        <?php echo esc_html($desc); ?>
                                    </p>
                                <?php endif; ?>

                                <span class="mt-4 inline-flex items-center gap-2 text-[#ff2d72] font-semibold">
                                    Читать
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M13 5l7 7-7 7v-4H4v-6h9V5z" />
                                    </svg>
                                </span>
                            </div>
                        </a>
                    </article>
                <?php endwhile;
                wp_reset_postdata(); ?>
            </section>

            <?php if (!$is_ajax && $q->max_num_pages > 1): ?>
                <div class="mt-10 flex justify-center">
                    <button id="blog-load-more"
                        class="px-4 h-10 rounded-[10px] border text-[15px] font-semibold bg-white text-[#ff2d72] border-[#ff2d72] hover:bg-[#ff2d72] hover:text-white transition"
                        data-current="<?php echo (int)$paged; ?>"
                        data-total="<?php echo (int)$q->max_num_pages; ?>"
                        data-ppp="<?php echo (int)$ppp; ?>"
                    >Показать ещё</button>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <p class="text-neutral-600">Пока нет опубликованных статей.</p>
        <?php endif; ?>

    </div>
</main>
<?php if ($is_ajax) {
    echo ob_get_clean();
    wp_die();
} ?>

<?php if (!$is_ajax): ?>
    <script>
    (function() {
      const btn = document.getElementById('blog-load-more');
      if (!btn) return;
      const list = document.getElementById('blog-list');
      let current = parseInt(btn.dataset.current || '1', 10);
      const total = parseInt(btn.dataset.total || '1', 10);
      btn.addEventListener('click', async () => {
        if (current >= total) return;
        btn.disabled = true;
        btn.textContent = 'Загрузка...';
        const next = current + 1;
        try {
          const res = await fetch(window.location.pathname + '?ajax_blog=1&paged=' + next, { credentials: 'same-origin' });
          const html = await res.text();
          const tmp = document.createElement('div');
          tmp.innerHTML = html;
          tmp.querySelectorAll('#blog-list article').forEach(card => list.appendChild(card));
          current = next;
          btn.dataset.current = String(current);
          if (current >= total) {
            btn.style.display = 'none';
          } else {
            btn.disabled = false;
            btn.textContent = 'Показать ещё';
          }
        } catch(e) {
          btn.disabled = false;
          btn.textContent = 'Показать ещё';
        }
      });
    })();
    </script>
<?php endif; ?>

<?php get_footer(); ?>
