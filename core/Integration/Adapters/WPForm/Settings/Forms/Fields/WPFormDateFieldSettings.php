<?php
/**
 * Created by PhpStorm.
 * User: Edgar
 * Date: 3/21/2019
 * Time: 6:20 AM
 */

namespace rnpdfimporter\core\Integration\Adapters\WPForm\Settings\Forms\Fields;

use rnpdfimporter\core\Integration\Processors\Settings\Forms\Fields\FieldSettingsBase;

class WPFormDateFieldSettings extends FieldSettingsBase
{
    public $DateFormat;
    public $TimeFormat;

    public function GetType()
    {
        return 'Date';
    }

    public function SetTimeFormat($timeFormat){
        $this->TimeFormat=$timeFormat;
        return $this;
    }

    public function SetDateFormat($dateFormat){
        $this->DateFormat=$dateFormat;
        return $this;
    }

    public function InitializeFromOptions($options)
    {
        parent::InitializeFromOptions($options); // TODO: Change the autogenerated stub
        $this->DateFormat=$options->DateFormat;
        $this->TimeFormat=$options->TimeFormat;
    }


}