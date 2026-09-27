import Alpine from 'alpinejs';
import '../../css/pages/onboarding.css';
import { fandomOnboarding } from './onboarding-state';

if (document.querySelector('[data-onboarding]')) {
    Alpine.data('fandomOnboarding', fandomOnboarding);
    Alpine.start();
}
