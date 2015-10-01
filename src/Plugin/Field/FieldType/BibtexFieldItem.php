<?php

/**
 * @file
 * Contains \Drupal\bibtex_field\Plugin\Field\FieldType\BibtexFieldItem.
 */

namespace Drupal\bibtex_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\text\Plugin\Field\FieldType\TextItemBase;

/**
 * Plugin implementation of the 'bibtex' field type.
 *
 * @FieldType(
 *   id = "bibtex",
 *   label = @Translation("BibTex (plain)"),
 *   description = @Translation("This field stores a long text BibTex format."),
 *   category = @Translation("Text"),
 *   default_widget = "bibtex_textarea_widget",
 *   default_formatter = "bibtex_default_formatter"
 * )
 */
class BibtexFieldItem extends TextItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'text',
          'size' => 'big',
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  static $propertyDefinitions;
  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions() {
    if (!isset(static::$propertyDefinitions)) {
      static::$propertyDefinitions['value'] = array(
        'type' => 'text',
        'label' => t('BibTex (plain)'),
      );
    }
    return static::$propertyDefinitions;
  }


}
