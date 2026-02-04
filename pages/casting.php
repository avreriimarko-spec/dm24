<?php

/**
 * Template Name: Кастинг (Квиз)
 * Description: Исправлена валидация W3C (select options value="").
 */
if (!defined('ABSPATH')) exit;

get_header();

$h1       = get_field('h1') ?: get_the_title();
$p        = get_field('p') ?: '';
$seo_text = get_field('seo') ?: '';
$faq      = get_field('faq') ?: [];

$ajax_url = admin_url('admin-ajax.php');
$nonce    = wp_create_nonce('casting_nonce');
?>

<main class="px-4 py-10 text-black">
    <div class="max-w-[900px] mx-auto space-y-10">

        <!-- Заголовок -->
        <header class="text-center">
            <h1 class="text-3xl md:text-5xl font-extrabold text-black tracking-wide drop-shadow-md">
                <?= esc_html($h1); ?>
            </h1>
            <?php if ($p): ?>
                <p class="mt-3 text-black"><?= esc_html($p); ?></p>
            <?php endif; ?>
        </header>

        <!-- Контейнер квиза -->
        <div id="quiz-container" class="rounded-2xl border border-[#2E2E33] shadow-xl p-6 md:p-8">
            <p class="text-black">Заполни простую форму ниже, чтобы подать заявку. Нам нужны только базовые данные. Без фото на этом этапе — мы уважаем твою приватность.</p>

            <!-- Прогресс (Валидный HTML без style) -->
            <div class="rounded-full h-2 bg-neutral-800 overflow-hidden mb-6">
                <div id="progress-fill" class="h-full w-0 transition-all duration-500 ease-in-out bg-[#ff2d72]"></div>
            </div>

            <!-- Форма -->
            <form id="casting-form" class="space-y-8" method="post" data-ajax="<?= esc_url($ajax_url) ?>" novalidate>
                <input type="hidden" name="action" value="casting_submit">
                <input type="hidden" name="nonce" value="<?= esc_attr($nonce) ?>">

                <!-- ШАГ 1 -->
                <div data-step="1" class="quiz-step space-y-5">
                    <p class="text-xl font-semibold text-black">Шаг 1: Основные данные</p>

                    <!-- Имя -->
                    <div>
                        <label class="block text-sm text-neutral-400 mb-1">Имя *</label>
                        <input name="name" type="text" required placeholder="Ваше имя"
                            class="w-full rounded-lg border border-[#3A3A40] text-black px-3 py-2 focus:border-[#ff2d72] focus:ring-2 focus:ring-[#ff2d72]/30 outline-none transition">
                    </div>

                    <!-- Возраст -->
                    <div>
                        <label class="block text-sm text-neutral-400 mb-1">Возраст *</label>
                        <select name="age" required
                            class="w-full rounded-lg border border-[#3A3A40] text-black px-3 py-2 focus:border-[#ff2d72] focus:ring-2 focus:ring-[#ff2d72]/30 outline-none transition">
                            <!-- ВАЖНО: value="" обязательно для required select -->
                            <option value="" disabled selected>Выберите возраст</option>
                            <option value="18–21">18–21</option>
                            <option value="22–25">22–25</option>
                            <option value="26–30">26–30</option>
                            <option value="30+">30+</option>
                        </select>
                    </div>

                    <div class="flex justify-end">
                        <button type="button"
                            class="bg-[#ff2d72] hover:bg-[#ff2d72]/90 text-white font-semibold px-5 py-2.5 rounded-lg transition"
                            onclick="nextStep()">Далее</button>
                    </div>
                </div>

                <!-- ШАГ 2 -->
                <div data-step="2" class="quiz-step space-y-5 hidden">
                    <p class="text-xl font-semibold text-black">Шаг 2: Внешность</p>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-black mb-1">Рост *</label>
                            <input name="height" type="number" required
                                class="w-full rounded-lg border border-[#3A3A40] text-black px-3 py-2 focus:border-[#ff2d72] focus:ring-2 focus:ring-[#ff2d72]/30 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm text-black mb-1">Вес *</label>
                            <input name="weight" type="number" required
                                class="w-full rounded-lg border border-[#3A3A40] text-black px-3 py-2 focus:border-[#ff2d72] focus:ring-2 focus:ring-[#ff2d72]/30 outline-none transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm text-black mb-1">Тип фигуры *</label>
                        <select name="body_type" required
                            class="w-full rounded-lg border border-[#3A3A40] text-black px-3 py-2 focus:border-[#ff2d72] focus:ring-2 focus:ring-[#ff2d72]/30 outline-none transition">
                            <!-- ВАЖНО: value="" обязательно для required select -->
                            <option value="" disabled selected>Выберите тип</option>
                            <option value="Стройная">Стройная</option>
                            <option value="Пышная">Пышная</option>
                            <option value="Средняя">Средняя</option>
                            <option value="Атлетичная">Атлетичная</option>
                        </select>
                    </div>

                    <div class="flex justify-between">
                        <button type="button"
                            class="border border-[#3A3A40] text-black px-5 py-2.5 rounded-lg hover:bg-[#2A2A2E]"
                            onclick="prevStep()">Назад</button>
                        <button type="button"
                            class="bg-[#ff2d72] hover:bg-[#ff2d72]/90 text-white px-5 py-2.5 rounded-lg"
                            onclick="nextStep()">Далее</button>
                    </div>
                </div>

                <!-- ШАГ 3 -->
                <div data-step="3" class="quiz-step space-y-5 hidden">
                    <p class="text-xl font-semibold text-black">Шаг 3: Контакты</p>

                    <!-- Выбор способа связи -->
                    <div>
                        <span class="block text-sm text-black mb-2">Как связаться? *</span>
                        <div class="flex gap-4 text-black">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="contact_type" value="telegram" required class="accent-[#ff2d72]">
                                <span>Telegram</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="contact_type" value="whatsapp" class="accent-[#ff2d72]">
                                <span>WhatsApp</span>
                            </label>
                        </div>
                    </div>

                    <!-- Контакт -->
                    <div>
                        <label class="block text-sm text-black mb-1">Контакт (ник или номер) *</label>
                        <input name="contact" type="text" required placeholder="@username или +380..."
                            class="w-full rounded-lg border border-[#3A3A40] text-black px-3 py-2 focus:border-[#ff2d72] focus:ring-2 focus:ring-[#ff2d72]/30 outline-none transition">
                    </div>

                    <div class="flex justify-between">
                        <button type="button"
                            class="border border-[#3A3A40] text-black px-5 py-2.5 rounded-lg hover:bg-[#2A2A2E]"
                            onclick="prevStep()">Назад</button>
                        <button type="submit"
                            class="bg-[#ff2d72] hover:bg-[#ff2d72]/90 text-white px-5 py-2.5 rounded-lg">Отправить</button>
                    </div>
                </div>
            </form>

            <!-- Сообщение об успехе -->
            <div id="success-message" class="hidden text-center mt-8 border border-[#3A3A40] p-8 rounded-xl">
                <h2 class="text-2xl font-bold mb-3 text-black">Анкета принята!</h2>
                <p class="text-black mb-5">Менеджер свяжется с вами в течение 30 минут.</p>
                <a href="/" class="inline-block bg-[#ff2d72] hover:bg-[#ff2d72]/90 text-white px-6 py-2.5 rounded-lg font-semibold">На главную</a>
            </div>
        </div>

        <!-- FAQ -->
        <?php if (!empty($faq['faq_items'])): ?>
            <section id="faq" class="rounded-2xl border border-[#2E2E33] p-6 md:p-8">
                <h2 class="text-2xl md:text-3xl font-bold mb-6 text-black text-center">FAQ</h2>
                <div class="space-y-3">
                    <?php foreach ($faq['faq_items'] as $row):
                        $q = trim((string)($row['question'] ?? ''));
                        $a = (string)($row['answer'] ?? '');
                        if ($q === '' && $a === '') continue; ?>
                        <details class="border border-[#3A3A40] rounded-lg p-4">
                            <summary class="cursor-pointer font-semibold text-[#ff2d72]"><?= esc_html($q); ?></summary>
                            <div class="mt-2 text-black text-sm"><?= wp_kses_post($a); ?></div>
                        </details>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- SEO текст -->
        <?php if ($seo_text): ?>
            <section class="content prose prose-invert max-w-none border border-[#2E2E33] rounded-2xl p-6 md:p-8 text-white">
                <?= apply_filters('the_content', $seo_text); ?>
            </section>
        <?php endif; ?>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let currentStep = 1;
            const totalSteps = 3;
            const steps = document.querySelectorAll('.quiz-step');
            const progress = document.getElementById('progress-fill');
            const form = document.getElementById('casting-form');
            const success = document.getElementById('success-message');

            const q = (s, c = document) => c.querySelector(s);
            const qa = (s, c = document) => Array.from(c.querySelectorAll(s));

            function showStep(n) {
                steps.forEach(s => s.classList.add('hidden'));
                q(`[data-step="${n}"]`)?.classList.remove('hidden');

                const percentage = (n / totalSteps) * 100;
                progress.style.width = percentage + '%';
            }

            function clearErrors(stepEl) {
                qa('input,select,textarea', stepEl).forEach(el => {
                    el.style.borderColor = '';
                    el.style.boxShadow = '';
                    el.removeAttribute('aria-invalid');
                    const lbl = el.closest('div')?.querySelector('label');
                    if (lbl) lbl.style.color = '';
                });
                qa('.js-error', stepEl).forEach(e => e.remove());
                qa('.js-radio-err', stepEl).forEach(e => e.style.boxShadow = '');
            }

            function markError(el, msg = 'Заполните поле') {
                el.style.borderColor = '#ff0000';
                el.style.boxShadow = '0 0 0 1px #ff0000';
                el.setAttribute('aria-invalid', 'true');
                const oldErr = el.parentElement.querySelector('.js-error');
                if (oldErr) oldErr.remove();

                const p = document.createElement('p');
                p.className = 'js-error mt-1 text-sm';
                p.style.color = '#ff0000';
                p.textContent = msg;
                const wrap = el.parentElement || el;
                const lbl = wrap.querySelector('label');
                if (lbl) lbl.style.color = '#ff0000';
                wrap.appendChild(p);
            }

            function markRadioGroupError(container, msg = 'Выберите вариант') {
                container.classList.add('js-radio-err');
                container.style.boxShadow = '0 0 0 1px #ff0000';
                const oldErr = container.querySelector('.js-error');
                if (oldErr) oldErr.remove();
                const p = document.createElement('p');
                p.className = 'js-error mt-2 text-sm';
                p.style.color = '#ff0000';
                p.textContent = msg;
                container.appendChild(p);
            }

            function validateStep(n, scrollToError = true) {
                const stepEl = q(`[data-step="${n}"]`);
                if (!stepEl) return true;

                clearErrors(stepEl);
                let ok = true,
                    firstBad = null;

                qa('[required]', stepEl).forEach(el => {
                    if (el.type === 'radio') return;
                    if (!String(el.value || '').trim()) {
                        ok = false;
                        if (!firstBad) firstBad = el;
                        markError(el);
                    }
                });

                const names = qa('input[type="radio"][name]', stepEl)
                    .reduce((set, el) => set.add(el.name), new Set());
                names.forEach(name => {
                    const group = qa(`input[type="radio"][name="${name}"]`, stepEl);
                    const required = group.some(r => r.hasAttribute('required'));
                    if (!required) return;
                    const checked = group.some(r => r.checked);
                    if (!checked) {
                        ok = false;
                        if (!firstBad) firstBad = group[0];
                        const container = group[0].closest('div') || stepEl;
                        markRadioGroupError(container, 'Выберите вариант');
                    }
                });

                if (n === 1) {
                    const name = q('input[name="name"]', stepEl);
                    if (name && name.value.trim().length < 2) {
                        ok = false;
                        if (!firstBad) firstBad = name;
                        markError(name, 'Минимум 2 символа');
                    }
                }
                if (n === 2) {
                    const height = q('input[name="height"]', stepEl);
                    const weight = q('input[name="weight"]', stepEl);
                    const inRange = (el, min, max) => {
                        const v = parseInt((el.value || '').trim(), 10);
                        return Number.isFinite(v) && v >= min && v <= max;
                    };
                    if (height && !inRange(height, 140, 200)) {
                        ok = false;
                        if (!firstBad) firstBad = height;
                        markError(height, 'Укажите рост 140–200 см');
                    }
                    if (weight && !inRange(weight, 40, 120)) {
                        ok = false;
                        if (!firstBad) firstBad = weight;
                        markError(weight, 'Укажите вес 40–120 кг');
                    }
                }
                if (n === 3) {
                    const type = q('input[name="contact_type"]:checked')?.value || '';
                    const contact = q('input[name="contact"]', stepEl);
                    if (!type) {
                        ok = false;
                        const radioWrap = q('input[name="contact_type"]', stepEl)?.closest('div').parentElement;
                        markRadioGroupError(radioWrap, 'Выберите способ связи');
                    }
                    if (contact) {
                        const val = contact.value.trim();
                        if (!val) {
                            ok = false;
                            if (!firstBad) firstBad = contact;
                            markError(contact);
                        } else if (type === 'telegram') {
                            const nick = val.replace(/^https?:\/\/t\.me\//i, '').replace(/^@/, '');
                            if (!/^[a-z0-9_]{5,32}$/i.test(nick)) {
                                ok = false;
                                if (!firstBad) firstBad = contact;
                                markError(contact, 'Telegram: @username или t.me/username (5–32)');
                            }
                        } else if (type === 'whatsapp') {
                            const digits = val.replace(/\D+/g, '');
                            if (!/^\d{10,15}$/.test(digits)) {
                                ok = false;
                                if (!firstBad) firstBad = contact;
                                markError(contact, 'WhatsApp: только цифры (10–15)');
                            }
                        }
                    }
                }

                if (!ok && scrollToError && firstBad) {
                    firstBad.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    firstBad.focus({
                        preventScroll: true
                    });
                }
                return ok;
            }

            window.nextStep = () => {
                if (validateStep(currentStep)) {
                    if (currentStep < totalSteps) {
                        currentStep++;
                        showStep(currentStep);
                    }
                }
            };

            window.prevStep = () => {
                if (currentStep > 1) {
                    currentStep--;
                    showStep(currentStep);
                }
            };

            qa('input,select,textarea', form).forEach(el => {
                const clear = () => {
                    el.style.borderColor = '';
                    el.style.boxShadow = '';
                    const lbl = el.closest('div')?.querySelector('label');
                    if (lbl) lbl.style.color = '';
                };
                el.addEventListener('input', clear);
                el.addEventListener('change', clear);
            });

            const contactInput = q('input[name="contact"]');
            qa('input[name="contact_type"]').forEach(r => {
                r.addEventListener('change', () => {
                    const container = r.closest('div').parentElement;
                    container.classList.remove('js-radio-err');
                    container.style.boxShadow = '';
                    const errText = container.querySelector('.js-error');
                    if (errText) errText.remove();

                    if (!contactInput) return;
                    contactInput.placeholder = r.value === 'telegram' ?
                        '@username или t.me/username' :
                        '+380XXXXXXXXX';
                });
            });

            form.addEventListener('submit', async e => {
                e.preventDefault();
                for (let s = 1; s <= totalSteps; s++) {
                    if (!validateStep(s, s === currentStep)) {
                        if (currentStep !== s) {
                            currentStep = s;
                            showStep(currentStep);
                            setTimeout(() => validateStep(s, true), 100);
                        }
                        return;
                    }
                }

                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerText = 'Отправка...';
                }

                try {
                    const fd = new FormData(form);
                    const res = await fetch(form.dataset.ajax, {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin'
                    });
                    const json = await res.json();
                    if (json.success) {
                        form.classList.add('hidden');
                        progress.style.width = '100%';
                        success.classList.remove('hidden');
                        success.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    } else {
                        alert(json.data?.message || 'Ошибка отправки');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerText = 'Отправить';
                        }
                    }
                } catch (error) {
                    console.error(error);
                    alert('Ошибка сети. Попробуйте позже.');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerText = 'Отправить';
                    }
                }
            });

            showStep(1);
        });
    </script>

</main>

<?php get_footer(); ?>