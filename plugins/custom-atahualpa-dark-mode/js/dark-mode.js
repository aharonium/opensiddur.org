/**
 * Open Siddur Dark Mode 
 * v.0.3.1.1
 * 
 * Theme manager.
 * Handles loading, saving and toggling the user's theme preference.
 */

(function () {

    'use strict';

    const STORAGE_KEY = 'opensiddur-theme';
    const SYSTEM_KEY  = 'opensiddur-system';
    
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');


    /**
     * Return the operating system's preferred theme.
     */
    
    function getSystemTheme() {

        return mediaQuery.matches
            ? 'dark'
            : 'light';

    }


    /**
     * Return the user preference if the setting is modified.
     */

    function getManualTheme() {

        const theme = localStorage.getItem(STORAGE_KEY);

        return theme === 'dark'
            ? 'dark'
            : 'light';

    }


    /**
     * Return the stored preference.
     */

    function getCurrentTheme() {

        return isSystemMode()
            ? getSystemTheme()
            : getManualTheme();    

    }


    /**
     * Helper for getCurrentTheme() function. 
     * 
     * New visitors get a system key
     */
     
    function isSystemMode() {

        const value = localStorage.getItem(SYSTEM_KEY);

        return value === null || value === 'true';

    }
    

    /**
     * Apply a manual theme.
     *
     * Theme may be:
     *     light
     *     dark
     */
     
    function setTheme(theme) {

        try {
            localStorage.setItem(STORAGE_KEY, theme);
        } catch (e) {}

        applyTheme();

    }


    /**
     * Apply the correct theme
     */
     
    function applyTheme() {

        const theme = getCurrentTheme();

        document.documentElement.dataset.theme = theme;

        updateButton();
        updateSystemButton();

    }


    /**
     * Update the theme preference indicator.
     */
    
    function updateButton() {

        const button = document.getElementById('osdm-toggle');

        if (!button) {
            return;
        }

        const theme = getCurrentTheme();

        button.textContent =
        theme === 'dark'
            ? '☀'
            : '🌙';
        

        button.title =
        theme === 'dark'
            ? 'Dark (click for Light)'
            : 'Light (click for Dark)';
        
        button.setAttribute('aria-label', button.title);

    }

    
    /**
     * Update the system button
     */

    function updateSystemButton() {

        const button =
            document.getElementById('osdm-system');

        if (!button) {
            return;
        }

        const enabled = isSystemMode();

        /* Update CSS class */

        button.classList.remove('manual', 'system');
        button.classList.add(
            enabled
                ? 'system'
                : 'manual'
        );

        /* Update icon */

        button.textContent =
            enabled
                ? '🖥️'
                : '👤';

        /* Update tooltip */

        button.title =
            enabled
                ? 'System (default)'
                : 'Manual (select)';

        button.setAttribute('aria-label', button.title);

    }
    

    /**
     * Toggle between light and dark 
     */

    function toggleTheme() {

        const next =
            getCurrentTheme() === 'dark'
                ? 'light'
                : 'dark';

        try {

            localStorage.setItem(SYSTEM_KEY, 'false');

        } catch (e) {}

        setTheme(next);

    }


    /**
     * Toggle between manual and system
     */

    function toggleSystemMode() {

        const enabled = !isSystemMode();

        try {

            localStorage.setItem(SYSTEM_KEY, enabled);

        } catch (e) {}

        applyTheme();

    }


    /**
     * Listen for system changes (compatible with older browsers)
     */

    if (mediaQuery.addEventListener) {

        mediaQuery.addEventListener('change', function () {

            if (isSystemMode()) {
                applyTheme();
            }

        });

    } else {

        mediaQuery.addListener(function () {

            if (isSystemMode()) {
                applyTheme();
            }

        });

    }
 

    /**
     * Initialize.
     *
     * The PHP already restored the saved theme before page render,
     * but if it didn't for some reason, restore it now.
     */
     
     document.addEventListener('DOMContentLoaded', function () {

        /* updateButton(); */
    
        applyTheme();

        document
            .getElementById('osdm-toggle')
            ?.addEventListener('click', toggleTheme);

        document
            .getElementById('osdm-system')
            ?.addEventListener('click', toggleSystemMode);
    
    });


    /**
     * Export a tiny public API.
     */
     
    window.OSDarkMode = {

        getCurrentTheme,
        setTheme,
        isSystemMode,
        applyTheme,
        toggleSystemMode,
        toggleTheme
        
    };

})();