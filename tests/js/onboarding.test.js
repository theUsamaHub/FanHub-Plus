import test from 'node:test';
import assert from 'node:assert/strict';
import { fandomOnboarding } from '../../resources/js/modules/onboarding-state.js';

test('only 3 to 5 selections can submit, with duplicate submissions blocked', () => {
    for (const count of [0, 1, 2, 3, 4, 5, 6]) {
        const state = fandomOnboarding();
        state.selected = Array.from({ length: count }, (_, i) => String(i));
        let prevented = false;
        state.submit({ preventDefault() { prevented = true; } });
        assert.equal(prevented, count < 3 || count > 5);
        assert.equal(state.submitting, count >= 3 && count <= 5);
        if (state.submitting) {
            state.submit({ preventDefault() { prevented = true; } });
            assert.equal(prevented, true);
        }
    }
});

test('modal blocks Escape, traps focus at both ends and recovers from back navigation', () => {
    const state = fandomOnboarding();
    const handlers = {};
    const first = { focus() { globalThis.document.activeElement = this; } };
    const last = { focus() { globalThis.document.activeElement = this; } };
    let opened = false;
    let pageshow;
    globalThis.window = { addEventListener(event, handler) { if (event === 'pageshow') pageshow = handler; } };
    globalThis.document = { activeElement: first };
    state.$watch = () => {};
    state.$el = {
        dataset: { selected: '[1,2,2]' },
        removeAttribute() {},
        showModal() { opened = true; },
        addEventListener(event, handler) { handlers[event] = handler; },
        querySelectorAll(selector) {
            assert.ok(selector.includes(':not([type="hidden"])'));
            return [first, last];
        },
    };
    state.init();
    assert.equal(opened, true);
    assert.deepEqual(state.selected, ['1', '2']);
    let prevented = false;
    handlers.keydown({ key: 'Escape', preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    handlers.keydown({ key: 'Tab', shiftKey: true, preventDefault() {} });
    assert.equal(document.activeElement, last);
    handlers.keydown({ key: 'Tab', shiftKey: false, preventDefault() {} });
    assert.equal(document.activeElement, first);
    state.submitting = true;
    pageshow();
    assert.equal(state.submitting, false);
});
