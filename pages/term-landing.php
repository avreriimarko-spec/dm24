<?php

/**
 * Template Name: Родительский шаблон (термы)
 * Description: Выводит записи связанного CPT плитками, без пагинации.
 */

if (!defined('ABSPATH')) exit;
get_header();

$page_id    = get_queried_object_id();
$h1         = function_exists('get_field') ? (get_field('h1', $page_id) ?: get_the_title($page_id)) : get_the_title($page_id);
$p          = function_exists('get_field') ? (get_field('p',  $page_id) ?: '') : '';
$text_block = function_exists('get_field') ? (get_field('seo', $page_id) ?: '') : '';

// Определяем CPT по слагу текущей страницы
$page_slug = (string) get_post_field('post_name', $page_id);
$slug_to_post_type = [
    'services'     => 'uslugi',
    'rajony'       => 'rajon',
    'metro'        => 'metro',
    'price'        => 'tsena',
    'vozrast'      => 'vozrast',
    'nationalnost' => 'nacionalnost',
    'ves'          => 'ves',
    'rost'         => 'rost',
    'grud'         => 'grud',
    'cvet-volos'   => 'tsvet-volos',

    // Поддержка старых слагов на случай legacy-страниц.
    'tsena'        => 'tsena',
    'nacionalnost' => 'nacionalnost',
    'tsvet-volos'  => 'tsvet-volos',
];
$post_type = $slug_to_post_type[$page_slug] ?? 'uslugi';

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

<main class="page-hero page-hero--uslugi">
    <div class="page-hero__inner max-w-[1200px] mx-auto text-black">

    <header class="mb-10 grid grid-cols-1 lg:grid-cols-[1fr_1.2fr] gap-6 items-end">
        <div>
            <h1 class="page-title"><?php echo esc_html($h1); ?></h1>
        </div>
        <?php if ($p) { ?>
            <div class="content text-[15px] md:text-[16px] leading-6 text-neutral-700">
                <?php echo wpautop(wp_kses_post($p)); ?>
            </div>
        <?php } ?>
    </header>

    <?php if ($q->have_posts()) { ?>
        <!-- Плитки ссылок -->
        <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 items-start">
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
                <li>
                    <a href="<?php echo esc_url($url); ?>"
                        class="group flex flex-col md:flex-row-reverse rounded-2xl border border-[rgba(232,101,160,.2)] bg-white shadow-[0_6px_20px_rgba(0,0,0,.05)] hover:shadow-[0_10px_26px_rgba(0,0,0,.08)] transition overflow-hidden"
                        aria-label="<?php echo esc_attr($title); ?>">
                        <?php if ($thumb_url) { ?>
                            <div class="md:w-[42%]">
                                <div class="aspect-[4/3] bg-neutral-100">
                                <img
                                    src="<?php echo esc_url($thumb_url); ?>"
                                    alt="<?php echo esc_attr($thumb_alt); ?>"
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                    decoding="async">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="p-4 md:p-5 flex-1">
                            <h3 class="text-[17px] md:text-[18px] font-semibold group-hover:text-rose-600 transition-colors">
                                <?php echo esc_html($title); ?>
                            </h3>
                            <?php if (!empty($desc)) { ?>
                                <p class="mt-2 text-[13px] md:text-[14px] text-neutral-600 leading-5 line-clamp-3">
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
        <div class="content mx-auto max-w-[1200px] mt-10 bg-white text-neutral-800 border border-[rgba(232,101,160,.18)] rounded-2xl p-6 md:p-8 shadow-[0_8px_24px_rgba(0,0,0,.04)]">
            <?php echo wpautop(wp_kses_post($text_block)); ?>
        </div>
    <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>
