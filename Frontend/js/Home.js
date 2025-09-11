// Icons
if (window.lucide) { lucide.createIcons(); }

/* =========================
   SWIPER - Carousel
   (scoped selectors + z-index)
========================= */
document.addEventListener('DOMContentLoaded', () => {
  const swiper = new Swiper('.stats-swiper', {
    loop: true,
    autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
    slidesPerView: 1,
    spaceBetween: 20,
    pagination: {
      el: '.stats-swiper .swiper-pagination',
      clickable: true,
    },
    navigation: {
      nextEl: '.stats-swiper .swiper-button-next',
      prevEl: '.stats-swiper .swiper-button-prev',
    },
    breakpoints: {
      768: { slidesPerView: 2 },
      1024: { slidesPerView: 3 }
    }
  });
});

/* =========================
   Smooth scrolling
========================= */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    const href = this.getAttribute('href');
    // Evitar bloquear enlaces "vacíos"
    if (href.length > 1) {
      e.preventDefault();
      const target = document.querySelector(href);
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }
  });
});

/* =========================
   Header background opacity
========================= */
window.addEventListener('scroll', () => {
  const header = document.querySelector('.header');
  header.style.background =
    window.scrollY > 100 ? 'rgba(255, 255, 255, 0.98)' : 'transparent';
});

/* =========================
   Feature cards hover effect
========================= */
document.querySelectorAll('.feature-card').forEach(card => {
  card.addEventListener('mouseenter', () => {
    card.style.transform = 'translateY(-6px) scale(1.01)';
  });
  card.addEventListener('mouseleave', () => {
    card.style.transform = 'translateY(0) scale(1)';
  });
});
