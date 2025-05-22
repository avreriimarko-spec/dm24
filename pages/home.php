<?php
/* 
Template name: Home
 */
get_header();
$h1 = get_field('h1');
$p = get_field('p');
$banner_html = get_field('banner-html');
$descr_html = get_field('descr-html');
$background_image = get_field('background-image');
$number = get_theme_mod('contact_number');
?>

<main class="w-full md:p-0 mx-auto mt-6 bg-white">
    <!-- BANNER -->
    <section class="mb-5 text-white rounded-xl shadow-xl p-8 relative bg-black">
        <!-- Фоновое изображение -->
<?php 
$background_image_id = attachment_url_to_postid($background_image); // Получаем ID изображения
?>
		            <h1 class=" text-white text-[20px] md:text-[40px] font-extrabold uppercase tracking-wider drop-shadow-lg">
                <?php echo esc_html($h1); ?>
            </h1>
            <div class="flex flex-col justify-start p-0 md:p-6 w-full max-w-4xl">
                <p class="text-[18px] md:text-[32px] text-red-800 font-semibold mt-2 text-left border-l-4 border-red-500 pl-4">
                    <?php echo esc_html($p); ?>
                </p>
                <div class="mt-4 text-lg leading-relaxed">
                    <?php echo wp_kses_post($banner_html); ?>
                </div>
            </div>
		        <img src="<?php echo esc_url($background_image); ?>"
            class="absolute inset-0 w-full h-full object-cover rounded-xl opacity-30" rel="preload" width="1200" height="400" alt="Красивая девушка в купальнике">
    </section>



    <!-- MODELS -->
    <h2 class="text-red-600 text-semibold text-[35px] md:text-[50px] mb-2 text-center">Анкеты проституток</h2>

        <div class="girls-wrapper flex flex-row gap-6 justify-center flex-wrap m-auto text-white">
            <?php
            $args = array(
                'post_type' => 'girls',
                'posts_per_page' => 18,
                'orderby' => 'date',
                'order' => 'DESC',
            );

            $girls = new WP_Query($args);
            $list = $girls->posts;
            shuffle($list);
            if (!empty($list)):
                foreach ($list as $model):
                    $girl_name = get_the_title($model->ID);
                    $girl_age = get_field('age', $model->ID);
                    $girl_height = get_field('height', $model->ID);
                    $girl_weight = get_field('weight', $model->ID);
                    $girl_bust = get_field('bust', $model->ID);
                    $girl_price = get_field('price', $model->ID);
                    // Приводим значение к числу, если это необходимо
                    $girl_price = floatval($girl_price);
                    // Умножаем на 2
                    $girl_price *= 2;
                    $img = get_field('photo', $model->ID);
                    $model_slug = $model->post_name;
                    $model_url = home_url("/{$model_slug}/");
                    $is_vip = mt_rand(0, 1);
                    $likes = rand(10, 50);
                    $views = rand(1000, 5000);
                    ?>

                    <div
                        class="girls-item w-[290px] bg-white text-red-600 border border-red-600 rounded-xl shadow-lg overflow-hidden transform transition-all hover:scale-105 hover:shadow-xl relative">

                        <a href="<?php echo esc_url($model_url); ?>" class="block relative">

                            <!-- Изображение -->
                            <div class="relative w-full h-[350px] overflow-hidden">
                                <img src="<?php echo esc_attr($img); ?>" title="<?php echo esc_html($girl_name); ?>"
                                    alt="<?php echo esc_html($girl_name); ?>"
                                    class="w-full h-full object-cover block rounded-t-xl transition-all duration-300 hover:scale-110" width="290" height="350" loading="lazy">
                            </div>

                            <!-- VIP бейдж -->
                            <?php if ($is_vip): ?>
                                <div
                                    class="absolute top-3 left-3 bg-red-600 text-white text-sm font-semibold px-3 py-1 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="20" height="20" fill="currentColor">
                                        <path d="M288 0c-19 0-34 15-34 34v68l-79-53c-14-9-33-4-41 11L4 186c-4 8-5 18-1 27s11 15 20 18l86 23-22 85c-2 10 1 21 8 28s17 12 27 12h352c10 0 20-4 27-12s10-18 8-28l-22-85 86-23c9-3 17-10 20-18s3-19-1-27l-130-126c-8-7-27-13-41-11l-79 53V34c0-19-15-34-34-34zM96 400c-18 0-32 14-32 32v32c0 18 14 32 32 32h384c18 0 32-14 32-32v-32c0-18-14-32-32-32H96z"/>
                                    </svg>
                                    VIP
                                </div>
                            <?php endif; ?>

                            <!-- Контент карточки -->
                            <div class="p-4 text-red-600 text-lg border-t border-red-600">
                                <h3 class="text-[26px] font-bold text-red-500 text-center"><?php echo esc_html($girl_name); ?>
                                </h3>
                                <p class="flex items-center gap-2 text-gray-700 text-sm mt-1">
                                                                        <svg class="w-4 h-4 text-red-400" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                            clip-rule="evenodd"></path>
                                    </svg> Ленинский пр-т (+2ст)
                                </p>
                                <div class="mt-3 space-y-1 ">
                                    <p class="flex items-center gap-2 text-[18px]">
                                                                                                                        <svg class="w-5 h-5 text-red-400" fill="currentColor" width="20" height="20"
                                            viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M6 3a1 1 0 011-1h6a1 1 0 011 1v1h2a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2h2V3zm6 2H8v1h4V5zm4 3H4v9h12V8z"
                                                clip-rule="evenodd"></path>
                                        </svg> <span
                                            class="font-medium">Возраст:</span> <?php echo esc_html($girl_age ?: '-'); ?>
                                    </p>
                                    <p class="flex items-center gap-2 text-[18px]">
                                                                                                                        <svg class="w-5 h-5 text-red-400" fill="currentColor" width="20" height="20"
                                            viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M3 3a1 1 0 011-1h12a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V3zm2 0v14h10V3H5zm4 2a1 1 0 00-2 0v10a1 1 0 102 0V5z"
                                                clip-rule="evenodd"></path>
                                        </svg> <span
                                            class="font-medium">Рост:</span> <?php echo esc_html($girl_height ?: '-'); ?> см
                                    </p>
                                    <p class="flex items-center gap-2 text-[18px]">
                                                                                <svg class="w-5 h-5 text-red-400" fill="currentColor" width="20" height="20"
                                            viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1zm-5 8.274l-.818 2.552a2 2 0 00.57 1.952A2 2 0 005 13a2 2 0 001.248-.222 2 2 0 00.57-1.952L6 10.274zm8 0l-.818 2.552a2 2 0 00.57 1.952A2 2 0 0015 13a2 2 0 001.248-.222 2 2 0 00.57-1.952L14 10.274z"
                                                clip-rule="evenodd"></path>
                                        </svg> <span class="font-medium">Вес:</span>
                                        <?php echo esc_html($girl_weight ?: '-'); ?> кг
                                    </p>
                                    <p class="flex items-center gap-2 text-[18px]">
                                                                                <svg class="w-5 h-5 text-red-400" fill="currentColor" width="20" height="20"
                                            viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                                clip-rule="evenodd"></path>
                                        </svg> <span class="font-medium">Грудь:</span>
                                        <?php echo esc_html($girl_bust ?: '-'); ?>
                                    </p>
                                   <p class="flex items-center gap-2 text-[18px]">
                                    <svg  version="1.1"  width="20" height="20" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 380.052 380.052" style="enable-background:new 0 0 380.052 380.052;" xml:space="preserve"><g><path style="fill:#f87171" d="M0,297.742c0,19.037,15.241,34.465,34.042,34.465h311.967c18.801,0,34.042-15.428,34.042-34.465 v-153.76H0V297.742z M254.254,175.407h81.497v24.808h-81.497V175.407z M254.254,217.927h81.497v24.808h-81.497V217.927z M254.254,260.456h81.497v24.808h-81.497V260.456z"/><path style="fill:#f87171" d="M0,82.31v26.645h380.043V82.31c0-19.045-15.241-34.465-34.042-34.465H34.042 C15.241,47.845,0,63.265,0,82.31z"/></g></svg>
                                        <span class="font-medium">Цена:</span>
                                        <?php echo esc_html($girl_price ?: '-'); ?> Br
                                    </p>
                                </div>

                                <!-- Лайки и просмотры -->
                                <div class="flex justify-between items-center mt-4 text-red-700 text-sm">
                                    <p class="flex items-center gap-1 text-red-700">
                                        <svg class="w-4 h-4 text-red-700" fill="red" width="20" height="20"
                                            viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                                clip-rule="evenodd"></path>
                                        </svg> <?php echo esc_html($likes); ?>
                                    </p>
                                    <p class="flex items-center gap-1 text-red-700">
                                                                                                                        <svg class="w-4 h-4 text-red-700" fill="red" width="20" height="20"
                                            viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M10 4a6 6 0 100 12 6 6 0 000-12zm0 10a4 4 0 110-8 4 4 0 010 8zm0-2a2 2 0 100-4 2 2 0 000 4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <?php echo esc_html($views); ?>
                                    </p>
                                </div>
                            </div>

                        </a>
                    </div>

                    <?php
                endforeach;
                wp_reset_postdata();
            else:
                ?>
                <p class="text-center text-lg text-red-600">Модели не найдены</p>
            <?php endif; ?>
        </div>


    <?php if (have_rows('content')): ?>
        <?php while (have_rows('content')):
            the_row(); ?>
            <section
                class="m-5 mt-5 p-6 md:p-8 bg-white border border-red-600 rounded-lg shadow-lg leading-relaxed text-red-600">
                <?php echo wp_kses_post(get_sub_field('descrep')); ?>
            </section>
        <?php endwhile; ?>
    <?php endif; ?>




</main>

<?php get_footer(); ?>