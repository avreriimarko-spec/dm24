<?php
/*
Template Name: HTML Sitemap
*/
get_header();
?>

<div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow-md">
    <h1 class="text-3xl font-bold text-gray-800 text-center mb-6">Карта сайта</h1>

    <h2 class="text-2xl font-semibold text-gray-700 border-b-2 border-blue-500 pb-2 mt-6">Страницы</h2>
    <ul class="list-none mt-3 space-y-2">
        <?php
        $pages = get_pages();
        foreach ($pages as $page) {
            echo '<li><a href="' . get_permalink($page->ID) . '" class="text-blue-600 hover:underline">' . $page->post_title . '</a></li>';
        }
        ?>
    </ul>
</div>


<?php get_footer(); ?>