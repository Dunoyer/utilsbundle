<?php

namespace FOPG\Component\UtilsBundle\SimpleHtml;

use FOPG\Component\UtilsBundle\SimpleHtml\Trait\SimpleHtmlNodeTrait;

class SimpleHtmlNode extends SimpleHtmlBase implements \ArrayAccess{

    use SimpleHtmlNodeTrait;

    /**
     * @var ?string
     */
    private ?string $_path = null;

    /**
     * @param string
     * @return \FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlNode
     */
    public function setPath(string $path): self
    {
        $this->_path = $path;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getInnertext(): ?string
    {
      $innerText = ($this->getIsText() || !$this->getChildren()->getLength()) ? $this->getText() : $this->findAll('./text()|./*')->getText();
      return $innerText;
    }

    /**
     * @return ?string
     */
    public function getInnerhtml(): ?string
    {
      $ret = '';
      foreach($this->getNode()->childNodes as $child) $ret .= $this->getDoc()->getDom()->saveHTML($child);
      return $ret;
    }

    public function getFirstChild(): ?SimpleHtmlNode
    {
      /** @var \DOMNodeList *childNodes */
      $childNodes = $this->getNode()->childNodes;
      /** @var int $length */
      $length     = $childNodes->length;
      return ($length > 0) ? new SimpleHtmlNode($childNodes[0], $this->getDoc()) : null;
    }

    public function getLastChild(): ?SimpleHtmlNode
    {
      /** @var \DOMNodeList *childNodes */
      $childNodes = $this->getNode()->childNodes;
      /** @var int $length */
      $length     = $childNodes->length;
      return ($length > 0) ? new SimpleHtmlNode($childNodes[$length-1], $this->getDoc()) : null;
    }

    /**
     * @return SimpleHtmlNodeList
     */
    public function getChildren(): SimpleHtmlNodeList
    {
      return $this->findAll('./*');
    }

    public function findNextOne(): ?SimpleHtmlNode {
      return $this->findOne('./following-sibling::*[1]|./following-sibling::text()[1]|./following-sibling::comment()[1]');
    }

    /**
     * @return ?string
     */
    public function getTagName(): ?string
    {
      return $this->getNode()->nodeName;
    }

    /**
     * @return ?string
     */
    public function getPath(): ?string
    {
        return $this->_path;
    }

    /**
     * @param \DOMElement $node
     * @param SimpleHtmlDom $doc
     */
    public function __construct(\DOMElement $node, SimpleHtmlDom $doc)
    {
        $this->setNode($node);
        $this->setPath($node->getNodePath());
        $this->setDoc($doc);
        $this->setIsText($node->nodeName == '#text');
    }

    public function destruct(): void
    {
        parent::destruct();

        $this->_path    = null;

        unset($this->_path);
    }

    /**
     * @param string $html
     * @return \DOMDocumentFragment
     */
    private function getFragment(string $html): \DOMDocumentFragment
    {
        $dom = $this->getDoc()->getDom();
        $fragment = $dom->createDocumentFragment() or die('nope');
        $html = str_replace("&", "&amp;", $html);
        $fragment->appendXML($html);
        return $fragment;
    }

    public function replace($html)
    {
        $node = empty($html) ? null : $this->insertBefore($html);
        $this->remove();
        return $node;
    }

    /**
     * @param string $html
     * @return SimpleHtmlDom
     */
    public function insertBefore(string $html): self
    {
        $fragment = $this->getFragment($html);
        $this->getNode()->parentNode->insertBefore($fragment, $this->getNode());
        return $this;
    }

    /**
     * @param string $htmlOrText
     * @return SimpleHtmlNode
     */
    public function insertAfter(string $html): self
    {
        $fragment = $this->getFragment($html);
        if($ref_node = $this->getNode()->nextSibling)
        {
            $this->getNode()->parentNode->insertBefore($fragment, $ref_node);
        }
        else
        {
            $this->getNode()->parentNode->appendChild($fragment);
        }
        return $this;
    }

    public function decamelize($str)
    {
        $str = preg_replace('/(^|[a-z])([A-Z])/e', 'strtolower(strlen("\\1") ? "\\1_\\2" : "\\2")', $str);
        return preg_replace('/ /', '_', strtolower($str));
    }

    public function hasClass(string $classname): bool
    {
        return in_array($classname, $this->getClasses());
    }
    /**
     * @return array
     */
    public function getClasses(): array
    {
        $strClasses   = $this->getAttribute('class') ?? "";
        $strClasses   = trim(preg_replace("/[ ]+/"," ", $strClasses));
        $output       = explode(" ", $strClasses);
        return $output;
    }
    /**
     * @param string $key
     * @return bool
     */
    public function hasAttribute(string $key): bool
    {
        $data = $this->getAttributes();
        return (in_array($key, array_keys($data)));
    }

    /**
     * @param string $key
     * @return ?string
     */
    public function getAttribute(string $key): ?string
    {
      $attrs = $this->getAttributes();
      return $attrs[$key] ?? null;
    }

    public function getAttributes(): array
    {
        $ret = [];
        foreach($this->getNode()->attributes as $attr)
        {
            $ret[$attr->nodeName] = $attr->nodeValue;
        }
        return $ret;
    }

    public function flatten($key = null, $level = 1)
    {
        $children = $this->children;
        $ret = array();
        $tag = $this->tag;
        if(
            $this->at('./preceding-sibling::' . $this->tag) ||
            $this->at('./following-sibling::' . $this->tag) ||
            ($key = $this->tag . 's')
        )
        {
            $count = $this->search('./preceding-sibling::' . $this->tag)->length + 1;
            $tag .= '_' . $count;
        }
        if($children->length == 0)
        {
            $ret[$this->decamelize(implode(' ', array_filter(array($key, $tag))))] = $this->text;
        }
        else
        {
            foreach($children as $child)
            {
                $ret = array_merge($ret, $child->flatten(implode(' ', array_filter(array($key, $level <= 0 ? $tag : null))), $level - 1));
            }
        }
        return $ret;
    }

    /**
     * @return bool
     */
    public function offsetExists($offset): bool { return true; }

    /**
     * @return mixed
     */
    public function offsetGet($offset): mixed { return $this->getNode()->getAttribute($offset); }

    /**
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        if($value)
        {
            $this->getNode()->setAttribute($key, $value);
        }
        else
        {
            $this->getNode()->removeAttribute($key);
        }
        //trigger_error('offsetSet not implemented', E_USER_WARNING);
    }

    /**
     * @return void
     */
    public function offsetUnset($offset): void { trigger_error('offsetUnset not implemented', E_USER_WARNING); }

    /**
     * @return ?string
     */
    public function title(): ?string { return $this->getNode()->getAttribute('title'); }

    /**
     * @return mixed
     */
    public function attribute(string $attr): mixed { return $this->getNode()->getAttribute($attr); }
}
