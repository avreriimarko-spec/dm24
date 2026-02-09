<?php

/**
 * Template Name: Вакансии (обновлённый стиль)
 * Description: Хлебные крошки, H1, список вакансий, форма отклика (AJAX), SEO HTML.
 */
if (!defined('ABSPATH')) exit;

get_header();

$ACCENT   = '#e865a0';
$has_acf  = function_exists('get_field');

/** Заголовки */
$h1   = $has_acf ? (get_field('h1') ?: get_the_title()) : get_the_title();
$lead = $has_acf ? (get_field('p')  ?: '') : '';
$h2   = $has_acf ? (get_field('h2') ?: 'Открытые вакансии') : 'Открытые вакансии';

/** Группа «vacancies» → повторитель «vacancies_blocks» */
$vacancies_group = $has_acf ? (get_field('vacancies') ?: []) : [];
$vacancy_blocks  = (is_array($vacancies_group) && !empty($vacancies_group['vacancies_blocks']) && is_array($vacancies_group['vacancies_blocks']))
    ? $vacancies_group['vacancies_blocks']
    : [];

/** SEO WYS */
$seo_html = $has_acf ? (get_field('seo') ?: '') : '';

/** AJAX */
$ajax_url = admin_url('admin-ajax.php');
$nonce    = wp_create_nonce('vacancy_apply_nonce');

/** Список должностей для селекта формы */
$positions = array_values(array_filter(array_map(function ($row) {
    return trim((string)($row['title'] ?? ''));
}, $vacancy_blocks)));
?>

<main class="px-4 py-10 bg-white text-black">
    <div class="max-w-[1100px] mx-auto">

        <!-- Хлебные крошки (делаем компактнее) -->
        <div class="mb-5">
            <style>
                /* Чуть подправим внешний вид крошек внутри этого шаблона */
                .breadcrumbs a {
                    color: #555
                }

                .breadcrumbs a:hover {
                    color: <?= esc_attr($ACCENT) ?>
                }

                .breadcrumbs .sep {
                    opacity: .5
                }
            </style>
        </div>

        <!-- H1 + лид -->
        <header class="mb-8 md:mb-10">
            <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight leading-tight"
                style="letter-spacing:-.02em">
                <?= esc_html($h1) ?>
            </h1>
            <?php if ($lead): ?>
                <div class="content mt-4 text-[15px] md:text-base text-neutral-700 leading-relaxed">
                    <?= wp_kses_post($lead) ?>
                </div>
            <?php endif; ?>
        </header>

        <!-- Заголовок раздела вакансий -->
        <!--         <h2 class="text-xl md:text-2xl font-bold mb-4"
            style="color:<?= esc_attr($ACCENT) ?>">
            <?= esc_html($h2) ?>
        </h2> -->

        <!-- Вакансии -->
        <!--         <?php if (!empty($vacancy_blocks)): ?>
            <section class="space-y-4 md:space-y-5 mb-10">
                <?php foreach ($vacancy_blocks as $it):
                                $v_title  = trim((string)($it['title']  ?? ''));
                                $v_desc   = (string)($it['desc']   ?? '');
                                $v_salary = trim((string)($it['salary'] ?? ''));
                ?>
                    <article class="rounded-2xl border bg-white overflow-hidden"
                        style="border-color:<?= esc_attr($ACCENT) ?>22">
                        <div class="p-5 md:p-6">
                            <div class="flex items-start justify-between gap-4">
                                <h3 class="text-lg md:text-xl font-semibold">
                                    <?= esc_html($v_title ?: 'Вакансия') ?>
                                </h3>
                                <?php if ($v_salary !== ''): ?>
                                    <div class="shrink-0 text-sm md:text-base font-semibold"
                                        style="color:<?= esc_attr($ACCENT) ?>">
                                        <?= esc_html($v_salary) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($v_desc !== ''): ?>
                                <div class="mt-3 prose max-w-none prose-p:leading-relaxed prose-ul:my-2 prose-li:my-1">
                                    <?= wp_kses_post(apply_filters('the_content', $v_desc)) ?>
                                </div>
                            <?php endif; ?>

                            <div class="mt-4">
                                <a href="#vacancy-apply"
                                    class="inline-flex items-center justify-center px-4 py-2 font-semibold text-white active:translate-y-px transition"
                                    style="background:<?= esc_attr($ACCENT) ?>;">
                                    Откликнуться
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php else: ?>
            <p class="text-neutral-600 mb-10">Сейчас открытых вакансий нет.</p>
        <?php endif; ?> -->

        <!-- Форма отклика -->
        <section id="vacancy-apply" class="rounded-2xl border bg-white p-5 md:p-6 mb-10"
            style="border-color:<?= esc_attr($ACCENT) ?>22">
            <h3 class="text-lg md:text-xl font-bold">Отправить заявку</h3>
            <p class="text-sm text-neutral-700 mt-1">Оставьте контакты — свяжемся с вами.</p>

            <form id="vacancy-form" class="mt-4 space-y-4" method="post" data-ajax="<?= esc_url($ajax_url) ?>">
                <input type="hidden" name="action" value="vacancy_apply">
                <input type="hidden" name="nonce" value="<?= esc_attr($nonce) ?>">
                <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off"><!-- honeypot -->

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="block">
                        <span class="block text-sm mb-1">Имя*</span>
                        <input name="name" type="text" required placeholder="Ваше имя"
                            class="w-full border px-3 py-2 bg-white placeholder-neutral-400 outline-none focus:ring-2"
                            style="border-color:<?= esc_attr($ACCENT) ?>33; --tw-ring-color: <?= esc_attr($ACCENT) ?>;">
                    </label>
                    <label class="block">
                        <span class="block text-sm mb-1">Телефон*</span>
                        <input name="phone" type="tel" required placeholder="+7 999 000-00-00"
                            class="w-full border px-3 py-2 bg-white placeholder-neutral-400 outline-none focus:ring-2"
                            style="border-color:<?= esc_attr($ACCENT) ?>33; --tw-ring-color: <?= esc_attr($ACCENT) ?>;">
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="block">
                        <span class="block text-sm mb-1">Город (необязательно)</span>
                        <input name="city" type="text" placeholder="Ваш город"
                            class="w-full border px-3 py-2 bg-white placeholder-neutral-400 outline-none focus:ring-2"
                            style="border-color:<?= esc_attr($ACCENT) ?>33; --tw-ring-color: <?= esc_attr($ACCENT) ?>;">
                    </label>
                </div>

                <label class="block">
                    <span class="block text-sm mb-1">Сообщение (необязательно)</span>
                    <textarea name="message" rows="4" placeholder="Коротко о себе, опыт, удобное время для связи"
                        class="w-full border px-3 py-2 bg-white placeholder-neutral-400 outline-none focus:ring-2"
                        style="border-color:<?= esc_attr($ACCENT) ?>33; --tw-ring-color: <?= esc_attr($ACCENT) ?>;"></textarea>
                </label>

                <label class="block">
                    <span class="block text-sm mb-1">Фото (до 5 шт., JPG/PNG/WebP, ≤ 8 МБ за файл)</span>
                    <input name="photo[]" type="file" accept=".jpg,.jpeg,.png,.webp" multiple
                        class="w-full border px-3 py-2 bg-white file:mr-3 file:border-0 file:px-3 file:py-2 file:text-white hover:file:opacity-90"
                        style="border-color:<?= esc_attr($ACCENT) ?>33; color:inherit; --file-bg: <?= esc_attr($ACCENT) ?>;">
                    <style>
                        input[type=file]::file-selector-button {
                            background: var(--file-bg);
                        }
                    </style>
                </label>

                <button id="vacancy-submit" type="submit"
                    class="inline-flex items-center justify-center px-5 py-2.5 font-semibold text-white active:translate-y-px transition"
                    style="background:<?= esc_attr($ACCENT) ?>;">
                    Отправить заявку
                </button>

                <div id="vacancy-alert" class="hidden mt-3 text-sm px-3 py-2 border"
                    style="border-color:<?= esc_attr($ACCENT) ?>33"></div>
            </form>
        </section>

        <!-- SEO-текст (HTML блок) -->
        <?php if (!empty($seo_html)): ?>
            <section class="mb-12">
                <div class="prose content max-w-none prose-p:leading-relaxed prose-h2:mt-6 prose-h2:mb-3">
                    <?= apply_filters('the_content', $seo_html) ?>
                </div>
            </section>
        <?php endif; ?>

    </div>
</main>

<script>
    (function() {
        const form = document.getElementById('vacancy-form');
        if (!form) return;
        const ajaxUrl = form.dataset.ajax || '/wp-admin/admin-ajax.php';
        const submitBtn = document.getElementById('vacancy-submit');
        const alertBox = document.getElementById('vacancy-alert');

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
                setAlert((j.data && j.data.message) || 'Заявка отправлена!');
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

<?php get_footer();
