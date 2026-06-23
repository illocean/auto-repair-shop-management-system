/**
 * Auto Repair Shop Management System
 * Client-side interactivity
 */

import { createIcons, icons } from 'lucide';

document.addEventListener('DOMContentLoaded', function () {
    createIcons({ icons });
    initMobileMenu();
    initActiveNavLinks();
    initTabSwitching();
    initFlashDismiss();
});

/* ── Mobile menu toggle ── */
function initMobileMenu() {
    const toggle = document.getElementById('menu-toggle');
    const menu = document.getElementById('mobile-menu');

    if (!toggle || !menu) return;

    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        menu.classList.toggle('show');
        const expanded = menu.classList.contains('show');
        toggle.setAttribute('aria-expanded', expanded);
    });

    // Close on outside click
    document.addEventListener('click', function (e) {
        if (!menu.contains(e.target) && !toggle.contains(e.target)) {
            menu.classList.remove('show');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && menu.classList.contains('show')) {
            menu.classList.remove('show');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.focus();
        }
    });
}

/* ── Active nav link highlighting ── */
function initActiveNavLinks() {
    const currentPath = window.location.pathname.replace(/\/$/, '');
    if (!currentPath) return;

    document.querySelectorAll('.nav-link, .nav-link-mobile').forEach(function (link) {
        const href = link.getAttribute('href');
        if (!href) return;

        // Build absolute URL from the href
        const linkUrl = new URL(href, window.location.origin);
        const linkPath = linkUrl.pathname.replace(/\/$/, '');

        // Match: exact path or path is a parent of current (for nesting)
        if (currentPath === linkPath || (linkPath !== '/' && currentPath.startsWith(linkPath + '/'))) {
            link.classList.add('active');
        }
    });
}

/* ── Tab switching (login/register) ── */
function initTabSwitching() {
    window.switchTab = function (tab) {
        document.querySelectorAll('.tab-pane').forEach(function (p) {
            p.classList.remove('active');
        });
        document.querySelectorAll('.tab-btn').forEach(function (b) {
            b.classList.remove('active');
        });

        var pane = document.getElementById('form-' + tab);
        var btn = document.getElementById('tab-' + tab + '-btn');
        if (pane) pane.classList.add('active');
        if (btn) btn.classList.add('active');
    };
}

/* ── Auto-dismiss flash messages ── */
function initFlashDismiss() {
    document.querySelectorAll('.alert-success').forEach(function (alert) {
        setTimeout(function () {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s';
            setTimeout(function () { alert.remove(); }, 300);
        }, 5000);
    });
}