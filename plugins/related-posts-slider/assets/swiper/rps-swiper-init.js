document.addEventListener('DOMContentLoaded', function () {
    var container = document.querySelector('#rps_hcarousel');
    if (!container) return;

    // Guard against running twice on the same container (e.g. if a caching/
    // script-combining plugin causes this file to execute more than once).
    if (container.classList.contains('swiper')) return;

    // Settings localized from PHP (cf5_rps options: per_page / scroll).
    // Falls back to sane defaults if wp_localize_script didn't run for
    // some reason (e.g. this script loaded outside of WordPress).
    var settings = window.cf5RpsSwiperSettings || {};
    var configuredSlides = parseInt(settings.slidesPerView, 10) || 7;
    var slidesToScroll   = parseInt(settings.slidesToScroll, 10) || 1;
    var autoplayDelay    = parseInt(settings.autoplayDelay, 10) || 4000;
    var isRTL             = !!settings.rtl || document.documentElement.dir === 'rtl';

    // Ensure required structure
    container.classList.add('swiper');

    var slideCount = container.children.length;

    var wrapper = document.createElement('div');
    wrapper.className = 'swiper-wrapper';

    while (container.firstChild) {
        var slide = document.createElement('div');
        slide.className = 'swiper-slide';
        slide.appendChild(container.firstChild);
        wrapper.appendChild(slide);
    }

    container.appendChild(wrapper);

    // The configured per_page value is treated as the largest-viewport
    // slide count; smaller breakpoints scale down from it so the carousel
    // still looks reasonable on narrow screens.
    var maxSlides = Math.max(1, configuredSlides);

    // Swiper's loop mode needs roughly 2x slidesPerView worth of real
    // slides to behave correctly (it duplicates slides to fake infinite
    // scrolling). With fewer than that, loop mode silently breaks next()
    // navigation and can stall autoplay — so only enable it when there's
    // actually enough content. This is evaluated against maxSlides (the
    // largest breakpoint), which is the strictest case; smaller breakpoints
    // show fewer slides per view, so they need proportionally less content
    // to loop safely, meaning this check covers them too.
    var loopEnabled = slideCount > maxSlides * 2;

    new Swiper('#rps_hcarousel', {
        direction: 'horizontal',
        loop: loopEnabled,
        slidesPerView: maxSlides,
        slidesPerGroup: Math.min(slidesToScroll, maxSlides),
        spaceBetween: 10,
        keyboard: {
            enabled: true,
            onlyInViewport: true
        },
        navigation: {
            nextEl: '#rps_next',
            prevEl: '#rps_prev'
        },
        autoplay: {
            delay: autoplayDelay,
            disableOnInteraction: false,
            pauseOnMouseEnter: true
        },
        rtl: isRTL,
        breakpoints: {
            0:    { slidesPerView: 1, slidesPerGroup: 1 },
            480:  { slidesPerView: Math.min(2, maxSlides), slidesPerGroup: Math.min(slidesToScroll, 2) },
            768:  { slidesPerView: Math.min(4, maxSlides), slidesPerGroup: Math.min(slidesToScroll, 4) },
            1024: { slidesPerView: maxSlides, slidesPerGroup: Math.min(slidesToScroll, maxSlides) }
        }
    });
});
