(() => {
    document.addEventListener('DOMContentLoaded', function () {
        const sections = document.querySelectorAll('.bb-book-showcase');

        if (!sections.length) {
            return;
        }

        sections.forEach((section) => {
            const buttons = section.querySelectorAll('.bb-tab-btn');
            const tabs = section.querySelectorAll('.bb-tab-content');

            if (!buttons.length || !tabs.length) {
                return;
            }

            buttons.forEach((btn) => {
                btn.addEventListener('click', () => {
                    const target = btn.getAttribute('data-tab-target');
                    const targetTab = target ? section.querySelector('[data-tab-content="' + target + '"]') : null;

                    if (!targetTab) {
                        return;
                    }

                    buttons.forEach((button) => button.classList.remove('active'));
                    tabs.forEach((tab) => tab.classList.remove('active'));

                    btn.classList.add('active');
                    targetTab.classList.add('active');
                });
            });
        });
    });
})();
