<?php
/**
 * Template Name: Блог
 */

/**
 * Получить URL картинки из ACF photo_statiya (ID/array/url)
 */
if (!function_exists('blog_get_img_url')) {
    function blog_get_img_url($photo, $size = 'large') {
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
}

/**
 * Функция для рендеринга карточки блога
 */
if (!function_exists('render_blog_card')) {
    function render_blog_card() {
        $acf_h1    = function_exists('get_field') ? (get_field('h1_statiya')   ?: '') : '';
        $acf_p     = function_exists('get_field') ? (get_field('p_statiya')    ?: '') : '';
        $acf_seo   = function_exists('get_field') ? (get_field('seo_statiya')  ?: '') : '';
        $acf_photo = function_exists('get_field') ? (get_field('photo_statiya') ?: '') : '';
        
        $permalink = get_permalink();
        $title     = $acf_h1 !== '' ? $acf_h1 : get_the_title();
        
        $desc_source = $acf_p !== '' ? $acf_p
            : ($acf_seo !== '' ? wp_strip_all_tags($acf_seo)
                : (has_excerpt() ? get_the_excerpt() : wp_strip_all_tags(get_the_content(''))));
        $desc = wp_trim_words($desc_source, 20, '…');
        
        $img_url = blog_get_img_url($acf_photo, 'large');
        if (!$img_url) {
            $thumb_id = get_post_thumbnail_id();
            $img      = $thumb_id ? wp_get_attachment_image_src($thumb_id, 'large') : null;
            $img_url  = $img ? $img[0] : '';
        }
        
        $date_human = date_i18n('j F Y', get_the_time('U'));
        $date_iso   = get_the_date('c');
        ?>
        <article class="flex flex-col group h-full bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300 border border-neutral-100">
            <a href="<?php echo esc_url($permalink); ?>" class="flex flex-col h-full">
                <div class="relative overflow-hidden aspect-[16/10]">
                    <?php if ($img_url): ?>
                        <img
                            src="<?php echo esc_url($img_url); ?>"
                            alt="<?php echo esc_attr($title); ?>"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            loading="lazy" decoding="async">
                    <?php else: ?>
                        <div class="w-full h-full bg-neutral-100 flex items-center justify-center text-neutral-400">
                            <svg class="w-10 h-10 opacity-30" fill="currentColor" viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>

                <div class="p-5 flex flex-col flex-grow">
                    <div class="flex items-center justify-between mb-3">
                        <time datetime="<?php echo esc_attr($date_iso); ?>" class="text-xs font-medium text-neutral-500 uppercase tracking-wider">
                            <?php echo esc_html($date_human); ?>
                        </time>
                    </div>

                    <h2 class="text-lg font-bold text-neutral-900 leading-tight mb-2 group-hover:text-accent transition-colors line-clamp-2">
                        <?php echo esc_html($title); ?>
                    </h2>

                    <?php if ($desc): ?>
                        <p class="text-sm text-neutral-600 line-clamp-3 mb-4 leading-relaxed">
                            <?php echo esc_html($desc); ?>
                        </p>
                    <?php endif; ?>

                    <div class="mt-auto pt-3 border-t border-neutral-100 flex items-center text-accent text-sm font-semibold">
                        Читать далее
                        <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </div>
                </div>
            </a>
        </article>
        <?php
    }
}

// Параметры запроса
$paged   = max(1, get_query_var('paged') ?: get_query_var('page') ?: 1);
$ppp     = 12;
$is_ajax = isset($_GET['ajax_blog']) && $_GET['ajax_blog'] === '1';

$args = [
    'post_type'      => 'blog',
    'post_status'    => 'publish',
    'posts_per_page' => $ppp,
    'paged'          => $paged,
];

$q = new WP_Query($args);

// --- AJAX ОБРАБОТЧИК ---
if ($is_ajax) {
    if ($q->have_posts()) {
        while ($q->have_posts()) {
            $q->the_post();
            render_blog_card();
        }
    }
    wp_reset_postdata();
    wp_die(); // Завершаем выполнение, чтобы не грузить header/footer
}

// --- ОБЫЧНЫЙ ВЫВОД СТРАНИЦЫ ---
get_header();

$heading = function_exists('get_field') ? (get_field('h1') ?: get_the_title()) : get_the_title();
$lead    = function_exists('get_field') ? (get_field('p')  ?: '') : '';
?>

<main class="page-hero page-hero--blog pb-16">
    <div class="page-hero__inner container mx-auto px-4">

        <!-- Заголовок и лид -->
        <header class="mb-10 text-center max-w-4xl mx-auto">
            <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 text-neutral-900">
                <?php echo esc_html($heading); ?>
            </h1>
            <?php if ($lead): ?>
                <p class="text-lg text-neutral-600 leading-relaxed max-w-2xl mx-auto">
                    <?php echo esc_html($lead); ?>
                </p>
            <?php endif; ?>
        </header>

        <!-- Список постов -->
        <?php if ($q->have_posts()): ?>
            <div id="blog-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php while ($q->have_posts()): $q->the_post();
                    render_blog_card();
                endwhile;
                wp_reset_postdata(); ?>
            </div>

            <!-- Кнопка "Показать ещё" -->
            <?php if ($q->max_num_pages > 1): ?>
                <div class="mt-12 flex justify-center">
                    <button id="blog-load-more"
                        class="group relative px-6 py-3 bg-white border border-neutral-300 text-neutral-700 font-medium rounded-full hover:border-accent hover:text-accent focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-all duration-300 flex items-center space-x-2"
                        data-current="<?php echo (int)$paged; ?>"
                        data-total="<?php echo (int)$q->max_num_pages; ?>"
                    >
                        <span class="relative z-10">Показать ещё</span>
                        <svg class="w-5 h-5 animate-spin hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="py-20 text-center">
                <div class="inline-block p-4 rounded-full bg-neutral-50 mb-4">
                    <svg class="w-12 h-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <p class="text-xl text-neutral-600">Статей пока нет.</p>
            </div>
        <?php endif; ?>

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('blog-load-more');
    if (!btn) return;
    
    const list = document.getElementById('blog-list');
    const spinner = btn.querySelector('svg');
    const label = btn.querySelector('span');
    
    let current = parseInt(btn.dataset.current || '1', 10);
    const total = parseInt(btn.dataset.total || '1', 10);
    
    // Если по каким-то причинам (бек/кэш) текущая >= total
    if (current >= total) {
        btn.style.display = 'none';
        return;
    }

    let isLoading = false;

    btn.addEventListener('click', function() {
        if (isLoading || current >= total) return;
        
        isLoading = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        if (spinner) spinner.classList.remove('hidden');
        label.textContent = 'Загрузка...';
        
        const next = current + 1;
        
        // Добавляем ?ajax_blog=1&paged=X
        // Примечание: window.location.pathname сохраняет текущий путь (например /blog/)
        const url = window.location.pathname + (window.location.search ? window.location.search + '&' : '?') + 'ajax_blog=1&paged=' + next;

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.text();
            })
            .then(html => {
                // Если пришел пустой ответ - значит постов нет
                if (!html.trim()) {
                    btn.style.display = 'none';
                    return;
                }
                
                // Вставляем HTML
                list.insertAdjacentHTML('beforeend', html);
                
                current = next;
                btn.dataset.current = current;
                
                if (current >= total) {
                    btn.style.display = 'none';
                }
            })
            .catch(err => {
                console.error(err);
                label.textContent = 'Ошибка';
                setTimeout(() => {
                     label.textContent = 'Показать ещё';
                }, 2000);
            })
            .finally(() => {
                isLoading = false;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
                if (spinner) spinner.classList.add('hidden');
                if (current < total) {
                    label.textContent = 'Показать ещё';
                }
            });
    });
});
</script>

<?php get_footer(); ?>
