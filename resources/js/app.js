import './main.js';
import 'bootstrap/dist/js/bootstrap.min.js';

const editorialPattern = [
    'portrait-large',
    'portrait',
    'portrait-tall',
    'portrait',
    'quiet',
    'landscape',
    'portrait-tall',
    'portrait',
    'landscape',
    'portrait',
    'quiet',
    'portrait-tall',
];

function classifyPhoto(width, height) {
    const ratio = width / height;

    if (ratio >= 2) {
        return 'panorama';
    }

    if (ratio >= 1.15) {
        return 'landscape';
    }

    return 'portrait';
}

function resolveSlot(orientation, preferredSlot) {
    if (orientation === 'panorama') {
        return 'panorama';
    }

    if (orientation === 'landscape') {
        return preferredSlot === 'panorama' ? 'panorama' : 'landscape';
    }

    if (preferredSlot === 'portrait-large' || preferredSlot === 'portrait-tall' || preferredSlot === 'quiet') {
        return preferredSlot;
    }

    return 'portrait';
}

function prepareHomeCollage(collage) {
    const items = [...collage.querySelectorAll('[data-home-photo]')];

    items.forEach((item, index) => {
        const image = item.querySelector('img');

        if (!image) {
            return;
        }

        const applyLayout = () => {
            const blockIndex = Math.floor(index / editorialPattern.length);
            const patternIndex = index % editorialPattern.length;
            const orientation = classifyPhoto(image.naturalWidth, image.naturalHeight);
            const preferredSlot = editorialPattern[patternIndex];

            item.dataset.orientation = orientation;
            item.dataset.slot = resolveSlot(orientation, preferredSlot);
            item.dataset.blockSide = blockIndex % 2 === 0 ? 'left' : 'right';
            item.classList.add('is-ready');
        };

        if (image.complete && image.naturalWidth > 0) {
            applyLayout();
            return;
        }

        image.addEventListener('load', applyLayout, { once: true });
        image.addEventListener('error', () => item.classList.add('is-ready'), { once: true });
    });
}

document.querySelectorAll('[data-home-collage]').forEach(prepareHomeCollage);
