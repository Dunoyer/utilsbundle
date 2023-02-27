<?php

namespace FOPG\Component\UtilsBundle\SimpleHtml;

use FOPG\Component\UtilsBundle\SimpleHtml\Trait\SimpleHtmlNodeTrait;

class SimpleHtmlNodeList implements \Iterator, \Countable, \ArrayAccess
{
    use SimpleHtmlNodeTrait;

    /**
     * @var \DOMNodeList
     */
    private ?\DOMNodeList $_nodeList = null;

    /**
     * @var \FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlBase
     */
    private ?SimpleHtmlBase $_doc = null;

    /**
     * @var int
     */
    private int $_counter = 0;

    /**
     * @var int
     */
    private int $length = 0;

    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param \DOMNodeList $nodeList
     * @return \FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlNodeList
     */
    public function setNodeList(\DOMNodeList $nodeList): self
    {
        $this->_nodeList = $nodeList;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getText(): ?string
    {
      $str = null;
      /** @var \DOMElement $element */
      foreach($this->_nodeList as $element)
        $str.=$element->nodeValue;
      return $str;
    }

    /**
     * @return \DOMNodeList
     */
    public function getNodeList(): ?\DOMNodeList
    {
        return $this->_nodeList;
    }

    /**
     * @return int
     */
    public function getCounter(): int
    {
        return $this->_counter;
    }

    /**
     * @param \FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlBase $doc
     * @return \FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlNodeList
     */
    public function setDoc(SimpleHtmlBase $doc): self
    {
        $this->_doc = $doc;

        return $this;
    }

    /**
     * @return \FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlBase
     */
    public function getDoc(): ?SimpleHtmlBase
    {
        return $this->_doc;
    }

    public function __construct(\DOMNodeList $nodeList,SimpleHtmlBase $doc)
    {
        $this->setNodeList($nodeList);
        $this->setDoc($doc);
    }

    public function destruct()
    {
        $this->_nodeList= null;
        $this->_doc     = null;
        $this->_counter  = 0;

        unset($this->_doc);
        unset($this->_nodeList);
        unset($this->_counter);
    }

    /**
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return (0 <= $offset && $offset < $this->getNodeList()->length);
    }

    /**
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return new SimpleHtmlNode($this->getNodeList()->item($offset), $this->getDoc());
    }

    /**
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        trigger_error('offsetSet not implemented', E_USER_WARNING);
    }

    /**
     * @return void
     */
    public function offsetUnset($offset): void
    {
        trigger_error('offsetUnset not implemented', E_USER_WARNING);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->getNodeList()->length;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->_counter = 0;
    }

    /**
     * @return \FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlNode
     */
    public function current(): mixed
    {
        return new SimpleHtmlNode($this->getNodeList()->item($this->getCounter()), $this->getDoc());
    }

    /**
     * @return int
     */
    public function key(): mixed
    {
        return $this->getCounter();
    }

    /**
     * @return void
     */
    public function next(): void
    {
        $this->_counter++;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return ($this->getCounter() < $this->getNodeList()->length);
    }

    public function last(): ?SimpleHtmlNode
    {
        return ($this->getNodeList()->length > 0) ? new SimpleHtmlNode($this->getNodeList()->item($this->getNodeList()->length - 1), $this->getDoc()) : null;
    }

    /**
     * @return \FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlNodeList
     */
    public function remove()
    {
        foreach($this as $node)
        {
            $node->remove();
        }
        return $this;
    }

    public function map($c)
    {
        $ret = array();
        foreach($this as $node)
        {
            $ret[] = $c($node);
        }
        return $ret;
    }

    //math methods
    public function doMath($nl, $op = 'plus')
    {
        $paths = array();
        $other_paths = array();

        foreach($this as $node)
        {
            $paths[] = $node->node->getNodePath();
        }

        foreach($nl as $node)
        {
            $other_paths[] = $node->node->getNodePath();
        }

        switch($op)
        {
            case 'plus':
                $new_paths = array_unique(array_merge($paths, $other_paths));
                break;
            case 'minus':
                $new_paths = array_diff($paths, $other_paths);
                break;
            case 'intersect':
                $new_paths = array_intersect($paths, $other_paths);
                break;
        }

        return new SimpleHtmlNodeList($this->getDoc()->xpath->query(implode('|', $new_paths)), $this->getDoc());
    }

    public function minus($nl)
    {
        return $this->doMath($nl, 'minus');
    }

    public function plus($nl)
    {
        return $this->doMath($nl, 'plus');
    }

    public function intersect($nl)
    {
        return $this->doMath($nl, 'intersect');
    }

    public function getInnertext(): ?string
    {
        /** @var SimpleHtmlNode $node */
        foreach($this as $node)
        {
          $retval[] = $node->getInnertext();
        }
        return implode("",$retval);
    }

    public function getInnerhtml(): ?string
    {
        /** @var SimpleHtmlNode $node */
        foreach($this as $node)
        {
          $retval[] = $node->getInnerhtml();
        }
        return implode("",$retval);
    }

    public function getFirstChild(): ?SimpleHtmlNode
    {
        /** @var SimpleHtmlNode $node */
        foreach($this as $node)
          return $node->getFirstChild();
        return null;
    }

    public function getLastChild(): ?SimpleHtmlNode
    {
        $lastNode = null;
        /** @var SimpleHtmlNode $node */
        foreach($this as $node)
          $lastNode = $node;
        return (null !== $lastNode) ? $lastNode->getLastChild() : null;
    }

    /**
     * @return int
     */
    public function length()
    {
        return $this->getNodeList()->length;
    }
}
