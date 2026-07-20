import Plugin from 'src/plugin-system/plugin.class';

export default class SmartBundlePlugin extends Plugin {
    init() {
        this.el.querySelector('[data-add-all-to-cart]')?.addEventListener('click', this.onAddAll.bind(this));
    }

    async onAddAll() {
        const productIds = Array.from(this.el.querySelectorAll('.smart-bundle-item:checked')).map((el) => el.value);

        for (const id of productIds) {
            await fetch('/checkout/line-item/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new URLSearchParams({
                    redirectTo: 'frontend.cart.offcanvas',
                    'lineItems[0][id]': id,
                    'lineItems[0][referencedId]': id,
                    'lineItems[0][type]': 'product',
                    'lineItems[0][quantity]': '1',
                    'lineItems[0][stackable]': '1',
                    'lineItems[0][removable]': '1',
                }),
            });
        }

        window.location.reload();
    }
}
