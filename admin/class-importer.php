<?php

namespace B6N\BTE\Admin;

use B6N\BTE\BlockThemeExporter;

class Importer
{
  private $errors = [];
  private $post_ids = [];
  private $styles = [];
  private $parts = [];
  private $templates = [];
  private $reusables = [];


  public function __construct($data)
  {
    if (property_exists($data, 'styles')) {
      $this->styles = $data->styles;
    }

    if (property_exists($data, 'parts')) {
      $this->parts = $data->parts;
    }

    if (property_exists($data, 'templates')) {
      $this->templates = $data->templates;
    }

    if (property_exists($data, 'reusables')) {
      $this->reusables = $data->reusables;
    }
  }


  public function run()
  {
    $this->import_styles();
    $this->import_parts();
    $this->import_templates();
    $this->import_reusables();

    $is_success = count($this->errors) === 0;

    if (!$is_success) {
      foreach ($this->post_ids as $post_id) {
        wp_delete_post($post_id, true);
      }
    }

    return (object)[
      'status' => $is_success,
      'errors' => $this->errors,
    ];
  }

  private function import_styles()
  {
    foreach ($this->styles as $item) {
      if (!$this->validate_object($item, ['name', 'content', 'theme|slug'])) {
        $this->errors[] = wp_sprintf(__('Something went wrong with importing styles for the theme %s. The post name of the failed import is "%s".', 'b6n-bte'), $item->theme->name, $item->name);
        continue;
      }

      $post = [
        'post_name' => $item->name,
        'post_title' => 'Custom Styles',
        'post_content' => wp_json_encode($item->content),
        'post_type' => BlockThemeExporter::POST_TYPES['styles'],
        'post_status' => 'publish',
        'tax_input' => [
          BlockThemeExporter::THEME_TERM => [$item->theme->slug],
        ],
      ];

      $post_id = wp_insert_post($post, true);

      if (is_wp_error($post_id)) {
        $this->errors[] = wp_sprintf(__('Something went wrong with importing styles for the theme %s. The post name of the failed import is "%s".', 'b6n-bte'), $item->theme->name, $item->name);
      } else {
        $this->post_ids[] = $post_id;
      }
    }
  }

  private function import_parts()
  {
    foreach ($this->parts as $item) {
      if (!$this->validate_object($item, ['name', 'title', 'content', 'region', 'theme|slug'])) {
        $this->errors[] = wp_sprintf(__('Something went wrong with importing the template part "%s" for the theme "%s". The post name of the failed import is "%s".', 'b6n-bte'), $item->title, $item->theme->name, $item->name);
        continue;
      }

      $post = [
        'post_name' => $item->name,
        'post_title' => $item->title,
        'post_content' => $item->content,
        'post_type' => BlockThemeExporter::POST_TYPES['parts'],
        'post_status' => 'publish',
        'tax_input' => [
          BlockThemeExporter::THEME_TERM => [$item->theme->slug],
          BlockThemeExporter::REGION_TERM => [$item->region]
        ],
      ];

      $post_id = wp_insert_post($post, true);

      if (is_wp_error($post_id)) {
        $this->errors[] = wp_sprintf(__('Something went wrong with importing the template part "%s" for the theme "%s". The post name of the failed import is "%s".', 'b6n-bte'), $item->title, $item->theme->name, $item->name);
      } else {
        $this->post_ids[] = $post_id;
      }
    }
  }

  private function import_templates()
  {
    foreach ($this->templates as $item) {
      if (!$this->validate_object($item, ['name', 'title', 'content', 'description', 'theme|slug'])) {
        $this->errors[] = wp_sprintf(__('Something went wrong with importing the template "%s" for the theme "%s". The post name of the failed import is "%s".', 'b6n-bte'), $item->title, $item->theme->name, $item->name);
        continue;
      }

      $post = [
        'post_name' => $item->name,
        'post_title' => $item->title,
        'post_content' => $item->content,
        'post_excerpt' => $item->description,
        'post_type' => BlockThemeExporter::POST_TYPES['templates'],
        'post_status' => 'publish',
        'tax_input' => [
          BlockThemeExporter::THEME_TERM => [$item->theme->slug],
        ],
      ];

      $post_id = wp_insert_post($post, true);

      if (is_wp_error($post_id)) {
        $this->errors[] = wp_sprintf(__('Something went wrong with importing the template "%s" for the theme "%s". The post name of the failed import is "%s".', 'b6n-bte'), $item->title, $item->theme->name, $item->name);
      } else {
        $this->post_ids[] = $post_id;
      }
    }
  }

  private function import_reusables()
  {
    foreach ($this->reusables as $item) {
      if (!$this->validate_object($item, ['name', 'title', 'content'])) {
        $this->errors[] = wp_sprintf(__('Something went wrong with importing the reusable block "%s". The post name of the failed import is "%s".', 'b6n-bte'), $item->title, $item->name);
        continue;
      }

      $post = [
        'post_name' => $item->name,
        'post_title' => $item->title,
        'post_content' => $item->content,
        'post_type' => BlockThemeExporter::POST_TYPES['reusables'],
        'post_status' => 'publish',
      ];

      $post_id = wp_insert_post($post, true);

      if (is_wp_error($post_id)) {
        $this->errors[] = wp_sprintf(__('Something went wrong with importing the reusable block "%s". The post name of the failed import is "%s".', 'b6n-bte'), $item->title, $item->name);
      } else {
        $this->post_ids[] = $post_id;
      }
    }
  }

  private function validate_object($object, $required_keys)
  {
    $is_valid = true;

    foreach ($required_keys as $required_key) {
      if (!$is_valid) {
        break;
      }

      $keys = explode('|', $required_key);
      $cloned = $object;
      foreach ($keys as $key) {
        if (property_exists($cloned, $key)) {
          $cloned = $cloned->$key;
          break;
        } else {
          $is_valid = false;
        }
      }
    }

    return $is_valid;
  }
}
