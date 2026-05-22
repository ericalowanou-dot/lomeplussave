import './bootstrap';
import './like.js';
import { initPartialReload } from './partialReload.js';
import 'alpinejs';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    initPartialReload();
});




