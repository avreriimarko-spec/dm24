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

$enc_wa  = $wa_href ? base64_encode($wa_href) : '';
$enc_tg  = $tg_href ? base64_encode($tg_href) : '';
$enc_tel = $tel_href ? base64_encode($tel_href) : '';
?>

<style>
    .contacts-page {
        --contacts-accent: <?= esc_attr($ACCENT) ?>;
        --contacts-accent-soft: rgba(232, 101, 160, 0.55);
        --contacts-muted: #6b7280;
        background: #f5f5f7;
        color: #0f172a;
        padding: 36px 16px 44px;
    }

    .contacts-page__inner {
        max-width: 1120px;
        margin: 0 auto;
    }

    .contacts-page__lead {
        margin-bottom: 20px;
    }

    .contacts-page__title {
        margin: 0 0 10px;
        font-size: clamp(28px, 3.5vw, 44px);
        line-height: 1.1;
        font-weight: 800;
    }

    .contacts-page__lead-text {
        margin: 0;
        color: #4b5563;
    }

    .contacts-page__grid {
        display: grid;
        gap: 22px;
        align-items: start;
    }

    .contacts-card {
        border: 1px solid var(--contacts-accent-soft);
        background: #fff;
        padding: 22px;
    }

    .contacts-card__title {
        margin: 0 0 18px;
        font-size: clamp(28px, 3.2vw, 32px);
        line-height: 1.05;
    }

    .contacts-card__hint {
        margin: -8px 0 20px;
        color: #4b5563;
        font-size: 14px;
    }

    .contacts-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .contacts-item {
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid var(--contacts-accent-soft);
        padding: 12px;
        text-decoration: none;
        color: inherit;
    }

    .contacts-item + .contacts-item {
        margin-top: 12px;
    }

    .contacts-item__icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: var(--contacts-accent);
        color: #fff;
    }

    .contacts-item__icon svg {
        width: 18px;
        height: 18px;
    }

    .contacts-item__label {
        margin: 0 0 2px;
        font-size: 12px;
        line-height: 1.2;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        color: #4b5563;
        font-weight: 700;
    }

    .contacts-item__value {
        margin: 0;
        font-size: 16px;
        line-height: 1.15;
        font-weight: 800;
        word-break: break-word;
    }

    .contacts-hours {
        margin-top: 20px;
        padding-top: 18px;
        border-top: 1px solid #e5e7eb;
    }

    .contacts-hours__label {
        margin: 0 0 5px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        font-size: 13px;
        color: #4b5563;
        font-weight: 700;
    }

    .contacts-hours__value {
        margin: 0;
        font-size: 30px;
        line-height: 1.2;
        font-weight: 800;
    }

    .contacts-form {
        margin: 0;
        display: grid;
        gap: 14px;
    }

    .contacts-field__label {
        display: block;
        margin: 0 0 7px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        color: #374151;
        font-weight: 700;
    }

    .contacts-field__control {
        width: 100%;
        border: 1px solid #d1d5db;
        padding: 11px 13px;
        font-size: 16px;
        line-height: 1.25;
        color: #111827;
        background: #fff;
        outline: none;
    }

    .contacts-field__control:focus {
        border-color: var(--contacts-accent);
        box-shadow: 0 0 0 2px rgba(232, 101, 160, 0.12);
    }

    .contacts-field__control::placeholder {
        color: #9ca3af;
    }

    textarea.contacts-field__control {
        min-height: 142px;
        resize: vertical;
    }

    .contacts-submit {
        width: 100%;
        border: 0;
        background: #e1061d;
        color: #fff;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        min-height: 48px;
        padding: 12px 16px;
        cursor: pointer;
    }

    .contacts-submit:hover {
        filter: brightness(0.95);
    }

    .contacts-submit:disabled {
        cursor: not-allowed;
        opacity: 0.65;
    }

    .contacts-privacy {
        margin: 8px 0 0;
        font-size: 13px;
        text-align: center;
        color: var(--contacts-muted);
    }

    #contact-alert {
        border: 1px solid rgba(232, 101, 160, 0.35);
        padding: 9px 12px;
        font-size: 14px;
    }

    #contact-alert.hidden {
        display: none;
    }

    @media (max-width: 1023px) {
        .contacts-item__value,
        .contacts-hours__value {
            font-size: 24px;
        }
    }

    @media (min-width: 1024px) {
        .contacts-page {
            padding-top: 44px;
        }

        .contacts-page__grid {
            grid-template-columns: minmax(310px, 440px) minmax(0, 1fr);
            gap: 24px;
        }
    }
</style>

<main class="contacts-page">
    <div class="contacts-page__inner">
        <header class="contacts-page__lead">
            <h1 class="contacts-page__title"><?= esc_html($heading); ?></h1>
            <?php if ($lead): ?>
                <p class="contacts-page__lead-text"><?= esc_html($lead); ?></p>
            <?php endif; ?>
            <div class="prose prose-sm max-w-none text-black [&>ul>li]:mb-2 mt-4">
                <?php
                while (have_posts()) {
                    the_post();
                    the_content();
                }
                ?>
            </div>
        </header>

        <section class="contacts-page__grid" aria-label="Контакты и форма связи">
            <aside class="contacts-card">
                <h2 class="contacts-card__title">Свяжитесь с нами</h2>

                <ul class="contacts-list flex flex-col gap-1">
                    <?php if ($tg_href && $tg_login_clean): ?>
                        <li>
                            <a class="contacts-item protected-contact"
                                href="javascript:void(0);"
                                data-enc="<?= esc_attr($enc_tg) ?>"
                                data-go="tg"
                                target="_blank"
                                rel="noopener">
                                <span class="contacts-item__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M22 3 2 10.3l6.7 2.2L19 6.2 10.6 14l.3 6 3.2-4.4 4.8 3.6L22 3z" />
                                    </svg>
                                </span>
                                <span>
                                    <span class="contacts-item__label">Telegram</span>
                                    <span class="contacts-item__value">@<?= esc_html($tg_login_clean) ?></span>
                                </span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($wa_href && $wa_display): ?>
                        <li>
                            <a class="contacts-item protected-contact"
                                href="javascript:void(0);"
                                data-enc="<?= esc_attr($enc_wa) ?>"
                                data-go="wa"
                                target="_blank"
                                rel="noopener">
                                <span class="contacts-item__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path fill="#fff" d="M12.04 2c-5.49 0-9.95 4.46-9.95 9.95 0 1.75.46 3.46 1.33 4.96L2 22l5.24-1.37a9.9 9.9 0 0 0 4.8 1.23h.01c5.49 0 9.95-4.46 9.95-9.95A9.95 9.95 0 0 0 12.04 2Zm0 18.13a8.14 8.14 0 0 1-4.15-1.14l-.3-.18-3.11.81.83-3.03-.2-.31a8.14 8.14 0 1 1 6.93 3.85Zm4.46-6.08c-.24-.12-1.4-.69-1.62-.77-.22-.08-.38-.12-.54.12-.16.24-.62.77-.76.93-.14.16-.28.18-.52.06-.24-.12-1.01-.37-1.92-1.17-.71-.63-1.19-1.41-1.33-1.65-.14-.24-.01-.37.1-.49.1-.1.24-.28.36-.41.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.29-.74-1.77-.2-.47-.39-.41-.54-.42h-.46c-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.6 4.12 3.65.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.4-.57 1.6-1.12.2-.55.2-1.02.14-1.12-.06-.1-.22-.16-.46-.28Z"/>
                                    </svg>
                                </span>
                                <span>
                                    <span class="contacts-item__label">WhatsApp</span>
                                    <span class="contacts-item__value"><?= esc_html($wa_display) ?></span>
                                </span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($email): ?>
                        <li>
                            <a class="contacts-item" href="mailto:<?= antispambot($email) ?>">
                                <span class="contacts-item__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5L4 8V6l8 5 8-5Z" />
                                    </svg>
                                </span>
                                <span>
                                    <span class="contacts-item__label">Email</span>
                                    <span class="contacts-item__value"><?= esc_html($email) ?></span>
                                </span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($phone && $tel_href): ?>
                        <li>
                            <a class="contacts-item protected-contact"
                                href="javascript:void(0);"
                                data-enc="<?= esc_attr($enc_tel) ?>">
                                <span class="contacts-item__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M22 16.9v2a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 1h2a2 2 0 0 1 2 1.7c.2 1.3.5 2.6 1 3.8a2 2 0 0 1-.5 2.2L7.7 9.8a16 16 0 0 0 6.5 6.5l1.1-1.8a2 2 0 0 1 2.2-.6c1.2.5 2.5.8 3.8 1a2 2 0 0 1 1.7 2z" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                                <span>
                                    <span class="contacts-item__label">Телефон</span>
                                    <span class="contacts-item__value"><?= esc_html($phone) ?></span>
                                </span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="contacts-hours">
                    <p class="contacts-hours__label">Режим работы</p>
                    <p class="contacts-hours__value">Ежедневно: 10:00 - 22:00</p>
                </div>
            </aside>

            <section class="contacts-card">
                <h2 class="contacts-card__title">Напишите нам</h2>
                <p class="contacts-card__hint">Оставьте заявку, и наш менеджер свяжется с вами в ближайшее время.</p>

                <form id="contact-form" class="contacts-form" method="post" data-ajax="<?= esc_url($ajax_url) ?>">
                    <input type="hidden" name="action" value="send_contacts_form">
                    <input type="hidden" name="nonce" value="<?= esc_attr($nonce) ?>">
                    <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">

                    <label class="contacts-field">
                        <span class="contacts-field__label">Ваше имя</span>
                        <input name="name" type="text" required placeholder="Иван" class="contacts-field__control">
                    </label>

                    <label class="contacts-field">
                        <span class="contacts-field__label">Номер телефона</span>
                        <input name="phone" type="tel" required placeholder="+7 999 123-45-67" class="contacts-field__control">
                    </label>

                    <label class="contacts-field">
                        <span class="contacts-field__label">Email</span>
                        <input name="email" type="email" placeholder="mail@example.com" class="contacts-field__control">
                    </label>

                    <label class="contacts-field">
                        <span class="contacts-field__label">Комментарий</span>
                        <textarea name="message" rows="5" placeholder="Ваш вопрос или комментарий..." class="contacts-field__control"></textarea>
                    </label>

                    <button id="contact-submit" type="submit" class="contacts-submit">
                        Отправить сообщение
                    </button>

                    <p class="contacts-privacy">Ваши данные конфиденциальны и не будут переданы третьим лицам.</p>

                    <div id="contact-alert" class="hidden"></div>
                </form>
            </section>
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
            }
        });
    })();
</script>

<?php get_footer(); ?>
