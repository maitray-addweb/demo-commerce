<?php
/**
 * @file
 * Field widget definition.
 */

namespace Drupal\cshs\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;

use Drupal\cshs\CshsOptionsFromHelper;

/**
 * Provides "cshs" field widget.
 *
 * @FieldWidget(
 *   id = "cshs",
 *   label = @Translation("Client-side hierarchical select"),
 *   field_types = {
 *     "entity_reference",
 *   },
 * )
 */
class CshsWidget extends WidgetBase {

  use CshsOptionsFromHelper {
    defaultSettings as helperDefaultSettings;
    settingsSummary as helperSettingsSummary;
    settingsForm as helperSettingsForm;
    formElement as helperFormElement;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return static::helperDefaultSettings() + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return $this->helperSettingsSummary();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return $this->helperSettingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['target_id'] = array_merge($element, $this->helperFormElement(), [
      '#label' => $this->fieldDefinition->getLabel(),
      '#value_key' => 'target_id',
      '#default_value' => isset($items[$delta]->target_id) ? $items[$delta]->target_id : '',
    ]);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // This widget is only available for taxonomy terms.
    return 'taxonomy_term' === $field_definition->getFieldStorageDefinition()->getSetting('target_type');
  }

  /**
   * {@inheritdoc}
   */
  public function getVocabulary() {
    $settings = $this->getFieldSettings();

    if (empty($settings['handler_settings']['target_bundles'])) {
      return NULL;
    }

    return $this->getVocabularyStorage()->load(reset($settings['handler_settings']['target_bundles']));
  }

}
