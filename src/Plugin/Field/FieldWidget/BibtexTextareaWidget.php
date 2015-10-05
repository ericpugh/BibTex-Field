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

}
