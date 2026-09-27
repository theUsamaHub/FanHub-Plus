import test from 'node:test';
import assert from 'node:assert/strict';
import { initNearbyEvents } from '../../resources/js/modules/nearby-events.js';

class Element {
    constructor() { this.listeners = {}; this.hidden = true; this.disabled = false; this.value = '5'; this.innerHTML = 'normal results'; this.dataset = {}; }
    addEventListener(name, callback) { this.listeners[name] = callback; }
    fire(name, event = {}) { return this.listeners[name]?.(event); }
    setAttribute() {}
    removeAttribute(name) { delete this[name]; }
    closest() { return null; }
    querySelectorAll() { return []; }
    reset() {}
}
function setup(geo = true) {
    const keys = ['controls', 'start', 'reset', 'radius', 'radius-label', 'status', 'location', 'accuracy', 'map', 'clear-filters'];
    const elements = Object.fromEntries(keys.map(key => [key, new Element()]));
    const results = new Element();
    const form = new Element();
    const featured = new Element();
    elements.controls.dataset.endpoint = '/events/nearby';
    elements.controls.querySelector = selector => elements[selector.slice(13, -1)];
    const page = { querySelector: selector => ({ '[data-nearby-controls]': elements.controls, '[data-event-results]': results, '.events-filter-form': form, '[data-featured-events]': featured })[selector] };
    let success, failure, calls = 0, request, options;
    Object.defineProperty(globalThis, 'navigator', { configurable: true, value: geo ? { geolocation: { getCurrentPosition(ok, fail, settings) { calls++; success = ok; failure = fail; options = settings; } } } : {} });
    globalThis.document = { querySelector: () => ({ content: 'csrf' }) };
    const fields = { city: { value: 'Test city' }, q: { value: '' } };
    form.elements = { namedItem(name) { return fields[name]; } };
    globalThis.FormData = class { *[Symbol.iterator]() { for (const [key, field] of Object.entries(fields)) yield [key, field.value]; } };
    globalThis.fetch = async (url, options) => { request = JSON.parse(options.body); return { ok: true, json: async () => ({ html: 'nearby results', total: 1 }) }; };
    initNearbyEvents(page);
    return { ...elements, results, form, get calls() { return calls; }, get request() { return request; }, get options() { return options; }, grant: (coords = { latitude: 0, longitude: 0, accuracy: 30 }) => success({ coords }), deny: code => failure({ code }) };
}
const flush = () => new Promise(resolve => setImmediate(resolve));

test('location is requested only on click; search, radius, pagination and reset work', async () => {
    const ui = setup();
    assert.equal(ui.calls, 0);
    ui.start.fire('click');
    assert.equal(ui.calls, 1);
    assert.equal(ui.options.maximumAge, 0);
    assert.equal(ui.options.enableHighAccuracy, true);
    assert.equal(ui.start.disabled, true);
    ui.grant();
    await flush();
    assert.equal(ui.request.radius, 5);
    assert.equal(ui.request.latitude, 0);
    assert.equal(ui.request.city, 'Test city');
    assert.equal(ui.results.innerHTML, 'nearby results');
    assert.equal(ui.start.disabled, false);
    ui.radius.value = '10';
    ui.radius.fire('change');
    await flush();
    assert.equal(ui.request.radius, 10);
    ui.results.fire('click', { target: { closest: () => ({ href: 'https://example.com/events/nearby?page=2' }) }, preventDefault() {} });
    await flush();
    assert.equal(ui.request.page, 2);
    ui.reset.fire('click');
    assert.equal(ui.results.innerHTML, 'normal results');
    assert.equal(ui.location.hidden, true);
    assert.equal(ui.map.href, undefined);
});

test('approximate device locations show accuracy and nearest distance instead of a misleading empty message', async () => {
    const ui = setup();
    globalThis.fetch = async () => ({ ok: true, json: async () => ({ html: 'empty results', total: 0, nearest_distance_km: 62.3 }) });
    ui.start.fire('click');
    ui.grant({ latitude: 24, longitude: 67, accuracy: 12000 });
    await flush();
    assert.match(ui.accuracy.textContent, /12.0 km/);
    assert.match(ui.status.textContent, /only accurate/);
    assert.match(ui.status.textContent, /62.3 km/);
    assert.match(ui.map.href, /24%2C67/);
    assert.equal(ui.location.hidden, false);
});

test('clearing filters keeps nearby mode and reuses current coordinates', async () => {
    const ui = setup();
    ui.start.fire('click');
    ui.grant();
    await flush();
    assert.equal(ui['clear-filters'].hidden, false);
    ui['clear-filters'].fire('click');
    await flush();
    assert.equal(ui.request.city, '');
    assert.equal(ui.request.radius, 5);
    assert.equal(ui.request.latitude, 0);
    assert.equal(ui.calls, 1);
});

test('invalid browser coordinates never reach the backend', async () => {
    const ui = setup();
    ui.start.fire('click');
    ui.grant({ latitude: NaN, longitude: 67 });
    await flush();
    assert.equal(ui.request, undefined);
    assert.equal(ui.start.disabled, false);
    assert.match(ui.status.textContent, /valid location/);
});

test('denial, unavailable and timeout release loading and preserve listing', () => {
    for (const code of [1, 2, 3]) {
        const ui = setup();
        ui.start.fire('click');
        ui.deny(code);
        assert.equal(ui.start.disabled, false);
        assert.equal(ui.results.innerHTML, 'normal results');
        assert.match(ui.status.textContent, /denied|determined|timed out/);
    }
    const ui = setup(false);
    ui.start.fire('click');
    assert.match(ui.status.textContent, /does not support/);
});

test('backend error preserves results and reset ignores stale location callbacks', async () => {
    const ui = setup();
    globalThis.fetch = async () => ({ ok: false });
    ui.start.fire('click');
    ui.grant();
    await flush();
    assert.equal(ui.start.disabled, false);
    assert.equal(ui.results.innerHTML, 'normal results');
    assert.match(ui.status.textContent, /could not be loaded/);
    ui.start.fire('click');
    ui.reset.fire('click');
    ui.grant();
    await flush();
    assert.equal(ui.results.innerHTML, 'normal results');
});
