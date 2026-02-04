<?php
/*
Template Name: Авторизация
*/
if (!defined('ABSPATH')) exit;
get_header();

$ACCENT = '#e1315a';
?>
<main class="mx-auto w-full lg:w-[900px] px-4 py-12">
    <div class="rounded-xl border border-neutral-200 bg-white p-8">
        <h1 class="text-3xl font-bold mb-2">Авторизация</h1>
        <p class="text-neutral-600 mb-8">Войдите в личный кабинет.</p>

        <form action="" method="post" class="space-y-5" novalidate>
            <div>
                <label class="block text-sm font-medium mb-1" for="login-username">Email или телефон</label>
                <input id="login-username" name="username" type="text" inputmode="email"
                    class="w-full rounded-lg border border-neutral-300 px-3 py-2 outline-none focus:border-[<?php echo esc_attr($ACCENT); ?>]"
                    placeholder="name@example.com" autocomplete="username" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1" for="login-password">Пароль</label>
                <input id="login-password" name="password" type="password"
                    class="w-full rounded-lg border border-neutral-300 px-3 py-2 outline-none focus:border-[<?php echo esc_attr($ACCENT); ?>]"
                    placeholder="Введите пароль" autocomplete="current-password" required>
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="remember" class="rounded border-neutral-300">
                    <span class="text-black">Запомнить меня</span>
                </label>
            </div>

            <button type="submit"
                class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg text-white font-medium hover:opacity-90"
                style="background: <?php echo esc_attr($ACCENT); ?>;">
                Войти
            </button>
        </form>

        <div class="mt-6 text-sm text-neutral-700">
            Нет аккаунта?
            <a class="text-[<?php echo esc_attr($ACCENT); ?>] hover:underline" href="<?php echo esc_url(home_url('sing-up')); ?>">
                Зарегистрироваться
            </a>
        </div>

        <div class="mt-8">
            <a href="<?php echo esc_url(home_url('/')); ?>"
                class="inline-flex items-center gap-2 text-black px-4 py-2 rounded-lg border border-neutral-300 hover:border-[<?php echo esc_attr($ACCENT); ?>] hover:text-[<?php echo esc_attr($ACCENT); ?>] transition-colors">
                ← На главную
            </a>
        </div>
    </div>
</main>
<?php get_footer();
