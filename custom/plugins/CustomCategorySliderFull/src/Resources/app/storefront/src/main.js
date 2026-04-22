import Plugin from 'src/plugin-system/plugin.class';
import { tns } from 'tiny-slider/src/tiny-slider';
import './scss/category-slider-full.scss';

class CustomCategorySliderFullPlugin extends Plugin {
    init() {
        const track = this.el.querySelector('.ccsf-track');

        if (!track) {
            return;
        }

        const slides = track.children.length;
        if (slides <= 1) {
            this.el.classList.add('is-static');
            return;
        }

        let opts = {};
        const rawOpts = this.el.getAttribute('data-ccsf-slider');

        if (rawOpts) {
            try {
                opts = JSON.parse(rawOpts);
            } catch (e) {
                opts = {};
            }
        }

        const controlsContainer = this.el.querySelector('.ccsf-controls');

        tns({
            container: track,
            items: Number(opts.items) || 4,
            slideBy: Number(opts.slideBy) || 1,
            gutter: Number(opts.gutter) || 12,
            controls: true,
            controlsContainer: controlsContainer || false,
            prevButton: this.el.querySelector('.ccsf-prev'),
            nextButton: this.el.querySelector('.ccsf-next'),
            nav: false,
            mouseDrag: true,
            speed: 350,
            loop: false,
            responsive: {
                0: { items: 1.2, gutter: 10 },
                576: { items: 2.2, gutter: 10 },
                768: { items: 3.2, gutter: 12 },
                992: { items: Number(opts.items) || 4, gutter: Number(opts.gutter) || 12 },
            },
        });
    }
}

const PluginManager = window.PluginManager;
PluginManager.register('CustomCategorySliderFullPlugin', CustomCategorySliderFullPlugin, '.js-ccsf-slider');
