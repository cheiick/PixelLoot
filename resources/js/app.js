import './bootstrap';

import Alpine from 'alpinejs';
import AOS from 'aos';
import 'aos/dist/aos.css';

window.Alpine = Alpine;
window.AOS = AOS;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    const isDark = document.documentElement.classList.contains('dark');
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', isDark.toString());
        button.textContent = isDark ? '☀️' : '🌙';
    });

    AOS.init({
        duration: 550,
        once: true,
        offset: 70,
    });

    const categoryCarousel = document.querySelector('[data-category-carousel]');
    if (categoryCarousel) {
        document.querySelectorAll('[data-category-scroll]').forEach((button) => {
            button.addEventListener('click', () => {
                categoryCarousel.scrollBy({
                    left: Number(button.dataset.categoryScroll) * categoryCarousel.clientWidth * 0.9,
                    behavior: 'smooth',
                });
            });
        });
    }

    const wishlistNotice = document.querySelector('[data-wishlist-notice]');
    if (wishlistNotice) {
        window.setTimeout(() => wishlistNotice.remove(), 5000);
    }

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
                toggle.setAttribute('aria-pressed', isDark.toString());
                toggle.textContent = isDark ? '☀️' : '🌙';
            });
        });
    });
});
