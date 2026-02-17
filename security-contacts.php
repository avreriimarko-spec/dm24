<?php
/**
 * Функция для защищенного вывода контактов.
 * * @param string $mod_name  Имя настройки из Customizer
 * @param string $type      Тип контакта: 'telegram', 'whatsapp', 'phone', 'email'
 * @param string $content   Текст или HTML внутри ссылки
 * @param string $class     CSS классы
 */
function the_safe_contact($mod_name, $type, $content = '', $class = '') {
    $value = get_theme_mod($mod_name);
    if (empty($value)) return;

    $clean_val = trim($value);
    $final_url = '';

    switch ($type) {
        case 'telegram':
            $tg_user = str_replace(['@', 'https://t.me/', 'http://t.me/', 't.me/'], '', $clean_val);
            $final_url = 'https://t.me/' . $tg_user;
            break;
        case 'whatsapp':
            $wa_num = preg_replace('/[^0-9]/', '', $clean_val);
            $final_url = 'https://wa.me/' . $wa_num;
            break;
        case 'phone':
            $tel_num = preg_replace('/[^0-9+]/', '', $clean_val);
            $final_url = 'tel:' . $tel_num;
            break;
        case 'email':
            $final_url = 'mailto:' . $clean_val;
            break;
        default:
            $final_url = $clean_val;
            break;
    }

    $encoded_url = base64_encode($final_url);
    if (empty($content)) $content = $value; 

    // В PHP сразу не выводим href, но оставляем data-enc
    ?>
    <a class="protected-contact <?php echo esc_attr($class); ?>" 
       data-enc="<?php echo esc_attr($encoded_url); ?>" 
       rel="nofollow">
        <?php echo $content; ?>
    </a>
    <?php
}

/**
 * JS-скрипт: 
 * 1. Убирает href (чтобы не было подсказки javascript:void(0))
 * 2. Делает переход только по клику
 */
add_action('wp_footer', function() {
    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var links = document.querySelectorAll('.protected-contact');

        links.forEach(function(link) {
            // 1. УДАЛЯЕМ атрибут href. 
            // Теперь браузер не видит "адрес" и ничего не пишет внизу при наведении.
            link.removeAttribute('href');
            
            // 2. Возвращаем курсор "рука", так как у ссылок без href он пропадает
            link.style.cursor = 'pointer';

            // 3. Обрабатываем клик
            link.addEventListener('click', function(e) {
                // Если элемент помечен как data-go, обработку берет на себя глобальный JS.
                if (this.hasAttribute('data-go')) return;

                // Получаем зашифрованные данные
                var enc = this.getAttribute('data-enc');
                
                if (enc) {
                    try {
                        var realUrl = atob(enc);
                        
                        // Логика открытия:
                        // Телефон и почта -> в этом же окне (вызов приложения)
                        if (realUrl.indexOf('tel:') === 0 || realUrl.indexOf('mailto:') === 0) {
                            window.location.href = realUrl;
                        } 
                        // Мессенджеры и сайты -> в новой вкладке
                        else {
                            window.open(realUrl, '_blank');
                        }
                    } catch (err) { 
                        console.error('Error decoding contact'); 
                    }
                }
            });
        });
    });
    </script>
    <?php
}, 100);
