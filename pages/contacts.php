<?php

/**
 * Template Name: Контакты (Рандом 6 полей)
 */
if (!defined('ABSPATH')) exit;

get_header();

$ACCENT = '#e865a0';

/* ===== ACF / поля страницы ===== */
$heading = function_exists('get_field') ? (get_field('h1') ?: get_the_title()) : get_the_title();
$lead    = function_exists('get_field') ? (get_field('p')  ?: '') : '';

/* ============================================================
   ЛОГИКА РАНДОМНЫХ КОНТАКТОВ
   (Берем все доступные поля: Main + 1..5 и выбираем случайное)
   ============================================================ */

// Хелперы для очистки
$clean_tg_login = function ($v) {
    $v = trim((string)$v);
    $v = preg_replace('~^https?://t\.me/~i', '', $v); // убираем url
    $v = ltrim($v, '@'); // убираем собачку
    return preg_replace('~[^a-z0-9_]+~i', '', $v); // только валидные символы
};

$clean_wa_phone = function ($v) {
    return preg_replace('~\D+~', '', (string)$v); // только цифры
};

// 1. Сбор WhatsApp (Основной + 1..5)
$wa_pool = [];
// Основное поле
if ($val = get_theme_mod('contact_whatsapp')) {
    $wa_pool[] = $val;
}
// Дополнительные поля 1-5
for ($i = 1; $i <= 5; $i++) {
    if ($val = get_theme_mod("contact_whatsapp_$i")) {
        $wa_pool[] = $val;
    }
}
// Выбираем случайный WhatsApp
$wa_display = !empty($wa_pool) ? $wa_pool[array_rand($wa_pool)] : '';


// 2. Сбор Telegram (Основной + 1..5)
$tg_pool = [];
// Основное поле
if ($val = get_theme_mod('contact_telegram')) {
    $tg_pool[] = $val;
}
// Дополнительные поля 1-5
for ($i = 1; $i <= 5; $i++) {
    if ($val = get_theme_mod("contact_telegram_$i")) {
        $tg_pool[] = $val;
    }
}
// Выбираем случайный Telegram
$tg_display = !empty($tg_pool) ? $tg_pool[array_rand($tg_pool)] : '';


// 3. Статичные контакты (Телефон и Email не меняются)
$phone = trim((string) get_theme_mod('contact_number'));
$email = trim((string) (get_theme_mod('contact_email') ?: get_option('admin_email')));


/* ============================================================
   ФОРМИРОВАНИЕ ССЫЛОК
   ============================================================ */

// Телефон
$tel_href = $phone ? 'tel:' . preg_replace('~\D+~', '', $phone) : '';

// WhatsApp
$wa_href = '';
if ($wa_display) {
    $wa_clean = $clean_wa_phone($wa_display);
    if ($wa_clean) $wa_href = 'https://wa.me/' . $wa_clean;
}

// Telegram
$tg_href = '';
$tg_login_clean = '';
if ($tg_display) {
    $tg_login_clean = $clean_tg_login($tg_display);
    if ($tg_login_clean) $tg_href = 'https://t.me/' . $tg_login_clean;
}

/* ===== AJAX ===== */
$ajax_url = admin_url('admin-ajax.php');
$nonce    = wp_create_nonce('contacts_form_nonce');
?>

<main class="px-4 py-10 bg-white text-black">
    <div class="max-w-[1100px] mx-auto">
        <!-- Заголовок -->
        <header class="mb-6 md:mb-8">
            <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight leading-tight"
                style="letter-spacing:-.02em;">
                <?= esc_html($heading); ?>
            </h1>
            <?php if ($lead): ?>
                <p class="mt-4 text-[15px] md:text-base text-neutral-700 leading-relaxed">
                    <?= esc_html($lead); ?>
                </p>
            <?php endif; ?>
        </header>

        <!-- Верхний контент + контакты -->
        <section class="mb-8">
            <div class="prose prose-sm max-w-none text-black [&>ul>li]:mb-2">
                <?php
                while (have_posts()) {
                    the_post();
                    the_content();
                }
                ?>
            </div>

            <!-- Список контактов -->
            <div class="mt-6 rounded-2xl border bg-white p-5 md:p-6"
                style="border-color:<?= esc_attr($ACCENT) ?>22">
                <h2 class="text-lg font-bold mb-3">Контакты</h2>
                <ul class="space-y-3 text-[15px]">

                    <!-- Email -->
                    <?php if ($email): ?>
                        <li class="flex items-center gap-3">
                            <span class="inline-flex w-8 h-8 rounded-full border shrink-0 items-center justify-center"
                                style="border-color:<?= esc_attr($ACCENT) ?>22;color:<?= esc_attr($ACCENT) ?>">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5L4 8V6l8 5 8-5Z" />
                                </svg>
                            </span>
                            <a class="hover:underline font-medium" href="mailto:<?= antispambot($email) ?>">
                                <?= esc_html($email) ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- WhatsApp (Рандомный) -->
                    <?php if ($wa_href && $wa_display): ?>
                    <?php $enc_wa = base64_encode($wa_href); ?>
                    <li class="flex items-center gap-3">
                        <span class="inline-flex w-8 h-8 rounded-full border shrink-0 items-center justify-center"
                            style="border-color:<?= esc_attr($ACCENT) ?>22;color:<?= esc_attr($ACCENT) ?>">
                            <svg class="w-4 h-4" viewBox="0 0 16 16" aria-hidden="true">
                                <path fill="currentColor" d="M13.6 2.3A8 8 0 0 0 0 8c0 1.4.4 2.7 1 3.9L0 16l4.2-1.1A8 8 0 1 0 13.6 2.3ZM8 14.5a6.5 6.5 0 0 1-3.6-1l-2.7.7.7-2.5A6.5 6.5 0 1 1 8 14.5Zm3.6-4.3c-.2-.1-1.2-.6-1.4-.6s-.3-.1-.5.1-.5.6-.6.8-.2.1-.4 0c-.2-.1-.9-.3-1.7-1-.6-.5-1-1.2-1.1-1.4-.1-.2 0-.3.1-.5l.3-.3c.1-.1.1-.2.2-.3 0-.2 0-.3-.1-.4l-.6-1.4c-.1-.2-.3-.2-.4-.2H4c-.2 0-.4.1-.6.3-.2.2-.7.7-.7 1.6s.7 1.9.8 2c.1.1 1.4 2.1 3.4 2.9.5.2.9.3 1.2.4.5.1.9.1 1.3.1.4 0 1.2-.5 1.4-1 .1-.4.1-.8.1-.9s-.2-.1-.4-.2Z" />
                            </svg>
                        </span>
                        <a class="hover:underline font-medium protected-contact" 
                        href="javascript:void(0);" 
                        data-enc="<?= esc_attr($enc_wa) ?>" 
                        target="_blank" 
                        rel="noopener">
                            WhatsApp: +77275467994
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($tg_href && $tg_login_clean): ?>
                    <?php $enc_tg = base64_encode($tg_href); ?>
                    <li class="flex items-center gap-3">
                        <span class="inline-flex w-8 h-8 rounded-full border shrink-0 items-center justify-center"
                            style="border-color:<?= esc_attr($ACCENT) ?>22;color:<?= esc_attr($ACCENT) ?>">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22 3 2 10.3l6.7 2.2L19 6.2 10.6 14l.3 6 3.2-4.4 4.8 3.6L22 3z" />
                            </svg>
                        </span>
                        <a class="hover:underline font-medium protected-contact" 
                        href="javascript:void(0);" 
                        data-enc="<?= esc_attr($enc_tg) ?>" 
                        target="_blank" 
                        rel="noopener">
                            Telegram
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($phone && $tel_href): ?>
                    <?php $enc_tel = base64_encode($tel_href); ?>
                    <li class="flex items-center gap-3">
                        <span class="inline-flex w-8 h-8 rounded-full border shrink-0 items-center justify-center"
                            style="border-color:<?= esc_attr($ACCENT) ?>22;color:<?= esc_attr($ACCENT) ?>">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M22 16.9v2a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 1h2a2 2 0 0 1 2 1.7c.2 1.3.5 2.6 1 3.8a2 2 0 0 1-.5 2.2L7.7 9.8a16 16 0 0 0 6.5 6.5l1.1-1.8a2 2 0 0 1 2.2-.6c1.2.5 2.5.8 3.8 1a2 2 0 0 1 1.7 2z" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <a class="hover:underline font-medium protected-contact" 
                        href="javascript:void(0);" 
                        data-enc="<?= esc_attr($enc_tel) ?>">
                            <?= esc_html($phone) ?>
                        </a>
                    </li>
                <?php endif; ?>
                </ul>
            </div>
        </section>

        <!-- Форма -->
        <section class="rounded-2xl border bg-white"
            style="border-color:<?= esc_attr($ACCENT) ?>22">
            <div class="p-5 md:p-6 border-b" style="border-color:<?= esc_attr($ACCENT) ?>22">
                <h2 class="text-xl font-bold">Напишите нам</h2>
                <p class="text-sm text-neutral-700 mt-1">Ответим как можно быстрее.</p>
            </div>

            <div class="p-5 md:p-6">
                <form id="contact-form" class="space-y-4" method="post" data-ajax="<?= esc_url($ajax_url) ?>">
                    <input type="hidden" name="action" value="send_contacts_form">
                    <input type="hidden" name="nonce" value="<?= esc_attr($nonce) ?>">
                    <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="block">
                            <span class="block text-sm mb-1">Имя*</span>
                            <input name="name" type="text" required placeholder="Ваше имя"
                                class="w-full rounded-xl border px-3 py-2 bg-white placeholder-neutral-400 outline-none focus:ring-2"
                                style="border-color:<?= esc_attr($ACCENT) ?>33; --tw-ring-color: <?= esc_attr($ACCENT) ?>;">
                        </label>
                        <label class="block">
                            <span class="block text-sm mb-1">Телефон*</span>
                            <input name="phone" type="tel" required placeholder="+7 999 000-00-00"
                                class="w-full rounded-xl border px-3 py-2 bg-white placeholder-neutral-400 outline-none focus:ring-2"
                                style="border-color:<?= esc_attr($ACCENT) ?>33; --tw-ring-color: <?= esc_attr($ACCENT) ?>;">
                        </label>
                    </div>

                    <label class="block">
                        <span class="block text-sm mb-1">Комментарий</span>
                        <textarea name="message" rows="5" placeholder="Коротко опишите вопрос"
                            class="w-full rounded-xl border px-3 py-2 bg-white placeholder-neutral-400 outline-none focus:ring-2"
                            style="border-color:<?= esc_attr($ACCENT) ?>33; --tw-ring-color: <?= esc_attr($ACCENT) ?>;"></textarea>
                    </label>

                    <button id="contact-submit" type="submit"
                        class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl font-semibold text-white active:translate-y-px transition"
                        style="background:<?= esc_attr($ACCENT) ?>;">
                        Отправить
                    </button>

                    <div id="contact-alert" class="hidden mt-3 text-sm rounded-xl px-3 py-2 border"
                        style="border-color:<?= esc_attr($ACCENT) ?>33"></div>
                </form>
            </div>
        </section>
    </div>
</main>

<script>
    (function() {
        const form = document.getElementById('contact-form');
        if (!form) return;
        const ajaxUrl = form.dataset.ajax || '/wp-admin/admin-ajax.php';
        const submitBtn = document.getElementById('contact-submit');
        const alertBox = document.getElementById('contact-alert');

        function setAlert(msg) {
            alertBox.classList.remove('hidden');
            alertBox.textContent = msg;
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            alertBox.classList.add('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-60', 'cursor-not-allowed');

            try {
                const fd = new FormData(form);
                const res = await fetch(ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: fd
                });
                const txt = await res.text();
                let j;
                try {
                    j = JSON.parse(txt);
                } catch (_) {
                    throw new Error('Сбой сети или неверный ответ сервера');
                }
                if (!j || !j.success) throw new Error((j && j.data && j.data.message) || 'Ошибка отправки');
                setAlert((j.data && j.data.message) || 'Спасибо! Мы свяжемся с вами.');
                form.reset();
            } catch (err) {
                setAlert(err.message || 'Не удалось отправить. Попробуйте позже.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
            }
        });
    })();
</script>

<?php get_footer(); ?>