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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

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
class BibtexDefaultFormatter extends FormatterBase implements ContainerFactoryPluginInterface{

  /**
   * The BibtexParser service.
   *
   * @var \Drupal\bibtex_field\BibtexParser
   */
  protected $bibtexParser;

  /**
   * Constructs a new ContentDirectController.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\bibtex_field\BibtexParser $bibtex_parser
   *   BibtexParser service.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    BibtexParser $bibtex_parser) {
      parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
      $this->bibtexParser = $bibtex_parser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('bibtex_field.bibtex_parser')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = array();
    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    foreach ($items as $delta => $item) {
      $parsed_bibtex = $this->bibtexParser->parseData($item->value);
      if(!empty($parsed_bibtex)) {
        //get a themeable array of items
        $renderable_bibtex = $this->bibtexParser->getRenderable();
        $elements[$delta] = array(
          '#theme' => 'bibtex_default_formatter',
          '#bibtex' => $renderable_bibtex,
          //'#cache' => Cache::PERMANENT,
        );
      }
    }

    return $elements;
  }

}
