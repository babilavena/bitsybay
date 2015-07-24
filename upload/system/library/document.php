<?php

/**
 * LICENSE
 *
 * This source file is subject to the GNU General Public License, Version 3
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @package    BitsyBay Engine
 * @copyright  Copyright (c) 2015 The BitsyBay Project (http://bitsybay.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License, Version 3
 */

final class Document {

    /**
     * @var string
     */
    private $title = false;

    /**
     * @var string
     */
    private $description = false;

    /**
     * @var string
     */
    private $keywords = false;

    /**
     * @var array
     */
    private $links = array();

    /**
     * @var array
     */
    private $styles = array();

    /**
     * @var array
     */
    private $scripts = array();

    /**
     * @var array
     */
    private $schemas = array();

    /**
    * Set page title
    *
    * @param string $title
    * @param bool $template
    * @return null
    */
    public function setTitle($title, $template = true) {
        $this->title = $title . ($template ? ' | ' . PROJECT_NAME : false);
    }

    /**
    * Get page title
    *
    * @return string
    */
    public function getTitle() {
        return $this->title;
    }

    /**
    * Set page description
    *
    * @param string $description
    * @return null
    */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
    * Get page description
    *
    * @return string
    */
    public function getDescription() {
        return $this->description;
    }

    /**
    * Set page keywords
    *
    * @param string $keywords
    * @return null
    */
    public function setKeywords($keywords) {
        $this->keywords = $keywords;
    }

    /**
    * Get page keywords
    *
    * @return string Returns string of keywords comma separated
    */
    public function getKeywords() {
        return $this->keywords;
    }

    /**
    * Set page links
    *
    * @param string $href
    * @param string $rel
    * @return null
    */
    public function addLink($href, $rel) {
        $this->links[$href] = array(
            'href' => $href,
            'rel'  => $rel
        );
    }

    /**
    * Get page links
    *
     * @return array Returns associative array: array(array('href'=>'value','rel'=>'value'), ...)
    */
    public function getLinks() {
        return $this->links;
    }

    /**
    * Set page style
    *
    * @param string $href
    * @param string $rel
    * @param string $media
    * @return null
    */
    public function addStyle($href, $rel = 'stylesheet', $media = 'screen') {
        $this->styles[$href] = array(
            'href'  => $href,
            'rel'   => $rel,
            'media' => $media
        );
    }

    /**
    * Get page styles
    *
    * @return array Returns associative array: array(array('href'=>'value','rel'=>'value','media'=>'value'), ...)
    */
    public function getStyles() {
        return $this->styles;
    }

    /**
    * Set page script
    *
    * @param string $script
    * @return null
    */
    public function addScript($script) {
        $this->scripts[md5($script)] = $script;
    }

    /**
    * Get page scripts
    *
    * @return array
    */
    public function getScripts() {
        return $this->scripts;
    }

    /**
    * Set page schema
    *
    * @param string $itemtype
    * @param string $itemprop
    * @param string $content
    * @return null
    */
    public function addSchema($itemtype, $itemprop, $content) {
        $this->schemas[$itemtype][] = array(
            'itemprop'  => $itemprop,
            'content'   => $content
        );
    }

    /**
    * Get page schemas
    *
    * @return array
    */
    public function getSchemas() {
        return $this->schemas;
    }
}
