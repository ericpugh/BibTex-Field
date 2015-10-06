<?php

/**
 * @file
 * Contains \Drupal\bibtex_field\Plugin\Field\FieldFormatter\BibtexDefaultFormatter.
 */

namespace Drupal\bibtex_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\Html;
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
      $bibliography = new BibtexParser(NULL, $item->value);
      if($bibliography->count > 0) {
        //get a theme-able array of items
        $bibtex = $bibliography->getRenderable();
        //$debug = '<pre>' . print_r($bib->items, true) . '</pre>';

        $elements[$delta] = array(
          '#theme' => 'bibtex_default_formatter',
          '#bibtex' => $bibtex,
          //'#cache' => Cache::PERMANENT,
        );
      }
    }

    return $elements;
  }

}
