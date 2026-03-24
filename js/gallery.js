document.addEventListener('DOMContentLoaded', () => {
    const lightbox = document.getElementById('lightbox');
    const lbImg = document.getElementById('lb-img');
    const lbVideo = document.getElementById('lb-video');
    const lbCaption = document.getElementById('lb-caption');

    const btnClose = document.getElementById('lb-close');
    const btnPrev = document.getElementById('lb-prev');
    const btnNext = document.getElementById('lb-next');

    const triggers = Array.from(document.querySelectorAll('.gallery-trigger'));
    let currentIndex = 0;

    if (triggers.length === 0) return;

    triggers.forEach((trigger, index) => {
        trigger.style.cursor = 'pointer';
        trigger.addEventListener('click', () => {
            currentIndex = index;
            updateLightbox();
            lightbox.classList.add('active');
        });
    });

    function updateLightbox() {
        const item = triggers[currentIndex];
        const type = item.getAttribute('data-type');
        const src = item.getAttribute('data-src');
        const alt = item.getAttribute('data-alt');

        // Reset obu tagów
        lbImg.hidden = true;
        lbVideo.hidden = true;
        lbVideo.pause();

        if (type === 'video') {
            lbVideo.src = src;
            lbVideo.hidden = false;
        } else {
            lbImg.src = src;
            lbImg.alt = alt;
            lbImg.hidden = false;
        }
        lbCaption.textContent = alt;
    }

    function closeLightbox() {
        lightbox.classList.remove('active');
        lbVideo.pause(); // Zatrzymaj muzykę z wideo po zamknięciu
    }

    function showNext() {
        currentIndex = (currentIndex + 1) % triggers.length;
        updateLightbox();
    }

    function showPrev() {
        currentIndex = (currentIndex - 1 + triggers.length) % triggers.length;
        updateLightbox();
    }

    btnClose.addEventListener('click', closeLightbox);

    btnNext.addEventListener('click', (e) => {
        e.stopPropagation();
        showNext();
    });

    btnPrev.addEventListener('click', (e) => {
        e.stopPropagation();
        showPrev();
    });

    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox || e.target.classList.contains('lightbox-content')) {
            closeLightbox();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (!lightbox.classList.contains('active')) return;

        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowRight') showNext();
        if (e.key === 'ArrowLeft') showPrev();
    });
});