<?php

/**
 * @file
 * Contains \Drupal\bibtex_field\Plugin\Field\FieldWidget\BibtexTextareaWidget.
 */

namespace Drupal\bibtex_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextareaWidget;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Plugin implementation of the 'bibtex_textarea_widget' widget.
 *
 * @FieldWidget(
 *   id = "bibtex_textarea_widget",
 *   label = @Translation("BibTeX textarea"),
 *   field_types = {
 *     "bibtex"
 *   }
 * )
 */
class BibtexTextareaWidget extends StringTextareaWidget {

  /**
   * {@inheritdoc}
   */
//  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
//    $main_widget = parent::formElement($items, $delta, $element, $form, $form_state);
//
//    $element = $main_widget['bibtex'];
//    $element['#type'] = 'textarea';
//    $element['#format'] = array();
//    $element['#default_value'] = isset($items[$delta]->bibtex) ? $items[$delta]->bibtex : NULL;
//    $element['#base_type'] = $main_widget['bibtex']['#type'];
//    return $element;
//  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['value'] = array(
      '#title' => t('Bibtex'),
      '#type' => 'textarea',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#format' => array(),
    );
    $element['format']['#access'] = false;
    return $element;
  }
  /**
   * {@inheritdoc}
   */
//  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
//    if ($violation->arrayPropertyPath == array('format') && isset($element['format']['#access']) && !$element['format']['#access']) {
//      // Ignore validation errors for formats if formats may not be changed,
//      // i.e. when existing formats become invalid. See filter_process_format().
//      return FALSE;
//    }
//    return $element;
//  }

}
