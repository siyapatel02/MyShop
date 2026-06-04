let homeBanners = [];
let currentBannerIndex = 0;
let bannerInterval = null;

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('homeBannerSlider')) {
        loadHomeBanners();
    }
});

async function loadHomeBanners() {
    try {
        const response = await fetch(API_URL + 'banners/list.php');
        const result = await response.json();

        const slider = document.getElementById('homeBannerSlider');

        if (!slider) return;

        if (!result.status || !result.data || result.data.length === 0) {
            slider.innerHTML = '';
            return;
        }

        homeBanners = result.data.filter(banner => banner.status === 'active');

        if (homeBanners.length === 0) {
            slider.innerHTML = '';
            return;
        }

        renderHomeBannerSlider();

        if (homeBanners.length > 1) {
            startBannerAutoSlide();
        }

    } catch (error) {
        console.log(error);
    }
}

function renderHomeBannerSlider() {
    const slider = document.getElementById('homeBannerSlider');

    let slidesHtml = '';

    homeBanners.forEach((banner, index) => {
        slidesHtml += `
            <div class="home-banner-slide ${index === currentBannerIndex ? 'active' : ''}">
                <img 
                    src="${banner.image_url}" 
                    alt="${banner.title}"
                    class="home-banner-img"
                >

                <div class="home-banner-overlay"></div>

                <div class="home-banner-content">
                    <h1>${banner.title}</h1>

                    ${banner.subtitle ? `<p>${banner.subtitle}</p>` : ''}

                    ${banner.button_text ? `
                        <a 
                            href="${banner.button_link || '#'}" 
                            class="home-banner-btn"
                        >
                            ${banner.button_text}
                        </a>
                    ` : ''}
                </div>
            </div>
        `;
    });

    let dotsHtml = '';

    if (homeBanners.length > 1) {
        homeBanners.forEach((banner, index) => {
            dotsHtml += `
                <button 
                    class="home-banner-dot ${index === currentBannerIndex ? 'active' : ''}"
                    onclick="goToBanner(${index})"
                ></button>
            `;
        });
    }

    slider.innerHTML = `
        <div class="home-banner-wrapper">
            ${slidesHtml}

            ${homeBanners.length > 1 ? `
                <button class="home-banner-arrow left" onclick="prevBanner()">‹</button>
                <button class="home-banner-arrow right" onclick="nextBanner()">›</button>

                <div class="home-banner-dots">
                    ${dotsHtml}
                </div>
            ` : ''}
        </div>
    `;
}

function nextBanner() {
    currentBannerIndex++;

    if (currentBannerIndex >= homeBanners.length) {
        currentBannerIndex = 0;
    }

    renderHomeBannerSlider();
}

function prevBanner() {
    currentBannerIndex--;

    if (currentBannerIndex < 0) {
        currentBannerIndex = homeBanners.length - 1;
    }

    renderHomeBannerSlider();
}

function goToBanner(index) {
    currentBannerIndex = index;
    renderHomeBannerSlider();
    restartBannerAutoSlide();
}

function startBannerAutoSlide() {
    bannerInterval = setInterval(() => {
        nextBanner();
    }, 4000);
}

function restartBannerAutoSlide() {
    if (bannerInterval) {
        clearInterval(bannerInterval);
    }

    if (homeBanners.length > 1) {
        startBannerAutoSlide();
    }
}