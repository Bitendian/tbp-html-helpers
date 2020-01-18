<?php

namespace Bitendian\TBP\HTML\Helpers\Alerts;

use Bitendian\TBP\UI\AbstractWidget;
use Bitendian\TBP\UI\Templater;
use Bitendian\TBP\Utils\SystemMessages;
use stdClass;

class AlertsWidget extends AbstractWidget
{
    private $infos = [];
    private $warnings = [];
    private $errors = [];

    public $alerts = [];

    public function fetch(&$params)
    {
        foreach (SystemMessages::getInfos() as &$systemMessage) {
            $this->infos[] = $systemMessage[0];
        }
        foreach (SystemMessages::getWarnings() as &$systemMessage) {
            $this->warnings[] = $systemMessage[0];
        }
        foreach (SystemMessages::getErrors() as &$systemMessage) {
            $this->errors[] = $systemMessage[0];
        }
    }

    public function render()
    {
        $alert = new stdClass();
        foreach ($this->infos as &$info) {
            $alert->Text = $info;
            $alert->Type = 'info';
            $this->alerts[] = new Templater(__DIR__ . DIRECTORY_SEPARATOR . 'Alert.template', $alert);
        }
        foreach ($this->warnings as &$warning) {
            $alert->Text = $warning;
            $alert->Type = 'warning';
            $this->alerts[] = new Templater(__DIR__ . DIRECTORY_SEPARATOR . 'Alert.template', $alert);
        }
        foreach ($this->errors as &$error) {
            $alert->Text = $error;
            $alert->Type = 'danger';
            $this->alerts[] = new Templater(__DIR__ . DIRECTORY_SEPARATOR . 'Alert.template', $alert);
        }
        echo new Templater(__DIR__ . DIRECTORY_SEPARATOR . 'Alerts.template', $this);
    }
}
