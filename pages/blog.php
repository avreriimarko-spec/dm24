<!-- 
Template Name: Блог
-->

<?php get_header();
$h1 = get_field('h1');
$img = get_field('img');
$p = get_field('p');

?>

<main class="max-w-[1500px] mx-auto mt-6">
    <!-- Обёртка -->
    <div class="bg-gradient-to-r from-black via-[#330000] to-[#660000] border border-red-600 p-5 rounded-xl shadow-lg hover:shadow-xl transition duration-300 w-full ">
        <!-- Хлебные крошки -->
        <div class="bg-gradient-to-r from-deepred to-darkgray px-6 py-3 text-sm md:text-base text-gray-300">
            <nav>
                <ul class="flex flex-wrap justify-start items-center space-x-2">
                    <li>
                        <a href="/" class="hover:text-white transition flex items-center gap-1">
                            Главная
                        </a>
                    </li>
                    <li class="text-gray-500">»</li>
                    <li><?php echo esc_html(get_the_title()); ?></li>
                </ul>
            </nav>
        </div>

        <div class="mx-auto flex flex-col gap-2">

            <!-- H1 с градиентом -->
            <div class="text-center bg-gradient-to-r from-deepred to-darkgray">
                <h1 class="text-2xl md:text-3xl font-extrabold uppercase tracking-wide leading-snug text-white ">
                    <?php echo esc_html($h1); ?>
                </h1>
            </div>

            <!-- Описание -->
            <div class="bg-gradient-to-r from-deepred to-darkgray text-center">
                <p class="text-base md:text-lg text-gray-300 leading-relaxed max-w-5xl mx-auto">
                    <?php echo esc_html($p); ?>
                </p>
            </div>

        </div>

    </div>


    <!-- Контент -->
    <div class="container mx-auto px-4 mt-8">
        <!-- Первый пост (самый новый) -->
        <div class="flex flex-col md:flex-row mb-12 justify-start w-full">
            <?php
            $args = array(
                'post_type' => 'blog', // Кастомный тип записей
                'posts_per_page' => 1, // Получаем только первый (самый новый) пост
                'orderby' => 'date',
                'order' => 'DESC'
            );

            $blog_posts = new WP_Query($args);

            if ($blog_posts->have_posts()):
                while ($blog_posts->have_posts()):
                    $blog_posts->the_post();
                    $post_id = get_the_ID();

                    // Получаем данные из ACF
                    $post_h1 = get_field('h1') ?: get_the_title();
                    $post_p = get_field('p') ?: wp_trim_words(get_the_excerpt(), 20, '...');
                    $img = get_field("img"); // ACF поле возвращает ID изображения
                    if ($img) {
                        $post_img = wp_get_attachment_image_url($img, 'medium_large'); // получаем URL нужного размера
                    }
                    $post_url = get_permalink();
                    ?>

                    <!-- Фото -->
                    <div class="mb-6 md:mb-0">
                        <a href="<?php echo esc_url($post_url); ?>">
                            <div class="overflow-hidden rounded-sm shadow-md">
                                <img src="<?php echo esc_url($post_img); ?>" alt="<?php echo esc_attr($post_h1); ?>"
                                    class="w-[600px] h-[400px] object-cover transition-transform duration-500 hover:scale-105 rounded rounded-lg">
                            </div>
                        </a>
                    </div>

                    <!-- Контент -->
                    <div class="w-full md:w-1/4 flex flex-col justify-start md:pl-10">

                        <!-- Дата публикации -->
                        <p class="text-sm text-gray-500 mb-4">
                            <?php echo get_the_date('F j, Y'); ?> <!-- Формат даты: Месяц день, год -->
                        </p>

                        <!-- Заголовок поста -->
                        <h2 class="text-3xl font-bold mb-6 uppercase text-red-600">
                            <?php echo esc_html($post_h1); ?>
                        </h2>

                        <!-- Краткое описание -->
                        <p class="text-[17px] text-gray-700 leading-relaxed mb-6">
                            <?php echo esc_html($post_p); ?>
                        </p>

                    </div>



                    <?php
                endwhile;
                wp_reset_postdata();
            else:
                echo '<p class="text-center text-lg text-gray-600">Постов пока нет</p>';
            endif;
            ?>
        </div>

        <!-- Остальные посты (по 3 в строке) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <?php
            $args = array(
                'post_type' => 'blog', // Кастомный тип записей
                'posts_per_page' => 9, // Количество постов (оставляем 9, так как первый уже показан)
                'orderby' => 'date',
                'order' => 'DESC'
            );

            $blog_posts = new WP_Query($args);

            if ($blog_posts->have_posts()):
                while ($blog_posts->have_posts()):
                    $blog_posts->the_post();
                    $post_id = get_the_ID();

                    // Получаем данные из ACF
                    $post_h1 = get_field('h1') ?: get_the_title();
                    $post_p = get_field('p') ?: wp_trim_words(get_the_excerpt(), 20, '...');
                    $img = get_field("img"); // ACF поле возвращает ID изображения
                    if ($img) {
                        $post_img = wp_get_attachment_image_url($img, 'medium_large'); // получаем URL нужного размера
                    }
                    $post_url = get_permalink();
                    ?>

                    <div class="flex flex-col mb-8">
                        <a href="<?php echo esc_url($post_url); ?>">
                            <!-- Фото -->
                            <div class="w-full mb-6">
                                <div class="overflow-hidden rounded-sm shadow-md">
                                    <img src="<?php echo esc_url($post_img); ?>" alt="<?php echo esc_attr($post_h1); ?>"
                                        class="w-full h-[300px] object-cover transition-transform duration-500 hover:scale-105 rounded rounded-lg">
                                </div>
                            </div>

                            <!-- Контент -->
                            <div class="flex flex-col justify-center">
                                <h2 class="text-2xl font-semibold mb-4 text-red-600 inline-block ">
                                    <?php echo esc_html($post_h1); ?>
                                </h2>
                                <p class="text-base text-gray-600 leading-relaxed mb-6">
                                    <?php echo esc_html($post_p); ?>
                                </p>

                            </div>
                        </a>
                    </div>

                    <?php
                endwhile;
                wp_reset_postdata();
            else:
                echo '<p class="text-center text-lg text-gray-600">Постов пока нет</p>';
            endif;
            ?>
        </div>
    </div>
</main>






<?php get_footer(); ?>