<?php
namespace FOPG\Component\UtilsBundle\SimpleHtml;

class SimpleHtmlDom extends SimpleHtmlBase{

    /**
     * @var ?\DOMXPath
     */
    private ?\DOMXPath $_xpath = null;

    /**
     * @var ?SimpleHtmlNode
     */
    private ?SimpleHtmlNode $_root = null;

    /**
     * @param \DOMXPath $xpath
     * @return \FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlDom
     */
    public function setXpath(\DOMXPath $xpath)
    {
        $this->_xpath = $xpath;

        return $this;
    }

    public function getXpath(): ?\DOMXPath
    {
        return $this->_xpath;
    }

    /**
     * @param ?string $html
     * @param bool $is_xml
     */
    public function __construct(?string $html = null,bool $is_xml = false)
    {
        $this->setDoc($this);
        if($html)
        {
            $this->load($html, $is_xml);
        }
    }

    /**
     * @param string $html
     * @param bool $is_xml
     */
     public function load(string $html,bool $is_xml = false)
     {
         $this->setDom(new \DOMDocument());
         if(true === $is_xml)
         {
             @$this->getDom()->loadXML(preg_replace('/xmlns=".*?"/ ', '', $html));
         }
         else
         {
             @$this->getDom()->loadHTML($html);
         }
         $this->setXpath(new \DOMXPath($this->getDom()));
         $this->_root = $this->findOne('body');
     }

     /**
      * @param string $file
      * @param bool $is_xml
      */
     public function load_file(string $file,bool $is_xml = false): void
     {
         $this->load(file_get_contents($file), $is_xml);
     }

     public function getTitle(): ?string
     {
       $title = $this->findOne('title');
       $text  = (null !== $title) ? $title->getText() : null;
       unset($title);
       return $text;
     }

     public function destruct()
     {
         parent::destruct();

         $this->_xpath = null;
         $this->_root  = null;

         unset($this->_xpath);
         unset($this->_root);
     }
}
