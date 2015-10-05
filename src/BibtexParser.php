<?php
/**
 * @file
 * Contains \Drupal\bibtex_field\BibtexParser.
 */

namespace Drupal\bibtex_field;

class BibtexParser
{
  var $count;
  var $items;
  var $types;
  var $filename;
  var $inputdata;

  /**
   * BibTeX_Parser( $file, $data )
   *
   * Constructor
   * @param String $file if filename is used
   * @param String $data if input is a string
   */
  function __construct( $file = null, $data = null ) {
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

    if( $file ) {
      $this->filename = $file;
    } elseif( $data ) {
      $this->inputdata = $data;
    }

    $this->parse();

  }

  /**
   * parse()
   *
   * Main method that parses the BibTeX data.
   * @return arary() of parsed data
   */
  public function parse() {
    $value = array();
    $var = array();
    $this->count = -1;
    $lineindex = 0;
    $fieldcount = -1;

    if( $this->filename ) {
      $lines = file($this->filename);
    } else {
      $lines = preg_split( '/\n/', $this->inputdata );
    }

    if (!$lines) {
      return;
    }

    foreach($lines as $line) {
      $lineindex++;
      $this->items['lineend'][$this->count] = $lineindex;
      $line = trim($line);
      $raw_line = $line + '\n';
      $line=str_replace("'","`",$line);
      $seg=str_replace("\"","`",$line);
      $ps=strpos($seg,'=');
      $segtest=strtolower($seg);

      // some funny comment string
      if (strpos($segtest,'@string')!==false) {
        continue;
      }

      // pybliographer comments
      if (strpos($segtest,'@comment')!==false) {
        continue;
      }

      // normal TeX style comment
      if (strpos($seg,'%%')!==false) {
        continue;
      }

      /* ok when there is nothing to see, skip it! */
      if (!strlen($seg)) {
        continue;
      }

      if ("@" == $seg[0]) {
        $this->count++;
        $this->items['raw'][$this->count] = $line . "\r\n";

        $ps=strpos($seg,'@');
        $pe=strpos($seg,'{');
        $this->types[$this->count]=trim(substr($seg, 1,$pe-1));
        $fieldcount=-1;
        $this->items['linebegin'][$this->count] = $lineindex;
      } elseif ($ps!==false ) {
        // #of item increase
        // one field begins
        $this->items['raw'][$this->count] .= $line . "\r\n";
        $ps=strpos($seg,'=');
        $fieldcount++;
        $var[$fieldcount]=strtolower(trim(substr($seg,0,$ps)));

        if ($var[$fieldcount]=='pages') {
          $ps=strpos($seg,'=');
          $pm=strpos($seg,'--');
          $pe=strpos($seg,'},');
          $pagefrom[$this->count] = substr($seg,$ps,$pm-$ps);
          $pageto[$this->count]=substr($seg,$pm,$pe-$pm);
          $bp=str_replace('=','',$pagefrom[$this->count]); $bp=str_replace('{','',$bp);$bp=str_replace('}','',$bp);$bp=trim(str_replace('-','',$bp));
          $ep=str_replace('=','',$pageto[$this->count]); $bp=str_replace('{','',$bp);$bp=str_replace('}','',$bp);;$ep=trim(str_replace('-','',$ep));
        }
        $pe=strpos($seg,'},');

        if ($pe===false) {
          $value[$fieldcount]=strstr($seg,'=');
        } else {
          $value[$fieldcount]=substr($seg,$ps,$pe);
        }
      } else {
        $this->items['raw'][$this->count] .= $line . "\r\n";
        $pe=strpos($seg,'},');

        if ($fieldcount > -1) {
          if ($pe===false) {
            $value[$fieldcount].=' '.strstr($seg,' ');
          } else {
            $value[$fieldcount] .=' '.substr($seg,$ps,$pe);
          }
        }
      }

      if ($fieldcount > -1) {
        $v = $value[$fieldcount];
        $v=str_replace('=','',$v);
        $v=str_replace('{','',$v);
        $v=str_replace('}','',$v);
        $v=str_replace(',',' ',$v);
        $v=str_replace('\'',' ',$v);
        $v=str_replace('\"',' ',$v);
        // test!
        $v=str_replace('`',' ',$v);
        $v=trim($v);
        $this->items["$var[$fieldcount]"][$this->count]="$v";
      }

    }
    return ($this->count > 0) ? true : false;
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
        if((int) $key == $key && $key >= 0) {
          //the numeric index of the entry has to be a positive integer
          if($items['author'][$key]){
            $renderable[$key]['author'] = $items['author'][$key];
          }
          if($items['year'][$key]){
            $renderable[$key]['year'] = $items['year'][$key];
          }
          if($items['title'][$key]){
            $renderable[$key]['title'] = $items['title'][$key];
          }
          if($items['booktitle'][$key]) {
            $renderable[$key]['booktitle'] = $items['booktitle'][$key];
          }
          if($items['group'][$key]){
            $renderable[$key]['group'] = $items['group'][$key];
          }
          if($items['publisher'][$key]){
            $renderable[$key]['publisher'] = $items['publisher'][$key];
          }
          if($items['journal'][$key]){
            $renderable[$key]['journal'] = $items['journal'][$key];
          }
          if($items['volume'][$key]){
            $renderable[$key]['volume'] = $items['volume'][$key];
          }
          if($items['chapter'][$key]){
            $renderable[$key]['chapter'] = $items['chapter'][$key];
          }
          if($items['page-start'][$key]){
            $renderable[$key]['page-start'] = $items['page-start'][$key];
          }
          if($items['page-end'][$key]){
            $renderable[$key]['page-end'] = $items['page-end'][$key];
          }
          if($items['pages'][$key]){
            $renderable[$key]['pages'] = $items['pages'][$key];
          }
          if($items['address'][$key]){
            $renderable[$key]['address'] = $items['address'][$key];
          }
          if($items['folder'][$key]){
            $renderable[$key]['folder'] = $items['folder'][$key];
          }
          if($items['type'][$key]){
            $renderable[$key]['type'] = $items['type'][$key];
          }
          if($items['linebegin'][$key]){
            $renderable[$key]['linebegin'] = $items['linebegin'][$key];
          }
          if($items['lineend'][$key]){
            $renderable[$key]['lineend'] = $items['lineend'][$key];
          }
          if($items['note'][$key]){
            $renderable[$key]['note'] = $items['note'][$key];
          }
          if($items['abstract'][$key]){
            $renderable[$key]['abstract'] = $items['abstract'][$key];
          }
          if($items['url'][$key]){
            $renderable[$key]['url'] = $items['url'][$key];
          }

        }
      }
      return !empty($renderable) ? $renderable : false;
    }
  }


}