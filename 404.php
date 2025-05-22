<!-- 
Template Name: 404
-->

<?php
header("HTTP/1.1 404 Not Found");

 get_header(); ?>

<main class="w-full md:w-2/3 mx-auto mt-4 text-center py-12 px-6 bg-white rounded-lg shadow-xl border border-red-700">
    <h1 class="text-[32px] md:text-[50px] font-bold text-red-500 drop-shadow-lg">
        404: Страница не найдена
    </h1>
    <p class="mt-4 text-lg text-red-500">К сожалению, мы не смогли найти страницу, которую вы искали.</p>
    <p class="mt-2 text-lg text-red-500">
        Возможно, она была удалена, её адрес изменился, или она никогда не существовала.
    </p>

    <h3 class="mt-8 text-xl font-semibold text-red-500">Что вы можете сделать:</h3>
    <ul class="mt-4 list-disc list-inside text-lg text-red-500 space-y-2">
        <li>Проверьте URL на наличие опечаток.</li>
        <li>
            <a href="/" class="text-red-500 hover:text-red-400 underline transition duration-300">
                Вернитесь на главную страницу
            </a> и попробуйте найти нужную информацию оттуда.
        </li>

    </ul>

    <p class="mt-6">
        <a href="/sitemap/"
            class="inline-block bg-red-600 text-white py-3 px-8 rounded-lg shadow-md hover:bg-red-500 transition duration-300">
            HTML-Sitemap
        </a>
    </p>

    <p class="mt-8 text-red-500">
        Мы приносим извинения за неудобства и благодарим вас за понимание.
    </p>
</main>




<?php get_footer(); ?>