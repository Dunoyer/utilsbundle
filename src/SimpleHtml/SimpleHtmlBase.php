<?php

namespace FOPG\Component\UtilsBundle\SimpleHtml;

class SimpleHtmlBase
{
  const TAG_REGEX = "/^(a|abbr|address|area|article|aside|audio|b|base|blockquote|body|br|button|canvas|caption|cite|code|col|colgroup|data|datalist|dd|detail|dialog|div|dl|dt|em|embed|fieldset|figcaption|figure|footer|form|font|frame|frameset|h1|h2|h3|h4|h5|h6|head|header|hgroup|hr|html|i|iframe|img|image|input|label|legend|li|map|mark|menu|meta|nav|noscript|object|ol|optgroup|option|p|param|pre|script|section|select|small|source|span|strong|style|sub|sup|table|tbody|td|textarea|tfoot|th|thead|title|tr|track|u|ul|var|video')$/";
  const TAGS_REGEX = "/^(a|abbr|address|area|article|aside|audio|b|base|blockquote|body|br|button|canvas|caption|cite|code|col|colgroup|data|datalist|dd|detail|dialog|div|dl|dt|em|embed|fieldset|figcaption|figure|footer|form|font|frame|frameset|h1|h2|h3|h4|h5|h6|head|header|hgroup|hr|html|i|iframe|img|image|input|label|legend|li|map|mark|menu|meta|nav|noscript|object|ol|optgroup|option|p|param|pre|script|section|select|small|source|span|strong|style|sub|sup|table|tbody|td|textarea|tfoot|th|thead|title|tr|track|u|ul|var|video')e?s$/";
  const ATTRIBUTE_REGEX = "/^(href|src|id|class|name|text|height|width|content|value|title|alt|data-[\w\-]+)$/";
  const ATTRIBUTES_REGEX = "/^(href|src|id|class|name|text|height|width|content|value|title|alt|data-[\w\-]+)e?s$/";

  /**
   * @var FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlBase
   */
  private ?SimpleHtmlBase $_doc = null;

  /**
   * @var FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlDom
   */
  private $_dom = null;

  /**
   * @var ?\DOMElement
   */
  private ?\DOMElement $_node = null;

  /**
   * @var bool
   */
  protected bool $is_text = false;

  /**
   * @param FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlBase $doc
   * @return FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlBase
   */
  public function setDoc(SimpleHtmlBase $doc)
  {
      $this->_doc = $doc;

      return $this;
  }

  /**
   * @return FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlBase
   */
  public function getDoc(): ?SimpleHtmlDom
  {
      return $this->_doc;
  }

  /**
   * @param \DOMDocument $dom
   * @return FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlBase
   */
  public function setDom(\DOMDocument $dom)
  {
      $this->_dom = $dom;

      return $this;
  }

  /**
   * @return \DOMDocument
   */
  public function getDom(): ?\DOMDocument
  {
      return $this->_dom;
  }

  /**
   * @param \DomElement $node
   * @return FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlBase
   */
  public function setNode(\DOMElement $node)
  {
      $this->_node = $node;

      return $this;
  }

  /**
   * @return \DOMElement
   */
  public function getNode()
  {
      return $this->_node;
  }

  /**
   * @param bool $isText
   * @return FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlBase
   */
  public function setIsText($isText)
  {
      $this->is_text = $isText;

      return $this;
  }

  /**
   * @return bool
   */
  public function getIsText()
  {
      return $this->is_text;
  }

  /**
   * @param ?string $selector
   * @return ?string
   */
  public function getHtml(?string $selector=null): ?string
  {
      $html = null;
      if(null !== $selector) {
        $current = $this->findOne($selector);
        if(null !== $current) {
          $html = $this->getDoc()->getDom()->saveHTML($current->getNode());
          $current->destruct();
          unset($current);
          return $html;
        }
      }
      else
        $html = $this->getDoc()->getDom()->saveHTML($this->getNode());
      return $html;
  }

  public function __toString()
  {
      return $this->getHtml();
  }

  public static function cleanupXpatWithIdAndClass(string $xpath): string
  {
    $tmp = preg_replace("/\[(\d+)\]/","#####$1#####",$xpath);
    $tmp = preg_replace("/\[[^\]]+\]/","",$tmp);
    return preg_replace("/#####(\d+)#####/","[$1]",$tmp);
  }

  /**
   * @param string $selector
   * @param bool $withClasses
   * @param float $minRate
   * @param int $minSize
   * @return array
   */
  public function getTextRate(string $selector, bool $withClasses=true, float $minRate=.5, int $minSize=20, string $excludeSelectors=''): array
  {
    $output = [];
    $nodeList = $this->findAll($selector);
    foreach($nodeList as $node) {
      $html = $node->html();
      $htmlRestricted = preg_replace("/<(\w+)([^>]*)>/i","<$1>", $html);
      $lnText = (float)strlen($node->text());
      $lnHtml = (float)strlen($htmlRestricted);
      $rate   = $lnText/$lnHtml;
      $item   = explode('/',$node->getPath());
      $length = count($item)-2;
      $max    = count($item);

      for($i=1; $i<$length;$i++) {
        $attrs = $node->attributes;
        $id = !empty($attrs['id']) ? '[id="'.trim($attrs['id']).'"]' : null;
        $class = !empty($attrs['class']) && (true === $withClasses) ? '[class="'.trim($attrs['class']).'"]' : null;
        $item[$max-$i]=$item[$max-$i].$id.$class;
        $node = $node->parent;
      }
      $tmp = implode("/", $item);
      $output[$tmp]=['rate' => $rate, 'size' => (int)$lnText, 'length' => count($item)];
      unset($node);
    }
    unset($nodeList);
    $newOutput = [];
    array_walk($output, function($data, $key)use(&$newOutput) {
      $newOutput[$key]=$data;
      $parent = preg_replace("/([\/][^\/]+)$/i", "", $key);
      while($parent) {
        $oldRate = !empty($newOutput[$parent]) ? $newOutput[$parent]['rate'] : 0;
        $oldSize = !empty($newOutput[$parent]) ? $newOutput[$parent]['size'] : 0;
        $newRate = $data['rate'];
        $newSize = $data['size'];
        if($oldSize+$newSize > 0) {
          $newOutput[$parent]['rate']  = (($oldRate*$oldSize)+($newRate*$newSize))/($oldSize+$newSize);
          $newOutput[$parent]['size']  = $oldSize+$newSize;
          $newOutput[$parent]['length']= count(explode('/',$parent));
        }

        $parent = preg_replace("/([\/][^\/]+)$/i", "", $parent);
      }
    });

    $tabExcludeSelectors = explode(',', preg_replace("/[ ]+/","",$excludeSelectors));
    foreach($newOutput as $key => $data) {
      $rate = $data['rate'];
      $size = $data['size'];
      preg_match("/\/(?<tag>[^\/\[]+)([^\/]*)$/", $key, $matches);
      $tag  = $matches['tag'];
      if($rate<$minRate || $size<$minSize || in_array($tag, $tabExcludeSelectors)) {
        unset($newOutput[$key]);
      }
    }

    // kill upper element if same length
    array_walk($newOutput, function($data, $key)use(&$newOutput) {
      $curRate = $data['rate'];
      $curSize = $data['size'];
      $curLgth = $data['length'];
      foreach($newOutput as $tKey => $cData) {
        $tRate = $cData['rate'];
        $tSize = $cData['size'];
        $tLgth = $cData['length'];
        if($tSize === $curSize && $tLgth < $curLgth) {
          unset($newOutput[$tKey]);
        }
      }
    });
    return $newOutput;
  }

  public function remove()
  {
    $this->getNode()->parentNode->removeChild($this->getNode());
    return $this;
  }

  public function str(){ return new SimpleHtmlString($this->text); }
  public function match($re){
    $str = new SimpleHtmlString($this->text);
    return $str->match($re);
  }
  public function scan($re){
    $str = new SimpleHtmlString($this->text);
    return $str->scan($re);
  }
  public function clean($str){ return trim(preg_replace('/\s+/', ' ', $str)); }
  public function trim($str){ return trim($str); }

  public function findAll(string $css): ?SimpleHtmlNodeList
  {
    $xpath =  SimpleHtmlCSS::xpath_for($css);
    $doc = $this->getDoc() ?? null;
    $nxpath = $doc ? $doc->getXpath() : null;
    return $nxpath ? new SimpleHtmlNodeList($nxpath->query($xpath, $this->getNode()), $this->getDoc()) : null;
  }

  /**
   * Find nodes with xpath expressions
   *
   * @param string $css
   * @param int $index
   * @return ?SimpleHtmlNode
   */
  public function findOne(string $css, int $index=0): ?SimpleHtmlNode {
    $xpath  =  SimpleHtmlCSS::xpath_for($css);
    $doc    = $this->getDoc() ?? null;
    $nxpath = $doc ? $doc->getXpath() : null;
    $node   = null;
    if(null !== $nxpath)
    {
      $nl = $nxpath->query($xpath, $this->getNode());
      if($index < 0)
        $index = $nl->length + $index;
      $node = $nl->item($index);
    }
    return $node ? new SimpleHtmlNode($node, $this->getDoc()) : null;
  }

  /**
   * @param string $id
   * @param int $index
   */
  public function findOneById(string $id, int $index=0): ?SimpleHtmlNode
  {
    return $this->findOne("#$id", $index);
  }

  /**
   * @param string $tag
   * @return ?SimpleHtmlNodeList
   */
  public function findByTagName(string $tag): ?SimpleHtmlNodeList
  {
    return $this->findAll($tag);
  }

  /**
   * @param string $tag
   * @return ?SimpleHtmlNodeList
   */
  public function findOneByTagName(string $tag): ?SimpleHtmlNode
  {
    return $this->findOne($tag, 0);
  }

  /**
   * @param string $id
   * @return SimpleHtmlNodeList
   */
  public function findById(string $id): SimpleHtmlNodeList
  {
    return $this->findAll("#$id");
  }

  public function __call($key, $args){

    $key = strtolower(str_replace('_', '', $key));
    switch($key){
      case 'plaintext': return $this->getText();
      case 'save': return $this->getHtml();
      case 'next': return $this->at('./following-sibling::*[1]|./following-sibling::text()[1]|./following-sibling::comment()[1]');
      case 'index': return $this->search('./preceding-sibling::*')->length + 1;
      // search functions
      case 'at': return $this->findOne($args[0], 0);
      case 'search': return isset($args[1]) ? $this->findOne($args[0], $args[1]) : $this->findAll($args[0]);
      // attributes
      case 'setattribute': return $this->$args[0] = $args[1];
      case 'removeattribute': return $this->$args[0] = null;
      // wrap
      case 'wrap': return $this->replace('<' . $args[0] . '>' . $this . '</' . $args[0] . '>');
      case 'unwrap': return $this->parent->replace($this);
      case 'str': return new SimpleHtmlString($this->text);
      // heirarchy
      case 'nextsibling': return $this->at('+ *');
      case 'prevsibling': return $this->at('./preceding-sibling::*[1]');
      case 'parent': return $this->at('./..');
      case 'childnodes':
        $nl = $this->search('./*');
        return isset($args[0]) ? $nl[$args[0]] : $nl;
      case 'child': // including text/comment nodes
        $nl = $this->search('./*|./text()|./comment()');
        return isset($args[0]) ? $nl[$args[0]] : $nl;
    }

    if(preg_match(self::TAGS_REGEX, $key, $m)) return $this->findAll($m[1]);
    if(preg_match(self::TAG_REGEX, $key, $m)) return $this->findOne($m[1], 0);

    if(preg_match('/(clean|trim|str)(.*)/', $key, $m) && isset($m[2])){
      $arg1 = $m[1];
      $arg2 = $m[2];
      return $this->$arg1($this->$arg2);
    }
	  if (in_array($key,array('dom','node','doc'))) return;
    if(!preg_match(self::ATTRIBUTE_REGEX, $key, $m)) trigger_error('Unknown method or property: ' . $key, E_USER_WARNING);
    if(!$this->getNode() || $this->getIsText()) return null;
    return $this->getNode()->getAttribute($key);
  }

  public function destruct()
  {
	 $this->_doc    = null;
	 $this->_node   = null;
	 $this->_dom    = null;

	 unset($this->_node);
	 unset($this->_dom);
	 unset($this->_doc);
  }
}
