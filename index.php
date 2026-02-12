<?php
get_header(); ?>

<main>
    <?php
    if (is_attachment() && have_posts()) {
        while (have_posts()) {
            the_post();
            get_template_part('pages/attachment');
        }
    } else {
        // Home template is self-sufficient and should be rendered once per request.
        get_template_part('pages/home');
    }
    ?>
</main>

<?php get_footer(); ?>
