<?php
$args = wp_parse_args($args ?? [], [
    'render_buttons' => false,
    'variant'        => 'desktop',
    'add_url'        => home_url('/cabinet/add-model/'),
]);
?>

<?php if (!defined('AUTH_UI_PRINTED')) {
    define('AUTH_UI_PRINTED', true); ?>
    <div id="authui-root" class="fixed inset-0 z-[100] hidden">
        <div id="authui-overlay" class="absolute inset-0 bg-black/75 opacity-0 transition-opacity"></div>

        <!-- LOGIN -->
        <div id="authui-login" class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div id="authui-login-box" class="w-[92%] max-w-md bg-black text-gray-100 shadow-2xl border border-gray-800 transform scale-95 opacity-0 transition-all">
                <div class="p-5 border-b border-gray-800 flex items-center justify-between">
                    <div class="font-semibold text-lg uppercase tracking-wide">Войти</div>
                    <button type="button" class="p-2 text-gray-400 hover:text-[#e865a0] text-2xl leading-none" data-authui-close>&times;</button>
                </div>
                <form class="p-5 space-y-3" id="authui-login-form" novalidate>
                    <label class="block">
                        <span class="text-xs uppercase tracking-wide text-gray-400">Email</span>
                        <input type="email" name="email" autocomplete="email" required class="mt-1 w-full h-11 bg-black border border-gray-800 px-3 outline-none focus:border-[#e865a0] text-white" placeholder="demo@example.com">
                    </label>
                    <label class="block">
                        <span class="text-xs uppercase tracking-wide text-gray-400">Пароль</span>
                        <input type="password" name="password" autocomplete="current-password" required class="mt-1 w-full h-11 bg-black border border-gray-800 px-3 outline-none focus:border-[#e865a0] text-white" placeholder="••••••••">
                    </label>
                    <div class="flex items-center justify-between pt-2">
                        <button type="submit" class="h-11 px-4 bg-[#e865a0] text-white font-semibold uppercase tracking-wide hover:opacity-90 transition">Войти</button>
                        <p class="text-sm text-gray-400 hover:text-[#e865a0] uppercase tracking-wide transition" data-authui-switch="add">Зарегистрироваться</p>
                    </div>
                </form>
            </div>
        </div>

        <!-- REGISTER (бывш. ADD) -->
        <div id="authui-add" class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div id="authui-add-box" class="w-[92%] max-w-lg bg-black text-gray-100 shadow-2xl border border-gray-800 transform scale-95 opacity-0 transition-all">
                <div class="p-5 border-b border-gray-800 flex items-center justify-between">
                    <div class="font-semibold text-lg uppercase tracking-wide">Регистрация</div>
                    <button type="button" class="p-2 text-gray-400 hover:text-[#e865a0] text-2xl leading-none" data-authui-close>&times;</button>
                </div>
                <form class="p-5 space-y-3" id="authui-add-form" novalidate>
                    <label class="block">
                        <span class="text-xs uppercase tracking-wide text-gray-400">Email</span>
                        <input type="email" name="reg_email" autocomplete="username" required class="mt-1 w-full h-11 bg-black border border-gray-800 px-3 outline-none focus:border-[#e865a0] text-white" placeholder="you@example.com">
                    </label>
                    <label class="block">
                        <span class="text-xs uppercase tracking-wide text-gray-400">Логин</span>
                        <input type="text" name="reg_login" required minlength="3" class="mt-1 w-full h-11 bg-black border border-gray-800 px-3 outline-none focus:border-[#e865a0] text-white" placeholder="username">
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-xs uppercase tracking-wide text-gray-400">Пароль</span>
                            <input type="password" name="reg_pass" autocomplete="current-password" required minlength="6" class="mt-1 w-full h-11 bg-black border border-gray-800 px-3 outline-none focus:border-[#e865a0] text-white" placeholder="••••••••">
                        </label>
                        <label class="block">
                            <span class="text-xs uppercase tracking-wide text-gray-400">Повтор пароля</span>
                            <input type="password" name="reg_pass2" autocomplete="current-password" required minlength="6" class="mt-1 w-full h-11 bg-black border border-gray-800 px-3 outline-none focus:border-[#e865a0] text-white" placeholder="••••••••">
                        </label>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-300">
                        <input type="checkbox" name="terms" required class="border-gray-700 bg-black" id="reg-terms-checkbox">
                        <label for="reg-terms-checkbox" class="uppercase tracking-wide text-[12px]">Я принимаю правила сайта</label>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <button type="submit" class="h-11 px-4 bg-[#e865a0] text-white font-semibold uppercase tracking-wide hover:opacity-90 transition">Зарегистрироваться</button>
                        <p class="text-sm text-gray-400 hover:text-[#e865a0] uppercase tracking-wide transition" data-authui-switch="login">У меня уже есть аккаунт</p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.AuthUI) return;

            const $ = (s, r = document) => r.querySelector(s);
            const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

            const root = $('#authui-root');
            if (!root) {
                console.error('AuthUI: #authui-root not found');
                return;
            }

            const overlay = $('#authui-overlay', root);
            const modals = {
                login: $('#authui-login', root),
                add: $('#authui-add', root)
            };
            const boxes = {
                login: $('#authui-login-box'),
                add: $('#authui-add-box')
            };

            if (!overlay || !modals.login || !modals.add || !boxes.login || !boxes.add) {
                console.error('AuthUI: Required elements not found', {
                    overlay,
                    modals,
                    boxes
                });
                return;
            }

            let open = null;

            function show(kind) {
                root.classList.remove('hidden');
                requestAnimationFrame(() => {
                    overlay.classList.remove('opacity-0');
                    const m = modals[kind];
                    const b = boxes[kind];
                    m.classList.remove('pointer-events-none');
                    requestAnimationFrame(() => {
                        b.classList.remove('opacity-0', 'scale-95');
                    });
                });
                open = kind;
                document.body.style.overflow = 'hidden';
            }

            function hide() {
                if (!open) return;
                overlay.classList.add('opacity-0');
                boxes[open].classList.add('opacity-0', 'scale-95');
                modals[open].classList.add('pointer-events-none');
                setTimeout(() => {
                    root.classList.add('hidden');
                    open = null;
                    document.body.style.overflow = '';
                }, 300);
            }

            overlay.addEventListener('click', hide);
            $$('[data-authui-close]', root).forEach(b => b.addEventListener('click', e => {
                e.preventDefault();
                hide();
            }));

            $$('[data-authui-switch]').forEach(a => {
                a.addEventListener('click', e => {
                    e.preventDefault();
                    const next = a.getAttribute('data-authui-switch') === 'add' ? 'add' : 'login';
                    const current = open || 'login';
                    boxes[current].classList.add('opacity-0', 'scale-95');
                    modals[current].classList.add('pointer-events-none');
                    setTimeout(() => {
                        modals[next].classList.remove('pointer-events-none');
                        requestAnimationFrame(() => {
                            boxes[next].classList.remove('opacity-0', 'scale-95');
                        });
                        open = next;
                    }, 150);
                });
            });

            window.AuthUI = {
                openLogin() {
                    show('login');
                },
                openAdd() {
                    show('add');
                },
                logout() {
                    document.cookie = 'auth_demo=; Max-Age=0; path=/';
                    location.reload();
                }
            };

            // Делегирование на кнопки
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('[data-auth-btn-login], [data-auth-btn-add]');
                if (!btn) return;
                e.preventDefault();
                e.stopPropagation();
                if (btn.hasAttribute('data-auth-btn-login')) window.AuthUI.openLogin();
                else if (btn.hasAttribute('data-auth-btn-add')) window.AuthUI.openAdd();
            }, true);

            // DEMO: логин
            const loginForm = $('#authui-login-form');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const email = this.email.value.trim();
                    const password = this.password.value.trim();
                    if (!email || !password) {
                        alert('Заполните все поля');
                        return;
                    }
                    document.cookie = 'auth_demo=1; Max-Age=' + (60 * 60 * 24 * 7) + '; path=/';
                    hide();
                    setTimeout(() => location.reload(), 300);
                });
            }

            // DEMO: регистрация
            const addForm = $('#authui-add-form');
            if (addForm) {
                addForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const email = this.reg_email.value.trim();
                    const login = this.reg_login.value.trim();
                    const pass = this.reg_pass.value;
                    const pass2 = this.reg_pass2.value;
                    const terms = this.terms?.checked ?? true;

                    if (!email || !login || !pass || !pass2) {
                        alert('Заполните все поля');
                        return;
                    }
                    if (login.length < 3) {
                        alert('Логин должен быть не короче 3 символов');
                        return;
                    }
                    if (pass.length < 6) {
                        alert('Пароль должен быть не короче 6 символов');
                        return;
                    }
                    if (pass !== pass2) {
                        alert('Пароли не совпадают');
                        return;
                    }
                    if (!terms) {
                        alert('Примите правила сайта');
                        return;
                    }

                    // Условная успешная регистрация
                    document.cookie = 'auth_demo=1; Max-Age=' + (60 * 60 * 24 * 7) + '; path=/';
                    alert('Регистрация прошла успешно!');
                    hide();
                    setTimeout(() => location.reload(), 300);
                });
            }
        });
    </script>
<?php } ?>

<?php if (!empty($args['render_buttons'])): ?>
    <?php $variant = $args['variant'] ?? 'mobile'; ?>
    <div class="<?php echo $variant === 'mobile' ? 'flex flex-col gap-3' : 'flex items-center gap-3'; ?>">
        <a href="#" data-auth-btn-login class="inline-flex items-center justify-center h-11 px-4 rounded-lg border border-gray-700 text-gray-200 hover:text-white hover:border-gray-500 transition">
            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M13 12H3" />
                <path d="M8 7l-5 5 5 5" />
                <path d="M21 3h-6v18h6" />
            </svg>
            Войти
        </a>
        <a href="#" data-auth-btn-add class="inline-flex items-center justify-center h-11 px-4 rounded-full bg-[#e865a0] text-white hover:opacity-90 transition">
            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 6L9 17l-5-5" />
            </svg>
            Зарегистрироваться
        </a>
    </div>
<?php endif; ?>
