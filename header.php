<?php $title = get_field('title');
$descr = get_field('descr');
$keywords = get_field('keywords');
?>


<!DOCTYPE html>
<html lang="ru">

<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo esc_html($title); ?></title>
        <meta name="description" content="<?php echo esc_html($descr); ?>" />
        <link rel="canonical" href="<?php echo esc_url(get_permalink()); ?>">
        <meta name="keywords" content="<?php echo esc_html($keywords); ?>" />
        <link rel="icon" href="favicon100.png"
                sizes="32x32" />
        <link rel="icon" href="favicon300.png"
                sizes="192x192" />
        <link rel="apple-touch-icon" href="favicon300.png" />
        <link rel="icon" type="image/x-icon" href="favicon.ico">
        <meta name='robots' content="index, follow, max-snippet:-1, max-video-preview:-1">
        <?php wp_head(); ?>
        <?php get_template_part('template-parts/schema/json-id'); ?>

</head>

<body class="bg-white">
       <header class="sticky top-0 z-20 mx-1 bg-white text-red-600 flex justify-between items-center border border-red-600 p-4 rounded-lg shadow-md backdrop-blur-md"
        data-x-data="{ open: true, openModal: false, openMenu: false, dropdownOpen: null }">



                        <!-- logo -->
                        <div class="hover:scale-110 transition duration-300">
                                <a href="https://escortminsk.com/">
                                        <img class="w-12 h-12 object-contain"
                                                src="<?php echo get_template_directory_uri(); ?>/assets/icons/logo.png"
                                                alt="logo" width="50" height="50">
                                </a>
                        </div>
                        <!-- navigation -->
                        <nav class="hidden md:flex w-full justify-center items-center">
                                <ul data-x-data="{ open: false, dropdownOpen: null }"
                                        class="relative text-[18px] font-normal flex flex-row gap-6 justify-center items-center text-lg list-none m-0">
                                        <li><a href="/individualki/"
                                                        class="text-red-600 transition-text duration-200">Индивидуалки</a>
                                        </li>
                                        <li><a href="/proverennyye-prostitutki/"
                                                        class="text-red-600 transition-text duration-200">Проверенные</a>
                                        </li>
                                        <li><a href="/prostitutki-na-vyyezd/"
                                                        class="text-red-600 transition-text duration-200">На
                                                        выезд</a></li>
                                        <li><a href="/elitnye-prostitutki/"
                                                        class="text-red-600 transition-text duration-200">Элитные</a>
                                        </li>
                                        <li><a href="/eskortnitsy/"
                                                        class="text-red-600 transition-text duration-200">Эскортницы</a>
                                        </li>
                                        <li><a href="/prsotitutki-s-video/"
                                                        class="text-red-600 transition-text duration-200">С
                                                        видео</a></li>
                                        <li><a href="/prsotitutki-bez-retushi/"
                                                        class="text-red-600 transition-text duration-200">Без
                                                        ретуши</a></li>
                                </ul>
                        </nav>
                        <!-- links -->
                        <div class="flex flex-row gap-6 justify-center items-center text-4xl">
                                <p data-x-on-click="openModal = true" class="text-[18px] cursor-pointer m-0">Фильтры</p>
                                <button data-x-on-click="openModal = true" title="Поиск"
                                        class="flex hover:scale-110 transition-scale duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="40" height="40" fill="currentColor" class="text-red-600">
    <path d="M505 442l-99-99c28-35 45-79 45-127C451 98 353 0 226 0S1 98 1 226s98 226 225 226c48 0 92-17 127-45l99 99c12 12 32 12 44 0s12-32 0-44zM226 384a158 158 0 1 1 0-316 158 158 0 1 1 0 316z"/>
</svg>
                                </button>
                        </div>

                        <button data-x-on-click="openMenu = !openMenu" title="Меню" class="md:hidden text-3xl text-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="40" height="40" fill="red" class="text-white">
    <path d="M16 132h416c9 0 16-7 16-16V84c0-9-7-16-16-16H16c-9 0-16 7-16 16v32c0 9 7 16 16 16zm0 128h416c9 0 16-7 16-16v-32c0-9-7-16-16-16H16c-9 0-16 7-16 16v32c0 9 7 16 16 16zm0 128h416c9 0 16-7 16-16v-32c0-9-7-16-16-16H16c-9 0-16 7-16 16v32c0 9 7 16 16 16z"/>
</svg>


                        </button>

                <!-- Mоб. версия nav -->
                <div data-x-show="openMenu" data-x-cloak data-x-transition
                        class="fixed inset-0 bg-white text-red-600 bg-opacity-80 z-50 flex flex-col items-center justify-center text-2xl">
                        <button data-x-on-click="openMenu = false" class="absolute top-5 right-5 text-4xl text-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 352 512" width="40" height="40" fill="red" class="text-white">
    <path d="M242 256l100-100c12-12 12-32 0-44s-32-12-44 0L192 212 92 112c-12-12-32-12-44 0s-12 32 0 44l100 100-100 100c-12 12-12 32 0 44s32 12 44 0l100-100 100 100c12 12 32 12 44 0s12-32 0-44L242 256z"/>
</svg>

                        </button>
                        <ul class="space-y-6 text-center list-none">
                                <li><a href="/individualki/" class="block text-red-500 hover:text-red-600"
                                                data-x-on-click="openMenu = false">Индивидуалки</a>
                                </li>
                                <li><a href="/proverennyye-prostitutki/" class="block text-red-500 hover:text-red-600"
                                                data-x-on-click="openMenu = false">Проверенные</a></li>
                                <li><a href="/prostitutki-na-vyyezd/" class="block text-red-500 hover:text-red-600"
                                                data-x-on-click="openMenu = false">На
                                                выезд</a></li>
                                <li><a href="/elitnye-prostitutki/" class="block text-red-500 hover:text-red-600"
                                                data-x-on-click="openMenu = false">Элитные</a>
                                </li>
                                <li><a href="/eskortnitsy/" class="block text-red-500 hover:text-red-600"
                                                data-x-on-click="openMenu = false">Эскортичны</a>
                                </li>
                                <li><a href="/prsotitutki-s-video/" class="block text-red-500 hover:text-red-600"
                                                data-x-on-click="openMenu = false">С
                                                видео</a></li>
                                <li><a href="/prsotitutki-bez-retushi/" class="block text-red-500 hover:text-red-600"
                                                data-x-on-click="openMenu = false">Без
                                                ретуши</a></li>
                        </ul>
                </div>

                <!-- Pop-up для поиска -->
                <div data-x-show="openModal" data-x-cloak data-x-transition
                        class="fixed top-0 left-0 w-full h-[100vh] bg-black bg-opacity-50 z-10 flex justify-center items-center">
                        <div
                                class="w-full h-full overflow-scroll bg-black text-white border border-red-600 rounded-lg relative p-6">
                                <div class="flex justify-between items-center border-b border-red-600 pb-4">
                                        <p class="text-red-600 text-xl font-semibold">Поиск</p>
                                        <button data-x-on-click="openModal = false" class="text-red-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 352 512" width="30" height="30" fill="white">
    <path d="M242 256l100-100c12-12 12-32 0-44s-32-12-44 0L192 212 92 112c-12-12-32-12-44 0s-12 32 0 44l100 100-100 100c-12 12-12 32 0 44s32 12 44 0l100-100 100 100c12 12 32 12 44 0s12-32 0-44L242 256z"/>
</svg>

                                        </button>
                                </div>

                                <div class="mx-auto px-4 overflow-y-auto md:h-auto">

                                        <!-- Прокручиваемый контейнер на мобильных -->
                                        <div class="mt-5 flex gap-6 overflow-x-auto md:overflow-x-hidden overflow-y-auto justify-center items-baseline md:items-center flex-col md:flex-row touch-auto"
                                                data-x-data="{ openCategory: null }">

                                                <!-- Услуги -->
                                                <div class="category bg-white p-4 rounded-lg border border-red-600 shadow-lg w-72"
                                                        data-x-data="{ activeSub: null }">

                                                        <!-- Заголовок "Услуги" -->
                                                        <div class="category-title text-red-500 font-semibold text-lg">
                                                                <a href="/intim-uslugi/">Услуги</a>
                                                        </div>

                                                        <ul class="category-list mt-2 space-y-1 list-none">

                                                                <!-- Секс Услуги -->
                                                                <li class="list-none">
                                                                        <div class="flex justify-between items-center cursor-pointer "
                                                                                data-x-on-click="activeSub = activeSub === 'sex' ? null : 'sex'">
                                                                                <p class="category-p">Секс Услуги</p>
                                                                                <button
                                                                                        class="text-red-500 text-xl font-bold focus:outline-none">
                                                                                        <span class="text-red-500"
                                                                                                data-x-text="activeSub === 'sex' ? '−' : '+'"></span>
                                                                                </button>
                                                                        </div>
                                                                        <ul data-x-show="activeSub === 'sex'"
                                                                                
                                                                                class="pl-4 mt-1 space-y-1 list-none">
                                                                                <li><a href="/klassicheskii-seks/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Классический
                                                                                                секс</a></li>
                                                                                <li><a href="/analnii-seks/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Анальный
                                                                                                секс</a></li>
                                                                                <li><a href="/lesbiyskiy-seks/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Секс
                                                                                                лесбийский</a></li>
                                                                                <li><a href="/dlya-pari/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Услуги
                                                                                                семейной паре</a></li>
                                                                                <li><a href="/gruppovoi-seks/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Секс
                                                                                                групповой</a></li>
                                                                        </ul>
                                                                </li>

                                                                <!-- Оральные услуги -->
                                                                <li>
                                                                        <div class="flex justify-between items-center cursor-pointer"
                                                                                data-x-on-click="activeSub = activeSub === 'oral' ? null : 'oral'">
                                                                                <p class="category-p">Оральные услуги
                                                                                </p>
                                                                                <button
                                                                                        class="text-red-500 text-xl font-bold focus:outline-none">
                                                                                        <span
                                                                                                data-x-text="activeSub === 'oral' ? '−' : '+'"></span>
                                                                                </button>
                                                                        </div>
                                                                        <ul data-x-show="activeSub === 'oral'"
                                                                                
                                                                                class="pl-4 mt-1 space-y-1 list-none">
                                                                                <li><a href="/minet-v-rezinke/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Минет
                                                                                                в презервативе</a></li>
                                                                                <li><a href="/minet-bez-rezinki/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Минет
                                                                                                без резинки</a></li>
                                                                                <li><a href="/glubokiy-minet/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Минет
                                                                                                глубокий</a></li>
                                                                                <li><a href="/kunilingus/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Кунилингус</a>
                                                                                </li>
                                                                        </ul>
                                                                </li>

                                                                <!-- Окончания -->
                                                                <li>
                                                                        <div class="flex justify-between items-center cursor-pointer"
                                                                                data-x-on-click="activeSub = activeSub === 'finish' ? null : 'finish'">
                                                                                <p class="category-p">Окончания</p>
                                                                                <button
                                                                                        class="text-red-500 text-xl font-bold focus:outline-none">
                                                                                        <span
                                                                                                data-x-text="activeSub === 'finish' ? '−' : '+'"></span>
                                                                                </button>
                                                                        </div>
                                                                        <ul data-x-show="activeSub === 'finish'"
                                                                                
                                                                                class="pl-4 mt-1 space-y-1 list-none">
                                                                                <li><a href="/okonchanie-na-grud/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Окончание
                                                                                                на грудь</a></li>
                                                                                <li><a href="/okonchanie-na-litso/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Окончание
                                                                                                на лицо</a></li>
                                                                                <li><a href="/okonchanie-v-rot/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Окончание
                                                                                                в рот</a></li>
                                                                        </ul>
                                                                </li>

                                                                <li>
                                                                        <div class="flex justify-between items-center cursor-pointer"
                                                                                data-x-on-click="activeSub = activeSub === 'strip' ? null : 'strip'">
                                                                                <p class="category-p">Стриптиз</p>
                                                                                <button
                                                                                        class="text-red-500 text-xl font-bold focus:outline-none">
                                                                                        <span
                                                                                                data-x-text="activeSub === 'strip' ? '−' : '+'"></span>
                                                                                </button>
                                                                        </div>
                                                                        <ul data-x-show="activeSub === 'strip'"
                                                                                
                                                                                class="pl-4 mt-1 space-y-1 list-none">
                                                                                <li><a href="/striptiz-profi/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Стриптиз
                                                                                                профи</a></li>
                                                                                <li><a href="/striptiz-ne-profi/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Стриптиз
                                                                                                не профи</a></li>
                                                                                <li><a href="/lesbi-otkrovennoe/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Лесби
                                                                                                откровенное</a></li>
                                                                                <li><a href="/lesbi-legkoe/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Лесби-шоу
                                                                                                легкое</a></li>
                                                                        </ul>
                                                                </li>

                                                                <!-- БДСМ -->
                                                                <li>
                                                                        <div class="flex justify-between items-center cursor-pointer"
                                                                                data-x-on-click="activeSub = activeSub === 'bdsm' ? null : 'bdsm'">
                                                                                <p class="category-p">БДСМ (Садо-Мазо)
                                                                                </p>
                                                                                <button
                                                                                        class="text-red-500 text-xl font-bold focus:outline-none">
                                                                                        <span
                                                                                                data-x-text="activeSub === 'bdsm' ? '−' : '+'"></span>
                                                                                </button>
                                                                        </div>
                                                                        <ul data-x-show="activeSub === 'bdsm'"
                                                                                
                                                                                class="pl-4 mt-1 space-y-1 list-none">
                                                                                <li><a href="/bdsm/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">БДСМ</a>
                                                                                </li>
                                                                                <li><a href="/bandag/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Бандаж</a>
                                                                                </li>
                                                                                <li><a href="/gospoja/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Госпожа</a>
                                                                                </li>
                                                                                <li><a href="/rolevye-igri/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Ролевые
                                                                                                игры</a></li>
                                                                                <li><a href="/legkaya-dominaciya/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Легкая
                                                                                                доминация</a></li>
                                                                                <li><a href="/porka/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Порка</a>
                                                                                </li>
                                                                                <li><a href="/rabinya/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Рабыня</a>
                                                                                </li>
                                                                                <li><a href="/fetish/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Фетиш</a>
                                                                                </li>
                                                                                <li><a href="/trampling/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Трамплинг</a>
                                                                                </li>
                                                                                <li><a href="/strapon/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Страпон</a>
                                                                                </li>
                                                                                <li><a href="/anilingus/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Анилингус</a>
                                                                                </li>
                                                                                <li><a href="/zolotoi-dogd-vidacha/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Золотой
                                                                                                дождь выдача</a></li>
                                                                                <li><a href="/zolotoi-dogd-priem/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Золотой
                                                                                                дождь прием</a></li>
                                                                                <li><a href="/kopro-vidacha/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Копро
                                                                                                (выдача)</a></li>
                                                                                <li><a href="/fisting-analniy/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Фистинг
                                                                                                анальный</a></li>
                                                                                <li><a href="/fisting-klassicheskiy/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Фистинг
                                                                                                классический</a></li>
                                                                        </ul>
                                                                </li>

                                                                <li>
                                                                        <div class="flex justify-between items-center cursor-pointer"
                                                                                data-x-on-click="activeSub = activeSub === 'massage' ? null : 'massage'">
                                                                                <p class="category-p">Массаж</p>
                                                                                <button
                                                                                        class="text-red-500 text-xl font-bold focus:outline-none">
                                                                                        <span
                                                                                                data-x-text="activeSub === 'massage' ? '−' : '+'"></span>
                                                                                </button>
                                                                        </div>
                                                                        <ul data-x-show="activeSub === 'massage'"
                                                                                
                                                                                class="pl-4 mt-1 space-y-1 list-none">
                                                                                <li><a href="/klassicheskiy-massag/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Массаж
                                                                                                Классический</a></li>
                                                                                <li><a href="/professionalniy-massag/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Массаж
                                                                                                Профессиональный</a>
                                                                                </li>
                                                                                <li><a href="/rasslablyaushii-massag/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Массаж
                                                                                                Расслабляющий</a></li>
                                                                                <li><a href="/taiskij-massag/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Массаж
                                                                                                Тайский</a></li>
                                                                                <li><a href="/urologicheskii-massag/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Массаж
                                                                                                Урологический</a></li>
                                                                                <li><a href="/tochechnij-massag/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Массаж
                                                                                                Точечный</a></li>
                                                                                <li><a href="/eroticheskij-massag/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Массаж
                                                                                                Эротический</a></li>
                                                                                <li><a href="/vetka-sakuri/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Массаж
                                                                                                Ветка сакуры</a></li>
                                                                        </ul>
                                                                </li>

                                                                <!-- Эскорт -->
                                                                <li>
                                                                        <div class="flex justify-between items-center cursor-pointer"
                                                                                data-x-on-clickdata-x-on-click="activeSub = activeSub === 'escort' ? null : 'escort'">
                                                                                <p class="category-p">Эскорт</p>
                                                                                <button
                                                                                        class="text-red-500 text-xl font-bold focus:outline-none">
                                                                                        <span
                                                                                                data-x-text="activeSub === 'escort' ? '−' : '+'"></span>
                                                                                </button>
                                                                        </div>
                                                                        <ul data-x-show="activeSub === 'escort'"
                                                                                
                                                                                class="pl-4 mt-1 space-y-1 list-none">
                                                                                <li><a href="/eskort-uslugi/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Эскорт-услуги</a>
                                                                                </li>
                                                                        </ul>
                                                                </li>

                                                        </ul>

                                                </div>



                                                <!-- Внешность -->
                                                <div class="category bg-white p-4 rounded-lg border border-red-600 shadow-lg w-72"
                                                        data-x-data="{ activeSub: null }">

                                                        <div class="category-title text-red-500 font-semibold text-lg">
                                                                <a href="/prostitutki-po-vneshnosti/">Внешность</a>
                                                        </div>

                                                        <ul class="category-list mt-2 space-y-1 list-none">

                                                                <!-- Национальность -->
                                                                <li>
                                                                        <div class="flex justify-between items-center cursor-pointer"
                                                                                data-x-on-click="activeSub = activeSub === 'nationality' ? null : 'nationality'">
                                                                                <p class="category-p">Национальность</p>
                                                                                <button
                                                                                        class="text-red-500 text-xl font-bold focus:outline-none">
                                                                                        <span
                                                                                                data-x-text="activeSub === 'nationality' ? '−' : '+'"></span>
                                                                                </button>
                                                                        </div>
                                                                        <ul data-x-show="activeSub === 'nationality'"
                                                                                
                                                                                class="pl-4 mt-1 space-y-1 list-none">
                                                                                <li><a href="/prostitutki-yevropeyki/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Европейская</a>
                                                                                </li>
                                                                                <li><a href="/prostitutki-aziatki/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Азиатская</a>
                                                                                </li>
                                                                                <li><a href="/prostitutki-chernokozhiye/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Чернокожие</a>
                                                                                </li>
                                                                                <li><a href="/prostitutki-kavkazki/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Кавказская</a>
                                                                                </li>
                                                                        </ul>
                                                                </li>

                                                                <!-- Боди-арт -->
                                                                <li>
                                                                        <div class="flex justify-between items-center cursor-pointer"
                                                                                data-x-on-click="activeSub = activeSub === 'body-art' ? null : 'body-art'">
                                                                                <p class="category-p">Боди-арт</p>
                                                                                <button
                                                                                        class="text-red-500 text-xl font-bold focus:outline-none">
                                                                                        <span
                                                                                                data-x-text="activeSub === 'body-art' ? '−' : '+'"></span>
                                                                                </button>
                                                                        </div>
                                                                        <ul data-x-show="activeSub === 'body-art'"
                                                                                
                                                                                class="pl-4 mt-1 space-y-1 list-none">
                                                                                <li><a href="/prostitutki-s-tatuirovkami/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Татуировки</a>
                                                                                </li>
                                                                                <li><a href="/prostitutki-s-pirsingom/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Пирсинг</a>
                                                                                </li>
                                                                                <li><a href="/prostitutki-s-silikonom/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Силиконовая
                                                                                                грудь</a></li>
                                                                        </ul>
                                                                </li>

                                                                <li>
                                                                        <div class="flex justify-between items-center cursor-pointer"
                                                                                data-x-on-click="activeSub = activeSub === 'intim' ? null : 'intim'">
                                                                                <p class="category-p">Интимная стрижка
                                                                                </p>
                                                                                <button
                                                                                        class="text-red-500 text-xl font-bold focus:outline-none">
                                                                                        <span
                                                                                                data-x-text="activeSub === 'intim' ? '−' : '+'"></span>
                                                                                </button>
                                                                        </div>
                                                                        <ul data-x-show="activeSub === 'intim'"
                                                                                
                                                                                class="pl-4 mt-1 space-y-1 list-none">
                                                                                <li><a href="/prostitutki-polnaya/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Полная
                                                                                                депиляция</a></li>
                                                                                <li><a href="/prostitutki-akuratnaya/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Аккуратная
                                                                                                стрижка</a></li>
                                                                                <li><a href="/prostitutki-naturalnaya/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Натуральная</a>
                                                                                </li>
                                                                        </ul>
                                                                </li>

                                                                <!-- Цвет волос -->
                                                                <li>
                                                                        <div class="flex justify-between items-center cursor-pointer"
                                                                                data-x-on-click="activeSub = activeSub === 'hair' ? null : 'hair'">
                                                                                <p class="category-p">Цвет волос</p>
                                                                                <button
                                                                                        class="text-red-500 text-xl font-bold focus:outline-none">
                                                                                        <span
                                                                                                data-x-text="activeSub === 'hair' ? '−' : '+'"></span>
                                                                                </button>
                                                                        </div>
                                                                        <ul data-x-show="activeSub === 'hair'"
                                                                                
                                                                                class="pl-4 mt-1 space-y-1 list-none">
                                                                                <li><a href="/prostitutki-bryunetki/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Брюнетки</a>
                                                                                </li>
                                                                                <li><a href="/prostitutki-shatenki/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Шатенки</a>
                                                                                </li>
                                                                                <li><a href="/prostitutki-ryzhiye/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Рыжие</a>
                                                                                </li>
                                                                                <li><a href="/prostitutki-rusyye/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Русые</a>
                                                                                </li>
                                                                                <li><a href="/prostitutki-blondinki/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Блондинки</a>
                                                                                </li>
                                                                        </ul>
                                                                </li>
                                                                <li>
                                                                        <div class="flex justify-between items-center cursor-pointer"
                                                                                data-x-on-click="activeSub = activeSub === 'size' ? null : 'size'">
                                                                                <p class="category-p">Размер груди</p>
                                                                                <button
                                                                                        class="text-red-500 text-xl font-bold focus:outline-none">
                                                                                        <span
                                                                                                data-x-text="activeSub === 'size' ? '−' : '+'"></span>
                                                                                </button>
                                                                        </div>
                                                                        <ul data-x-show="activeSub === 'size'"
                                                                                
                                                                                class="pl-4 mt-1 space-y-1 list-none">
                                                                                <li><a href="/prostitutki-s-malenkoj-grudiu/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">С
                                                                                                маленькой грудью</a>
                                                                                </li>
                                                                                <li><a href="/prostitutki-s-bolshoj-grudiu/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">С
                                                                                                большой грудью</a></li>
                                                                        </ul>
                                                                </li>

                                                                <!-- Возраст -->
                                                                <li>
                                                                        <div class="flex justify-between items-center cursor-pointer"
                                                                                data-x-on-click="activeSub = activeSub === 'age' ? null : 'age'">
                                                                                <p class="category-p">Возраст</p>
                                                                                <button
                                                                                        class="text-red-500 text-xl font-bold focus:outline-none">
                                                                                        <span
                                                                                                data-x-text="activeSub === 'age' ? '−' : '+'"></span>
                                                                                </button>
                                                                        </div>
                                                                        <ul data-x-show="activeSub === 'age'"
                                                                                
                                                                                class="pl-4 mt-1 space-y-1 list-none">
                                                                                <li><a href="/vzroslyye-prostitutki/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Взрослые</a>
                                                                                </li>
                                                                                <li><a href="/zrelyye-prostitutki/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Зрелые</a>
                                                                                </li>
                                                                                <li><a href="/molodyye-prostitutki/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Молодые</a>
                                                                                </li>
                                                                        </ul>
                                                                </li>

                                                                <!-- Телосложение -->
                                                                <li>
                                                                        <div class="flex justify-between items-center cursor-pointer"
                                                                                data-x-on-click="activeSub = activeSub === 'body' ? null : 'body'">
                                                                                <p class="category-p">Телосложение</p>
                                                                                <button
                                                                                        class="text-red-500 text-xl font-bold focus:outline-none">
                                                                                        <span
                                                                                                data-x-text="activeSub === 'body' ? '−' : '+'"></span>
                                                                                </button>
                                                                        </div>
                                                                        <ul data-x-show="activeSub === 'body'"
                                                                                
                                                                                class="pl-4 mt-1 space-y-1 list-none">
                                                                                <li><a href="/muskulistyye-prostitutki/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Мускулистые</a>
                                                                                </li>
                                                                                <li><a href="/pyshnyye-prostitutki/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Пышные</a>
                                                                                </li>
                                                                                <li><a href="/sportivnyye-prostitutki/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Спортивные</a>
                                                                                </li>
                                                                                <li><a href="/khudyye-prostitutki/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Худые</a>
                                                                                </li>
                                                                                <li><a href="/vysokiye-prostitutki/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Высокие</a>
                                                                                </li>
                                                                                <li><a href="/nizkiye-prostitutki/"
                                                                                                class="text-red-400 hover:text-red-500 transition text-[18px]">Низкие</a>
                                                                                </li>
                                                                        </ul>
                                                                </li>

                                                        </ul>

                                                </div>

                                                <!-- Районы -->
                                                <div
                                                        class="category bg-white h-full p-4 rounded-lg border border-red-600 shadow-lg w-72">
                                                        <div class="flex justify-between items-center cursor-pointer">
                                                                <a href="/prostitutki-po-rayonam/"
                                                                        class="category-title text-red-500 font-semibold text-lg hover:text-red-400 transition-colors duration-200">Районы</a>
                                                        </div>
                                                        <ul data-x-show="open" data-x-transition
                                                                class="category-list list-none">
                                                                <li><a href="/prostitutki-tsentralnyy-rayon/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Центральный</a>
                                                                </li>
                                                                <li><a href="/prostitutki-sovetskiy-rayon/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Советский</a>
                                                                </li>
                                                                <li><a href="/prostitutki-pervomayskiy-rayon/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Первомайский</a>
                                                                </li>
                                                                <li><a href="/prostitutki-partizanskiy-rayon/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Партизанский</a>
                                                                </li>
                                                                <li><a href="/prostitutki-zavodskoy-rayon/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Заводской</a>
                                                                </li>
                                                                <li><a href="/prostitutki-leninskiy-rayon/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Ленинский</a>
                                                                </li>
                                                                <li><a href="/prostitutki-oktyabrskiy-rayon/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Октябрьский</a>
                                                                </li>
                                                                <li><a href="/prostitutki-moskovskiy-rayon/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Московский</a>
                                                                </li>
                                                                <li><a href="/prostitutki-frunzenskiy-rayon/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Фрунзенский</a>
                                                                </li>
                                                        </ul>
                                                </div>

                                                <!-- Список метро -->
                                                <div
                                                        class="category bg-white  p-4 rounded-lg border border-red-600 shadow-lg w-72">
                                                        <div class="flex justify-between items-center cursor-pointer">
                                                                <a href="/prostitutki-metro/"
                                                                        class="category-title text-red-500 font-semibold text-lg hover:text-red-400 transition-colors duration-200">Список
                                                                        метро</a>
                                                        </div>
                                                        <ul data-x-show="open" data-x-transition
                                                                class="category-list list-none">
                                                                <li><a href="/prostitutki-metro-malinovka/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Малиновка</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-petrovshchina/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Петровщина</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-mikhalovo/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Михалово</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-grushevka/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Грушевка</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-institut-kultury/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Институт
                                                                                Культуры</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-ploshchad-lenina/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Площадь
                                                                                Ленина</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-oktyabrskaya/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Октябрьская</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-ploshchad-pobedy/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Площадь
                                                                                Победы</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-ploshchad-yakuba-kolasa/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Площадь
                                                                                Якуба
                                                                                Коласа</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-akademiya-nauk/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Академия
                                                                                Наук</a></li>
                                                                <li><a href="/prostitutki-metro-park-chelyuskintsev/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Парк
                                                                                Челюскинцев</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-moskovskaya/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Московская</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-vostok/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Восток</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-borisovskiy-trakt/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Борисовский
                                                                                тракт</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-uruchye/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Уручье</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-mogilevskaya/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Могилевская</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-avtozavodskaya/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Автозаводская</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-partizanskaya/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Партизанская</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-traktornyy-zavod/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Тракторный
                                                                                завод</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-proletarskaya/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Пролетарская</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-pervomayskaya/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Первомайская</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-kupalovskaya/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Купаловская</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-nemiga/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Немига</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-frunzenskaya/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Фрунзенская</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-molodezhnaya/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Молодежная</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-pushkinskaya/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Пушкинская</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-sportivnaya/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Спортивная</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-kuntsevshchina/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Кунцевщина</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-kamennaya-gorka/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Каменная
                                                                                горка</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-slutskiy-gostinets/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Слуцкий
                                                                                Гостинец</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-nemorshanskiy-sad/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Неморшанский
                                                                                Сад</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-aerodromnaya/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Аэродромная</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-kovalskaya-sloboda/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Ковальская
                                                                                Слобода</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-vokzalnaya/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Вокзальная</a>
                                                                </li>
                                                                <li><a href="/prostitutki-metro-ploshchad-frantishka-bogushevicha/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Площадь
                                                                                Франтишка
                                                                                Богушевича</a></li>
                                                                <li><a href="/prostitutki-metro-yubileynaya-ploshchad/"
                                                                                class="text-red-400 hover:text-red-500 text-[18px] transition-colors duration-200">Юбилейная
                                                                                площадь</a>
                                                                </li>
                                                        </ul>
                                                </div>
                                        </div>
                                </div>

                        </div>
                </div>
        </header>