<?php
namespace FOPG\Component\UtilsBundle\SimpleHtml;

class SimpleHtmlNode extends SimpleHtmlBase implements \ArrayAccess{

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
    private function get_fragment(string $html): \DOMDocumentFragment
    {
        $dom = $this->getDoc()->getDom();
        $fragment = $dom->createDocumentFragment() or die('nope');
        $html = str_replace("&", "&amp;", $html);
        $fragment->appendXML($html);
        return $fragment;
    }

    public function replace($html)
    {
        $node = empty($html) ? null : $this->before($html);
        $this->remove();
        return $node;
    }

    public function before(string $html): self
    {
        $fragment = $this->get_fragment($html);
        $this->getNode()->parentNode->insertBefore($fragment, $this->getNode());
        return $this;
    }

    public function after(string $html): self
    {
        $fragment = $this->get_fragment($html);
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

    public function attributes(): array
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

    public function __set($key, $value)
    {
        switch($key)
        {
            case 'text':
            case 'innertext':
            case 'innerText':
            case 'plaintext':
                $this->getNode()->nodeValue = $value;
                return;
            case 'outertext':
                $this->replace($value); return;
            case 'tag':
                $el = $this->replace('<' . $value . '>' . $this->innerhtml . '</' . $value . '>');
                foreach($this->getNode()->attributes as $key => $att)
                {
                    $el->$key = $att->nodeValue;
                }
                $this->setNode($el->node);
                return;
            //default: trigger_error('Unknown property: ' . $key, E_USER_WARNING);
            //case 'name': return $this->getNode()->nodeName;
        }

        if (in_array($key,array('_path','dom','doc','node')))
        {
            return;
        }

        //trigger_error('Unknown property: ' . $key, E_USER_WARNING);
        if($value === null)
        {
            $this->getNode()->removeAttribute($key);
        }
        else
        {
            $this->getNode()->setAttribute($key, $value);
        }
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
