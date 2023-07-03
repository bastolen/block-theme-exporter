<?php

/**
 * Plugin Name: Block Theme Exporter
 * Description: This plugin makes it possible to export changes made locally to a block theme
 * Version: 1.0.0
 * Author: Bas Tolen
 * Text Domain: b6n-bte
 * Domain Path: /languages
 */

namespace {
  define('B6N_BTE_VERSION', '1.0.0');
  define('B6N_BTE_NAME', 'block-theme-exporter');
  define('B6N_BTE_ENTRY', plugin_dir_path(__FILE__));

  if (!function_exists('debug')) {
    function debug(...$arr)
    {
      echo '<pre>';
      print_r($arr);
      echo '</pre>';
    }
  }

  if (!function_exists('dd')) {
    function dd(...$arr)
    {
      die(debug(...$arr));
    }
  }
}

namespace B6N\BTE {

  use B6N\BTE\BlockThemeExporter;

  spl_autoload_register(function ($classname) {
    $plugin_ns = __NAMESPACE__;
    if (substr($classname, 0, strlen($plugin_ns)) === $plugin_ns) {
      $separator = DIRECTORY_SEPARATOR;
      $class = str_replace('\\', $separator, str_replace('_', '-', strtolower($classname)));
      $plugin_ns_formatted = str_replace('\\', $separator, str_replace('_', '-', strtolower($plugin_ns))) . $separator;

      $class = substr($class, strlen($plugin_ns_formatted));

      $classes = dirname(__FILE__) . $separator . $class . '.php';
      $exploded_classes = explode($separator, $classes);
      $class_id = end($exploded_classes);
      $class_file = str_replace($class_id, 'class-' . $class_id, $classes);
      $interface_file = str_replace($class_id, 'interface-' . $class_id, $classes);
      $abstract_file = str_replace($class_id, 'abstract-' . $class_id, $classes);

      if (file_exists($class_file)) {
        require_once($class_file);
      } elseif (file_exists($interface_file)) {
        require_once($interface_file);
      } elseif (file_exists($abstract_file)) {
        require_once($abstract_file);
      }
    }
  });

  add_action('plugins_loaded', function () {
    $plugin = new BlockThemeExporter(B6N_BTE_NAME, B6N_BTE_VERSION);
    $plugin->run();
  });
}
