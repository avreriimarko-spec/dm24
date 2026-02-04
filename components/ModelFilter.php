<?php
/* ===== секция фильтра (аккордеон) ===== */
function render_filter_section(string $taxonomy, string $title, int $initial_count = 3): string
{
  $terms = get_terms([
    'taxonomy'   => $taxonomy,
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC',
  ]);
  if (empty($terms) || is_wp_error($terms)) return '';

  $has_more = count($terms) > $initial_count;
  $visible  = array_slice($terms, 0, $initial_count);
  $hidden   = $has_more ? array_slice($terms, $initial_count) : [];

  ob_start(); ?>
  <section class="border-b border-neutral-100 pb-4">
    <button type="button"
      class="flex w-full items-center justify-between px-4 py-2 select-none hover:bg-neutral-50 rounded-lg transition-colors"
      onclick="(function(btn){const b=btn.nextElementSibling; b.classList.toggle('hidden'); btn.querySelector('.chev').classList.toggle('rotate-180');})(this)">
      <span class="font-bold text-[16px] text-[#ff2d72]"><?= esc_html($title) ?></span>
      <svg class="chev w-5 h-5 text-neutral-500 transition-transform rotate-180" viewBox="0 0 24 24" fill="currentColor">
        <path d="M7 10l5 5 5-5H7z" />
      </svg>
    </button>

    <div class="px-4 pb-3 pt-1 space-y-2">
      <?php foreach ($visible as $term): ?>
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" class="filter-checkbox" name="<?= esc_attr($taxonomy) ?>[]" value="<?= esc_attr($term->slug) ?>">
          <span class="text-[15px] leading-6"><?= esc_html($term->name) ?></span>
        </label>
      <?php endforeach; ?>

      <?php if ($has_more): ?>
        <div class="mf-more hidden">
          <?php foreach ($hidden as $term): ?>
            <label class="flex items-center gap-3 cursor-pointer">
              <input type="checkbox" class="filter-checkbox" name="<?= esc_attr($taxonomy) ?>[]" value="<?= esc_attr($term->slug) ?>">
              <span class="text-[15px] leading-6"><?= esc_html($term->name) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
        <button type="button" class="mf-more-btn text-[14px] font-medium hover:underline"
          onclick="const d=this.previousElementSibling;d.classList.toggle('hidden');this.textContent=d.classList.contains('hidden')?'+ Больше':'− Скрыть';">
          + Больше
        </button>
      <?php endif; ?>
    </div>
  </section>
<?php
  return ob_get_clean();
}



/* ===== кнопка "Фильтры" + скрытая форма + скрипт для шторки справа ===== */
function render_model_filter(): string
{
  ob_start(); ?>


  <!-- СКРЫТАЯ форма для фильтров (будет перемещена в панель) -->
  <form id="models-filter" class="hidden models-filter-form text-sm" data-context="sheet">
    <input type="hidden" name="action" value="site_filter_models">
    <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('site_filter_nonce')); ?>">
    <input type="hidden" name="has_active_filters" value="0">
    <input type="hidden" name="paged" value="1">
    <input type="hidden" name="per_page" value="48">

    <?= render_filter_section('price_tax',        'Цена') ?>
    <?= render_filter_section('vozrast_tax',      'Возраст') ?>
    <?= render_filter_section('vneshnost_tax',    'Внешность') ?>
    <?= render_filter_section('nationalnost_tax', 'Национальность') ?>
    <?= render_filter_section('uslugi_tax', 'Услуги') ?>
    <?= render_filter_section('rayonu_tax',       'Районы') ?>
    <?= render_filter_section('metro_tax',        'Метро') ?>
    <?= render_filter_section('cvet-volos_tax',   'Цвет волос') ?>
    <?= render_filter_section('figura_tax',       'Вес') ?>
    <?= render_filter_section('rost_tax',         'Рост') ?>
    <?= render_filter_section('grud_tax',         'Грудь') ?>
  </form>

<?php
// ===== JS: панель справа (ПК) / на всю ширину (моб) + AJAX =====
  $script = <<<'JS'
(function () {
  if (window.MF_INIT) return; window.MF_INIT = true;

  document.addEventListener('DOMContentLoaded', function () {
    const ajaxUrl = (window.SiteModelsFilter && SiteModelsFilter.ajaxUrl) ? SiteModelsFilter.ajaxUrl : '/wp-admin/admin-ajax.php';

    // ----- читаем окружение с сервера -----
    const env = document.getElementById('mf-env');
    const mode      = env?.dataset?.mode || 'infinite';          // 'infinite' | 'button'
    const randSeed  = env?.dataset?.randSeed || '0';
    const nonce     = env?.dataset?.nonce || '';
    const baseTaxTx = env?.dataset?.basetaxTax || '';
    const baseTaxTm = env?.dataset?.basetaxTerms || '';
    const videoOnly = env?.dataset?.videoOnly === '1';
    const cheapOnly = env?.dataset?.cheapOnly === '1';
    const isNovye   = env?.dataset?.isNovye === '1';
    const isTaxPage = env?.dataset?.isTax === '1';
    const perPage   = parseInt(env?.dataset?.perPage || '0', 10) || 24;
    const currentPageFromEnv = parseInt(env?.dataset?.currentPage || '1', 10) || 1;
    const totalPagesFromEnv  = parseInt(env?.dataset?.totalPages || '1', 10) || 1;

    // ----- UI элементов фильтра -----
    const drawer   = document.createElement('aside');
    const overlay  = document.createElement('div');

    // Overlay styles
    overlay.id = 'mf-overlay';
    overlay.style.cssText = 'position: fixed; inset: 0; z-index: 9998; background-color: rgba(0,0,0,0.6); opacity: 0; pointer-events: none; transition: opacity 0.3s ease;';
    document.body.appendChild(overlay);

    // Drawer styles
    drawer.id = 'mf-drawer';
    drawer.style.cssText = 'position: fixed; top: 0; left: 0; z-index: 9999; width: 100%; max-height: 85vh; background-color: #fff; transform: translateY(-100%); transition: transform 0.3s ease; display: flex; flex-direction: column; border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.1); font-family: "Calibri", sans-serif; color: #000;';
    
    drawer.innerHTML = `
      <div class="h-14 px-4 border-b border-neutral-200 flex items-center justify-between shrink-0 bg-white rounded-t-2xl">
        <div class="text-lg font-bold text-black">Фильтры</div>
        <button type="button" id="mf-close" class="p-2 text-black hover:text-[#ff2d72] transition-colors" aria-label="Закрыть">
          <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
      <div class="flex-1 overflow-y-auto p-4 bg-white" id="mf-form-slot"></div>
      <div class="p-4 border-t border-neutral-200 flex gap-3 shrink-0 bg-white rounded-b-2xl">
        <button type="button" id="mf-reset" class="flex-1 h-11 border-2 border-black rounded-xl font-bold hover:bg-black hover:text-white transition uppercase text-sm tracking-wide text-black bg-transparent">Сбросить</button>
        <button type="button" id="mf-apply" class="flex-1 h-11 bg-[#ff2d72] text-white rounded-xl font-bold hover:bg-[#e01b5d] transition uppercase text-sm tracking-wide shadow-lg shadow-pink-200">Применить</button>
      </div>
    `;
    document.body.appendChild(drawer);

    // перенос формы фильтров в шторку
    const form = document.getElementById('models-filter');
    const slot = drawer.querySelector('#mf-form-slot');
    if (form && slot) { 
      slot.appendChild(form); 
      form.classList.remove('hidden'); 
      form.classList.add('grid', 'grid-cols-1', 'sm:grid-cols-2', 'lg:grid-cols-4', 'xl:grid-cols-5', 'gap-x-6', 'gap-y-8');
      // Ensure text is black inside form
      form.style.color = '#000';
    }
    const perPageInput = form ? form.querySelector('input[name="per_page"]') : null;
    if (perPageInput && perPage > 0) perPageInput.value = String(perPage);

    const btnOpen    = document.getElementById('mf-open');
    const btnClose   = drawer.querySelector('#mf-close');
    const btnApply   = drawer.querySelector('#mf-apply');
    const btnReset   = drawer.querySelector('#mf-reset');
    const sortSelect = document.getElementById('mf-sort');

    // контейнер для списка (внутри ожидается <ul id="mf-list">)
    const wrap = document.getElementById('ajax-models');
    if (!wrap) return;

    // кнопка «Показать ещё» (рендерится сервером на страницах с mode=button)
    const moreWrap = document.getElementById('mf-more-wrap');
    const moreBtn  = document.getElementById('mf-more-btn');

    // ---------- sentinel для бесконечной прокрутки ----------
    function ensureSentinel() {
      let s = document.getElementById('mf-sentinel');
      if (!s) {
        s = document.createElement('div');
        s.id = 'mf-sentinel';
        s.setAttribute('aria-hidden', 'true');
        s.style.height = '1px';
        wrap.appendChild(s);
      } else if (s.parentElement !== wrap) {
        wrap.appendChild(s);
      }
      return s;
    }

  // ---------- состояние ----------
  let currentPage = currentPageFromEnv;
  let isLoading   = false;
  let hasMore     = true;
  let totalPages  = totalPagesFromEnv;
  let observer    = null;

  function setUrlPage(page) {
    if (isTaxPage) return;
    try {
      const loc = new URL(window.location.href);
      const basePath = loc.pathname.replace(/\/page\/\d+\/?$/i, '').replace(/\/+$/, '') || '/';
      let newPath = basePath === '/' ? '/' : basePath;
      if (page > 1) {
        newPath += (newPath.endsWith('/') ? '' : '/') + 'page/' + page;
      }
      loc.pathname = newPath;
      history.replaceState(null, '', loc.toString());
      const canon = document.querySelector('link[rel="canonical"]');
      if (canon) canon.setAttribute('href', loc.toString());
    } catch (e) {}
  }
  function scrollToGridTop() {
    try {
      const anchor = document.getElementById('ajax-models') || wrap;
      if (!anchor) return;
      const top = anchor.getBoundingClientRect().top + window.pageYOffset - 20;
      window.scrollTo({ top, behavior: 'smooth' });
    } catch (e) {}
  }

    // утилиты
    function openDrawer() {
      overlay.style.pointerEvents = 'auto';
      overlay.style.opacity = '1';
      drawer.style.transform = 'translateY(0)';
      document.documentElement.style.overflow = 'hidden';
    }
    function closeDrawer() {
      overlay.style.opacity = '0';
      drawer.style.transform = 'translateY(-100%)';
      const off = () => { overlay.style.pointerEvents = 'none'; overlay.removeEventListener('transitionend', off); };
      overlay.addEventListener('transitionend', off);
      document.documentElement.style.overflow = '';
    }

    function setHasActiveFilters() {
      const checks = form.querySelectorAll('.filter-checkbox');
      const active = Array.from(checks).some(c => c.checked);
      const hidden = form.querySelector('input[name="has_active_filters"]');
      if (hidden) hidden.value = active ? '1' : '0';
      return active;
    }
    function hasActiveFiltersOrSort() {
      const hidden = form.querySelector('input[name="has_active_filters"]');
      const sortActive = sortSelect && sortSelect.value && sortSelect.value !== 'date_desc';
      return (hidden && hidden.value === '1') || sortActive || cheapOnly || videoOnly || isNovye;
    }

    function collectExcludeIds() {
      const list = wrap.querySelector('#mf-list') || wrap;
      return Array.from(list.querySelectorAll('.mf-item[data-id]'))
        .map(li => parseInt(li.getAttribute('data-id'), 10))
        .filter(Boolean);
    }

    // ---------- подготовка FormData (единый путь) ----------
    function buildFormData(page, { append }) {
  const pagedInput = form.querySelector('input[name="paged"]');
  if (pagedInput) pagedInput.value = String(page);
  if (perPageInput && perPage > 0) perPageInput.value = String(perPage);

  setHasActiveFilters();

  const fd = new FormData(form);

  // ключевые флаги для бэка
  fd.set('append', append ? '1' : '0');
  fd.set('paged', String(page));
  fd.set('rand_seed', String(randSeed));
  if (sortSelect) fd.set('sort', sortSelect.value);

  if (perPage > 0) fd.set('per_page', String(perPage));
  if (cheapOnly) fd.set('cheap_only', '1');
  if (isNovye) fd.set('is_novye', '1');

  // ── ГЛАВНОЕ: режимы догрузки ──────────────────────────────
  if (append) {
    // догружаем: работаем через исключения уже отрисованных
    fd.set('use_offset', '1');
    const ids = collectExcludeIds();
    if (ids.length) fd.set('exclude_ids', ids.join(','));
  } else {
    // полная замена: НЕЛЬЗЯ слать ни offset, ни exclude_ids
    fd.set('use_offset', '0');
    fd.delete('exclude_ids');
  }
  // ─────────────────────────────────────────────────────────

  // базовый такс-контекст
  if (baseTaxTx) fd.set('base_tax_taxonomy', baseTaxTx);
  if (baseTaxTm) fd.set('base_tax_terms',    baseTaxTm);

  // только видео?
  if (videoOnly) fd.set('video_only', '1');

  // обязательные поля ajax
  if (!fd.has('action')) fd.set('action', 'site_filter_models');
  if (!fd.has('nonce'))  fd.set('nonce',  nonce);

  return fd;
}


    // ---------- загрузка ----------
    async function fetchPage(page, replace = false) {
      if (isLoading || (!hasMore && !replace)) return;
      isLoading = true;
      try {
        const fd = buildFormData(page, { append: !replace });
        const res = await fetch(ajaxUrl, { method: 'POST', body: fd });
        const data = await res.json();

        const html = data?.data?.html || '';
        const meta = data?.data?.meta || {};
        if (typeof data?.data?.has_more !== 'undefined') {
          hasMore = !!data.data.has_more;
        } else if (typeof meta?.has_more !== 'undefined') {
          hasMore = !!meta.has_more;
        } else {
          hasMore = html.trim().length > 0;
        }
        if (meta?.max) totalPages = parseInt(meta.max, 10) || totalPages;
        if (meta?.paged) currentPage = parseInt(meta.paged, 10) || currentPage;

        if (replace) {
          // сервер отдаёт UL целиком (+кнопку для mode=button)
          wrap.innerHTML = html;
          setUrlPage(page);
          scrollToGridTop();
        } else {
          // сервер отдаёт ТОЛЬКО <li>…</li>
          const list = wrap.querySelector('#mf-list') || wrap;
          const tmp = document.createElement('div');
          tmp.innerHTML = html;
          const frag = document.createDocumentFragment();
          tmp.querySelectorAll('li.mf-item').forEach(li => frag.appendChild(li));
          list.appendChild(frag);
          if (mode === 'button') setUrlPage(page);
        }
      } catch (e) {
        hasMore = false;
      } finally {
        isLoading = false;
        updateUiByMode();
        bindPaginationNav();
      }
    }

    function resetAndReload() {
      currentPage = 1;
      hasMore = true;
      fetchPage(currentPage, true);
    }

    async function applyFiltersAndClose() {
      await resetAndReload();
      closeDrawer();
    }

    // ---------- infinite observer ----------
    function initObserver() {
      const s = ensureSentinel();
      if (observer) observer.disconnect();
      observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting && !isLoading && hasMore) {
            currentPage += 1;
            fetchPage(currentPage, false);
          }
        });
      }, { root: null, rootMargin: '200px', threshold: 0 });
      observer.observe(s);
    }

function updateUiByMode() {
  const localMoreWrap = document.getElementById('mf-more-wrap') || moreWrap;
  const localMoreBtn  = document.getElementById('mf-more-btn')  || moreBtn;

  if (mode === 'infinite') {
    if (localMoreWrap) localMoreWrap.style.display = 'none';
    initObserver();
  } else {
    if (observer) { observer.disconnect(); observer = null; }
    if (localMoreWrap) localMoreWrap.style.display = hasMore ? '' : 'none';
    if (localMoreBtn && !localMoreBtn._mfBound) {
      localMoreBtn.addEventListener('click', () => {
        if (isLoading || !hasMore) return;
        currentPage += 1;
        fetchPage(currentPage, false);
      });
      localMoreBtn._mfBound = true;
    }
  }
}

  function bindPaginationNav() {
    const nav = document.getElementById('mf-pages');
    if (!nav) return;
    nav.querySelectorAll('.mf-page-btn').forEach(btn => {
      if (btn.dataset.bound === '1') return;
      btn.dataset.bound = '1';
      btn.addEventListener('click', (e) => {
        const page = parseInt(btn.dataset.page, 10);
        if (!page || page === currentPage) return;
        if (hasActiveFiltersOrSort()) {
          e.preventDefault();
          currentPage = page;
          fetchPage(page, true);
        }
      });
    });
  }


    // ---------- привязки ----------
    if (btnOpen)  btnOpen.addEventListener('click', openDrawer);
    if (btnClose) btnClose.addEventListener('click', closeDrawer);
    overlay.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDrawer(); });

    if (btnApply) btnApply.addEventListener('click', applyFiltersAndClose);

    if (btnReset) btnReset.addEventListener('click', function () {
      drawer.querySelectorAll('.filter-checkbox').forEach(c => c.checked = false);
      resetAndReload();
    });

    if (sortSelect) sortSelect.addEventListener('change', resetAndReload);

    // стартовое состояние
    updateUiByMode();
    bindPaginationNav();
  });
})();

JS;
  wp_register_script('models-filter-drawer', '', [], null, true);
  wp_add_inline_script('models-filter-drawer', $script);
  wp_enqueue_script('models-filter-drawer');

  return ob_get_clean();
}
