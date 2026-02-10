<?php
/**
 * Component: Stories/Video Modal
 * Used in home.php when is_page('s-video')
 */

if (!defined('ABSPATH')) exit;

/* ==========================================================
   PHP: ПОЛУЧЕНИЕ ДАННЫХ
=========================================================== */
$models_with_video = get_posts([
    'post_type'      => 'models',
    'posts_per_page' => 30,
    'fields'         => 'ids',
    'no_found_rows'  => true,
    'meta_query'     => [['key' => 'video', 'value' => '', 'compare' => '!=']],
]);

$extract_video = static function ($post_id) {
    $raw = get_post_meta($post_id, 'video', true);
    if (is_array($raw)) {
        if (!empty($raw['url'])) return trim($raw['url']);
        if (!empty($raw[0])) {
            $f = $raw[0];
            if (is_array($f) && !empty($f['url'])) return trim($f['url']);
            if (is_string($f)) return trim($f);
        }
    } elseif (is_numeric($raw)) return wp_get_attachment_url((int)$raw);
    elseif (is_string($raw)) {
        $p = preg_split('~[\s,;]+~u', $raw, -1, PREG_SPLIT_NO_EMPTY);
        if (!empty($p[0])) return trim($p[0]);
    }
    return '';
};

$get_story_thumb = static function ($post_id) {
    $placeholder = get_stylesheet_directory_uri() . '/assets/images/placeholder-thumbs.webp';
    $photo = get_post_meta($post_id, 'photo', true);
    if (is_array($photo)) {
        $first = $photo[0] ?? null;
        if (is_array($first) && !empty($first['ID'])) {
            $img = wp_get_attachment_image_src((int)$first['ID'], 'thumbnail');
            if ($img) return ['src' => $img[0], 'width' => $img[1], 'height' => $img[2]];
        }
        if (is_numeric($first)) {
            $img = wp_get_attachment_image_src((int)$first, 'thumbnail');
            if ($img) return ['src' => $img[0], 'width' => $img[1], 'height' => $img[2]];
        }
        if (is_array($first) && !empty($first['url'])) return ['src' => esc_url($first['url']), 'width' => 96, 'height' => 96];
    }
    $thumb_id = get_post_thumbnail_id($post_id);
    if ($thumb_id) {
        $img = wp_get_attachment_image_src($thumb_id, 'thumbnail');
        if ($img) return ['src' => $img[0], 'width' => $img[1], 'height' => $img[2]];
    }
    return ['src' => esc_url($placeholder), 'width' => 96, 'height' => 96];
};
?>

<?php if ($models_with_video): ?>
<section id="stories-section" class="my-10 px-4">
    <div class="mx-auto max-w-[84rem] relative group">
        
        <div id="stories-container" class="flex gap-4 overflow-x-auto no-scrollbar py-4 px-1 cursor-grab select-none active:cursor-grabbing">
            <?php foreach ($models_with_video as $model_id):
                $video = $extract_video($model_id);
                if (!$video) continue;
                $name  = esc_html(get_post_meta($model_id, 'name', true) ?: get_the_title($model_id));
                $thumb = $get_story_thumb($model_id);
                $age    = get_field("age", $model_id);
                $height = get_field("height", $model_id);
                $weight = get_field("weight", $model_id);
                $bust   = get_field("bust", $model_id);
                $price  = get_field("price", $model_id);
            ?>
            <button class="story-btn story-ig flex-shrink-0 w-20 h-20 rounded-full p-[4px] relative transition-transform hover:scale-105"
                data-video="<?= esc_url($video) ?>"
                data-id="<?= $model_id ?>"
                data-name="<?= esc_attr($name) ?>"
                data-link="<?= esc_url(get_permalink($model_id)) ?>"
                data-age="<?= esc_attr($age) ?>"
                data-height="<?= esc_attr($height) ?>"
                data-weight="<?= esc_attr($weight) ?>"
                data-bust="<?= esc_attr($bust) ?>"
                data-price="<?= esc_attr($price) ?>">
                <span class="relative block w-full h-full rounded-full overflow-hidden ring-2 ring-white/50 pointer-events-none">
                    <img src="<?= esc_url($thumb['src']) ?>" alt="<?= $name ?>" width="<?= $thumb['width'] ?>" height="<?= $thumb['height'] ?>" class="w-full h-full object-cover rounded-full">
                </span>
            </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="video-modal" class="hidden fixed inset-0 bg-black/90 flex items-center justify-center z-[999999]">
        
        <div id="video-wrapper" class="relative w-full max-w-3xl bg-black rounded-xl overflow-hidden h-full md:h-auto md:aspect-[9/16] max-h-[90vh]">

            <iframe id="video-iframe" class="w-full h-full hidden" allowfullscreen></iframe>
            <video id="video-player" class="w-full h-full hidden rounded-xl bg-black object-contain" playsinline preload="metadata"></video>

            <button id="close-video" class="absolute top-4 right-4 text-white text-5xl font-bold z-50 hover:opacity-70 transition-opacity">&times;</button>
            <button id="story-prev" class="absolute left-2 top-1/2 -translate-y-1/2 text-white text-7xl opacity-80 hover:opacity-100 z-50">‹</button>
            <button id="story-next" class="absolute right-2 top-1/2 -translate-y-1/2 text-white text-7xl opacity-80 hover:opacity-100 z-50">›</button>

            <div class="story-bottom-bar absolute left-0 right-0 px-6 z-50 flex justify-between items-center" style="bottom: 30px;">
                <a id="story-name" href="#" class="text-white text-xl font-semibold underline-offset-2 hover:text-gray-300 transition-colors"></a>
                
                <button id="story-more" class="text-white text-lg px-4 py-2 bg-white/20 rounded-lg backdrop-blur hover:bg-white/30 transition-all active:scale-95 z-50">
                    Параметры
                </button>

                <button id="story-fav" class="text-white text-4xl select-none hover:scale-110 transition-transform cursor-pointer z-50">♡</button>
            </div>

            <div id="fav-toast" class="hidden absolute top-10 left-1/2 -translate-x-1/2 bg-white text-black px-6 py-2 rounded-full shadow-xl text-lg font-semibold z-[70] text-center whitespace-nowrap"></div>

        </div> 

        <div id="story-panel" 
             class="absolute top-1/2 left-1/2 w-[90%] max-w-sm 
                    bg-black/95 text-white p-6 rounded-2xl 
                    backdrop-blur-sm border border-white/10 shadow-2xl z-[80]">
            
            <button id="story-panel-close" class="absolute top-2 right-4 text-3xl text-gray-400 hover:text-white cursor-pointer p-2">&times;</button>
            <div id="story-panel-content" class="space-y-3 text-lg mt-4"></div>
        </div>

    </div>
</section>

<style>
/* ОСНОВНОЕ: ПЕРЕКРЫТИЕ ВСЕГО САЙТА */
#video-modal {
    z-index: 2147483647 !important; /* Максимальный Z-Index в браузере */
}

#stories-container { user-select:none; cursor:grab; }
#stories-container.active { cursor:grabbing; }

/* === ПАНЕЛЬ ПАРАМЕТРОВ === */
#story-panel {
    transform: translate(-50%, -50%) scale(0.9);
    opacity: 0;
    pointer-events: none;
    transition: all 0.2s ease-out;
}
#story-panel.open {
    transform: translate(-50%, -50%) scale(1) !important;
    opacity: 1 !important;
    pointer-events: auto !important;
}
@media (max-width:640px) { .story-bottom-bar { bottom: 80px !important; } }
#video-player::-webkit-media-controls-panel { display:none!important; }
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const KEYS = ["favModels","favModelsV1","favorites","favoritesModels"];
    
    // Элементы
    const modal   = document.getElementById("video-modal");
    const iframe  = document.getElementById("video-iframe");
    const video   = document.getElementById("video-player");
    const wrapper = document.getElementById("video-wrapper");
    const btnPrev = document.getElementById("story-prev");
    const btnNext = document.getElementById("story-next");
    const btnFav  = document.getElementById("story-fav");
    const btnMore = document.getElementById("story-more");
    const btnClose= document.getElementById("close-video");
    const nameEl  = document.getElementById("story-name");
    const panel   = document.getElementById("story-panel");
    const panelContent = document.getElementById("story-panel-content");
    const panelClose = document.getElementById("story-panel-close");
    const toast   = document.getElementById("fav-toast");

    let stories = [...document.querySelectorAll(".story-btn")].map(btn => ({
        id: parseInt(btn.dataset.id), name: btn.dataset.name, link: btn.dataset.link, video: btn.dataset.video,
        age: btn.dataset.age, height: btn.dataset.height, weight: btn.dataset.weight, bust: btn.dataset.bust, price: btn.dataset.price
    }));
    let current = 0;
    let toastTimer = null;

    // === ФУНКЦИЯ ИЗБРАННОГО ===
    function toggleFavorite(id) {
        let isAdded = false;
        KEYS.forEach(key => {
            let list = []; try { list = JSON.parse(localStorage.getItem(key) || "[]"); } catch(e) {}
            if (!Array.isArray(list)) list = [];
            const index = list.indexOf(id);
            if (index > -1) {
                list.splice(index, 1); isAdded = false;
            } else {
                list.push(id); isAdded = true;
            }
            localStorage.setItem(key, JSON.stringify(list));
        });
        return isAdded;
    }

    function checkIsFav(id) {
        let list = []; try { list = JSON.parse(localStorage.getItem("favorites") || "[]"); } catch(e) {}
        return list.includes(id);
    }

    // === ЗАЩИТА ОТ ВСПЛЫТИЯ СОБЫТИЙ ===
    function killEvent(e) { e.stopPropagation(); }
    const controls = [btnMore, btnFav, btnClose, panel, panelClose];
    controls.forEach(el => {
        if(!el) return;
        el.addEventListener("touchstart", killEvent, {passive: false});
        el.addEventListener("touchend", killEvent);
        el.addEventListener("mousedown", killEvent);
        el.addEventListener("mouseup", killEvent);
        el.addEventListener("click", killEvent);
    });

    // === ОТКРЫТИЕ ===
    function openStory(i) {
        current = i;
        const s = stories[i];
        panel.classList.remove("open");
        modal.classList.remove("hidden");
        document.body.style.overflow = 'hidden'; // Blocking scroll
        nameEl.textContent = s.name;
        nameEl.href = s.link;

        const isFav = checkIsFav(s.id);
        updateFavBtn(isFav);
        
        // Reset
        iframe.classList.add("hidden");
        video.classList.add("hidden");
        iframe.src = "";
        video.src = "";
        wrapper.classList.remove('portrait');

        const url = s.video || "";

        // Enhanced URL parsing (Merged from secondary script)
        if (/youtube\.com|youtu\.be/i.test(url)) {
            const id = url.match(/(?:v=|be\/)([A-Za-z0-9_-]+)/)?.[1];
            if (id) {
                iframe.classList.remove("hidden");
                iframe.src = `https://www.youtube.com/embed/${id}?autoplay=1&mute=0`;
            } else {
                // Fallback basic check
                iframe.classList.remove("hidden");
                iframe.src = url + (url.includes('?') ? '&' : '?') + "autoplay=1";
            }
        } else if (/vimeo\.com/i.test(url)) {
            const id = url.match(/vimeo\.com\/(\d+)/)?.[1];
            if (id) {
                iframe.classList.remove("hidden");
                iframe.src = `https://player.vimeo.com/video/${id}?autoplay=1`;
            }
        } else if (/\.(mp4|webm|mov|m4v|m3u8)(\?|$)/i.test(url)) {
            video.classList.remove("hidden");
            video.src = url;
            video.play().catch(() => {});
            video.addEventListener('loadedmetadata', function checkOrientation() {
                const isPortrait = video.videoHeight > video.videoWidth;
                if (isPortrait) wrapper.classList.add('portrait');
                video.removeEventListener('loadedmetadata', checkOrientation);
            });
        } else {
             // Fallback for unknown types - try native video if no other match, or iframe?
             // Original script assumed iframe for fallback if not 'youtu'.
             // Let's default to video native if it looks like a file, otherwise iframe.
             // But actually, the original script 1 defaulted to video.src = url unless 'youtu'.
             
             // Safer fallback:
             video.classList.remove("hidden");
             video.src = url;
             video.play().catch(e => {
                 // on failure maybe try iframe? 
                 // For now, stick to robust logic.
             });
        }
    }

    function updateFavBtn(isFav) {
        if (isFav) {
            btnFav.textContent = "♥"; btnFav.classList.add("text-red-500");
        } else {
            btnFav.textContent = "♡"; btnFav.classList.remove("text-red-500");
        }
    }

    function next(){ if (current < stories.length-1) openStory(current+1); }
    function prev(){ if (current > 0) openStory(current-1); }

    document.querySelectorAll(".story-btn").forEach((btn,i)=> btn.addEventListener("click", ()=>openStory(i)));

    // === КНОПКИ ===
    btnNext.onclick = (e) => { killEvent(e); next(); }
    btnPrev.onclick = (e) => { killEvent(e); prev(); }
    btnClose.onclick = (e) => {
        killEvent(e);
        modal.classList.add("hidden");
        document.body.style.overflow = '';
        iframe.src=""; video.pause();
        panel.classList.remove("open");
    };

    // Close on Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
             btnClose.click();
        }
    });

    // === ПАРАМЕТРЫ ===
    btnMore.onclick = (e) => {
        killEvent(e);
        if (!panel.classList.contains("open")) {
            const s = stories[current];
            panelContent.innerHTML = "";
            const fields = [["Возраст", s.age], ["Рост", s.height], ["Вес", s.weight], ["Грудь", s.bust], ["Цена", s.price]];
            let hasData = false;
            fields.forEach(([label,val])=>{
                if (!val) return; hasData = true;
                panelContent.innerHTML += `<div class="flex justify-between border-b border-white/20 py-2 last:border-0"><span class="opacity-70">${label}</span><span class="font-bold text-xl">${val}</span></div>`;
            });
            if(!hasData) panelContent.innerHTML = '<div class="text-center opacity-60">Нет данных</div>';
        }
        panel.classList.toggle("open");
    };
    panelClose.onclick = (e) => { killEvent(e); panel.classList.remove("open"); };
    wrapper.onclick = (e) => { if(panel.classList.contains("open")) { panel.classList.remove("open"); e.stopPropagation(); } };

    // === ЛАЙК ===
    btnFav.onclick = (e) => {
        killEvent(e);
        const s = stories[current];
        const isNowFav = toggleFavorite(s.id);
        updateFavBtn(isNowFav);
        toast.textContent = isNowFav ? `${s.name} добавлена в избранное` : `${s.name} удалена из избранного`;
        toast.classList.remove("hidden");
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(()=>toast.classList.add("hidden"), 2000);
    };

    // === СВАЙПЫ ===
    let startX = 0, swiping=false;
    function start(e){
        if (e.target.closest('button') || e.target.closest('#story-panel') || e.target.closest('#story-more')) return;
        startX = e.touches? e.touches[0].clientX : e.clientX; swiping=false;
    }
    function move(e){
        if (panel.classList.contains("open")) return;
        let dx = (e.touches? e.touches[0].clientX : e.clientX) - startX;
        if (Math.abs(dx)>15) swiping=true;
    }
    function end(e){
        if (!swiping) return; if (panel.classList.contains("open")) return;
        let dx = (e.changedTouches? e.changedTouches[0].clientX : e.clientX) - startX;
        if (dx>50) prev(); if (dx<-50) next();
    }
    
    [modal, wrapper].forEach(el=>{
        el.addEventListener("touchstart",start); el.addEventListener("touchmove",move); el.addEventListener("touchend",end);
        el.addEventListener("mousedown",start); el.addEventListener("mousemove",move); el.addEventListener("mouseup",end);
    });
});
</script>
<?php endif; ?>
