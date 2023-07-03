<?php

namespace B6N\BTE\Admin;

use B6N\BTE\BlockThemeExporter;
use WP_Query;

class Exporter
{
  private const DEFAULT_ARGS = [
    'orderby' => 'ID',
    'order' => 'asc',
    'post_status' => 'publish',
    'ignore_sticky_posts' => true,
    'no_found_rows' => true,
    'update_post_meta_cache' => false,
    'update_post_term_cache' => false,
    'lazy_load_term_meta' => false
  ];


  private $include_styles = false;
  private $include_parts = false;
  private $include_templates = false;
  private $include_reusables = false;

  public function __construct($args)
  {
    $this->include_styles = array_key_exists('include_styles', $args) ? $args['include_styles'] : false;
    $this->include_parts = array_key_exists('include_parts', $args) ? $args['include_parts'] : false;
    $this->include_templates = array_key_exists('include_templates', $args) ? $args['include_templates'] : false;
    $this->include_reusables = array_key_exists('include_reusables', $args) ? $args['include_reusables'] : false;
  }

  public function run()
  {
    $content = [];

    if ($this->include_styles) {
      $content['styles'] = $this->get_styles();
    }

    if ($this->include_parts) {
      $content['parts'] = $this->get_parts();
    }

    if ($this->include_templates) {
      $content['templates'] = $this->get_templates();
    }

    if ($this->include_reusables) {
      $content['reusables'] = $this->get_reusables();
    }

    return $content;
  }

  private function get_styles()
  {
    $args = array_merge(self::DEFAULT_ARGS, [
      'post_type' => BlockThemeExporter::POST_TYPES['styles'],
    ]);

    $query = new WP_Query();
    $content = $query->query($args);

    $result = [];
    /* @var WP_Post $item */
    foreach ($content as $item) {
      $result[] = [
        'name' => $item->post_name,
        'theme' => $this->get_theme($item->ID),
        'content' => json_decode($item->post_content)
      ];
    }

    return $result;
  }

  private function get_parts()
  {
    $args = array_merge(self::DEFAULT_ARGS, [
      'post_type' => BlockThemeExporter::POST_TYPES['parts'],
    ]);

    $query = new WP_Query();
    $content = $query->query($args);

    $result = [];
    /* @var WP_Post $item */
    foreach ($content as $item) {
      $result[] = [
        'title' => $item->post_title,
        'name' => $item->post_name,
        'region' => $this->get_region($item->ID),
        'theme' => $this->get_theme($item->ID),
        'content' => $item->post_content
      ];
    }

    return $result;
  }

  private function get_templates()
  {
    $args = array_merge(self::DEFAULT_ARGS, [
      'post_type' => BlockThemeExporter::POST_TYPES['templates'],
    ]);

    $query = new WP_Query();
    $content = $query->query($args);

    $result = [];
    /* @var WP_Post $item */
    foreach ($content as $item) {
      $result[] = [
        'title' => $item->post_title,
        'name' => $item->post_name,
        'description' => $item->post_excerpt,
        'theme' => $this->get_theme($item->ID),
        'content' => $item->post_content
      ];
    }

    return $result;
  }

  private function get_reusables()
  {
    $args = array_merge(self::DEFAULT_ARGS, [
      'post_type' => BlockThemeExporter::POST_TYPES['reusables'],
    ]);

    $query = new WP_Query();
    $content = $query->query($args);

    $result = [];
    /* @var WP_Post $item */
    foreach ($content as $item) {
      $result[] = [
        'title' => $item->post_title,
        'name' => $item->post_name,
        'content' => $item->post_content
      ];
    }

    return $result;
  }

  private function get_theme($post_id)
  {
    $stylesheet = '';
    $terms = get_the_terms($post_id, BlockThemeExporter::THEME_TERM);
    if (!is_wp_error($terms) && $terms && count($terms)) {
      $term = current($terms);
      $stylesheet = $term->name;
    }

    $theme = wp_get_theme($stylesheet);

    return [
      'name' => $theme->get('Name'),
      'slug' => $theme->get_stylesheet(),
    ];
  }

  private function get_region($post_id)
  {
    if (!defined('WP_TEMPLATE_PART_AREA_UNCATEGORIZED')) {
      define('WP_TEMPLATE_PART_AREA_UNCATEGORIZED', 'uncategorized');
    }

    $region = WP_TEMPLATE_PART_AREA_UNCATEGORIZED;

    $terms = get_the_terms($post_id, BlockThemeExporter::REGION_TERM);
    if (!is_wp_error($terms) && $terms && count($terms)) {
      $term = current($terms);
      $region = $term->name;
    }

    return $region;
  }
}
