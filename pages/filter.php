<?php
/**
 * Template Name: Категории
 */

$content = get_field('content');

$h1 = get_field('h1');
$h2 = get_field('h2');

get_header(); ?>

<main class="max-w-4xl mx-auto px-6 py-10">
    <section class="bg-gradient-to-r from-red-900 to-black border border-red-600 rounded-xl shadow-lg p-8 mb-6">
        <div class="text-white text-center">

            <!-- Контент -->
            <div class="max-w-3xl mx-auto mt-6">
                <h1 class="text-3xl md:text-4xl font-bold uppercase tracking-wide text-white">
                    <?php echo esc_html($h1); ?>
                </h1>
                <h2 class="text-xl md:text-2xl font-medium text-gray-300 mt-2">
                    <?php echo esc_html($h2); ?>
                </h2>
            </div>
        </div>
    </section>

    <?php
    // Получаем текущий URL и его первый сегмент
    $current_url = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $url_parts = explode('/', $current_url);
    $taxonomy_key = $url_parts[0]; // Берём первый сегмент URL
    
    // Получаем все зарегистрированные таксономии
    $all_taxonomies = get_taxonomies(array('public' => true), 'objects');

    // Проверяем, является ли первый сегмент URL ключом таксономии
    if (array_key_exists($taxonomy_key, $all_taxonomies)) {
        $found_taxonomy = $taxonomy_key;

        // Получаем все термины найденной таксономии
        $terms = get_terms(array(
            'taxonomy' => $found_taxonomy,
            'hide_empty' => false, // Показывать пустые категории
        ));

        if (!empty($terms) && !is_wp_error($terms)): ?>
            <ul class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <?php foreach ($terms as $term): ?>
                    <li>
                        <a href="<?php echo esc_url(get_term_link($term)); ?>"
                            class="block bg-white text-red-600 px-4 py-3 rounded-lg border border-red-600 shadow-md hover:bg-red-600 hover:text-black transition">
                            <?php echo esc_html($term->name); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500">Категории не найдены.</p>
        <?php endif; ?>
    <?php } else { ?>
        <p class="text-gray-500">Таксономия не найдена.</p>
    <?php } ?>

    <?php if (have_rows('content')): ?>
        <?php while (have_rows('content')):
            the_row(); ?>
            <section
                class="m-5 mt-5 p-6 md:p-6 bg-white text-red-600 border border-red-600 rounded-lg shadow-lg leading-relaxed">
                <?php echo wp_kses_post(get_sub_field('descrep')); ?>
            </section>
        <?php endwhile; ?>
    <?php endif; ?>
</main>

<?php get_footer(); ?>