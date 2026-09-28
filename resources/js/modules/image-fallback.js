const attempted = new WeakSet();
const useFallback = (image) => {
    if (!(image instanceof HTMLImageElement) || !image.dataset.imageFallback || attempted.has(image)) return;
    attempted.add(image);
    const fallback = new URL(image.dataset.imageFallback, document.baseURI).href;
    if (image.src !== fallback) image.src = fallback;
};
document.addEventListener('error', (event) => useFallback(event.target), true);
document.querySelectorAll('img[data-image-fallback]').forEach((image) => {
    if (image.complete && !image.naturalWidth) useFallback(image);
});
