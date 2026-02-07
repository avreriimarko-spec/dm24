<!--
Template Name: 404
-->
<?php
status_header(404);
nocache_headers();
get_header();
?>

<main class="px-4 py-16 md:py-24 bg-white text-black relative overflow-hidden">

    <!-- декоративные пятна -->
    <span class="pointer-events-none absolute -top-24 -left-24 w-72 h-72 rounded-full blur-[70px] opacity-25"
        style="background:#e865a0;"></span>
    <span class="pointer-events-none absolute -bottom-28 -right-20 w-80 h-80 rounded-full blur-[90px] opacity-20"
        style="background:#e865a0;"></span>

    <div class="max-w-[980px] mx-auto relative z-10">

        <!-- Заголовок -->
        <header class="text-center select-none">
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

            <h1 class="mt-4 md:mt-5 text-[26px] md:text-[34px] font-extrabold tracking-tight">
                Страница не найдена
            </h1>
            <p class="mt-3 text-neutral-700 max-w-[680px] mx-auto">
                Похоже, ссылка устарела, была удалена или вы опечатались в адресе.
            </p>
        </header>

        <!-- Действия -->
        <section class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="<?php echo esc_url(home_url('/')); ?>"
                class="text-center px-4 py-3 rounded-xl border border-[#e865a0] bg-[#e865a0] text-white
                hover:bg-white hover:text-[#e865a0] transition active:scale-[.99]">
                На главную
            </a>

            <button type="button"
                onclick="history.back()"
                class="text-center px-4 py-3 rounded-xl border border-neutral-300 bg-white text-black
                     hover:border-[#e865a0] hover:text-[#e865a0] transition active:scale-[.99]">
                Назад
            </button>
        </section>

        <!-- 🔥 Дополнительные полезные ссылки -->
        <section class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="<?php echo esc_url(home_url('/sitemap')); ?>"
                class="text-center px-4 py-3 rounded-xl border border-neutral-300 bg-white text-black
                hover:border-[#e865a0] hover:text-[#e865a0] transition active:scale-[.99]">
                Карта сайта
            </a>

            <a href="<?php echo esc_url(home_url('/vse-uslugi')); ?>"
                class="text-center px-4 py-3 rounded-xl border border-neutral-300 bg-white text-black
                hover:border-[#e865a0] hover:text-[#e865a0] transition active:scale-[.99]">
                Все услуги
            </a>
        </section>

    </div>
</main>

<?php get_footer(); ?>