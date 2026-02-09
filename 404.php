<!--
Template Name: 404
-->
<?php
status_header(404);
nocache_headers();
get_header();
?>

<main class="page-hero page-hero--404">
    <div class="page-hero__inner">
        <div class="page-card page-card--padded">

        <!-- Заголовок -->
        <header class="select-none text-center">
            <div class="inline-flex items-center justify-center">
                <span class="relative inline-block">
                    <span class="absolute -inset-4 rounded-[28px] bg-[rgba(232,101,160,.15)] blur-xl"></span>
                    <span class="relative text-[70px] md:text-[110px] font-extrabold leading-none tracking-tighter
                       bg-clip-text text-transparent"
                        style="background-image:linear-gradient(180deg,#e865a0 0%,#c21058 100%);">
                        404
                    </span>
                </span>
            </div>

            <h1 class="mt-4 md:mt-5 page-title">
                Страница не найдена
            </h1>
            <p class="mt-3 page-lead">Похоже, ссылка устарела, была удалена или вы опечатались в адресе.</p>
        </header>

        <!-- Действия -->
        <section class="mt-6 flex flex-col sm:flex-row gap-3">
            <a href="<?php echo esc_url(home_url('/')); ?>"
                class="btn-accent">
                На главную
            </a>

            <button type="button"
                onclick="history.back()"
                class="btn-ghost">
                Назад
            </button>
        </section>

        <!-- 🔥 Дополнительные полезные ссылки -->
        <section class="mt-6 flex flex-col sm:flex-row gap-3">
            <a href="<?php echo esc_url(home_url('/sitemap')); ?>"
                class="btn-ghost">
                Карта сайта
            </a>

            <a href="<?php echo esc_url(home_url('/vse-uslugi')); ?>"
                class="btn-ghost">
                Все услуги
            </a>
        </section>

        </div>
    </div>
</main>

<?php get_footer(); ?>
