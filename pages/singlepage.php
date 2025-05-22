<?php
/* 
Template Name: Single Page
Template Post Type: girls
 */
get_header();

$telegram = get_theme_mod('contact_telegram');
$whatsapp = get_theme_mod('contact_whatsapp');
$number = get_theme_mod('contact_number');
function get_terms_names($taxonomy)
{
    $terms = get_the_terms(get_the_ID(), $taxonomy);
    if ($terms && !is_wp_error($terms)) {
        return join(', ', wp_list_pluck($terms, 'name'));
    }
    return 'Не указано';
}

$girl_name = get_the_title($model_id);
?>

<main class="w-full md:w-2/3 mx-auto text-center mt-4">
    <nav class="bg-white text-red-600 backdrop-blur-lg p-4 rounded-xl shadow-xl border border-red-700 mb-4">
        <ul class="flex items-center space-x-3 text-white text-base">
            <li>
                <a href="https://escortminsk.com/"
                    class="flex items-center gap-2 text-red-600 hover:text-[#E50914] transition-colors duration-300 font-medium">
                    <svg class="w-5 h-5 text-[#E50914]" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 12l9-9 9 9M4 10v10a2 2 0 002 2h12a2 2 0 002-2V10"></path>
                    </svg>
                    Главная
                </a>
            </li>
            <li class="text-black">›</li>
            <li class="font-semibold text-lg text-red-600"><?php echo esc_html($girl_name); ?></li>
        </ul>
    </nav>



    <div class="p-6 md:p-8 bg-white backdrop-blur-lg rounded-xl shadow-xl border border-red-700">
        <h1 class="text-red-600 text-3xl font-extrabold mb-6 inline-block border-b-4 border-[#E50914] pb-2">
            <?php echo esc_html($girl_name); ?>
        </h1>

        <?php
        $main_photo = get_field('photo');
        $gallery = get_field('gallery');
        $age = get_field('age');
        $height = get_field('height');
        $weight = get_field('weight');
        $price = get_field('price');
        $bust = get_field('bust');
        $description = get_field('description');

        $price = str_replace(' ', '', $price); // Убираем пробелы
        $hour_price = floatval($price); // Преобразуем в число

        $hour_options = [
            2 => 2,
            3 => 3,
            5 => 4,
            7 => 5,
        ];


        ?>

        <?php if ($main_photo || $gallery): ?>
            <div class="flex flex-col items-center lg:flex-row lg:items-start gap-4 w-full max-w-screen-md mx-auto px-4">

                <!-- Галерея -->
                <div class="swiper swiper-container w-full max-w-md rounded-lg overflow-hidden">
                    <div class="swiper-wrapper">
                        <?php if ($main_photo): ?>
                            <div class="swiper-slide flex justify-center">
                                <img class="object-cover w-full h-auto max-h-[500px] rounded-lg shadow-md transition-all duration-300 hover:scale-105"
                                    src="<?php echo esc_url($main_photo); ?>" title="Модель <?php echo esc_html($girl_name); ?>"
                                    alt="<?php echo esc_html($girl_name); ?>">
                            </div>
                        <?php endif; ?>
                        <?php if ($gallery): ?>
                            <?php foreach ($gallery as $image): ?>
                                <div class="swiper-slide flex justify-center">
                                    <img class="object-cover w-full h-auto max-h-[500px] rounded-lg shadow-md transition-all duration-300 hover:scale-105"
                                        src="<?php echo esc_url($image['url']); ?>"
                                        title="Модель <?php echo esc_html($girl_name); ?>"
                                        alt="<?php echo esc_html($girl_name); ?>">
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Навигация -->
                    <div
                        class="swiper-button-next transition-all duration-300 hover:scale-110 !text-red-600 hover:text-[#E50914]">
                    </div>
                    <div
                        class="swiper-button-prev transition-all duration-300 hover:scale-110 !text-red-600 hover:text-[#E50914]">
                    </div>
                    <div class="swiper-pagination !text-red-600"></div>
                </div>

                <!-- Блок с Telegram и WhatsApp -->

            </div>
        <?php endif; ?>

        <div class="mt-4 w-full md:w-2/3 mx-auto">
            <table class="w-full border-collapse border border-red-600">
                <thead>
                    <tr class="bg-red-100">
                        <th class="px-4 py-2 border border-red-600 text-center text-red-600">Цена</th>
                    </tr>
                </thead>
                <tbody class="flex flex-col md:flex-row justify-center">
                    <?php foreach ($hour_options as $hours => $multiplier) {
                        $total = $hour_price * $multiplier;
                        // Проверка для объединения 2 и 3 часов в одну строку
                        if ($hours == 2 || $hours == 3) {
                            echo '<tr class="border border-red-600 flex flex-col w-full">';
                            echo '<td class="px-4 py-2"><strong class="text-[#E50914] text-[18px]">Цена за ' . $hours . ' часа</strong></td>';
                            echo '<td class="px-4 py-2 text-red-500">' . number_format($total, 0, '', ' ') . ' Br</td>';
                            echo '</tr>';
                        } else {
                            // Для остальных случаев
                            echo '<tr class="border border-red-600 flex flex-col w-full">';
                            echo '<td class="px-4 py-2"><strong class="text-[#E50914] text-[18px]">Цена за ' . $hours . ' часа</strong></td>';
                            echo '<td class="px-4 py-2 text-red-500">' . number_format($total, 0, '', ' ') . ' Br</td>';
                            echo '</tr>';
                        }
                    } ?>
                </tbody>
            </table>
        </div>

        <div class="flex flex-col md:flex-row bg-white gap-4 text-red-500 text-lg w-full justify-center mt-2 p-6 rounded-xl shadow-lg">

            <div class="border border-red-500 rounded-lg p-2 md:w-[170px] flex justify-center flex-col">
                <p><strong class="text-[#E50914]">Возраст:</strong> <?php echo esc_html($age); ?></p>
                <p><strong class="text-[#E50914]">Рост:</strong> <?php echo esc_html($height); ?> см</p>
            </div>

            <div class="border border-red-500 rounded-lg p-2 md:w-[170px] flex justify-center flex-col">
                <p><strong class="text-[#E50914] ">Вес:</strong> <?php echo esc_html($weight); ?> кг</p>
                <p><strong class="text-[#E50914]">Грудь:</strong> <?php echo esc_html($bust); ?></p>                   
            </div>

            <div class="flex flex-col gap-2 p-2 items-center border border-red-500 rounded-lg text-white shadow-md">
                    <h3 class="text-lg font-semibold text-red-600">Контакты для связи</h3>

                    <a href="https://t.me/<?php echo esc_html($telegram); ?>" target="_blank"
                        class="flex items-center gap-2 text-red-500 transition-colors duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            viewBox="0 0 16 16">
                            <path
                                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.287 5.906q-1.168.486-4.666 2.01-.567.225-.595.442c-.03.243.275.339.69.47l.175.055c.408.133.958.288 1.243.294q.39.01.868-.32 3.269-2.206 3.374-2.23c.05-.012.12-.026.166.016s.042.12.037.141c-.03.129-1.227 1.241-1.846 1.817-.193.18-.33.307-.358.336a8 8 0 0 1-.188.186c-.38.366-.664.64.015 1.088.327.216.589.393.85.571.284.194.568.387.936.629q.14.092.27.187c.331.236.63.448.997.414.214-.02.435-.22.547-.82.265-1.417.786-4.486.906-5.751a1.4 1.4 0 0 0-.013-.315.34.34 0 0 0-.114-.217.53.53 0 0 0-.31-.093c-.3.005-.763.166-2.984 1.09" />
                        </svg>
                        <span>Telegram</span>
                    </a>

                    <a href="https://wa.me/<?php echo esc_html($whatsapp); ?>" target="_blank"
                        class="flex items-center gap-2 text-red-500 transition-colors duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            viewBox="0 0 16 16">
                            <path
                                d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                        </svg>
                        <span>WhatsApp</span>
                    </a>
                </div>
            </div>

        </div>

    <div class="text-[#E50914]">        
        <p class="mt-6 text-center md:text-left text-[#E50914]">
    <strong class="text-[#E50914]">О себе:</strong>
    <?php 
        // Получаем текст из поля "content"
        $description = get_field('description'); 
        if ($description):
            echo wp_kses_post($description); // Выводим HTML безопасно
        endif;
    ?>
</p></div>



    </div>


    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            new Swiper(".swiper-container", {
                loop: true,
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
            });
        });
    </script>


    <div class="flex flex-col md:flex-row gap-6 justify-center mt-6 p-4">

        <!-- ������ Категории -->
        <div
            class="bg-gradient-to-r from-black via-[#330000] to-[#660000] border border-red-600 p-5 rounded-xl shadow-lg hover:shadow-xl transition duration-300 w-full md:w-1/2">
            <div class="flex items-center gap-3 mb-3">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20" fill="currentColor" class="text-red-500 text-xl">
    <path d="M464 128H272l-32-32H48C22 96 0 118 0 144v224c0 26 22 48 48 48h416c26 0 48-22 48-48V176c0-26-22-48-48-48z"/>
</svg>

                <p class="font-semibold text-white text-lg">Категории</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php
                $categories = get_the_terms(get_the_ID(), 'intim-uslugi');
                if ($categories && !is_wp_error($categories)) {
                    foreach ($categories as $category) {
                        $category_link = get_term_link($category);
                        if (!is_wp_error($category_link)) {
                            echo '<a href="/' . esc_html($category->slug) . '/" class="bg-red-800 text-white px-3 py-1 rounded-full text-sm hover:bg-red-600 transition duration-300">'
                                . esc_html($category->name) . '</a>';
                        }
                    }
                } else {
                    echo '<span class="text-gray-400">Не указано</span>';
                }
                ?>
            </div>
        </div>

        <!-- ������ Метки -->
        <div
            class="bg-gradient-to-r from-black via-[#330000] to-[#660000] border border-red-600 p-5 rounded-xl shadow-lg hover:shadow-xl transition duration-300 w-full md:w-1/2">
            <div class="flex items-center gap-3 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" width="20" height="20" fill="currentColor" class="text-red-500 text-xl">
    <path d="M497.9 0H320c-8.5 0-16.6 3.4-22.6 9.4L9.4 297.4c-12.5 12.5-12.5 32.8 0 45.3l192 192c12.5 12.5 32.8 12.5 45.3 0l288-288c6-6 9.4-14.1 9.4-22.6V32c0-17.7-14.3-32-32-32zm-41.1 128a32 32 0 1 1 0-64 32 32 0 1 1 0 64zm181.3 112-32-32c-6.2-6.2-16.4-6.2-22.6 0L308.7 482.3c-6.2 6.2-6.2 16.4 0 22.6l32 32c6.2 6.2 16.4 6.2 22.6 0l274.4-274.4c6.2-6.2 6.2-16.4 0-22.6z"/>
</svg>

                <p class="font-semibold text-white text-lg">Метки</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php
                $tags = get_the_terms(get_the_ID(), 'filter');
                if ($tags && !is_wp_error($tags)) {
                    foreach ($tags as $tag) {
                        $tag_link = get_term_link($tag);
                        if (!is_wp_error($tag_link)) {
                            echo '<a href="/' . esc_html($tag->slug) . '/" class="bg-red-800 text-white px-3 py-1 rounded-full text-sm hover:bg-red-600 transition duration-300">'
                                . esc_html($tag->name) . '</a>';
                        }
                    }
                } else {
                    echo '<span class="text-gray-400">Не указано</span>';
                }
                ?>
            </div>
        </div>

    </div>





    <div class="max-w-screen-xl mx-auto px-4 mt-10">
        <!-- Заголовок -->
        <h2
            class="text-4xl font-bold text-center text-white bg-gradient-to-r from-red-700 to-black/80 p-6 rounded-xl shadow-lg border-b-4 border-red-500">
            Другие эскорт модели, похожие на <span class="text-red-400"><?php echo esc_html($girl_name); ?></span>
        </h2>

        <!-- Грид для карточек -->
        <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 mt-8">
            <?php
            $args = [
                'post_type' => 'girls',
                'posts_per_page' => 3,
                'orderby' => 'date',
                'order' => 'DESC',
                'post__not_in' => [get_the_ID()],
            ];

            $girls = new WP_Query($args);
            $list = $girls->posts;
            shuffle($list);

            if (!empty($list)):
                foreach ($list as $model):
                    $girl_name = get_the_title($model->ID);
                    $girl_age = get_field('age', $model->ID) ?: '-';
                    $girl_height = get_field('height', $model->ID) ?: '-';
                    $girl_weight = get_field('weight', $model->ID) ?: '-';
                    $girl_views = mt_rand(1000, 4000); // Генерируем случайные просмотры
                    $girl_likes = mt_rand(5, 20); // Генерируем случайные лайки
                    $girl_bust = get_field('bust', $model->ID) ?: '-';
                    $img = get_field('photo', $model->ID) ?: 'default.jpg';
                    $model_url = home_url("/{$model->post_name}/");
                    $is_vip = mt_rand(0, 1); // Рандомный VIP
                    ?>

                    <!-- Карточка модели -->
                    <div
                        class="bg-white text-red-500 border border-red-600 rounded-xl shadow-lg overflow-hidden relative transition transform hover:scale-105 hover:shadow-xl">
                        <a href="<?php echo esc_url($model_url); ?>" class="block relative">

                            <!-- VIP-значок -->
                            <?php if ($is_vip): ?>
                                <div
                                    class="absolute top-3 left-3 bg-red-600 text-white text-sm font-semibold px-3 py-1 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="20" height="20" fill="currentColor">
                                        <path d="M288 0c-19 0-34 15-34 34v68l-79-53c-14-9-33-4-41 11L4 186c-4 8-5 18-1 27s11 15 20 18l86 23-22 85c-2 10 1 21 8 28s17 12 27 12h352c10 0 20-4 27-12s10-18 8-28l-22-85 86-23c9-3 17-10 20-18s3-19-1-27l-130-126c-8-7-27-13-41-11l-79 53V34c0-19-15-34-34-34zM96 400c-18 0-32 14-32 32v32c0 18 14 32 32 32h384c18 0 32-14 32-32v-32c0-18-14-32-32-32H96z"/>
                                    </svg>
                                    VIP
                                </div>
                            <?php endif; ?>

                            <!-- Фото модели -->
                            <div class="relative w-full h-[450px] overflow-hidden">
                                <img src="<?php echo esc_attr($img); ?>" alt="<?php echo esc_html($girl_name); ?>"
                                    class="w-full h-full object-cover rounded-t-xl transition duration-300 hover:scale-105">
                            </div>

                            <!-- Информация о модели -->
                            <div class="p-4 text-red-600 text-lg border-t border-red-600">
                                <h3 class="text-xl font-bold text-red-500 text-[26px]"><?php echo esc_html($girl_name); ?></h3>
                                <p class="flex items-center gap-2 text-gray-400 text-sm mt-1">
                                                                        <svg class="text-red-400" fill="currentColor" width="20" height="20" viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                            clip-rule="evenodd"></path>
                                    </svg> Ленинский пр-т (+2ст)
                                </p>
                                <div class="mt-3 space-y-1">
                                    <p class="flex items-center gap-2 text-[18px]">
                                        <!-- Иконка дня рождения (birthday-cake) -->
                                        <svg class="w-5 h-5 text-red-400" fill="currentColor" width="20" height="20"
                                            viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M6 3a1 1 0 011-1h6a1 1 0 011 1v1h2a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2h2V3zm6 2H8v1h4V5zm4 3H4v9h12V8z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="font-medium">Возраст:</span> <?php echo esc_html($girl_age ?: '-'); ?>
                                    </p>
                                    <p class="flex items-center gap-2 text-[18px]">
                                        <!-- Иконка роста (ruler-vertical) -->
                                        <svg class="w-5 h-5 text-red-400" fill="currentColor" width="20" height="20"
                                            viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M3 3a1 1 0 011-1h12a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V3zm2 0v14h10V3H5zm4 2a1 1 0 00-2 0v10a1 1 0 102 0V5z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="font-medium">Рост:</span> <?php echo esc_html($girl_height ?: '-'); ?> см
                                    </p>
                                    <p class="flex items-center gap-2 text-[18px]">
                                        <!-- Иконка веса (weight) -->
                                        <svg class="w-5 h-5 text-red-400" fill="currentColor" width="20" height="20"
                                            viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1zm-5 8.274l-.818 2.552a2 2 0 00.57 1.952A2 2 0 005 13a2 2 0 001.248-.222 2 2 0 00.57-1.952L6 10.274zm8 0l-.818 2.552a2 2 0 00.57 1.952A2 2 0 0015 13a2 2 0 001.248-.222 2 2 0 00.57-1.952L14 10.274z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="font-medium">Вес:</span> <?php echo esc_html($girl_weight ?: '-'); ?> кг
                                    </p>
                                    <p class="flex items-center gap-2 text-[18px]">
                                        <!-- Иконка сердца (heart) -->
                                        <svg class="w-5 h-5 text-red-400" fill="currentColor" width="20" height="20"
                                            viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="font-medium">Грудь:</span> <?php echo esc_html($girl_bust ?: '-'); ?>
                                    </p>
                                </div>

                                <!-- Лайки и просмотры -->
                                <div class="flex justify-between items-center mt-4 text-gray-400 text-sm">
                                    <p class="flex items-center gap-1 text-[16px]">
                                                                                <svg class="w-4 h-4 text-red-400" fill="currentColor" width="20" height="20"
                                            viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                                clip-rule="evenodd"></path>
                                        </svg> <?php echo esc_html($girl_likes); ?>
                                    </p>
                                    <p class="flex items-center gap-1 text-[16px]">
                                                                                <svg class="w-4 h-4 text-red-400" fill="currentColor" width="20" height="20"
                                            viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M10 4a6 6 0 100 12 6 6 0 000-12zm0 10a4 4 0 110-8 4 4 0 010 8zm0-2a2 2 0 100-4 2 2 0 000 4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <?php echo esc_html(number_format($girl_views)); ?>
                                    </p>
                                </div>
                            </div>

                        </a>
                    </div>

                <?php endforeach;
                wp_reset_postdata();
            else: ?>
                <p class="text-center text-lg text-gray-400">Модели не найдены</p>
            <?php endif; ?>
        </div>
    </div>



</main>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        new Swiper('.swiper', {
            loop: true,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev'
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true
            }
        });
    });
</script>



<?php get_footer(); ?>