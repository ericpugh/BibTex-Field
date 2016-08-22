<?php
/**
 * @file
 * Contains \Drupal\bibtex_field\BibtexParser.
 */

namespace Drupal\bibtex_field;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Cache\CacheBackendInterface;

class BibtexParser {

  /**
   * Logger Factory Service Object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;
  /**
   * Cache Factory Service Object.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Constant Cache Id.
   */
  const CACHE_ID = 'bibtex_field:parsed_data';


  /**
   * Number of bibtex items.
   *
   * @var integer
   */
  var $count;

  /**
   * Parsed array of bibtex items.
   *
   * @var array
   */
  var $items;

  /**
   * Item types in a given bibtex string.
   *
   * @var array
   */
  var $types;

  /**
   * Lines of bibtex item strings.
   *
   * @var array
   */
  var $data;

  /**
   * Construct the BibtexParser service
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The Cache Backend
   */
  function __construct(LoggerChannelFactoryInterface $logger_factory, CacheBackendInterface $cache_backend) {
      $this->loggerFactory = $logger_factory;
      $this->cache = $cache_backend;
      // Set skeleton item
      $this->items = array(
        'note' => array(),
        'abstract' => array(),
        'year' => array(),
        'group' => array(),
        'publisher' => array(),
        'page-start' => array(),
        'page-end' => array(),
        'pages' => array(),
        'address' => array(),
        'url' => array(),
        'volume' => array(),
        'chapter' => array(),
        'journal' => array(),
        'author' => array(),
        'raw' => array(),
        'title' => array(),
        'booktitle' => array(),
        'folder' => array(),
        'type' => array(),
        'linebegin' => array(),
        'lineend' => array()
      );
    
  }

  public function parseData($data) {
    // Set each line as a separate array element to be parsed.
    $this->data = preg_split('/\n/', $data);
    return $this->parse();
  }

  /**
   * parse()
   *
   * Main method that parses the BibTeX data.
   * @return array() of parsed data
   */
  protected function parse() {
    //if ($cache = $this->cache->get($this::CACHE_ID)) {
      // Retrieve data from cache.
      //return $cache->data;
    //}
    //else {
      // Parse the bibtex array stored in data property.
      $value = array();
      $var = array();
      $this->count = -1;
      $line_index = 0;
      $field_count = -1;

      if (!$this->data) {
        return array();
      }

      foreach ($this->data as $line) {
        $line_index++;
        $this->items['lineend'][$this->count] = $line_index;
        $line = trim($line);
        $raw_line = $line . '\n';
        $line = str_replace("'", "`", $line);
        $seg = str_replace("\"", "`", $line);
        $ps = strpos($seg, '=');
        $segtest = strtolower($seg);

        // some funny comment string
        if (strpos($segtest, '@string') !== FALSE) {
          continue;
        }

        // pybliographer comments
        if (strpos($segtest, '@comment') !== FALSE) {
          continue;
        }

        // normal TeX style comment
        if (strpos($seg, '%%') !== FALSE) {
          continue;
        }

        /* ok when there is nothing to see, skip it! */
        if (!strlen($seg)) {
          continue;
        }

        if ("@" == $seg[0]) {
          $this->count++;
          $this->items['raw'][$this->count] = $line . "\r\n";

          $ps = strpos($seg, '@');
          $pe = strpos($seg, '{');
          $this->types[$this->count] = trim(substr($seg, 1, $pe - 1));
          $field_count = -1;
          $this->items['linebegin'][$this->count] = $line_index;
        }
        elseif ($ps !== FALSE) {
          // #of item increase
          // one field begins
          $this->items['raw'][$this->count] .= $line . "\r\n";
          $ps = strpos($seg, '=');
          $field_count++;
          $var[$field_count] = strtolower(trim(substr($seg, 0, $ps)));

          if ($var[$field_count] == 'pages') {
            $ps = strpos($seg, '=');
            $pm = strpos($seg, '--');
            $pe = strpos($seg, '},');
            $page_from[$this->count] = substr($seg, $ps, $pm - $ps);
            $page_to[$this->count] = substr($seg, $pm, $pe - $pm);
            $bp = str_replace('=', '', $page_from[$this->count]);
            $bp = str_replace('{', '', $bp);
            $bp = str_replace('}', '', $bp);
            $bp = trim(str_replace('-', '', $bp));
            $ep = str_replace('=', '', $page_to[$this->count]);
            $bp = str_replace('{', '', $bp);
            $bp = str_replace('}', '', $bp);
            $ep = trim(str_replace('-', '', $ep));
          }
          $pe = strpos($seg, '},');

          if ($pe === FALSE) {
            $value[$field_count] = strstr($seg, '=');
          }
          else {
            $value[$field_count] = substr($seg, $ps, $pe);
          }
        }
        else {
          $this->items['raw'][$this->count] .= $line . "\r\n";
          $pe = strpos($seg, '},');

          if ($field_count > -1) {
            if ($pe === FALSE) {
              $value[$field_count] .= ' ' . strstr($seg, ' ');
            }
            else {
              $value[$field_count] .= ' ' . substr($seg, $ps, $pe);
            }
          }
        }

        if ($field_count > -1) {
          $v = $value[$field_count];
          $v = str_replace('=', '', $v);
          $v = str_replace('{', '', $v);
          $v = str_replace('}', '', $v);
          $v = str_replace(',', ' ', $v);
          $v = str_replace('\'', ' ', $v);
          $v = str_replace('\"', ' ', $v);
          // test!
          $v = str_replace('`', ' ', $v);
          $v = trim($v);
          $this->items["$var[$field_count]"][$this->count] = "$v";
        }

      }

      if (count($this->items) > 0) {
        // Set the cache expiration.
        $expireTime = new \DateTime('+2 hours');
        $cache_expire = $expireTime->getTimestamp();
        $this->cache->set($this::CACHE_ID, $this->items, $cache_expire);
        return $this->items;
      }
      else {
        return array();
      }
    //}
  }

  /**
   * getRenderable()
   *
   * Convert parsed items to renderable array of items.
   * @return array
   */
  public function getRenderable() {
    if($this->items['raw']) {

      $items = $this->items;
      $entries = $this->items['raw'];
      $renderable = array();

      //raw contains the full entries with indexes matching the index of entry elements
      foreach ($entries as $key => $entry) {
        if((int) $key == $key && (int) $key >= 0) {
          //the numeric index of the entry has to be a positive integer
          if (isset($items['author'][$key])) {
            $renderable[$key]['author'] = $items['author'][$key];
          }
          if (isset($items['year'][$key])) {
            $renderable[$key]['year'] = $items['year'][$key];
          }
          if (isset($items['title'][$key])) {
            $renderable[$key]['title'] = $items['title'][$key];
          }
          if (isset($items['booktitle'][$key])) {
            $renderable[$key]['booktitle'] = $items['booktitle'][$key];
          }
          if (isset($items['group'][$key])) {
            $renderable[$key]['group'] = $items['group'][$key];
          }
          if (isset($items['publisher'][$key])) {
            $renderable[$key]['publisher'] = $items['publisher'][$key];
          }
          if (isset($items['journal'][$key])) {
            $renderable[$key]['journal'] = $items['journal'][$key];
          }
          if (isset($items['volume'][$key])) {
            $renderable[$key]['volume'] = $items['volume'][$key];
          }
          if (isset($items['chapter'][$key])) {
            $renderable[$key]['chapter'] = $items['chapter'][$key];
          }
          if (isset($items['page-start'][$key])) {
            $renderable[$key]['page-start'] = $items['page-start'][$key];
          }
          if (isset($items['page-end'][$key])) {
            $renderable[$key]['page-end'] = $items['page-end'][$key];
          }
          if (isset($items['pages'][$key])) {
            $renderable[$key]['pages'] = $items['pages'][$key];
          }
          if (isset($items['address'][$key])) {
            $renderable[$key]['address'] = $items['address'][$key];
          }
          if (isset($items['folder'][$key])) {
            $renderable[$key]['folder'] = $items['folder'][$key];
          }
          if (isset($items['type'][$key])) {
            $renderable[$key]['type'] = $items['type'][$key];
          }
          if (isset($items['linebegin'][$key])) {
            $renderable[$key]['linebegin'] = $items['linebegin'][$key];
          }
          if($items['lineend'][$key]){
            $renderable[$key]['lineend'] = $items['lineend'][$key];
          }
          if (isset($items['note'][$key])) {
            $renderable[$key]['note'] = $items['note'][$key];
          }
          if (isset($items['abstract'][$key])) {
            $renderable[$key]['abstract'] = $items['abstract'][$key];
          }
          if (isset($items['url'][$key])) {
            $renderable[$key]['url'] = $items['url'][$key];
          }

        }
      }
      return $renderable;
    }
    return array();
  }


}