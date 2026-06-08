import './bootstrap';

import Alpine from 'alpinejs';
import { registerSipengMascotAssistant } from './sipepeng-mascot-assistant';

document.addEventListener('alpine:init', () => {
    registerSipengMascotAssistant(Alpine);

    Alpine.store('sidebar', {
        collapsed: localStorage.getItem('sipeng-sidebar-collapsed') === '1',
        mobileOpen: false,

        toggleCollapse() {
            this.collapsed = ! this.collapsed;
            localStorage.setItem('sipeng-sidebar-collapsed', this.collapsed ? '1' : '0');
        },

        openMobile() {
            this.mobileOpen = true;
        },

        closeMobile() {
            this.mobileOpen = false;
        },
    });
});

window.Alpine = Alpine;

Alpine.start();
