
/**
 * College ERP user menu and theme toggle.
 *
 * @module theme_erp/user_menu
 */

export const init = () => {

    // ------------------------------------------
    // User dropdown.
    // ------------------------------------------

    document.addEventListener('click', (event) => {

        const button = event.target.closest('.erp-user-chip');

        const userMenu = document.querySelector('.erp-user-menu');
        const menu = userMenu?.querySelector('.erp-dropdown');

        if (!userMenu || !menu) {
            return;
        }

        // Account button clicked.
        if (button && userMenu.contains(button)) {

            event.preventDefault();
            event.stopPropagation();

            const isOpen = menu.classList.contains('show');

            menu.classList.toggle('show', !isOpen);

            button.setAttribute(
                'aria-expanded',
                String(!isOpen)
            );

            return;
        }

        // Click outside account menu.
        if (!userMenu.contains(event.target)) {

            menu.classList.remove('show');

            userMenu.querySelector('.erp-user-chip')
                ?.setAttribute('aria-expanded', 'false');
        }
    });

    // Close dropdown using Escape.
    document.addEventListener('keydown', (event) => {

        if (event.key !== 'Escape') {
            return;
        }

        const menu = document.querySelector('.erp-dropdown');
        const button = document.querySelector('.erp-user-chip');

        menu?.classList.remove('show');
        button?.setAttribute('aria-expanded', 'false');
        button?.focus();
    });

    // ------------------------------------------
    // Theme toggle.
    // ------------------------------------------

    const html = document.documentElement;
    const themeButton = document.getElementById('erp-theme-toggle');

    if (themeButton) {

        const getTheme = () => {
            try {
                return localStorage.getItem('erp-moodle-theme') || 'light';
            } catch (e) {
                return 'light';
            }
        };

        const setTheme = (theme) => {

            html.setAttribute('data-bs-theme', theme);

            try {
                localStorage.setItem('erp-moodle-theme', theme);
            } catch (e) {}

            themeButton.setAttribute(
                'aria-pressed',
                String(theme === 'dark')
            );

            themeButton.setAttribute(
                'title',
                theme === 'dark'
                    ? 'Switch to light theme'
                    : 'Switch to dark theme'
            );
        };

        setTheme(getTheme());

        themeButton.addEventListener('click', () => {
            setTheme(
                getTheme() === 'dark' ? 'light' : 'dark'
            );
        });
    }
};
