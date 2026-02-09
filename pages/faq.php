<?php

/**
 * Template Name: FAQ
 * Description: Вопрос—ответ с акцентом #e865a0 и обновлённым дизайном.
 */
if (!defined('ABSPATH')) exit;

get_header();

/** === ACF === */
$has_acf = function_exists('get_field');
$h1      = $has_acf ? (get_field('h1') ?: get_the_title()) : get_the_title();
$lead    = $has_acf ? (get_field('p')  ?: '') : '';

/** Собираем элементы FAQ из разных возможных ACF-структур */
$faq_items = [];
if ($has_acf) {
    // 1) группа faq -> repeater faq_blocks
    $faq_group = get_field('faq');
    if (is_array($faq_group) && !empty($faq_group['faq_blocks']) && is_array($faq_group['faq_blocks'])) {
        foreach ($faq_group['faq_blocks'] as $row) {
            $q = trim((string)($row['question'] ?? ''));
            $a = (string)($row['answer'] ?? '');
            if ($q !== '' || $a !== '') $faq_items[] = ['q' => $q, 'a' => $a];
        }
    }
    // 2) repeater faq_faq_blocks
    if (empty($faq_items) && have_rows('faq_faq_blocks')) {
        while (have_rows('faq_faq_blocks')) {
            the_row();
            $q = trim((string)get_sub_field('question'));
            $a = (string)get_sub_field('answer');
            if ($q !== '' || $a !== '') $faq_items[] = ['q' => $q, 'a' => $a];
        }
    }
    // 3) верхнеуровневый repeater faq_blocks
    if (empty($faq_items) && have_rows('faq_blocks')) {
        while (have_rows('faq_blocks')) {
            the_row();
            $q = trim((string)get_sub_field('question'));
            $a = (string)get_sub_field('answer');
            if ($q !== '' || $a !== '') $faq_items[] = ['q' => $q, 'a' => $a];
        }
    }
}

/** JSON-LD FAQPage */
$faq_ld = [];
foreach ($faq_items as $it) {
    if ($it['q'] === '' || $it['a'] === '') continue;
    $faq_ld[] = [
        '@type'          => 'Question',
        'name'           => wp_strip_all_tags($it['q']),
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text'  => wp_kses_post($it['a']),
        ],
    ];
}
?>
<main class="page-hero page-hero--faq">
    <div class="page-hero__inner max-w-[1100px] mx-auto">

        <header class="text-left mb-8">
            <h1 class="page-title">
                <?php echo esc_html($h1); ?>
            </h1>
            <div class="mt-2 h-1 w-24 rounded-full bg-[#e865a0]"></div>

            <?php if ($lead): ?>
                <p class="page-lead">
                    <?php echo esc_html($lead); ?>
                </p>
            <?php endif; ?>
        </header>

        <?php if (!empty($faq_items)): ?>
            <!-- Панель с FAQ: мягкая карточка с лёгкой тенью -->
            <section id="faq-acc" class="faq-card">
                <ul class="divide-y divide-neutral-200">
                    <?php foreach ($faq_items as $i => $it):
                        $qid    = 'faq-q-' . ($i + 1);
                        $aid    = 'faq-a-' . ($i + 1);
                        $q_txt  = $it['q'];
                        $a_html = $it['a'];
                    ?>
                        <li class="list-none">
                            <details class="group">
                                <!-- ВОПРОС -->
                                <summary
                                    id="<?php echo esc_attr($qid); ?>"
                                    class="flex items-start justify-between w-full cursor-pointer px-5 md:px-6 py-4 md:py-5 select-none">

                                    <!-- ИСПРАВЛЕНО: div заменен на span для валидации W3C -->
                                    <span class="flex items-start gap-3">
                                        <!-- Акцентная метка слева -->
                                        <span class="mt-1 inline-block w-1 h-5 rounded-full bg-[#e865a0]"></span>
                                        <span class="text-[16px] md:text-[17px] font-semibold text-black">
                                            <?php echo esc_html($q_txt ?: 'Вопрос'); ?>
                                        </span>
                                    </span>

                                    <!-- Иконка-переключатель -->
                                    <span class="relative ml-4 inline-flex items-center justify-center w-7 h-7 rounded-full border border-[rgba(232,101,160,.28)] text-[#e865a0]">
                                        <svg class="w-4 h-4 acc-chevron transition-transform duration-200 group-open:rotate-180" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M7 10l5 5 5-5H7z" />
                                        </svg>
                                    </span>
                                </summary>

                                <!-- ОТВЕТ -->
                                <div id="<?php echo esc_attr($aid); ?>" class="px-5 md:px-6 pb-5 -mt-2">
                                    <div class="rounded-xl border border-neutral-200 bg-neutral-50 mb-1 mt-1 px-4 md:px-5 py-4 text-[15px] leading-relaxed text-neutral-800">
                                        <?php echo wp_kses_post(wpautop($a_html)); ?>
                                    </div>
                                </div>
                            </details>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php else: ?>
            <p class="text-neutral-600">Пока нет вопросов и ответов.</p>
        <?php endif; ?>

    </div>

    <script>
        // (опционально) – если хотите, чтобы открывался только один пункт за раз
        document.addEventListener('click', function(e) {
            const s = e.target.closest('summary');
            if (!s) return;
            const d = s.parentElement;
            if (!(d && d.tagName === 'DETAILS')) return;
            if (!d.open) {
                // закрываем остальные
                document.querySelectorAll('#faq-acc details[open]').forEach(el => {
                    if (el !== d) el.removeAttribute('open');
                });
            }
        });
    </script>
</main>

<?php get_footer();
