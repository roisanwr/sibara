document.addEventListener('DOMContentLoaded', function() {
    // Element selectors
    const body = document.body;
    const appContainer = document.getElementById('app-container');
    const sidebarToggleButton = document.getElementById('sidebar-toggle-button');
    const mobileOverlay = document.getElementById('mobile-overlay');
    const themeToggle = document.getElementById('theme-toggle');
    const sunIcon = document.getElementById('sun-icon');
    const moonIcon = document.getElementById('moon-icon');
    
    const notificationButton = document.getElementById('notification-button');
    const notificationDropdown = document.getElementById('notification-dropdown');

    const profileButton = document.getElementById('profile-button');
    const profileDropdown = document.getElementById('profile-dropdown');

    // --- Sidebar Functionality ---
    if (sidebarToggleButton) {
        sidebarToggleButton.addEventListener('click', () => {
            if (window.innerWidth <= 767) {
                appContainer.classList.toggle('sidebar-mobile-open');
            } else {
                body.classList.toggle('sidebar-collapsed');
            }
        });
    }
    
    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', () => {
            appContainer.classList.remove('sidebar-mobile-open');
        });
    }

    // --- Submenu Functionality ---
    const submenuContainers = document.querySelectorAll('.submenu-container');
    submenuContainers.forEach(container => {
        const button = container.querySelector('button');
        const submenu = container.querySelector('.submenu');
        const arrow = button.querySelector('svg:last-child');
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            // Close other open submenus
             submenuContainers.forEach(otherContainer => {
                if (otherContainer !== container) {
                   otherContainer.querySelector('.submenu').classList.remove('open');
                   otherContainer.querySelector('button svg:last-child').classList.remove('rotate-180');
                }
            });
            submenu.classList.toggle('open');
            arrow.classList.toggle('rotate-180');
        });
    });

    // --- Theme Toggler ---
     if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            if (currentTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'light');
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
            }
        });
    }

    // --- Dropdown Logic ---
    function setupDropdown(button, dropdown) {
         if(!button || !dropdown) return;
         button.addEventListener('click', (event) => {
            event.stopPropagation();
            closeAllDropdowns(dropdown);
            dropdown.classList.toggle('show');
        });
    }

    function closeAllDropdowns(exceptDropdown = null) {
        document.querySelectorAll('.dropdown').forEach(d => {
            if (d !== exceptDropdown) {
                d.classList.remove('show');
            }
        });
    }
    
    setupDropdown(notificationButton, notificationDropdown);
    setupDropdown(profileButton, profileDropdown);

    // Close dropdowns if clicked outside
    window.addEventListener('click', () => {
        closeAllDropdowns();
    });
});
