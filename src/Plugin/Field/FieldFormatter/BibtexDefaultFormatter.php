<?php

/**
 * @file
 * Contains \Drupal\bibtex_field\Plugin\Field\FieldFormatter\BibtexDefaultFormatter.
 */

namespace Drupal\bibtex_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\bibtex_field\BibtexParser;

/**
 * Plugin implementation of the 'bibtex_default_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "bibtex_default_formatter",
 *   label = @Translation("Default BibTeX"),
 *   field_types = {
 *     "bibtex",
 *   },
 *   quickedit = {
 *     "editor" = "plain_text"
 *   }
 * )
 */
class BibtexDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
    $elements = array();

    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    foreach ($items as $delta => $item) {
      //Convert value to HTML
      $markup = '';
      $bib = new BibtexParser(NULL, $item->value);
      if($bib->count > 0){
        $markup = '';
        $debug = '<pre>' . print_r($bib->items, true) . '</pre>';
      }
      $elements[$delta] = array(
        '#type' => 'markup',
        '#markup' => $markup . '<br />' . $debug,
        //'#format' => $item->format,
        //'#theme' => 'bibtex_default_formatter',
      );
    }

    return $elements;
  }

}
