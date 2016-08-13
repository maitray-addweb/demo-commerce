<?php
/**
 * @file
 * Definition of CshsTaxonomyIndexTid.
 */

namespace Drupal\cshs\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Plugin\views\filter\TaxonomyIndexTid;
use Drupal\cshs\CshsOptionsFromHelper;

/**
 * Filter by term ID.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("cshs_taxonomy_index_tid")
 */
class CshsTaxonomyIndexTid extends TaxonomyIndexTid {

  use CshsOptionsFromHelper;

  const ID = 'cshs';

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    if (in_array($this->value, ['All', CSHS_DEFAULT_NONE_VALUE])) {
      $this->value = [];
    }

    return parent::adminSummary();
  }

  /**
   * {@inheritdoc}
   */
  public function defineOptions() {
    $options = parent::defineOptions();

    foreach (static::defaultSettings() + ['type' => static::ID] as $option => $value) {
      $options[$option] = ['default' => $value];
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildExtraOptionsForm($form, $form_state);

    $form['type']['#options'] += [
      static::ID => $this->t('Client-side hierarchical select'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildExposeForm(&$form, FormStateInterface $form_state) {
    parent::buildExposeForm($form, $form_state);

    if (static::ID === $this->options['type']) {
      // Disable the "multiple" option in the exposed form settings.
      $form['expose']['multiple']['#access'] = FALSE;
      $form += $this->settingsForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);

    if (empty($this->getVocabulary()) && $this->options['limit']) {
      $form['markup'] = [
        '#markup' => '<div class="form-item">' . $this->t('An invalid vocabulary is selected. Please change it in the options.') . '</div>',
      ];

      return;
    }

    if (static::ID === $this->options['type'] && $this->options['exposed']) {
      $identifier = $this->options['expose']['identifier'];
      // Fix default value.
      $user_input = $form_state->getUserInput();

      // Set the element type to CSHS.
      $form['value'] = array_merge($form['value'], $this->formElement(), ['#multiple' => FALSE]);

      if (isset($user_input[$identifier]) && 'All' === $user_input[$identifier]) {
        $user_input[$identifier] = [CSHS_DEFAULT_NONE_VALUE];
        $form_state->setUserInput($user_input);
      }
    }

    if (empty($form_state->getValue('exposed'))) {
      // Retain the helper option.
      $this->helper->buildOptionsForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings() {
    return $this->options;
  }

  /**
   * {@inheritdoc}
   */
  public function getSetting($key) {
    return isset($this->options[$key]) ? $this->options[$key] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getVocabularyStorage() {
    return $this->vocabularyStorage;
  }

  /**
   * {@inheritdoc}
   */
  public function getVocabulary() {
    return $this->vocabularyStorage->load($this->options['vid']);
  }

}
