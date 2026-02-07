<?php

/**
 * The template for displaying the footer
 */
if (!defined('ABSPATH')) exit;

// === ОСНОВНЫЕ ПЕРЕМЕННЫЕ ===
$year      = (int) date('Y');
$site_name = get_bloginfo('name') ?: 'almaty.kyzdarki.net';
$home_url  = home_url('/');
$logo_url  = get_stylesheet_directory_uri() . '/assets/icons/logo.png';

// === КОНТАКТЫ (С ЛОГИКОЙ DEEP CONTACTS) ===
$__mr_norm_tg = function ($v) {
    $v = trim((string)$v);
    if ($v === '') return '';
    $v = preg_replace('~^https?://t\.me/~i', '', $v);
    $v = ltrim($v, '@');
    return preg_replace('~[^a-z0-9_]+~i', '', $v);
};
$__mr_norm_wa = function ($v) {
    return preg_replace('~\D+~', '', (string)$v);
};

// Контекст "Дешевые"
$is_cheap_page = is_page('deshevyye-prostitutki');
$is_cheap_model = is_singular('models') && has_term('deshevyye-prostitutki', 'price_tax');
$use_cheap_contacts = ($is_cheap_page || $is_cheap_model);

$__final_tg = '';
$__final_wa = '';

if ($use_cheap_contacts) {
    $__final_tg = $__mr_norm_tg(get_theme_mod('contact_telegram_5'));
    $__final_wa = $__mr_norm_wa(get_theme_mod('contact_whatsapp_5'));
}

// Обычная логика
if (empty($__final_tg) && empty($__final_wa)) {
    $__tg_variants = [];
    $__wa_variants = [];

    $main_tg = get_theme_mod('contact_telegram');
    $main_wa = get_theme_mod('contact_whatsapp');
    if (!empty($main_tg)) $__tg_variants[] = $__mr_norm_tg($main_tg);
    if (!empty($main_wa)) $__wa_variants[] = $__mr_norm_wa($main_wa);

    for ($i = 1; $i <= 4; $i++) {
        $tg = get_theme_mod("contact_telegram_$i");
        $wa = get_theme_mod("contact_whatsapp_$i");
        if (!empty($tg)) $__tg_variants[] = $__mr_norm_tg($tg);
        if (!empty($wa)) $__wa_variants[] = $__mr_norm_wa($wa);
    }

    $__final_tg = !empty($__tg_variants) ? $__tg_variants[array_rand($__tg_variants)] : '';
    $__final_wa = !empty($__wa_variants) ? $__wa_variants[array_rand($__wa_variants)] : '';
}

$tg_user_handle   = $__final_tg !== '' ? $__final_tg : 'Kyzdar-Almaty';
$wa_number_digits = $__final_wa !== '' ? $__final_wa : '79874684644';
$tg_channel_handle = ltrim((string) get_theme_mod('contact_telegram_channel', 'Telegram_Channel_Name'), '@');

// === [ЗАЩИТА] КОДИРОВАНИЕ ДЛЯ ФУТЕРА ===
// Кодируем ссылки для блока ссылок в самом футере
$enc_tg_user    = base64_encode('https://t.me/' . $tg_user_handle);
$enc_tg_channel = base64_encode('https://t.me/' . $tg_channel_handle);
$enc_wa_number  = base64_encode('https://wa.me/' . $wa_number_digits);


unset($__mr_norm_tg, $__mr_norm_wa, $__tg_variants, $__wa_variants, $__final_tg, $__final_wa, $i, $is_cheap_page, $is_cheap_model, $use_cheap_contacts);


// === НАВИГАЦИЯ В ФУТЕРЕ ===
$nav_links = [
    ['label' => 'Политика конфиденциальности', 'slug' => 'politika-konfidentsialnosti'],
    ['label' => 'Условия пользования',         'slug' => 'usloviya-polzovaniya'],
    ['label' => 'Карта сайта',                 'slug' => 'sitemap'],
    ['label' => 'Эскорт кастинг',              'slug' => 'escort-kasting'],
    ['label' => 'Эскорт вакансии',             'slug' => 'escort-vakansii'],
    ['label' => 'Контакты',                    'slug' => 'kontakty'],
    ['label' => 'О сайте',                     'slug' => 'o-sajte'],
    ['label' => 'Отзывы',                      'slug' => 'otzyvy'],
    ['label' => 'Все услуги',                  'slug' => 'vse-uslugi'],
    ['label' => 'Faq',                        'slug' => 'faq'],
    ['label' => 'Блог',                        'slug' => 'blog'],
];

?>
<footer class="bg-[#212529] text-gray-400">
    <div class="mx-auto max-w-7xl px-4 py-16">
        <?php
        // Утилиты
        $normalize_slug = static function ($link) {
            $raw = '';
            if (!empty($link['slug'])) {
                $raw = (string)$link['slug'];
            } elseif (!empty($link['url'])) {
                $u = wp_parse_url($link['url']);
                $raw = $u['path'] ?? '';
            }
            return trim(strtolower($raw), '/');
        };

        $legal_slugs_map = ['politika-konfidentsialnosti', 'usloviya-polzovaniya'];

        $legal_links = [];
        $main_links  = [];

        if (!empty($nav_links) && is_array($nav_links)) {
            foreach ($nav_links as $link) {
                $label = trim($link['label'] ?? '');
                if ($label === '') continue;
                $norm = $normalize_slug($link);
                if (in_array($norm, $legal_slugs_map, true)) {
                    $legal_links[] = $link;
                } else {
                    $main_links[]  = $link;
                }
            }
        }

        $build_url = static function ($link) {
            if (!empty($link['url'])) return esc_url($link['url']);
            $slug = trim($link['slug'] ?? '', '/');
            return esc_url(home_url("/{$slug}"));
        };

        $site_name = $site_name ?? get_bloginfo('name');
        $year      = $year ?? date_i18n('Y');

        // Получаем текущий URL для сравнения
        $current_url = home_url(add_query_arg([], $GLOBALS['wp']->request));
        $current_url = user_trailingslashit($current_url);
        ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-12 max-w-5xl mx-auto">
            <nav class="flex flex-col items-start w-full space-y-6 order-1" aria-label="Основная навигация">
                <?php if (!empty($main_links)): ?>
                    <div>
                        <h2 class="text-white font-semibold mb-4 text-lg">Навигация</h2>
                        <ul class="space-y-3" id="footer-nav-list">
                            <?php foreach ($main_links as $index => $link_data):
                                $url = $build_url($link_data);
                                $is_active = (user_trailingslashit($url) === $current_url);
                            ?>
                                <li class="<?= $index > 1 ? 'hidden extra-link' : '' ?>">
                                    <?php if ($is_active): ?>
                                        <span class="text-white font-medium cursor-default">
                                            <?= esc_html($link_data['label'] ?? '') ?>
                                        </span>
                                    <?php else: ?>
                                        <a href="<?= $url ?>"
                                            class="hover:text-white hover:underline transition-colors duration-200">
                                            <?= esc_html($link_data['label'] ?? '') ?>
                                        </a>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <?php if (count($main_links) > 2): ?>
                            <button type="button"
                                id="footer-nav-toggle"
                                class="mt-4 flex items-center gap-2 text-sm text-gray-400 hover:text-white transition-colors">
                                <svg id="footer-nav-icon" class="w-4 h-4 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 9l6 6 6-6" />
                                </svg>
                                <span>Показать ещё</span>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </nav>

            <nav class="flex flex-col w-full items-start text-left sm:items-center sm:text-center space-y-6 order-2"
                aria-label="Правовая информация">
                <?php if (!empty($legal_links)): ?>
                    <div>
                        <h2 class="text-white font-semibold mb-4 text-lg">Правовая информация</h2>
                        <ul class="space-y-3">
                            <?php foreach ($legal_links as $link_data):
                                $url = $build_url($link_data);
                                $is_active = (user_trailingslashit($url) === $current_url);
                            ?>
                                <li>
                                    <?php if ($is_active): ?>
                                        <span class="text-white font-medium cursor-default">
                                            <?= esc_html($link_data['label'] ?? '') ?>
                                        </span>
                                    <?php else: ?>
                                        <a href="<?= $url ?>"
                                            class="hover:text-white hover:underline transition-colors duration-200">
                                            <?= esc_html($link_data['label'] ?? '') ?>
                                        </a>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </nav>

            <nav class="flex flex-col w-full items-start sm:items-end sm:text-right space-y-6 order-3"
                aria-label="Контакты">
                <div>
                    <h2 class="text-white font-semibold mb-4 text-lg">Связаться</h2>
                    <ul class="space-y-4">
                        <?php if ($tg_user_handle): ?>
                            <li>
                                <a href="javascript:void(0);" 
                                   data-enc="<?= esc_attr($enc_tg_user) ?>"
                                   class="protected-contact inline-flex items-center gap-3 group"
                                   rel="nofollow">
                                    <svg class="w-5 h-5 text-sky-400" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M22 3 2 10.3l6.7 2.2L19 6.2 10.6 14l.3 6 3.2-4.4 4.8 3.6L22 3z" />
                                    </svg>
                                    <span class="group-hover:text-white group-hover:underline transition-colors">Telegram</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if ($tg_channel_handle): ?>
                            <li>
                                <a href="javascript:void(0);" 
                                   data-enc="<?= esc_attr($enc_tg_channel) ?>"
                                   class="protected-contact inline-flex items-center gap-3 group"
                                   rel="nofollow">
                                    <svg class="w-5 h-5 text-sky-400" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M22 3 2 10.3l6.7 2.2L19 6.2 10.6 14l.3 6 3.2-4.4 4.8 3.6L22 3z" />
                                    </svg>
                                    <span class="group-hover:text-white group-hover:underline transition-colors">Telegram Channel</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if ($wa_number_digits): ?>
                            <li>
                                <a href="javascript:void(0);" 
                                   data-enc="<?= esc_attr($enc_wa_number) ?>"
                                   class="protected-contact inline-flex items-center gap-3 group"
                                   rel="nofollow">
                                    <svg viewBox="0 0 24 24" class="w-5 h-5 text-green-500" fill="currentColor">
                                        <path d="M20.52 3.48A11.79 11.79 0 0 0 12 0C5.37 0 0 5.37 0 12c0 2.1.55 4.06 1.51 5.75L0 24l6.4-1.68A12 12 0 0 0 12 24c6.63 0 12-5.37 12-12 0-3.21-1.25-6.23-3.48-8.52zM12 21.5a9.43 9.43 0 0 1-4.8-1.32l-.34-.2-3.8 1 .99-3.7-.22-.35A9.43 9.43 0 1 1 21.5 12 9.5 9.5 0 0 1 12 21.5zm5.36-7.22c-.29-.15-1.7-.84-1.96-.93-.26-.09-.45-.15-.64.15-.19.29-.74.93-.9 1.12-.17.19-.33.21-.62.08-.29-.14-1.22-.45-2.33-1.49-.86-.77-1.43-1.72-1.59-2.01-.17-.29-.02-.45.13-.6.14-.14.29-.36.43-.54.14-.17.19-.3.29-.5.1-.2.05-.37-.02-.54-.08-.17-.62-1.49-.85-2.04-.22-.54-.45-.46-.64-.47l-.55-.01c-.2 0-.5.07-.76.36-.26.29-1.01.98-1.01 2.39 0 1.41 1.03 2.77 1.17 2.96.14.19 2.04 3.12 4.95 4.37.69.3 1.24.47 1.66.6.69.22 1.31.19 1.8.11.55-.08 1.69-.69 1.94-1.35.24-.66.24-1.24.17-1.36-.07-.12-.26-.19-.55-.34z" />
                                    </svg>
                                    <span class="group-hover:text-white group-hover:underline transition-colors">WhatsApp: +77275467994</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>

        <div class="mt-16 pt-8 border-t border-gray-800 text-center max-w-4xl mx-auto">
            <p class="text-sm">© <?= esc_html($site_name) ?>, <?= esc_html($year) ?></p>
            <p class="text-sm leading-relaxed mt-3">
                Наше агентство не предлагает интимных услуг и не отвечает за поступки моделей и посетителей сайта. Все сопровождение осуществляется исключительно на взаимной договоренности сторон. Наши услуги в рамках эскорта сводятся исключительно к сопровождению.
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('footer-nav-toggle');
            if (!btn) return;
            const icon = document.getElementById('footer-nav-icon');
            const extraLinks = document.querySelectorAll('.extra-link');
            let expanded = false;

            btn.addEventListener('click', () => {
                expanded = !expanded;
                extraLinks.forEach(el => el.classList.toggle('hidden', !expanded));
                icon.classList.toggle('rotate-180', expanded);
                btn.querySelector('span').textContent = expanded ? 'Скрыть' : 'Показать ещё';
            });
        });
    </script>
</footer>

<?php
// === Логика ссылок и активной страницы ===
$home_link = user_trailingslashit(home_url('/'));
$current_url = user_trailingslashit(home_url(add_query_arg([], $GLOBALS['wp']->request)));
$is_home_active = ($home_link === $current_url);

// === Логика контактов (как делали ранее) ===
// 1. Определяем: это "Дешевые"?
$is_cheap_context = (is_page('deshevyye-prostitutki') || has_term('deshevyye-prostitutki', 'price_tax', get_the_ID()));

// 2. Выбираем контакты
if ($is_cheap_context) {
    $raw_tg = get_theme_mod('contact_telegram_5');
    $raw_wa = get_theme_mod('contact_whatsapp_5');
} else {
    $tg_pool = [];
    $wa_pool = [];
    if ($t = get_theme_mod('contact_telegram')) $tg_pool[] = $t;
    if ($w = get_theme_mod('contact_whatsapp')) $wa_pool[] = $w;
    for ($i = 1; $i <= 4; $i++) {
        if ($t = get_theme_mod("contact_telegram_$i")) $tg_pool[] = $t;
        if ($w = get_theme_mod("contact_whatsapp_$i")) $wa_pool[] = $w;
    }
    $raw_tg = !empty($tg_pool) ? $tg_pool[array_rand($tg_pool)] : '';
    $raw_wa = !empty($wa_pool) ? $wa_pool[array_rand($wa_pool)] : '';
}

// 3. Нормализация
$tg_clean = trim((string)$raw_tg);
$tg_clean = preg_replace('~^https?://t\.me/~i', '', $tg_clean);
$tg_clean = ltrim($tg_clean, '@');
$tg_clean = preg_replace('~[^a-z0-9_]+~i', '', $tg_clean);
$wa_clean = preg_replace('~\D+~', '', (string)$raw_wa);

// === [ЗАЩИТА] КОДИРОВАНИЕ ДЛЯ ПЛАВАЮЩЕЙ ПАНЕЛИ ===
$enc_float_tg = base64_encode('https://t.me/' . $tg_clean);
$enc_float_wa = base64_encode('https://wa.me/' . $wa_clean);
?>

<div class="fixed z-50 bg-[#212529] backdrop-blur-sm transition-colors hover:bg-[#212529]/95
            /* Мобильные стили */
            m-2 rounded-lg inset-x-0 bottom-0
            pb-[max(0.25rem,env(safe-area-inset-bottom))]
            /* Десктопные стили */
            md:m-0 md:inset-auto md:right-4 md:bottom-4 
            md:rounded-2xl md:shadow-xl md:px-4 md:py-3 md:bg-[#212529]/85">

    <ul class="flex items-center gap-0 
               /* Мобильные: на всю ширину */
               justify-stretch py-2 px-2 max-w-screen-xl mx-auto
               /* Десктопные: сжимаем */
               md:justify-center md:p-0 md:mx-0 md:w-auto">

        <?php if (!$is_home_active): ?>
            <li class="flex-1 text-center md:flex-none md:w-[55px]">
                <a href="<?php echo esc_url($home_link); ?>" class="flex flex-col items-center justify-center py-1 min-w-0">
                    <span class="inline-grid w-10 h-10 place-items-center rounded-full bg-[#e865a0] text-white">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 10.5 12 3l9 7.5" />
                            <path d="M5 10v9a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-9" />
                            <path d="M10 21v-6h4v6" />
                        </svg>
                    </span>
                </a>
            </li>
        <?php endif; ?>

        <li class="flex-1 text-center md:flex-none md:w-[55px]">
            <a href="javascript:void(0);" 
               data-enc="<?= esc_attr($enc_float_tg) ?>"
               class="protected-contact flex flex-col items-center justify-center py-1 min-w-0"
               rel="nofollow">
                <span class="inline-grid w-10 h-10 place-items-center rounded-full bg-sky-500 text-white">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9.9 13.4l-.4 5.6c.6 0 .8-.3 1.1-.6l2.7-2.6 5.6 4.1c1 .6 1.8.3 2.1-.9l3.8-17.7c.3-1.2-.4-1.7-1.4-1.4L1.5 9.6c-1.2.3-1.2 1-.2 1.3l5.6 1.7 12.9-8.1c.6-.4 1.2-.2.7.2" />
                    </svg>
                </span>
            </a>
        </li>

        <li class="flex-1 text-center md:flex-none md:w-[55px]">
            <a href="javascript:void(0);" 
               data-enc="<?= esc_attr($enc_float_wa) ?>"
               class="protected-contact flex flex-col items-center justify-center py-1 min-w-0"
               rel="nofollow">
                <span class="inline-grid w-10 h-10 place-items-center rounded-full bg-green-500 text-white">
                    <svg viewBox="0 0 24 24" class="w-5 h-5 text-white" fill="currentColor">
                        <path d="M20.52 3.48A11.79 11.79 0 0 0 12 0C5.37 0 0 5.37 0 12c0 2.1.55 4.06 1.51 5.75L0 24l6.4-1.68A12 12 0 0 0 12 24c6.63 0 12-5.37 12-12 0-3.21-1.25-6.23-3.48-8.52zM12 21.5a9.43 9.43 0 0 1-4.8-1.32l-.34-.2-3.8 1 .99-3.7-.22-.35A9.43 9.43 0 1 1 21.5 12 9.5 9.5 0 0 1 12 21.5zm5.36-7.22c-.29-.15-1.7-.84-1.96-.93-.26-.09-.45-.15-.64.15-.19.29-.74.93-.9 1.12-.17.19-.33.21-.62.08-.29-.14-1.22-.45-2.33-1.49-.86-.77-1.43-1.72-1.59-2.01-.17-.29-.02-.45.13-.6.14-.14.29-.36.43-.54.14-.17.19-.3.29-.5.1-.2.05-.37-.02-.54-.08-.17-.62-1.49-.85-2.04-.22-.54-.45-.46-.64-.47l-.55-.01c-.2 0-.5.07-.76.36-.26.29-1.01.98-1.01 2.39 0 1.41 1.03 2.77 1.17 2.96.14.19 2.04 3.12 4.95 4.37.69.3 1.24.47 1.66.6.69.22 1.31.19 1.8.11.55-.08 1.69-.69 1.94-1.35.24-.66.24-1.24.17-1.36-.07-.12-.26-.19-.55-.34z" />
                    </svg>
                </span>
            </a>
        </li>

        <li class="hidden flex-1 text-center justify-center md:flex-none md:w-[55px]" data-scroll-btn>
            <button type="button" class="flex flex-col items-center justify-center py-1 min-w-0 w-full h-full cursor-pointer">
                <span class="inline-grid w-10 h-10 place-items-center rounded-full bg-[#e865a0] text-white">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 19V5"></path>
                        <path d="M5 12l7-7 7 7"></path>
                    </svg>
                </span>
            </button>
        </li>

    </ul>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const scrollBtn = document.querySelector('[data-scroll-btn]');
        if (!scrollBtn) return;

        // 1. Логика появления кнопки при скролле
        function toggleScrollBtn() {
            // Если прокрутили больше 20px, показываем кнопку
            if (window.scrollY > 20) {
                scrollBtn.classList.remove('hidden');
            } else {
                scrollBtn.classList.add('hidden');
            }
        }

        // Слушаем событие скролла (passive: true для производительности)
        window.addEventListener('scroll', toggleScrollBtn, {
            passive: true
        });
        // Запускаем один раз при загрузке (если страница обновлена посередине)
        toggleScrollBtn();

        // 2. Логика клика (плавный скролл наверх)
        scrollBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    });
</script>

<?php wp_footer(); ?>

</body>