import './bootstrap';
import './like.js';
import { initPartialReload } from './partialReload.js';
import { initNavigationFeedback } from './navigationFeedback.js';
import 'alpinejs';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    initNavigationFeedback();
    initPartialReload();
});




