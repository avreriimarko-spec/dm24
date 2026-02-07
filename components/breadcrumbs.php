<?php

/**
 * Breadcrumbs — учитываем «хабы» из URL (pages, не таксы)
 */
if (!defined('ABSPATH')) exit;

$opt = wp_parse_args($args ?? [], [
    // ближе к хедеру по умолчанию
    'class'      => 'mt-0 mb-0',
    'home_label' => 'Главная',
    'blog_label' => 'Блог',
]);

if (is_front_page()) return;

/** Карта «хабов»: сегмент URL => подпись */
$HUB_PAGES = [
    'ves'          => 'Вес',
    'vozrast'      => 'Возраст',
    'grud'         => 'Грудь',
    'drygie'       => 'Другие',
    'metro'        => 'Метро',
    'nationalnost' => 'Национальность',
    'rajon'        => 'Районы',
    'rost'         => 'Рост',
    'cvet-volos'   => 'Цвет волос',
    'price'        => 'Цена',
];

$uriPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$segs    = ($uriPath === '') ? [] : explode('/', $uriPath);

$hubIdx = null;
for ($i = 0; $i < min(3, count($segs)); $i++) {
    if (isset($HUB_PAGES[$segs[$i]])) {
        $hubIdx = $i;
        break;
    }
}
$hubSeg      = is_int($hubIdx) ? $segs[$hubIdx] : '';
$hubName     = $hubSeg ? $HUB_PAGES[$hubSeg] : '';
$is_hub_root = ($hubSeg && count($segs) === ($hubIdx + 1));

$hubUrl = '';
if ($hubSeg) {
    $prefix  = $hubIdx > 0 ? implode('/', array_slice($segs, 0, $hubIdx)) . '/' : '';
    $hubPage = get_page_by_path($hubSeg, OBJECT, 'page');
    $hubUrl  = ($hubPage && !is_wp_error($hubPage))
        ? get_permalink($hubPage->ID)
        : home_url('/' . $prefix . $hubSeg . '/');
}

$blog_url = get_post_type_archive_link('blog');
if (!$blog_url) {
    $page = get_page_by_path('blog', OBJECT, 'page');
    $blog_url = ($page && !is_wp_error($page)) ? get_permalink($page) : home_url('/blog/');
}

$crumbs   = [];
$crumbs[] = ['label' => $opt['home_label'], 'url' => home_url('/')];

$add_hub = function (array &$arr) use ($hubSeg, $hubName, $hubUrl, $is_hub_root) {
    if (!$hubSeg || $is_hub_root) return;
    $arr[] = ['label' => $hubName, 'url' => $hubUrl];
};

if (is_singular('blog')) {
    $crumbs[] = ['label' => $opt['blog_label'], 'url' => $blog_url];
    $crumbs[] = ['label' => get_the_title(), 'url' => ''];
} elseif (is_post_type_archive('blog')) {
    $crumbs[] = ['label' => $opt['blog_label'], 'url' => ''];
} else {
    if (is_page()) {
        $post = get_post();
        $anc  = $post ? array_reverse(get_post_ancestors($post)) : [];
        if (!empty($anc)) {
            foreach ($anc as $aid) $crumbs[] = ['label' => get_the_title($aid), 'url' => get_permalink($aid)];
            $crumbs[] = ['label' => get_the_title($post), 'url' => ''];
        } else {
            if ($is_hub_root) {
                $crumbs[] = ['label' => $hubName ?: get_the_title($post), 'url' => ''];
            } else {
                $add_hub($crumbs);
                $crumbs[] = ['label' => get_the_title($post), 'url' => ''];
            }
        }
    } elseif (is_singular()) {
        $add_hub($crumbs);
        $crumbs[] = ['label' => get_the_title(), 'url' => ''];
    } else {
        $add_hub($crumbs);
        $crumbs[] = ['label' => wp_get_document_title(), 'url' => ''];
    }
}
?>

<div class="breadcrumbs-wrapper">
    <nav class="<?php echo esc_attr($opt['class']); ?>" aria-label="Breadcrumb">
        <div class="bg-[#31363d]">
            <ol class="max-w-[1280px] 2xl:max-w-[1400px] mx-auto px-4 py-1
               flex items-center justify-center flex-wrap gap-x-1.5 gap-y-1.5 text-[13px] md:text-[14px]">
                <?php foreach ($crumbs as $i => $c): ?>
                    <?php if ($i > 0): ?>
                        <li aria-hidden="true" class="px-1 text-[#e865a0] select-none">›</li>
                    <?php endif; ?>
                    <li class="leading-none">
                        <?php if (!empty($c['url'])): ?>
                            <a href="<?php echo esc_url($c['url']); ?>"
                                class="text-white hover:text-[#e865a0] transition-colors">
                                <?php echo esc_html($c['label']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-white" aria-current="page">
                                <?php echo esc_html($c['label']); ?>
                            </span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </nav>
</div>