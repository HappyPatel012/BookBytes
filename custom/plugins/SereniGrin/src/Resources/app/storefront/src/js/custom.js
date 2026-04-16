document.addEventListener('DOMContentLoaded', function () {
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
