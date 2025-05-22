<!-- 
Template Name: Terms
-->

<?php get_header();
$html = get_field('html');
?>

<style>
    h2 {
        color: #02293D;
        font-size: 28px;
        font-weight: bold;
        margin-top: 1rem;
    }

    a {
        font-weight: bold;
    }
</style>


<main class="w-full md:w-2/3 mx-auto mt-6">
    <h1 class="text-center text-[30px] md:text-[40px]">Условия пользования</h1>
    <section class="mt-5 p-4 md:p-4 bg-white text-black">
        <?php echo wp_kses_post($html); ?>
    </section>
</main>

<?php get_footer(); ?>