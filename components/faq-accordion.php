<?php
/**
 * Component: FAQ Accordion
 */

if (!defined('ABSPATH')) exit;

$post_id      = (int) get_query_var('landing_source_post_id');
if ($post_id <= 0) {
    $post_id = get_queried_object_id();
}
$faq_h1       = function_exists('get_field') ? (get_field('faq_h1', $post_id) ?: '') : '';
$faq_p        = function_exists('get_field') ? (get_field('faq_p',  $post_id) ?: '') : '';
$faq_rows_raw = function_exists('get_field') ? (get_field('faq',    $post_id) ?: []) : [];

$faq_rows = [];
if (is_array($faq_rows_raw)) {
    foreach ($faq_rows_raw as $row) {
        $b = $row['faq_block'] ?? $row;
        $q = trim((string)($b['question'] ?? $row['question'] ?? ''));
        $a_raw = (string)($b['answer'] ?? $row['answer'] ?? '');
        $a_check = trim(wp_strip_all_tags(do_shortcode($a_raw)));
        if ($q !== '' && $a_check !== '') {
            $faq_rows[] = ['q' => $q, 'a' => $a_raw];
        }
    }
}
?>

<?php if (!empty($faq_rows)) : ?>
    <section id="faq" class="py-12 mx-auto max-w-[1280px] 2xl:max-w-[1400px] px-4">
        <?php if (!empty($faq_h1)) : ?>
            <h2 class="text-[28px] md:text-[34px] font-extrabold text-center tracking-tight">
                <?= esc_html($faq_h1) ?>
            </h2>
        <?php endif; ?>

        <?php if (!empty($faq_p)) : ?>
            <p class="text-neutral-700 text-center mt-2 mb-8 max-w-[820px] mx-auto"><?= esc_html($faq_p) ?></p>
        <?php endif; ?>

        <div class="space-y-4">
            <?php foreach ($faq_rows as $i => $item) :
                $pid = 'faq-panel-' . ($i + 1);
                $q   = $item['q'];
                $a   = $item['a'];
                $is_open = ($i === 0);
            ?>
                <div class="faq-item rounded-xl border border-neutral-200 overflow-hidden bg-white shadow-[0_1px_0_rgba(0,0,0,.04)]">
                    <button
                        type="button"
                        class="faq-trigger w-full text-left px-5 py-4 flex items-center justify-between gap-4 bg-white hover:bg-neutral-50 transition-colors border-l-4"
                        style="border-left-color:#e865a0"
                        aria-expanded="<?= $is_open ? 'true' : 'false' ?>"
                        aria-controls="<?= esc_attr($pid) ?>">
                        <span class="pr-6 font-semibold text-[15px] leading-snug"><?= esc_html($q) ?></span>
                        <svg class="chev w-5 h-5 text-neutral-700 transition-transform duration-200 <?= $is_open ? 'rotate-180' : '' ?>" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M7 10l5 5 5-5H7z" />
                        </svg>
                    </button>

                    <!-- тонкая линия-акцент под вопросом -->
                    <div class="h-px bg-neutral-200 relative after:absolute after:left-0 after:top-0 after:h-px after:w-24 after:bg-[#e865a0]"></div>

                    <div id="<?= esc_attr($pid) ?>" class="faq-panel <?= $is_open ? '' : 'hidden' ?>">
                        <div class="px-5 md:px-6 py-4 md:py-5">
                            <div class="prose prose-sm max-w-[820px] mx-auto text-neutral-800 prose-a:text-[#e865a0] prose-a:underline hover:prose-a:no-underline">
                                <?= wp_kses_post(apply_filters('the_content', $a)) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const items = document.querySelectorAll('.faq-item');
                items.forEach((item) => {
                    const btn = item.querySelector('.faq-trigger');
                    const pan = item.querySelector('.faq-panel');
                    const icon = item.querySelector('.chev');
                    if (!btn || !pan) return;

                    btn.addEventListener('click', () => {
                        const open = btn.getAttribute('aria-expanded') === 'true';
                        btn.setAttribute('aria-expanded', String(!open));
                        pan.classList.toggle('hidden');
                        if (icon) icon.classList.toggle('rotate-180');
                    });
                });
            });
        </script>
    </section>
<?php endif; ?>
