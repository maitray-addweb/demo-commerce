<?php
/**
 * @file
 * Views integration.
 */

/**
 * Implements hook_views_plugins_filter_alter().
 */
function cshs_views_plugins_filter_alter(array &$plugins) {
  $plugins['taxonomy_index_tid']['class'] = Drupal\cshs\Plugin\views\filter\CshsTaxonomyIndexTid::class;
}
