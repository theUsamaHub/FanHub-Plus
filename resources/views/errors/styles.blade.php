:root { color-scheme: dark; --error-bg: #15111c; --error-surface: #211b2b; --error-text: #f8f4fc; --error-muted: #b4a5c5; --error-line: #bfa7d528; --error-accent: #ff9b35; }
:root[data-theme='light'] { color-scheme: light; --error-bg: #fff9f2; --error-surface: #fff; --error-text: #20172b; --error-muted: #74607e; --error-line: #70538330; --error-accent: #a9480c; }
* { box-sizing: border-box; }
body { margin: 0; }
.error-page { min-height: 100vh; min-height: 100svh; display: flex; flex-direction: column; background: var(--error-bg); color: var(--error-text); font-family: 'Saira', Arial, sans-serif; }
.error-page a { color: inherit; text-decoration: none; }
.error-page a:focus-visible { outline: 2px solid var(--error-accent); outline-offset: 5px; border-radius: 5px; }
.error-header { width: min(1280px, 100%); margin: 0 auto; padding: 30px 40px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
.error-header .fh-brand { display: inline-flex; align-items: center; gap: 10px; }
.error-header .fh-brand-mark { width: 38px; height: 42px; flex-shrink: 0; }
.error-header .fh-brand-wordmark { font: 800 25px/1 Arial, sans-serif; letter-spacing: -1px; }
.error-header .fh-brand-wordmark > span { color: #fca129; }
.error-header .fh-brand-wordmark small { display: block; font: 600 6px/1 Arial, sans-serif; letter-spacing: 1.4px; margin-top: 7px; color: var(--error-muted); }
.error-home { font-size: 12px; color: var(--error-muted) !important; }
.error-home span { margin-left: 12px; color: var(--error-accent); }
.error-main { flex: 1; width: min(1200px, 100%); margin: auto; padding: 40px; display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr); align-items: center; gap: 70px; }
.error-art { position: relative; display: grid; place-items: center; min-height: 400px; isolation: isolate; }
.error-art::before { content: ''; position: absolute; inset: 15%; border-radius: 50%; background: radial-gradient(circle, #ff922e18, transparent 70%); z-index: -1; }
.error-orbit { position: absolute; width: 70%; aspect-ratio: 1; border: 1px solid var(--error-line); border-radius: 50%; transform: rotate(-30deg) scaleY(.8); }
.error-orbit--outer { width: 100%; transform: rotate(35deg) scaleY(.7); }
.error-art strong { font: 700 clamp(105px, 14vw, 190px)/1 'Rajdhani', Arial, sans-serif; letter-spacing: -7px; background: linear-gradient(125deg, var(--error-accent), #ce9fdb); background-clip: text; -webkit-background-clip: text; color: transparent; }
.error-star { position: absolute; right: 8%; top: 15%; color: var(--error-accent); font-size: 38px; }
.error-coordinate { position: absolute; bottom: 15%; font-size: 9px; letter-spacing: 2px; color: var(--error-muted); }
.error-kicker { display: flex; align-items: center; gap: 9px; color: var(--error-accent); font-size: 10px; font-weight: 600; letter-spacing: 1.8px; }
.error-kicker span { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.error-content h1 { font: 700 clamp(36px, 4.6vw, 62px)/1.08 'Rajdhani', Arial, sans-serif; margin: 20px 0; letter-spacing: -.7px; }
.error-description { font-size: 14px; line-height: 1.9; color: var(--error-muted); max-width: 460px; overflow-wrap: anywhere; }
.error-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 30px; }
.error-button { display: inline-flex; align-items: center; justify-content: center; gap: 20px; padding: 14px 20px; min-height: 48px; background: #ff9b35; color: #241507 !important; font-size: 12px; font-weight: 600; border: 1px solid #ff9b35; border-radius: 9px; }
.error-button:hover { filter: brightness(1.08); }
.error-button--quiet { background: var(--error-surface); color: var(--error-text) !important; border-color: var(--error-line); }
.error-help { margin-top: 24px; font-size: 11px; color: var(--error-muted); }
.error-help a { color: var(--error-accent); text-decoration: underline; text-underline-offset: 4px; }
.error-footer { width: min(1200px, calc(100% - 80px)); margin: 30px auto 0; padding: 24px 0; border-top: 1px solid var(--error-line); display: flex; flex-wrap: wrap; justify-content: space-between; gap: 18px; font-size: 10px; color: var(--error-muted); }
.error-footer > span:first-child { letter-spacing: 1.3px; }
.error-footer nav { display: flex; gap: 20px; }
@media (max-width: 760px) { .error-header { padding: 22px; }.error-main { grid-template-columns: minmax(0, 1fr); gap: 10px; padding: 0 24px 24px; }.error-art { min-height: 250px; width: min(360px, 100%); margin: auto; }.error-art strong { font-size: 120px; }.error-content { text-align: center; }.error-kicker, .error-actions { justify-content: center; }.error-description { margin-inline: auto; }.error-footer { width: calc(100% - 48px); justify-content: center; text-align: center; }.error-home { font-size: 10px; }.error-header .fh-brand-wordmark { font-size: 21px; }.error-header .fh-brand-mark { width: 30px; } }
@media (max-width: 380px) { .error-home { display: none; }.error-actions { flex-direction: column; }.error-art { min-height: 220px; } }
