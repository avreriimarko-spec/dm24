<!-- 
Template Name: Page Layout
-->

<?php get_header();
;
$img = get_field("img");
$h1 = get_field('h1');
$h2 = get_field('h2');
$p = get_field('p');
$bread = get_the_title();
$html = get_field('html');
$title_img = get_field('title_img');
$title_alt = get_field('alt_img');
$models_descr = get_field('models_descr');

?>

<main class="w-full md:w-2/3 mx-auto p-2 mt-6">
    <!-- BANNER -->
    <section class="bg-gradient-to-r from-red-900 to-black border border-red-600 rounded-xl shadow-lg p-8">
        <div class="text-white text-center">

            <!-- Контент -->
            <div class="max-w-3xl mx-auto mt-6">
                <h1 class="text-3xl md:text-4xl font-bold uppercase tracking-wide text-white">
                    <?php echo esc_html($h1); ?>
                </h1>
                <h2 class="text-xl md:text-2xl font-medium text-gray-300 mt-2">
                    <?php echo esc_html($h2); ?>
                </h2>
                <p class="text-sm md:text-lg text-gray-400 mt-4 leading-relaxed">
                    <?php echo esc_html($p); ?>
                </p>
            </div>
        </div>
    </section>




    <nav
        class="text-white text-sm mb-6 mt-4 p-4 bg-gradient-to-r from-red-900 to-black rounded-xl shadow-lg border border-red-600">
        <ul class="flex items-center space-x-3">
            <li>
                <a href="https://escortminsk.com/" class="text-white transition-colors duration-300 font-medium flex items-center">
                    <svg class="w-5 h-5 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2L2 8h3v8h4v-4h4v4h4V8h3L10 2z"></path>
                    </svg>
                    Главная
                </a>
            </li>
            <li class="text-gray-500">›</li>
            <li class="text-lg font-semibold text-white"><?php echo esc_html($bread); ?></li>
        </ul>
    </nav>



    <!-- MODELS -->
    <h2 class="text-red-600 text-[40px] text-center mt-14 mb-2"><?php echo esc_html($models_descr); ?> </h2>

    <section>
        <div class="girls-wrapper flex flex-wrap justify-center gap-6 m-auto text-white">
            <?php
            $current_url = trim($_SERVER['REQUEST_URI'], '/');
            $show_all_models = ($current_url === 'modeli');

            $taxonomies = array('kategoriya', 'filter');
            $tax_query = array();

            if (!$show_all_models) {
                $found_taxonomy = '';
                foreach ($taxonomies as $taxonomy) {
                    if (term_exists($current_url, $taxonomy)) {
                        $found_taxonomy = $taxonomy;
                        break;
                    }
                }

                if ($found_taxonomy) {
                    $tax_query[] = array(
                        'taxonomy' => $found_taxonomy,
                        'field' => 'slug',
                        'terms' => $current_url,
                    );
                }
            }

            $paged = isset($_POST['paged']) ? (int) $_POST['paged'] : 1; // Получаем страницу через POST
            
            $args = array(
                'post_type' => 'girls',
                'posts_per_page' => 12,
                'orderby' => 'date',
                'order' => 'DESC',
                'paged' => $paged,
            );

            if (!empty($tax_query)) {
                $args['tax_query'] = $tax_query;
            }

            $girls = new WP_Query($args);

            if ($girls->have_posts()):
                while ($girls->have_posts()):
                    $girls->the_post();
                    $girl_name = get_the_title();
                    $girl_age = get_field('age');
                    $girl_height = get_field('height');
                    $girl_weight = get_field('weight');
                    $girl_price = get_field('price', $model->ID);
                    // Приводим значение к числу, если это необходимо
                    $girl_price = floatval($girl_price);
                    // Умножаем на 2
                    $girl_price *= 2;
                    $img = get_field('photo');
                    $model_slug = get_post_field('post_name');
                    $model_url = home_url("/{$model_slug}");
                    $is_vip = mt_rand(0, 1);
                    $likes = rand(10, 20);
                    $views = rand(1000, 5000);
                    ?>

                    <div
                        class="girls-item w-[350px] bg-white text-red-600 border border-red-600 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition transform hover:scale-105 relative">
                        <a href="<?php echo esc_url($model_url); ?>/" class="block">

                            <!-- Фото модели -->
                            <div class="relative w-full h-[400px]">
                                <img src="<?php echo esc_attr($img); ?>" alt="<?php echo esc_html($girl_name); ?>"
                                    class="w-full h-full object-cover rounded-xl transition duration-300 group-hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent">
                                </div>

                                <!-- VIP-метка -->
                                <?php if ($is_vip): ?>
                                <div
                                    class="absolute top-3 left-3 bg-red-600 text-white text-sm font-semibold px-3 py-1 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="20" height="20" fill="currentColor">
                                        <path d="M288 0c-19 0-34 15-34 34v68l-79-53c-14-9-33-4-41 11L4 186c-4 8-5 18-1 27s11 15 20 18l86 23-22 85c-2 10 1 21 8 28s17 12 27 12h352c10 0 20-4 27-12s10-18 8-28l-22-85 86-23c9-3 17-10 20-18s3-19-1-27l-130-126c-8-7-27-13-41-11l-79 53V34c0-19-15-34-34-34zM96 400c-18 0-32 14-32 32v32c0 18 14 32 32 32h384c18 0 32-14 32-32v-32c0-18-14-32-32-32H96z"/>
                                    </svg>
                                    VIP
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Информация -->
                            <div class="p-4 text-red-600 text-lg border-t border-red-600">
                                <h3 class="text-[26px] font-bold ml-4 text-red-500"><?php echo esc_html($girl_name); ?></h3>
                                <p class="flex items-center gap-2 text-gray-700 text-sm mt-1">
                                    <!-- Иконка локации (map-marker-alt) -->
                                    <svg class="w-4 h-4 text-red-400" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Ленинский пр-т (+2ст)
                                </p>
                                <div class="mt-3 space-y-1">
                                    <p class="flex items-center gap-2 text-[18px]">
                                        <!-- Иконка дня рождения (birthday-cake) -->
                                        <svg class="w-5 h-5 text-red-400" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M6 3a1 1 0 011-1h6a1 1 0 011 1v1h2a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2h2V3zm6 2H8v1h4V5zm4 3H4v9h12V8z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="font-medium">Возраст:</span> <?php echo esc_html($girl_age ?: '-'); ?>
                                    </p>
                                    <p class="flex items-center gap-2 text-[18px]">
                                        <!-- Иконка роста (ruler-vertical) -->
                                        <svg class="w-5 h-5 text-red-400" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M3 3a1 1 0 011-1h12a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V3zm2 0v14h10V3H5zm4 2a1 1 0 00-2 0v10a1 1 0 102 0V5z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="font-medium">Рост:</span> <?php echo esc_html($girl_height ?: '-'); ?> см
                                    </p>
                                    <p class="flex items-center gap-2 text-[18px]">
                                        <!-- Иконка веса (weight) -->
                                        <svg class="w-5 h-5 text-red-400" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1zm-5 8.274l-.818 2.552a2 2 0 00.57 1.952A2 2 0 005 13a2 2 0 001.248-.222 2 2 0 00.57-1.952L6 10.274zm8 0l-.818 2.552a2 2 0 00.57 1.952A2 2 0 0015 13a2 2 0 001.248-.222 2 2 0 00.57-1.952L14 10.274z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="font-medium">Вес:</span> <?php echo esc_html($girl_weight ?: '-'); ?> кг
                                    </p>
                                    <p class="flex items-center gap-2 text-[18px]">
                                        <!-- Иконка сердца (heart) -->
                                        <svg class="w-5 h-5 text-red-400" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="font-medium">Грудь:</span> <?php echo esc_html($girl_bust ?: '-'); ?>
                                    </p>
                                    <p class="flex items-center gap-2 text-[18px]">
                                    <svg  version="1.1"  width="20" height="20" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 380.052 380.052" style="enable-background:new 0 0 380.052 380.052;" xml:space="preserve"><g><path style="fill:#f87171" d="M0,297.742c0,19.037,15.241,34.465,34.042,34.465h311.967c18.801,0,34.042-15.428,34.042-34.465 v-153.76H0V297.742z M254.254,175.407h81.497v24.808h-81.497V175.407z M254.254,217.927h81.497v24.808h-81.497V217.927z M254.254,260.456h81.497v24.808h-81.497V260.456z"/><path style="fill:#f87171" d="M0,82.31v26.645h380.043V82.31c0-19.045-15.241-34.465-34.042-34.465H34.042 C15.241,47.845,0,63.265,0,82.31z"/></g></svg>
                                        <span class="font-medium">Цена:</span>
                                        <?php echo esc_html($girl_price ?: '-'); ?> Br
                                    </p>
                                </div>

                                <!-- Лайки и просмотры -->
                                <div class="flex justify-between items-center text-center text-gray-400 text-sm">
                                    <p class="flex items-center gap-1">
                                        <!-- Иконка сердца (heart) -->
                                        <svg class="w-4 h-4 text-red-400" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <?php echo esc_html($likes); ?>
                                    </p>
                                    <p class="flex items-center gap-1">
                                        <!-- Иконка глаза (eye) -->
                                        <svg class="w-4 h-4 text-red-400" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg">
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
                endwhile;
                wp_reset_postdata();
            else:
                echo '<p class="text-center text-lg text-red-500">Модели не найдены</p>';
            endif;
            ?>
        </div>

        <!-- Пагинация через POST без изменения URL -->
        <div class="pagination mx-auto mt-7 flex justify-center flex-wrap space-x-2">
            <?php if ($girls->max_num_pages > 1): ?>
                <form method="post" class="flex space-x-2">
                    <?php for ($i = 1; $i <= $girls->max_num_pages; $i++): ?>
                        <button type="submit" name="paged" value="<?php echo $i; ?>"
                            class="border border-red-600 text-black rounded-lg px-4 py-2 text-center <?php echo ($i == $paged) ? 'bg-red-600 text-white' : 'hover:bg-red-600'; ?>">
                            <?php echo $i; ?>
                        </button>
                    <?php endfor; ?>
                </form>
            <?php endif; ?>
        </div>
    </section>



<?php 

global $post;
$page_id = $post->ID;
$acf_data = get_field('content', $page_id);

if (!empty($acf_data)): ?>
    <?php foreach ($acf_data as $row): ?>
        <section class="mt-5 p-6 md:p-8 bg-white text-red-600 border border-red-600 rounded-lg shadow-lg leading-relaxed">
            <?php echo wp_kses_post($row['descrep']); ?>
        </section>
    <?php endforeach; ?>
<?php endif; ?>




</main>

<?php get_footer(); ?>