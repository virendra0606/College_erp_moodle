// This file is part of theme_erp.
// Moodle AMD requires define() — NOT ES module export syntax.

define(['jquery'], function($) {
    'use strict';

    return {
        init: function() {

            var html = document.documentElement;

            // ── Theme toggle ──────────────────────────────────────────────
            var themeBtn = document.getElementById('erp-theme-toggle');

            function getTheme() {
                try { return localStorage.getItem('erp-moodle-theme') || 'light'; }
                catch (e) { return 'light'; }
            }

            function setTheme(theme) {
                html.setAttribute('data-bs-theme', theme);
                try { localStorage.setItem('erp-moodle-theme', theme); } catch (e) {}
                if (themeBtn) {
                    themeBtn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
                    themeBtn.setAttribute('title',
                        theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme'
                    );
                }
            }

            if (themeBtn) {
                setTheme(getTheme());
                themeBtn.addEventListener('click', function() {
                    setTheme(getTheme() === 'dark' ? 'light' : 'dark');
                });
            }

            // ── Profile dropdown ──────────────────────────────────────────
            var userBtn  = document.getElementById('erp-user-btn');
            var userMenu = document.getElementById('erp-profile-menu');

            if (userBtn && userMenu) {

                function isOpen() {
                    return userMenu.classList.contains('erp-pm-open');
                }
                function openMenu() {
                    userMenu.classList.add('erp-pm-open');
                    userBtn.setAttribute('aria-expanded', 'true');
                    var first = userMenu.querySelector('a.erp-pm-item');
                    if (first) { first.focus(); }
                }
                function closeMenu() {
                    userMenu.classList.remove('erp-pm-open');
                    userBtn.setAttribute('aria-expanded', 'false');
                }

                userBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    isOpen() ? closeMenu() : openMenu();
                });

                document.addEventListener('click', function(e) {
                    if (!userBtn.contains(e.target) && !userMenu.contains(e.target)) {
                        closeMenu();
                    }
                });

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && isOpen()) {
                        closeMenu();
                        userBtn.focus();
                    }
                });

                userMenu.addEventListener('keydown', function(e) {
                    var items = Array.prototype.slice.call(
                        userMenu.querySelectorAll('a.erp-pm-item')
                    );
                    var idx = items.indexOf(document.activeElement);
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        items[idx < items.length - 1 ? idx + 1 : 0].focus();
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (idx > 0) { items[idx - 1].focus(); }
                        else { closeMenu(); userBtn.focus(); }
                    } else if (e.key === 'Tab') {
                        closeMenu();
                    }
                });
            }
        }
    };
});