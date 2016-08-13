<?php
/**
 * @file
 * Group by root formatter.
 */

namespace Drupal\cshs\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the "Group by root" formatter.
 *
 * @FieldFormatter(
 *   id = "cshs_group_by_root",
 *   label = @Translation("Hierarchy grouped by root"),
 *   description = @Translation("Display the hierarchy of the taxonomy term grouped by root."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class CshsGroupByRootFormatter extends CshsFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $linked = !empty($this->getSetting('linked'));

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $term) {
      $parents = static::getTermParents($term);
      $root = array_shift($parents);
      $terms = [];

      foreach ($parents as $parent) {
        $terms[] = $linked ? $parent->link() : $parent->label();
      }

      $elements[$root->id()] = [
        '#theme' => 'cshs_term_group',
        '#title' => $root->label(),
        '#terms' => $terms,
      ];
    }

    return $elements;
  }

}
