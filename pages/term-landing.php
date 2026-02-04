<?php

/**
 * Template Name: Родительский шаблон (услуги)
 * Description: Всегда выводит все записи CPT `uslugi` плитками, без пагинации.
 */

if (!defined('ABSPATH')) exit;
get_header();

$page_id    = get_queried_object_id();
$h1         = function_exists('get_field') ? (get_field('h1', $page_id) ?: get_the_title($page_id)) : get_the_title($page_id);
$p          = function_exists('get_field') ? (get_field('p',  $page_id) ?: '') : '';
$text_block = function_exists('get_field') ? (get_field('seo', $page_id) ?: '') : '';

// Жёстко фиксируем CPT
$post_type = 'uslugi';

// Проверка регистрации CPT
if (!post_type_exists($post_type)) {
    echo '<main class="mx-auto w-full lg:w-[1200px] px-4 py-8">';
    echo '<h1 class="text-3xl font-bold mb-4">' . esc_html($h1) . '</h1>';
    if ($p) {
        echo '<div class="prose prose-neutral max-w-none mb-6 content">' . wpautop(wp_kses_post($p)) . '</div>';
    }
    echo '<p class="text-black">Тип записи <code>' . esc_html($post_type) . '</code> не найден. Проверьте регистрацию через register_post_type().</p>';
    echo '</main>';
    get_footer();
    return;
}

// Запрос всех услуг (без пагинации)
$q = new WP_Query([
    'post_type'           => $post_type,
    'post_status'         => 'publish',
    'posts_per_page'      => -1, // все записи
    'orderby'             => ['menu_order' => 'ASC', 'title' => 'ASC'],
    'no_found_rows'       => true,
    'ignore_sticky_posts' => true,
]);
?>

<main class="mx-auto w-full lg:w-[1200px] px-4 py-8">

    <header class="mb-8">
        <h1 class="text-3xl font-bold leading-tight"><?php echo esc_html($h1); ?></h1>
        <?php if ($p) { ?>
            <div class="prose prose-neutral max-w-none mt-3 text-black content">
                <?php echo wpautop(wp_kses_post($p)); ?>
            </div>
        <?php } ?>
    </header>

    <?php if ($q->have_posts()) { ?>
        <!-- Плитки ссылок -->
        <ul class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php
            while ($q->have_posts()) {
                $q->the_post();
                $pid    = get_the_ID();
                $title  = get_the_title();
                $url    = get_permalink();

                // Превью
                $thumb_id  = get_post_thumbnail_id($pid);
                $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'medium') : '';
                $thumb_alt = $thumb_id ? get_post_meta($thumb_id, '_wp_attachment_image_alt', true) : '';
                if ($thumb_alt === '') $thumb_alt = $title;

                // 1) Описание из ACF p_atc
                $desc_raw = function_exists('get_field') ? (get_field('p_atc', $pid) ?: '') : '';
                // 2) Fallback: excerpt -> content
                if ($desc_raw === '' || (is_string($desc_raw) && trim($desc_raw) === '')) {
                    $desc_raw = get_the_excerpt() ?: get_the_content('');
                }
                // 3) Очищаем и режем до 22 слов
                $desc = wp_trim_words(wp_strip_all_tags((string) $desc_raw), 22, '…');
            ?>
                <li class="group">
                    <a href="<?php echo esc_url($url); ?>"
                        class="block rounded-lg border border-neutral-200 hover:border-rose-300 transition-colors overflow-hidden"
                        aria-label="<?php echo esc_attr($title); ?>">
                        <?php if ($thumb_url) { ?>
                            <div class="aspect-[3/2] bg-neutral-100">
                                <img
                                    src="<?php echo esc_url($thumb_url); ?>"
                                    alt="<?php echo esc_attr($thumb_alt); ?>"
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                    decoding="async">
                            </div>
                        <?php } ?>
                        <div class="p-4">
                            <h3 class="font-semibold group-hover:text-rose-600 transition-colors">
                                <?php echo esc_html($title); ?>
                            </h3>
                            <?php if (!empty($desc)) { ?>
                                <p class="mt-2 text-sm text-neutral-600 line-clamp-3">
                                    <?php echo esc_html($desc); ?>
                                </p>
                            <?php } ?>
                        </div>
                    </a>
                </li>
            <?php }
            wp_reset_postdata(); ?>
        </ul>
    <?php } else { ?>
        <p class="text-neutral-600">Не найдено записей типа <code><?php echo esc_html($post_type); ?></code>.</p>
    <?php } ?>

    <?php if (!empty($text_block)) : ?>
        <div class="content mx-auto max-w-[1280px] 2xl:max-w-[1400px] px-4 mt-6 bg-neutral-50 text-neutral-800 border border-neutral-200 rounded-sm">
            <?php echo wpautop(wp_kses_post($text_block)); ?>
        </div>
    <?php endif; ?>

</main>

<?php get_footer(); ?>