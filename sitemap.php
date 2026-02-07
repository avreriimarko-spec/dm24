<?php
/*
Template Name: HTML Sitemap
*/
get_header();

$ACCENT = '#e865a0';

/** helpers */
function esc_chip_link($url, $text)
{
    return '<a href="' . esc_url($url) . '" class="chip">' . esc_html($text) . '</a>';
}

/**
 * Рендер секции таксы — ссылки только по term-slug
 * без базового префикса и без закрывающего слэша
 * пример: /vysokie
 */
function render_tax_section(string $label, string $tx)
{
    $terms = get_terms([
        'taxonomy'   => $tx,
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);
    if (empty($terms) || is_wp_error($terms)) return;

    echo '<section class="mb-10">';
    echo   '<h2 class="s-heading">' . esc_html($label) . '</h2>';
    echo   '<div class="chips">';
    foreach ($terms as $t) {
        // формируем «открытую» ссылку без завершающего слэша
        $url = home_url('/' . ltrim($t->slug, '/'));
        echo esc_chip_link($url, $t->name);
    }
    echo   '</div>';
    echo '</section>';
}

/** список используемых таксономий */
$tax_map = [
    'Цена'           => 'price_tax',
    'Возраст'        => 'vozrast_tax',
    'Национальность' => 'nationalnost_tax',
    'Районы'         => 'rayonu_tax',
    'Метро'          => 'metro_tax',
    'Рост'           => 'rost_tax',
    'Грудь'          => 'grud_tax',
    'Вес'            => 'ves_tax',
    'Цвет волос'     => 'cvet-volos_tax',
    'Услуги'         => 'uslugi_tax',
];
?>

<main class="px-4 !mt-14 mb-12">
    <div class="mx-auto w-full max-w-[1200px] bg-white text-black rounded-2xl border" style="border-color:<?= esc_attr($ACCENT) ?>">
        <div class="p-6 md:p-10">

            <h1 class="text-3xl md:text-4xl font-extrabold text-center mb-8">Карта сайта</h1>

            <!-- Страницы -->
            <section class="mb-10">
                <h2 class="s-heading">Страницы сайта</h2>
                <div class="chips">
                    <?php
                    $pages = get_pages(['sort_column' => 'menu_order']);
                    foreach ($pages as $page) {
                        echo esc_chip_link(untrailingslashit(get_permalink($page->ID)), $page->post_title);
                    }
                    ?>
                </div>
            </section>

            <!-- Модели -->
            <?php
            $pt = 'models';
            $pt_obj = get_post_type_object($pt);
            if ($pt_obj) {
                $ids = get_posts([
                    'post_type'   => $pt,
                    'numberposts' => -1,
                    'orderby'     => 'title',
                    'order'       => 'ASC',
                    'post_status' => 'publish',
                    'fields'      => 'ids'
                ]);
                if ($ids) {
                    echo '<section class="mb-10">';
                    echo   '<h2 class="s-heading">' . esc_html($pt_obj->labels->name) . '</h2>';
                    echo   '<div class="chips">';
                    foreach ($ids as $id) {
                        echo esc_chip_link(untrailingslashit(get_permalink($id)), get_the_title($id));
                    }
                    echo   '</div>';
                    echo '</section>';
                }
            }
            ?>

            <!-- Блог -->
            <?php
            $blog_pt  = 'blog';
            $blog_obj = get_post_type_object($blog_pt);
            if ($blog_obj) {
                $posts = get_posts([
                    'post_type'   => $blog_pt,
                    'numberposts' => -1,
                    'orderby'     => 'date',
                    'order'       => 'DESC',
                    'post_status' => 'publish',
                ]);
                if ($posts) {
                    echo '<section class="mb-10">';
                    echo   '<h2 class="s-heading">' . esc_html($blog_obj->labels->name ?: 'Блог') . '</h2>';
                    echo   '<div class="chips">';
                    foreach ($posts as $post) {
                        echo esc_chip_link(untrailingslashit(get_permalink($post->ID)), get_the_title($post->ID));
                    }
                    echo   '</div>';
                    echo '</section>';
                }
            }
            ?>

            <!-- Таксономии -->
            <?php
            foreach ($tax_map as $label => $tx) {
                if (taxonomy_exists($tx)) render_tax_section($label, $tx);
            }
            ?>

        </div>
    </div>
</main>


<?php get_footer(); ?>