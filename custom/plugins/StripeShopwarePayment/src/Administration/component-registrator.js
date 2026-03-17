const { Locale, Component, Module } = Shopware;

export const componentRegistrator = {
    registerModule(module) {
        this.registerTranslations(module);
        Module.register(module.name, module);
    },

    registerComponent(componentFile) {
        const component = this.transformToComponent(componentFile);
        this.registerTranslations(component);

        // Components can be either own components or extensions from existing components. Register them accordingly.
        if (component.extendsFrom) {
            Component.extend(component.name, component.extendsFrom, component);
        } else {
            Component.register(component.name, component);
        }
    },

    registerOverride(componentFile) {
        const component = this.transformToComponent(componentFile);
        this.registerTranslations(component);

        Component.override(component.overrideFrom, component);
    },

    registerTranslations(component) {
        if (!component.translations) {
            return;
        }

        Object.keys(component.translations).forEach((locale) => {
            Locale.extend(locale, component.translations[locale]);
        });
    },

    transformToComponent(componentFile) {
        const component = componentFile.default;
        if (componentFile.template) {
            component.template = componentFile.template;
        }

        return component;
    },
};
