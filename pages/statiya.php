<?php
/*
Template Name: Страница статьи
Template Post Type: blog
*/

$img = get_field("img"); // ACF поле возвращает ID изображения
if ($img) {
    $img_url = wp_get_attachment_image_url($img, 'medium_large');
}

$h1 = get_field("h1"); // Заголовок статьи
$p = get_field("p"); // Краткое описание

get_header();
?>

<main class="w-full md:w-[1000px] mx-auto mt-6 text-white">

    <!-- Навигация -->
    <nav class="text-red-600 backdrop-blur-lg p-4 w-full m-auto mb-4">
        <ul class="flex items-center space-x-3 text-white text-base">
            <li>
                <a href="/" class="flex items-center gap-2 text-red-600 transition-colors duration-300 font-medium">
                    Главная
                </a>
            </li>
            <li class="text-gray-600">»</li>
            <!--             <li>
                <a href="<?php echo home_url('/blog/'); ?>"
                    class="text-white transition-colors duration-300 font-medium">
                    Блог
                </a>
            </li>
            <li class="text-white">»</li> -->
            <li class="text-gray-600"><?php echo esc_html($h1); ?></li>
        </ul>
    </nav>

    <?php
    // Получаем всю группу полей article_info
    $article_info = get_field('article_info');

    // Извлекаем конкретные поля из группы
    $date = isset($article_info['article_date']) ? $article_info['article_date'] : '';
    $author = isset($article_info['article_author']) ? $article_info['article_author'] : '';
    $readtime = isset($article_info['article_readtime']) ? $article_info['article_readtime'] : '';
    ?>

    <div class="flex flex-col md:flex-row justify-center items-center gap-2 text-sm text-gray-600 mb-4">
        <?php if ($date): ?>
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 text-[#dc2626]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span><?php echo esc_html($date); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($author): ?>
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 text-[#dc2626]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M5.121 17.804A11.954 11.954 0 0112 15c2.5 0 4.847.76 6.879 2.063M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Автор: <?php echo esc_html($author); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($readtime): ?>
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 text-[#dc2626]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span><?php echo esc_html($readtime); ?></span>
            </div>
        <?php endif; ?>
    </div>



    <!-- Заголовок и изображение -->
    <div class="flex justify-center items-center text-center flex-col p-2">
        <h1 class="text-3xl font-bold mb-6 uppercase inline-block text-red-600"><?php echo esc_html($h1); ?></h1>
        <p class="text-[16px] md:text-[20px] text-gray-600 mb-4"><?php echo esc_html($p); ?></p>
        <?php if ($img): ?>
            <img class="w-full max-h-[600px] object-cover rounded-sm shadow-lg" src="<?php echo esc_url($img_url); ?>"
                title="Девушка" alt="<?php echo esc_html($h1); ?>">
        <?php endif; ?>
    </div>

    <!-- Контент статьи -->
    <?php if (have_rows('content')): ?>
        <?php while (have_rows('content')):
            the_row(); ?>
            <section class="seoText-stat ">
                <?php echo wp_kses_post(get_sub_field('descrep')); ?>
            </section>
        <?php endwhile; ?>
    <?php endif; ?>

</main>

<?php get_footer(); ?>