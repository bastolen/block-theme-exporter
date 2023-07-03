<?php

namespace B6N\BTE;

use B6N\BTE\Admin\Admin;

class BlockThemeExporter
{

  public const THEME_TERM = 'wp_theme';
  public const REGION_TERM = 'wp_template_part_area';

  public const POST_TYPES = [
    'styles' => 'wp_global_styles',
    'parts' => 'wp_template_part',
    'templates' => 'wp_template',
    'reusables' => 'wp_block',
  ];

  private $name;
  private $version;

  public function __construct($name, $version)
  {
    $this->name = $name;
    $this->version = $version;
  }

  public function run()
  {
    $this->init_i18n();
    $admin = new Admin($this->name, $this->version);
    $admin->init();
  }

  private function init_i18n()
  {
    load_plugin_textdomain(
      'b6n-bte',
      false,
      B6N_BTE_NAME . '/languages/'
    );
  }
}
