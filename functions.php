<?php

// Подключение модуля защиты контактов от Google
require_once get_template_directory() . '/security-contacts.php'; 
// Если файл положили в папку inc, то: . '/inc/security-contacts.php';

add_action('wp_enqueue_scripts', function () {
    // 1️⃣ Всегда: Tailwind и ваш основной стиль
    wp_enqueue_style(
        'tailwind',
        get_template_directory_uri() . '/assets/css/output.css',
        [],
        null,
        'all'
    );
    wp_enqueue_style(
        'anketa-card',
        get_template_directory_uri() . '/assets/css/anketa-card.css',
        [],
        null,
        'all'
    );
    wp_enqueue_style(
        'cards',
        get_template_directory_uri() . '/assets/css/cards.css',
        [],
        null,
        'all'
    );
    wp_enqueue_style(
        'style',
        get_template_directory_uri() . '/style.css',
        ['tailwind'],
        null,
        'all'
    );

    // Предзагрузка Tailwind
    echo '<link rel="preload" href="'
        . get_template_directory_uri()
        . '/assets/css/output.css" as="style">';

    // Ваш основной скрипт (если нужен везде)
    wp_enqueue_script(
        'smain-js',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        null,
        true
    );


    // Swiper CSS
    wp_enqueue_style(
        'swiper-css',
        get_template_directory_uri() . '/assets/css/swiper-bundle.min.css',
        [],
        null,
        'all'
    );
    // Swiper JS (defer)
    wp_enqueue_script(
        'swiper-js',
        get_template_directory_uri() . '/assets/js/swiper-bundle.min.js',
        [],
        null,
        true
    );
    wp_script_add_data('swiper-js', 'defer', true);
}, 1);


// 1.1. Подключаем наш фронтенд-скрипт и даём ему параметры

add_action('template_redirect', function () {
    ob_start(function ($buffer) {
        // Удаляем блок <script type="speculationrules">...</script>
        return preg_replace(
            '/<script[^>]*type="speculationrules"[^>]*>.*?<\/script>/is',
            '',
            $buffer
        );
    });
});

add_theme_support('post-thumbnails');
add_theme_support('title-tag');
add_theme_support('custom-logo');

function remove_unused_scripts()
{
    wp_dequeue_script('jquery'); // Отключаем jQuery, если не нужен
}
add_action('wp_enqueue_scripts', 'remove_unused_scripts', 100);


function disable_classic_theme_styles()
{
    remove_action('wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles');
}
add_action('wp_enqueue_scripts', 'disable_classic_theme_styles', 1);

remove_action('wp_head', 'wp_generator');
// Удаление генерации ссылок на API
remove_action('wp_head', 'rest_output_link_wp_head', 10);
remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
remove_action('template_redirect', 'rest_output_link_header', 11, 0);
// Удаление RSD ссылки
remove_action('wp_head', 'rsd_link');
// Удаление shortlink
remove_action('wp_head', 'wp_shortlink_wp_head', 10);
remove_action('template_redirect', 'wp_shortlink_header', 11);
// Удаление oEmbed ссылок
remove_action('wp_head', 'wp_oembed_add_host_js');
// Удаление meta-тегов, связанных с Windows Tiles
remove_action('wp_head', 'wp_site_icon', 99);
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');


function move_jquery_to_footer()
{
    if (!is_admin()) {
        wp_deregister_script('jquery'); // Отключаем стандартное подключение
        wp_register_script(
            'jquery',
            includes_url('/js/jquery/jquery.min.js'),
            array(),
            null,
            true // Подключаем в футере
        );
        wp_enqueue_script('jquery');
    }
}
add_action('wp_enqueue_scripts', 'move_jquery_to_footer');

function disable_global_styles()
{
    wp_dequeue_style('global-styles'); // Отключаем стили
    wp_dequeue_style('wp-block-library'); // Отключаем базовые стили блоков
    wp_dequeue_style('wp-block-library-theme'); // Отключаем стили темы
}
add_action('wp_enqueue_scripts', 'disable_global_styles', 100);

function remove_block_css()
{
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('wc-blocks-style'); // Для WooCommerce
}
add_action('wp_enqueue_scripts', 'remove_block_css', 100);

add_filter('use_block_editor_for_post', '__return_false', 10);
add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('global-styles');
}, 100);


function custom_contact_settings($wp_customize)
{
    // Раздел "Контактные данные"
    $wp_customize->add_section('contact_section', [
        'title'       => __('Контактные данные', 'textdomain'),
        // Обновили описание
        'description' => __('Здесь вы можете настроить контактные данные. Telegram и WhatsApp поддерживают до 5 вариантов.', 'textdomain'),
        'priority'    => 30,
    ]);

    // Телефон
    $wp_customize->add_setting('contact_number', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('contact_number', [
        'label'       => __('Телефон', 'textdomain'),
        'section'     => 'contact_section',
        'type'        => 'text',
        'description' => __('Введите основной номер, например: +7 999 123-45-67', 'textdomain'),
    ]);

    $wp_customize->add_setting('contact_telegram_channel', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control('contact_telegram_channel', array(
        'label'       => __('Telegram-канал', 'textdomain'),
        'section'     => 'contact_section',
        'type'        => 'text',
        'description' => __('Введите username (без @) или полный URL.', 'textdomain'),
    ));

    // === Telegram (5 вариантов с особым заголовком для 5-го) ===
    for ($i = 1; $i <= 5; $i++) {
        // Обычный заголовок или специальный для 5-го элемента
        $label = ($i === 5)
            ? __('Telegram (для Дешевых анкет и страницы)', 'textdomain')
            : __("Telegram #$i", 'textdomain');

        $wp_customize->add_setting("contact_telegram_$i", [
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        $wp_customize->add_control("contact_telegram_$i", [
            'label'       => $label,
            'section'     => 'contact_section',
            'type'        => 'text',
            'description' => __('Введите username без @ или ссылку, например: t.me/username', 'textdomain'),
        ]);
    }

    // === WhatsApp (5 вариантов с особым заголовком для 5-го) ===
    for ($i = 1; $i <= 5; $i++) {
        // Обычный заголовок или специальный для 5-го элемента
        $label = ($i === 5)
            ? __('WhatsApp (для Дешевых анкет и страницы)', 'textdomain')
            : __("WhatsApp #$i", 'textdomain');

        $wp_customize->add_setting("contact_whatsapp_$i", [
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        $wp_customize->add_control("contact_whatsapp_$i", [
            'label'       => $label,
            'section'     => 'contact_section',
            'type'        => 'text',
            'description' => __('Введите номер WhatsApp в формате: +79991234567', 'textdomain'),
        ]);
    }

    // Email
    $wp_customize->add_setting('contact_email', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('contact_email', [
        'label'       => __('Email', 'textdomain'),
        'section'     => 'contact_section',
        'type'        => 'text',
        'description' => __('Введите вашу почту', 'textdomain'),
    ]);

    // Agency
    $wp_customize->add_setting('contact_agency', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('contact_agency', [
        'label'       => __('Agency', 'textdomain'),
        'section'     => 'contact_section',
        'type'        => 'text',
        'description' => __('Введите название агентства', 'textdomain'),
    ]);

    // Street
    $wp_customize->add_setting('contact_street', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('contact_street', [
        'label'       => __('Street', 'textdomain'),
        'section'     => 'contact_section',
        'type'        => 'text',
        'description' => __('Введите адрес агентства', 'textdomain'),
    ]);
}
add_action('customize_register', 'custom_contact_settings');

function custom_model_card_settings($wp_customize)
{
    $wp_customize->add_section('model_card_section', [
        'title'       => __('Анкеты: карточка', 'textdomain'),
        'description' => __('Настройки отображения карточек анкет.', 'textdomain'),
        'priority'    => 31,
    ]);

    $wp_customize->add_setting('model_card_desc_length', [
        'default'           => 220,
        'sanitize_callback' => static function ($value) {
            $val = (int) $value;
            if ($val < 160) $val = 160;
            if ($val > 260) $val = 260;
            return $val;
        },
    ]);

    $wp_customize->add_control('model_card_desc_length', [
        'label'       => __('Длина описания в карточке (160–260)', 'textdomain'),
        'section'     => 'model_card_section',
        'type'        => 'number',
        'input_attrs' => [
            'min'  => 160,
            'max'  => 260,
            'step' => 10,
        ],
    ]);
}
add_action('customize_register', 'custom_model_card_settings');

function get_random_contacts()
{
    $i = rand(1, 4); // случайный вариант от 1 до 4

    $tg  = get_theme_mod("contact_telegram_$i");
    $wa  = get_theme_mod("contact_whatsapp_$i");
    $tel = get_theme_mod("contact_number");
    $mail = get_theme_mod("contact_email");

    return [
        'telegram' => $tg,
        'whatsapp' => $wa,
        'number'   => $tel,
        'email'    => $mail,
        'agency'   => get_theme_mod('contact_agency'),
        'street'   => get_theme_mod('contact_street'),
        'index'    => $i, // для отладки или аналитики
    ];
}


function get_contact_whatsapp()
{
    $number = get_theme_mod('contact_number');
    if ($number) {
        // Удаляем "+" в начале, если он есть
        $number = ltrim($number, '+');
        return 'https://wa.me/' . esc_attr($number);
    }
}
add_shortcode('whatsapp_button', 'get_contact_whatsapp');


function get_contact_telegram()
{
    $telegram = get_theme_mod('contact_telegram');
    if ($telegram) {
        return 'https://t.me/' . esc_attr($telegram);
    }
}
add_shortcode('telegram_button', 'get_contact_telegram');


add_filter('redirect_canonical', function ($redirect_url, $requested_url) {
    if (strpos($requested_url, 'sitemap.xml') !== false) {
        return false; // отключаем редирект именно для sitemap.xml
    }
    return $redirect_url;
}, 10, 2);



remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('template_redirect', 'rest_output_link_header', 11);
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
remove_action('wp_head', 'rel_canonical');
add_filter('rest_enabled', '__return_false');
add_filter('rest_jsonp_enabled', '__return_false');
remove_action('xmlrpc_rsd_apis', 'rest_output_rsd');
remove_action('wp_head', 'rest_output_link_wp_head', 10);
remove_action('template_redirect', 'rest_output_link_header', 11);
remove_action('auth_cookie_malformed', 'rest_cookie_collect_status');
remove_action('auth_cookie_expired', 'rest_cookie_collect_status');
remove_action('auth_cookie_bad_username', 'rest_cookie_collect_status');
remove_action('auth_cookie_bad_hash', 'rest_cookie_collect_status');
remove_action('auth_cookie_valid', 'rest_cookie_collect_status');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_oembed_add_host_js');
remove_action('template_redirect', 'rest_output_link_header', 11);
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', '_wp_render_title_tag', 1);
add_filter('xmlrpc_enabled', '__return_false');

// Полное отключение всех RSS-лент
function disable_feed()
{
    wp_die(__('RSS-фиды отключены. Пожалуйста, заходите напрямую на сайт.'));
}
add_action('do_feed', 'disable_feed', 1);
add_action('do_feed_rdf', 'disable_feed', 1);
add_action('do_feed_rss', 'disable_feed', 1);
add_action('do_feed_rss2', 'disable_feed', 1);
add_action('do_feed_atom', 'disable_feed', 1);
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'feed_links_extra', 3);


add_action('pre_get_posts', function ($query) {
    if (!is_admin() && $query->is_main_query() && is_tax()) {
        $slug = get_query_var('term');
        $page = get_page_by_path($slug, OBJECT, 'page');

        if ($page) {
            $query->set('post_type', 'page');
            $query->set('page_id', $page->ID);
            $query->is_page = true;
            $query->is_tax = false;
            $query->is_archive = false;
            $query->is_singular = true;
            $query->is_404 = false;
        }
    }
});



// Отключаем meta name="robots", который WP добавляет через wp_head
add_action('init', function () {
    // В ядре он вешается как add_action( 'wp_head', 'wp_robots', 1 )
    remove_action('wp_head', 'wp_robots', 1);
}, 11);

// Глобальный массив для сбора моделей
$GLOBALS['site_ldjson_models'] = [];

function site_ldjson_collect_model($model)
{
    if (!empty($model)) {
        $GLOBALS['site_ldjson_models'][] = $model;
    }
}

add_image_size('model_card', 334, 500, true); // hard crop

add_filter('acf/settings/show_updates', '__return_false');
