<?php
/**
 * @file
 * CSHS helper.
 */

namespace Drupal\cshs;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Component\Utility\Tags;
use Drupal\taxonomy\VocabularyStorage;
use Drupal\taxonomy\TermStorage;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Entity\Term;

/**
 * Defines a class for getting options for a cshs form element from vocabulary.
 */
trait CshsOptionsFromHelper {

  /**
   * Defines the default settings for this plugin.
   *
   * @return array
   *   A list of default settings, keyed by the setting name.
   */
  public static function defaultSettings() {
    return [
      'parent' => 0,
      'level_labels' => '',
      'force_deepest' => FALSE,
    ];
  }

  /**
   * Returns the array of settings, including defaults for missing settings.
   *
   * @return array
   *   The array of settings.
   */
  abstract public function getSettings();

  /**
   * Returns the value of a setting, or its default value if absent.
   *
   * @param string $key
   *   The setting name.
   *
   * @return mixed
   *   The setting value.
   */
  abstract public function getSetting($key);

  /**
   * Returns the taxonomy vocabulary to work with.
   *
   * @return Vocabulary
   *   Taxonomy vocabulary object.
   */
  abstract public function getVocabulary();

  /**
   * Returns a short summary for the settings.
   *
   * @return array
   *   A short summary of the settings.
   */
  public function settingsSummary() {
    $settings = $this->getSettings();
    $summary = [];

    $summary[] = t('Force deepest: @force_deepest', [
      '@force_deepest' => empty($settings['force_deepest']) ? t('Yes') : t('No'),
    ]);

    $summary[] = t('Parent: @parent', [
      '@parent' => empty($settings['parent']) ? t('None') : $this->getTermStorage()->load($settings['parent'])->label(),
    ]);

    $summary[] = t('Level labels: @level_labels', [
      '@level_labels' => empty($settings['level_labels']) ? t('None') : $settings['level_labels'],
    ]);

    return $summary;
  }

  /**
   * Returns a form to configure settings.
   *
   * @param array $form
   *   The form where the settings form is being included in.
   * @param FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form definition for the settings.
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $vocabulary = $this->getVocabulary();
    // Add a none option.
    $options = ['---'];

    // Build options for parent select field.
    foreach ($this->getTermStorage()->loadTree($vocabulary->id()) as $term) {
      $options[$term->tid] = str_repeat('- ', $term->depth) . $term->name;
    }

    $element['force_deepest'] = [
      '#type' => 'checkbox',
      '#title' => t('Force selection of deepest level'),
      '#description' => t('If checked the user will be forced to select terms from the deepest level.'),
      '#default_value' => $this->getSetting('force_deepest'),
    ];

    $element['parent'] = [
      '#type' => 'select',
      '#title' => t('Parent'),
      '#description' => t('Select a parent term to use only a subtree of a vocabulary for this field.'),
      '#options' => $options,
      '#default_value' => $this->getSetting('parent'),
    ];

    $element['level_labels'] = [
      '#type' => 'textfield',
      '#title' => t('Labels per hierarchy-level'),
      '#description' => t('Enter labels for each hierarchy-level separated by comma.'),
      '#default_value' => $this->getSetting('level_labels'),
    ];

    $form_state->set('vocabulary', $vocabulary);

    return $element;
  }

  /**
   * Returns the form for a single widget.
   *
   * @return array
   *   The form elements for a single widget.
   */
  public function formElement() {
    $vocabulary = $this->getVocabulary();
    $settings = $this->getSettings();

    return [
      '#type' => 'cshs',
      '#labels' => Tags::explode($settings['level_labels']),
      '#parent' => $settings['parent'],
      '#options' => $this->getOptions($vocabulary->id(), $settings['parent']),
      '#vocabulary' => $vocabulary,
      '#force_deepest' => $settings['force_deepest'],
      '#default_value' => [CSHS_DEFAULT_NONE_VALUE],
    ];
  }

  /**
   * Collects the options.
   *
   * @param string $vocabulary_id
   *   Name of taxonomy vocabulary.
   * @param int $parent
   *   ID of a parent term.
   *
   * @return array[]
   *   Widget options.
   */
  protected function getOptions($vocabulary_id, $parent = 0) {
    $options = [
      CSHS_DEFAULT_NONE_VALUE => [
        'name' => CSHS_DEFAULT_NONE_LABEL,
        'parent_tid' => 0,
      ],
    ];

    /** @var Term $term */
    foreach ($this->getTermStorage()->loadTree($vocabulary_id, $parent, NULL, TRUE) as $term) {
      $parents = array_values($term->parents);

      $options[$term->id()] = [
        'name' => str_repeat('- ', $term->depth) . $term->label(),
        'parent_tid' => (int) reset($parents),
      ];
    }

    return $options;
  }

  /**
   * Get storage object for terms.
   *
   * @return TermStorage
   *   Taxonomy term storage.
   */
  protected function getTermStorage() {
    return self::getStorage('taxonomy_term');
  }

  /**
   * Get storage object for vocabularies.
   *
   * @return VocabularyStorage
   *   Taxonomy vocabulary storage.
   */
  protected function getVocabularyStorage() {
    return self::getStorage('taxonomy_vocabulary');
  }

  /**
   * Get storage object for an entity.
   *
   * @param string $entity_type
   *   Name of an entity type.
   *
   * @return EntityStorageInterface
   *   Storage object.
   */
  protected static function getStorage($entity_type) {
    return \Drupal::entityTypeManager()->getStorage($entity_type);
  }

}
