<?php
get_header(); ?>

<main>
    <?php
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            if (is_attachment()) {
                get_template_part('pages/attachment');
            } else {
                get_template_part('pages/home');
            }
        }
    } else {
        get_template_part('pages/home'); /* pages/404 -> pages/home */
    }
    ?>
</main>

<?php get_footer(); ?>