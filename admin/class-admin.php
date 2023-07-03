<?php

namespace B6N\BTE\Admin;

use B6N\BTE\BlockThemeExporter;

class Admin
{
  private $name;
  private $version;

  private $errors = [];

  public function __construct($name, $version)
  {
    $this->name = $name;
    $this->version = $version;
  }

  public function init()
  {
    add_action('admin_menu', [$this, 'admin_menu']);
  }

  public function admin_menu()
  {
    $page = add_management_page(
      __('Block Theme Exporter', 'b6n-bte'),
      __('Block Theme Exporter', 'b6n-bte'),
      'manage_options',
      'b6n-bte',
      array($this, 'render_page')
    );

    add_action('load-' . $page, [$this, 'do_action']);
  }

  public function render_page()
  {
    include __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'page.php';
  }

  public function do_action()
  {
    if (isset($_REQUEST['action'])) {
      $action = $_REQUEST['action'];

      switch ($action) {
        case 'export':
          $this->do_export();
          break;
        case 'import':
          $this->do_import();
          break;
        default:
          break;
      }
    }
  }

  private function do_export()
  {
    check_admin_referer('bte-export');
    $categories = array_key_exists('category', $_REQUEST) ? $_REQUEST['category'] : [];
    $categories = array_values(array_filter($categories, function ($value) {
      return in_array($value, array_keys(BlockThemeExporter::POST_TYPES));
    }));
    if (count($categories) < 1) {
      return;
    }
    $args = [];
    if (in_array('styles', $categories)) {
      $args['include_styles'] = true;
    }

    if (in_array('parts', $categories)) {
      $args['include_parts'] = true;
    }

    if (in_array('templates', $categories)) {
      $args['include_templates'] = true;
    }

    if (in_array('reusables', $categories)) {
      $args['include_reusables'] = true;
    }

    $exporter = new Exporter($args);

    $filename = str_replace(['https://', '.'], ['bte_', '_'], site_url('', 'https')) . '.json';
    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Content-Type: application/json; charset=' . get_option('blog_charset'), true);
    print wp_json_encode($exporter->run());
    exit;
  }

  private function do_import()
  {
    check_admin_referer('bte-import');
    if (empty($_FILES['file']['tmp_name'])) {
      return;
    }
    $content = file_get_contents($_FILES['file']['tmp_name']);
    $json = json_decode($content);
    if (!$json) {
      return;
    }

    $importer = new Importer($json);
    $result = $importer->run();
    if ($result->status) {
      add_action('admin_notices', [$this, 'admin_success_notices']);
    } else {
      $this->errors = $result->errors;
      add_action('admin_notices', [$this, 'admin_error_notices']);
    }
  }

  public function admin_success_notices()
  {
    $class = 'notice notice-success is-dismissible';
    $message = __('Theming import is finished successfully!', 'b6n-bte');

    printf('<div class="%s"><p>%s</p></div>', esc_attr($class), esc_html($message));
  }

  public function admin_error_notices()
  {
    $class = 'notice notice-error';
    $message = __('Theming import went wrong, the following errors have occurred:', 'b6n-bte');

    printf('<div class="%s"><p>%s</p><ul>%s</ul></div>', esc_attr($class), esc_html($message), implode(array_map(function ($error) {
      return sprintf('<li>%s</li>', esc_html($error));
    }, $this->errors)));
  }
}
