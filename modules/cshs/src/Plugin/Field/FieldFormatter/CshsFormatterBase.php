<?php
/**
 * @file
 * Base formatter for CSHS field.
 */

namespace Drupal\cshs\Plugin\Field\FieldFormatter;

// Core.
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
// Modules.
use Drupal\taxonomy\Entity\Term;

/**
 * Base formatter for CSHS field.
 */
abstract class CshsFormatterBase extends EntityReferenceFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return ['linked' => FALSE] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = [];

    $element['linked'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Link to term page'),
      '#default_value' => $this->getSetting('linked'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = $this->t('Linked to term page: @linked', [
      '@linked' => $this->getSetting('linked') ? $this->t('Yes') : $this->t('No'),
    ]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // This formatter is only available for taxonomy terms.
    return $field_definition->getFieldStorageDefinition()->getSetting('target_type') === 'taxonomy_term';
  }

  /**
   * Returns an array of all parents of a given term.
   *
   * @param Term $term
   *   Taxonomy term.
   *
   * @return Term[]
   *   Parent terms of a given term.
   */
  protected static function getTermParents(Term $term) {
    return array_reverse(\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadAllParents($term->id()));
  }

}
