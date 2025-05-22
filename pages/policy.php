<!-- 
Template Name: Policy
-->

<?php get_header();
$html = get_field('html');
?>




<main class="w-full md:w-2/3 mx-auto mt-6">
    <h1 class="text-center text-[30px] md:text-[40px]">Политика Конфиденциальности</h1>
    <section class="mt-5 p-4 md:p-4 bg-white text-black">
        <?php echo wp_kses_post($html); ?>
    </section>
</main>

<?php get_footer(); ?>