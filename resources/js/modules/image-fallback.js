// Handle missing uploads as well as absent database artwork, including cached errors.
document.querySelectorAll('img[data-image-fallback]').forEach((image) => {
    const useFallback = () => {
        if (image.src !== image.dataset.imageFallback) image.src = image.dataset.imageFallback;
    };
    image.addEventListener('error', useFallback);
    if (image.complete && !image.naturalWidth) useFallback();
});
