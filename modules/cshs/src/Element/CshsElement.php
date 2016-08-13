<?php
/**
 * @file
 * Definition of "cshs" element.
 */

namespace Drupal\cshs\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Select;

/**
 * Provides a CSHS element.
 *
 * @FormElement("cshs")
 */
class CshsElement extends Select {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = parent::getInfo();

    $info['#label'] = '';
    $info['#labels'] = [];
    $info['#parent'] = 0;
    $info['#force_deepest'] = FALSE;
    $info['#none_value'] = CSHS_DEFAULT_NONE_VALUE;
    $info['#none_label'] = CSHS_DEFAULT_NONE_LABEL;
    // Standard properties.
    $info['#theme'] = 'cshs_select';
    $info['#process'][] = [static::class, 'processElement'];
    $info['#element_validate'][] = [static::class, 'validateElement'];

    return $info;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    return FALSE !== $input && NULL !== $input ? $input : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function processElement(array $element) {
    $element['#attached']['library'][] = 'cshs/cshs.base';
    $element['#attached']['drupalSettings']['cshs'][$element['#id']] = [
      'labels' => $element['#labels'],
      'noneLabel' => $element['#none_label'],
      'noneValue' => $element['#none_value'],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function validateElement(array &$element, FormStateInterface $form_state, array &$complete_form) {
    // See if we are on the field settings form.
    if ('field_ui_field_edit_form' !== $complete_form['#form_id']) {
      // The value not selected.
      if ($element['#value'] == $element['#none_value']) {
        $form_state->setValueForElement($element, NULL);

        // Set an error if user doesn't select anything and field is required.
        if ($element['#required']) {
          $form_state->setError($element, t('@label field is required.', [
            '@label' => $element['#label'],
          ]));
        }
      }
      // Do we want to force the user to select terms from the deepest level?
      elseif ($element['#force_deepest']) {
        /* @var \Drupal\taxonomy\TermStorage $storage */
        $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
        $term = $storage->load($element['#value']);

        // Set an error if term has children.
        if (!empty($storage->loadChildren($term->id(), $term->getVocabularyId()))) {
          $form_state->setError($element, t('You need to select a term from the deepest level in @label field.', [
            '@label' => $element['#label'],
          ]));
        }
      }
    }
  }

}
