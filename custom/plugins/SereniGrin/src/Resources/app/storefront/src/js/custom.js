document.addEventListener('DOMContentLoaded', function () {
    const authButtons = document.querySelectorAll('.sg-auth-tab-btn');
    const authPanels = document.querySelectorAll('.sg-auth-panel');

    // New auth switcher (login/signup tabs)
    if (authButtons.length && authPanels.length) {
        const setActiveAuthTab = (target) => {
            authPanels.forEach((panel) => {
                const isActive = panel.getAttribute('data-auth-panel') === target;
                panel.classList.toggle('is-active', isActive);
                panel.hidden = !isActive;
            });

            authButtons.forEach((button) => {
                const isActive = button.getAttribute('data-auth-target') === target;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        };

        const initialActiveButton = document.querySelector('.sg-auth-tab-btn.is-active') || authButtons[0];
        if (initialActiveButton) {
            const initialTarget = initialActiveButton.getAttribute('data-auth-target');
            if (initialTarget) {
                setActiveAuthTab(initialTarget);
            }
        }

        authButtons.forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                const target = button.getAttribute('data-auth-target');
                if (!target) {
                    return;
                }

                setActiveAuthTab(target);
            });
        });
    }

    // Legacy tab switcher used in other custom sections
    const buttons = document.querySelectorAll('.tab-btn');
    const tabs = document.querySelectorAll('.tab-content');

    if (!buttons.length || !tabs.length) {
        return;
    }

    const refreshVisibleSliders = (container) => {
        if (!window.PluginManager) {
            return;
        }

        const sliderElements = container.querySelectorAll('[data-product-slider]');

        sliderElements.forEach((sliderEl) => {
            const sliderInstance = window.PluginManager.getPluginInstanceFromElement(sliderEl, 'ProductSlider');

            if (sliderInstance && typeof sliderInstance.rebuild === 'function') {
                sliderInstance.rebuild();
            }
        });

        window.dispatchEvent(new Event('resize'));
    };

    const setActiveTab = (target) => {
        tabs.forEach((tab) => {
            tab.style.display = 'none';
            tab.classList.remove('is-active');
        });

        buttons.forEach((button) => {
            button.classList.remove('active');
        });

        const activeTab = document.querySelector('.' + target);
        if (!activeTab) {
            return;
        }

        activeTab.style.display = 'block';
        activeTab.classList.add('is-active');

        const activeButton = document.querySelector('.tab-btn[data-tab="' + target + '"]');
        if (activeButton) {
            activeButton.classList.add('active');
        }

        window.setTimeout(() => refreshVisibleSliders(activeTab), 80);
    };

    const initialButton = document.querySelector('.tab-btn.active') || buttons[0];
    const initialTarget = initialButton ? initialButton.getAttribute('data-tab') : null;
    if (initialTarget) {
        setActiveTab(initialTarget);
    }

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-tab');
            if (!target) {
                return;
            }
            setActiveTab(target);
        });
    });
});
