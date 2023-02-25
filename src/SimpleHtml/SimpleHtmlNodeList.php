<?php
namespace FOPG\Component\UtilsBundle\SimpleHtml;

class SimpleHtmlNodeList implements \Iterator, \Countable, \ArrayAccess
{

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

    // magic methods
    public function __call($key, $values)
    {
        $key = strtolower(str_replace('_', '', $key));
        switch($key)
        {
            case 'to_a':
                $retval = array();
                foreach($this as $node)
                {
                    $retval[] = new SimpleHtmlNode($this->getNodeList()->item($this->getCounter()), $this->getDoc());
                }
                return $retval;
                break;
            default:
        }
        // otherwise

        $retval = array();

        /*
        if(preg_match(TAGS_REGEX, $key, $m)) return $this->find($m[1]);
        if(preg_match(TAG_REGEX, $key, $m)) return $this->find($m[1], 0);
        */

        if(
            preg_match(SimpleHtmlBase::ATTRIBUTES_REGEX, $key, $m) ||
            preg_match('/^((clean|trim|str).*)s$/', $key, $m)
        )
        {
            foreach($this as $node)
            {
                $arg = $m[1];
                $retval[] = $node->$arg;
            }
            return $retval;
        }

        if(preg_match(SimpleHtmlBase::ATTRIBUTE_REGEX, $key, $m))
        {
            foreach($this as $node)
            {
                $arg = $m[1];
                $retval[] = $node->$arg;
            }
            return implode('', $retval);
        }

        // what now?
        foreach($this as $node)
        {
            $retval[] = isset($values[0]) ? $node->$key($values[0]) : $node->$key();
        }
        return implode('', $retval);
    }

    public function __get($key)
    {
        return $this->$key();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->html();
    }

    /**
     * @return int
     */
    public function length()
    {
        return $this->getNodeList()->length;
    }
}
