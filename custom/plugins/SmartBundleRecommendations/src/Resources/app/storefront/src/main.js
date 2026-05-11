import PluginManager from 'src/plugin-system/plugin.manager';
import SmartBundlePlugin from './plugin/smart-bundle.plugin';

PluginManager.register('SmartBundlePlugin', SmartBundlePlugin, '[data-smart-bundle]');
