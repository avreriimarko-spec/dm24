<?php
/*
Template Name: Регистрация
*/
if (!defined('ABSPATH')) exit;
get_header();

$ACCENT = '#e1315a';
?>
<main class="mx-auto w-full lg:w-[900px] px-4 py-12">
    <div class="rounded-xl border border-neutral-200 bg-white p-8">
        <h1 class="text-3xl font-bold mb-2">Регистрация</h1>
        <p class="text-neutral-600 mb-8">Создайте аккаунт за минуту.</p>

        <form action="" method="post" class="space-y-5" novalidate>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1" for="reg-name">Имя</label>
                    <input id="reg-name" name="name" type="text"
                        class="w-full rounded-lg border border-neutral-300 px-3 py-2 outline-none focus:border-[<?php echo esc_attr($ACCENT); ?>]"
                        placeholder="Ваше имя" autocomplete="name" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="reg-phone">Телефон</label>
                    <input id="reg-phone" name="phone" type="tel" inputmode="tel"
                        class="w-full rounded-lg border border-neutral-300 px-3 py-2 outline-none focus:border-[<?php echo esc_attr($ACCENT); ?>]"
                        placeholder="+7 777 123-45-67" autocomplete="tel">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1" for="reg-email">Email</label>
                <input id="reg-email" name="email" type="email"
                    class="w-full rounded-lg border border-neutral-300 px-3 py-2 outline-none focus:border-[<?php echo esc_attr($ACCENT); ?>]"
                    placeholder="name@example.com" autocomplete="email" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1" for="reg-pass">Пароль</label>
                    <input id="reg-pass" name="password" type="password"
                        class="w-full rounded-lg border border-neutral-300 px-3 py-2 outline-none focus:border-[<?php echo esc_attr($ACCENT); ?>]"
                        placeholder="Минимум 8 символов" autocomplete="new-password" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="reg-pass2">Повторите пароль</label>
                    <input id="reg-pass2" name="password2" type="password"
                        class="w-full rounded-lg border border-neutral-300 px-3 py-2 outline-none focus:border-[<?php echo esc_attr($ACCENT); ?>]"
                        placeholder="Ещё раз пароль" autocomplete="new-password" required>
                </div>
            </div>

            <button type="submit"
                class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg text-white font-medium hover:opacity-90"
                style="background: <?php echo esc_attr($ACCENT); ?>;">
                Зарегистрироваться
            </button>
        </form>

        <div class="mt-6 text-sm text-neutral-700">
            Уже есть аккаунт?
            <a class="text-[<?php echo esc_attr($ACCENT); ?>] hover:underline" href="<?php echo esc_url(home_url('login')); ?>">
                Войти
            </a>
        </div>

        <div class="mt-8">
            <a href="<?php echo esc_url(home_url('/')); ?>"
                class="inline-flex items-center text-black gap-2 px-4 py-2 rounded-lg border border-neutral-300 hover:border-[<?php echo esc_attr($ACCENT); ?>] hover:text-[<?php echo esc_attr($ACCENT); ?>] transition-colors">
                ← На главную
            </a>
        </div>
    </div>
</main>
<?php get_footer();
