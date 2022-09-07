<?php

namespace PluginLister\Utility;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Core\PluginCollection;

class Plugins
{
    public function get(): array
    {
        $pluginCollection = new PluginCollection();
        $plugins = Configure::read('plugins');

        $display = [];

        foreach (array_keys($plugins) as $pluginName) {
            $plugin = $pluginCollection->get($pluginName);
            $name = $plugin->getName();
            $loaded = Plugin::isLoaded($name) ? "Yes" : "No";
            $display[] = [get_class($plugin), $name, $loaded];
        }

        return $display;
    }
    public function getWithComposer(): array
    {

        chdir(ROOT);

        exec('composer dump-autoload -o');

        $vendorDir = realpath(CAKE_CORE_INCLUDE_PATH . '/../../');

        /**
         * @var \Composer\Autoload\ClassLoader
         */
        $classMap = require $vendorDir . '/autoload.php';

        if (false === empty($classMap)) {
            $classes = array_keys($classMap->getClassMap());
        }

        $children = [];

        foreach ($classes as $class) {
            if (
                substr($class, -6) === 'Plugin' &&
                is_subclass_of($class, \Cake\Core\BasePlugin::class)
            ) {
                $children[] = $class;
            }
        }

        $display = array_map(function ($pluginClass) {
            $name = (new $pluginClass())->getName();
            $loaded = Plugin::isLoaded($name) ? "Yes" : "No";
            return [$pluginClass, $name, $loaded];
        }, $children);

        return $display;
    }
}
