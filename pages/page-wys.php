<?php

/**
 * Template Name: Текстовая страница (WYSIWYG)
 * Description: Светлый контентный шаблон (монохром).
 */
get_header();

// Заголовок и лид из ACF (необязательно)
$h1   = function_exists('get_field') ? (get_field('h1') ?: get_the_title()) : get_the_title();
$lead = function_exists('get_field') ? (get_field('lead') ?: '') : '';

// Контент: приоритет ACF-поля "html", иначе стандартный контент страницы
$acf_html = '';
if (function_exists('get_field')) {
    $acf_html = (string) get_field('html'); // ACF WYSIWYG/textarea с HTML
}

// Если в контенте уже есть H1, не выводим дополнительный H1 шаблона.
$content_source = $acf_html !== '' ? $acf_html : (string) get_post_field('post_content', get_the_ID());
$content_has_h1 = preg_match('~<h1\b[^>]*>~iu', $content_source) === 1;
?>

<main class="bg-white text-black px-4 py-8 md:py-12">
    <div class="max-w-[900px] mx-auto">
        <!-- Контентный контейнер -->
        <div class="bg-white text-black rounded-sm p-6 md:p-8">

            <?php if (!$content_has_h1): ?>
                <h1 class="text-2xl md:text-4xl text-center font-extrabold mb-3 text-black">
                    <?php echo esc_html($h1); ?>
                </h1>
            <?php endif; ?>

            <?php if ($lead): ?>
                <p class="mb-5 text-black/80">
                    <?php echo esc_html($lead); ?>
                </p>
            <?php endif; ?>

            <!-- Контент -->
            <div class="content">
                <?php
                if ($acf_html !== '') {
                    echo apply_filters('the_content', $acf_html);
                } else {
                    while (have_posts()) {
                        the_post();
                        the_content();
                    }
                }
                ?>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>
