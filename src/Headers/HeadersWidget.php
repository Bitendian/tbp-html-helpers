<?php

namespace Bitendian\TBP\HTML\Helpers\Headers;

use Bitendian\TBP\UI\Templater;
use Bitendian\TBP\UI\AbstractWidget;
use Bitendian\TBP\Utils\HtmlHeaders;

class HeadersWidget extends AbstractWidget
{
    private $scriptsArray = [];
    private $styleSheetsArray = [];

    public $title = '';
    public $styleSheets = [];
    public $scripts = [];

    public function fetch(&$params)
    {
        $this->scriptsArray = HtmlHeaders::getScripts();
        $this->styleSheetsArray = HtmlHeaders::getScripts();
        $this->title = HtmlHeaders::getTitle();
    }

    public function render()
    {
        $context = new \stdClass();
        foreach ($this->scriptsArray as &$script) {
            $context->script = $script;
            $this->scripts[] = new Templater(__DIR__ . DIRECTORY_SEPARATOR . 'Script.template', $context);
        }
        foreach ($this->styleSheetsArray as &$styleSheet) {
            $context->styleSheet = $styleSheet;
            $this->styleSheets[] = new Templater(__DIR__ . DIRECTORY_SEPARATOR . 'StyleSheet.template', $context);
        }
        echo new Templater(__DIR__ . DIRECTORY_SEPARATOR . 'Headers.template', $this);
    }
}