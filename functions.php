<?php

add_action('wp_enqueue_scripts', function () {

    // 1️⃣ Загружаем Tailwind ПЕРВЫМ, чтобы стили применялись сразу
    wp_enqueue_style('tailwind', get_template_directory_uri() . '/assets/css/output.css', [], null, 'all');

    // 2️⃣ Загружаем главный стиль после Tailwind, чтобы он его мог переопределять
    wp_enqueue_style('style', get_template_directory_uri() . '/style.css', ['tailwind'], null, 'all');

    // 3️⃣ Загружаем Swiper CSS
    wp_enqueue_style('swiper-css', get_template_directory_uri() . '/assets/css/swiper-bundle.min.css', [], null, 'all');

    // 4️⃣ Загружаем Alpine первым из JS и ставим defer
    wp_enqueue_script('alpine-js', get_template_directory_uri() . '/assets/js/alpine.min.js', [], null, true);
    wp_script_add_data('alpine-js', 'defer', true);

    // 5️⃣ Конфиг Alpine загружается после Alpine
    wp_enqueue_script('alpine-config', get_template_directory_uri() . '/assets/js/alpine-config.js', ['alpine-js'], null, true);

    // 6️⃣ Загружаем Swiper JS (defer, чтобы не блокировал рендеринг)
    wp_enqueue_script('swiper-js', get_template_directory_uri() . '/assets/js/swiper-bundle.min.js', [], null, true);
    wp_script_add_data('swiper-js', 'defer', true);

    // 7️⃣ Inline-скрипт для Swiper
    wp_add_inline_script('swiper-js', "
        document.addEventListener('DOMContentLoaded', function () { 
            new Swiper('.swiper', { 
                loop: true, 
                navigation: { 
                    nextEl: '.swiper-button-next', 
                    prevEl: '.swiper-button-prev' 
                } 
            }); 
        });
    ");

    // 8️⃣ Предзагрузка важных файлов (ускоряет загрузку)
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/css/output.css" as="style">';
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/js/alpine.min.js" as="script">';
    
}, 1);



add_action('template_redirect', 'force_trailing_slash_redirect');

function force_trailing_slash_redirect() {
    if (is_404() || is_admin()) return;

    $current_url = home_url(add_query_arg(array(), $GLOBALS['wp']->request));
    $has_slash = substr($_SERVER['REQUEST_URI'], -1) === '/';

    if (!is_front_page() && !is_singular('attachment') && !$has_slash) {
        wp_redirect(trailingslashit($current_url), 301);
        exit;
    }
}


add_theme_support('post-thumbnails');
add_theme_support('title-tag');
add_theme_support('custom-logo');

function remove_unused_scripts() {
    wp_dequeue_script('jquery'); // Отключаем jQuery, если не нужен
}
add_action('wp_enqueue_scripts', 'remove_unused_scripts', 100);


function disable_classic_theme_styles() {
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


function move_jquery_to_footer() {
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

function disable_global_styles() {
    wp_dequeue_style('global-styles'); // Отключаем стили
    wp_dequeue_style('wp-block-library'); // Отключаем базовые стили блоков
    wp_dequeue_style('wp-block-library-theme'); // Отключаем стили темы
}
add_action('wp_enqueue_scripts', 'disable_global_styles', 100);

function remove_block_css() {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('wc-blocks-style'); // Для WooCommerce
}
add_action('wp_enqueue_scripts', 'remove_block_css', 100);

add_filter('use_block_editor_for_post', '__return_false', 10);
add_action('wp_enqueue_scripts', function() {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('global-styles');
}, 100);


function custom_contact_settings($wp_customize)
{
    // Добавляем раздел в настройках
    $wp_customize->add_section('contact_section', array(
        'title' => __('Контактные данные', 'textdomain'),
        'description' => __('Здесь вы можете настроить контактные данные', 'textdomain'),
        'priority' => 30,
    ));

    // Добавляем настройку для Telegram
    $wp_customize->add_setting('contact_telegram', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('contact_telegram', array(
        'label' => __('Telegram', 'textdomain'),
        'section' => 'contact_section',
        'type' => 'text',
        'description' => __('Введите ваш Telegram-ник или ссылку, например: username', 'textdomain'),
    ));

    // Добавляем настройку для WhatsApp
    $wp_customize->add_setting('contact_whatsapp', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('contact_whatsapp', array(
        'label' => __('WhatsApp', 'textdomain'),
        'section' => 'contact_section',
        'type' => 'text',
        'description' => __('Введите номер телефона для WhatsApp в формате: +1234567890', 'textdomain'),
    ));

    // Добавляем настройку для Number

    $wp_customize->add_setting('contact_number', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('contact_number', array(
        'label' => __('Number', 'textdomain'),
        'section' => 'contact_section',
        'type' => 'text',
        'description' => __('Введите номер телефона формате: +1234567890', 'textdomain'),
    ));

    // Добавляем настройку для Escort Telegram

    $wp_customize->add_setting('contact_escort_telegram', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('contact_escort_telegram', array(
        'label' => __('Escort Москва Telegram', 'textdomain'),
        'section' => 'contact_section',
        'type' => 'text',
        'description' => __('Введите ваш Telegram-ник или ссылку, например: username', 'textdomain'),
    ));


    // Добавляем настройку для EMAIL

    $wp_customize->add_setting('contact_email', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('contact_email', array(
        'label' => __('Email', 'textdomain'),
        'section' => 'contact_section',
        'type' => 'text',
        'description' => __('Введите вашу почту', 'textdomain'),
    ));


    // Добавляем настройку для Agency

    $wp_customize->add_setting('contact_agency', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('contact_agency', array(
        'label' => __('Agency', 'textdomain'),
        'section' => 'contact_section',
        'type' => 'text',
        'description' => __('Введите вашу агенцию', 'textdomain'),
    ));


    // Добавляем настройку для STREET

    $wp_customize->add_setting('contact_street', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('contact_street', array(
        'label' => __('Street', 'textdomain'),
        'section' => 'contact_section',
        'type' => 'text',
        'description' => __('Введите улицы вашей агенции', 'textdomain'),
    ));


    // Добавляем настройку для INN

    $wp_customize->add_setting('contact_inn', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('contact_inn', array(
        'label' => __('INN', 'textdomain'),
        'section' => 'contact_section',
        'type' => 'text',
        'description' => __('Введите ИНН агенции', 'textdomain'),
    ));


    // Добавляем настройку для OGRN

    $wp_customize->add_setting('contact_ogrn', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('contact_ogrn', array(
        'label' => __('OGRN', 'textdomain'),
        'section' => 'contact_section',
        'type' => 'text',
        'description' => __('Введите ОГРН вашей агенции', 'textdomain'),
    ));

    // Добавляем настройку для KPP

    $wp_customize->add_setting('contact_kpp', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('contact_kpp', array(
        'label' => __('KPP', 'textdomain'),
        'section' => 'contact_section',
        'type' => 'text',
        'description' => __('Введите КПП вашей агенции', 'textdomain'),
    ));
}

add_action('customize_register', 'custom_contact_settings');

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


function get_contact_phone()
{
    $number = get_theme_mod('contact_number');
    if ($number) {
        return 'tel:' . esc_attr($number);
    }
}
add_shortcode('phone_button', 'get_contact_phone');

function dequeue_swiper_on_pages()
{
    if (is_page(['home', 'contacts', 'pagelayout', 'policy', 'rabota', 'terms', '404'])) {
        wp_dequeue_script('swiper-js');
        wp_dequeue_style('swiper-css');
    }
}
add_action('wp_enqueue_scripts', 'dequeue_swiper_on_pages', 99);





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
function disable_feed() {
    wp_die( __('RSS-фиды отключены. Пожалуйста, заходите напрямую на сайт.') );
}
add_action('do_feed', 'disable_feed', 1);
add_action('do_feed_rdf', 'disable_feed', 1);
add_action('do_feed_rss', 'disable_feed', 1);
add_action('do_feed_rss2', 'disable_feed', 1);
add_action('do_feed_atom', 'disable_feed', 1);
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'feed_links_extra', 3);

add_action('save_post', function($post_id) {
    if (get_post_type($post_id) !== 'page') return;

    // Принудительно обновляем `content`, даже если он есть
    $acf_data = get_post_meta($post_id, 'content', true);
    update_post_meta($post_id, 'content', $acf_data);
}, 10, 1);


add_action('pre_get_posts', function($query) {
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


// define('WP_DEBUG', true);  
// define('WP_DEBUG_LOG', true);  
// define('WP_DEBUG_DISPLAY', true);  
// @ini_set('display_errors', 1);  






