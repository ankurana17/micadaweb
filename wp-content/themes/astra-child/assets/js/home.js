/* Native public homepage interactions; no Angular or application API changes. */
(() => {
    'use strict';
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const notice = document.querySelector('[data-notice]');
    if (notice && typeof notice.showModal === 'function') {
        notice.showModal();
        notice.querySelector('[data-notice-close]').addEventListener('click', () => notice.close());
        notice.addEventListener('click', event => { if (event.target === notice) notice.close(); });
    }
    document.querySelectorAll('[data-pause-marquee], .micada-panels marquee').forEach(marquee => {
        marquee.addEventListener('mouseenter', () => marquee.stop());
        marquee.addEventListener('mouseleave', () => { if (!reduced) marquee.start(); });
        marquee.addEventListener('focusin', () => marquee.stop());
        marquee.addEventListener('focusout', () => { if (!reduced) marquee.start(); });
        if (reduced) marquee.stop();
    });
    const menuButton = document.querySelector('.micada-menu-toggle');
    menuButton?.addEventListener('click', () => {
        const open = document.querySelector('#micada-primary').classList.toggle('is-open');
        menuButton.setAttribute('aria-expanded', String(open));
    });
    document.querySelectorAll('.micada-menu details').forEach(details => {
        details.addEventListener('toggle', () => {
            if (details.open) document.querySelectorAll('.micada-menu details').forEach(other => { if (other !== details) other.open = false; });
        });
    });
    document.addEventListener('click', event => {
        if (!event.target.closest('.micada-navigation')) document.querySelectorAll('.micada-menu details').forEach(details => details.open = false);
    });
    const carousel = document.querySelector('[data-carousel]');
    const track = carousel?.querySelector('.micada-slides-track');
    const slides = track ? [...track.querySelectorAll('.micada-slide')] : [...document.querySelectorAll('.micada-slide')];
    const dots = [...document.querySelectorAll('[data-slide]')];
    const dotControls = document.querySelector('.micada-dots');
    let active = 0;
    let carouselTimer = null;
    let dotsHovered = false;

    const showSlide = (index, animated = true) => {
        if (!slides.length) return;
        active = (index + slides.length) % slides.length;
        if (track) {
            track.style.transition = animated ? 'transform 0.8s cubic-bezier(0.25, 1, 0.5, 1)' : 'none';
            track.style.transform = `translateX(-${active * 100}%)`;
        } else {
            slides.forEach((slide, number) => { slide.hidden = number !== active; });
        }
        dots.forEach((dot, number) => dot.setAttribute('aria-pressed', String(number === active)));
    };

    const stopCarousel = () => {
        if (carouselTimer) {
            window.clearInterval(carouselTimer);
            carouselTimer = null;
        }
    };

    const startCarousel = () => {
        stopCarousel();
        if (slides.length > 1 && !dotsHovered) {
            carouselTimer = window.setInterval(() => {
                if (!document.hidden) {
                    showSlide(active + 1);
                }
            }, 3500);
        }
    };

    dots.forEach((dot, index) => {
        dot.addEventListener('mouseenter', () => {
            showSlide(index);
        });
        dot.addEventListener('click', () => {
            showSlide(index);
        });
        dot.addEventListener('focus', () => {
            showSlide(index);
        });
    });

    dotControls?.addEventListener('pointerenter', () => {
        dotsHovered = true;
        stopCarousel();
    });
    dotControls?.addEventListener('pointerleave', () => {
        dotsHovered = false;
        startCarousel();
    });

    // Touch swipe support for mobile
    if (track) {
        let touchStartX = 0;
        track.addEventListener('touchstart', e => {
            touchStartX = e.touches[0].clientX;
            stopCarousel();
        }, { passive: true });

        track.addEventListener('touchend', e => {
            const touchEndX = e.changedTouches[0].clientX;
            const diff = touchStartX - touchEndX;
            if (Math.abs(diff) > 40) {
                if (diff > 0) {
                    showSlide(active + 1);
                } else {
                    showSlide(active - 1);
                }
            }
            startCarousel();
        }, { passive: true });
    }

    startCarousel();
    const scrollTimers = [];
    document.querySelectorAll('[data-auto-scroll]').forEach(container => {
        let paused = false;
        ['mouseenter','focusin','touchstart'].forEach(name => container.addEventListener(name, () => paused = true, { passive: true }));
        ['mouseleave','focusout','touchend'].forEach(name => container.addEventListener(name, () => paused = false, { passive: true }));
        scrollTimers.push(window.setInterval(() => {
            if (paused || document.hidden || container.scrollHeight <= container.clientHeight) return;
            container.scrollTop = container.scrollTop + container.clientHeight >= container.scrollHeight - 2 ? 0 : container.scrollTop + 1.5;
        }, 50));
    });
    const photos = [...document.querySelectorAll('[data-photo]')];
    const lightbox = document.querySelector('[data-lightbox]');
    let currentPhoto = 0;
    const showPhoto = index => {
        currentPhoto = (index + photos.length) % photos.length;
        const photo = photos[currentPhoto].querySelector('img');
        lightbox.querySelector('img').src = photo.src;
        lightbox.querySelector('img').alt = photo.alt;
        lightbox.querySelector('figcaption').textContent = photo.alt;
    };
    photos.forEach((button, index) => button.addEventListener('click', () => { showPhoto(index); lightbox.showModal(); }));
    lightbox?.querySelector('[data-lightbox-close]').addEventListener('click', () => lightbox.close());
    lightbox?.querySelector('[data-photo-previous]').addEventListener('click', () => showPhoto(currentPhoto - 1));
    lightbox?.querySelector('[data-photo-next]').addEventListener('click', () => showPhoto(currentPhoto + 1));
    lightbox?.addEventListener('keydown', event => {
        if (event.key === 'ArrowLeft') showPhoto(currentPhoto - 1);
        if (event.key === 'ArrowRight') showPhoto(currentPhoto + 1);
    });
    document.querySelectorAll('[data-login-ws]').forEach(link => link.addEventListener('click', () => {
        try { sessionStorage.setItem('loginWS', link.dataset.loginWs); } catch (_) { /* The application also identifies STP from its route. */ }
    }));
    const panel = document.querySelector('#micada-information');
    let panelRequest;
    const loadPanel = async (url, push) => {
        panelRequest?.abort();
        panelRequest = new AbortController();
        panel.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(url, { signal: panelRequest.signal });
            if (!response.ok) throw new Error('Page unavailable');
            const html = new DOMParser().parseFromString(await response.text(), 'text/html');
            const content = html.querySelector('#micada-information');
            if (!content) throw new Error('Content unavailable');
            panel.innerHTML = content.innerHTML;
            panel.scrollTop = 0;
            if (push) window.history.pushState({ micadaPanel: true }, '', url);
            document.querySelector('#micada-panels').scrollIntoView({ behavior: reduced ? 'auto' : 'smooth' });
            document.querySelectorAll('.micada-menu details').forEach(details => details.open = false);
        } catch (error) {
            if (error.name !== 'AbortError') window.location.assign(url);
        } finally { panel.removeAttribute('aria-busy'); }
    };
    document.querySelector('.micada-navigation')?.addEventListener('click', event => {
        const link = event.target.closest('a');
        if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button || link.target === '_blank') return;
        const url = new URL(link.href);
        if (url.origin === location.origin && url.hash === '#micada-panels') {
            event.preventDefault();
            loadPanel(url.href, true);
        }
    });
    window.addEventListener('popstate', () => loadPanel(location.href, false));
    window.addEventListener('pagehide', () => { stopCarousel(); scrollTimers.forEach(window.clearInterval); });
})();
