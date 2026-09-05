/**
 * Omoja Male Boutique Main JavaScript Helper
 */

document.addEventListener('DOMContentLoaded', () => {
    // Initialize Swiper Hero Banner Carousel if present
    if (document.querySelector('.hero-swiper')) {
        new Swiper('.hero-swiper', {
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    }

    // Initialize Instagram Grid Swiper / Category Swiper
    if (document.querySelector('.category-swiper')) {
        new Swiper('.category-swiper', {
            slidesPerView: 2,
            spaceBetween: 16,
            breakpoints: {
                640: { slidesPerView: 3, spaceBetween: 20 },
                1024: { slidesPerView: 5, spaceBetween: 24 }
            }
        });
    }
});

// Toast notification helper using SweetAlert2
function showToast(icon, title) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: title,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: '#002210',
            color: '#ffffff'
        });
    } else {
        alert(title);
    }
}
