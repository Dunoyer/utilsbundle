<?php

namespace FOPG\Component\UtilsBundle\SimpleHtml;

class SimpleHtml extends SimpleHtmlDom
{
    /**
     * @var FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlDom
     */
    private $_container;

    /**
     * @return
     */
    public function getContainer(): SimpleHtmlDom
    {
        return $this->_container;
    }

    /**
     * Find html encoding
     *
     * @return string
     */
    public function getEncoding(): string
    {
        /**
         * First case : search encoding in meta informations
         *
         */
        foreach($this->_container->find('meta') as $meta)
        {
            foreach($meta->attributes() as $attrKey => $attrValue)
            {
                if(strtolower($attrKey) == 'content' && preg_match("/(.*)charset=(?<encoding>[a-z0-9\_\-]+)/i", $attrValue, $matches))
                {
                    return strtoupper($matches['encoding']);
                }
                elseif(strtolower($attrKey) == 'charset')
                {
                    return strtoupper($attrValue);
                }
            }
        }

        /**
         * Second case : autodetection
         *
         */
        $encoding =  mb_detect_encoding($this->_container->html());
        return ($encoding == 'ASCII') ? 'ISO-8859-1' : strtoupper($encoding);

    }

    public function load($html, $isXml=false): void
    {
        $this->_container = new SimpleHtmlDom($html, $isXml);
    }

    /**
     * @param string $html
     * @return FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtml
     */
    public static function str_get_html($html): SimpleHtml
    {
        return new SimpleHtml($html, false);
    }

    public static function file_get_html($url): SimpleHtml
    {
        return self::str_get_html(file_get_contents($url));
    }

    public static function str_get_xml($html): SimpleHtml
    {
        return new SimpleHtml($html, true);
    }

    public static function file_get_xml($url): SimpleHtml
    {
        return self::str_get_xml(file_get_contents($url));
    }
}
