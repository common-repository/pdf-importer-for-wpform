<?php


namespace rnpdfimporter\core\Integration\Processors\Settings\Forms\Fields;


class DateFieldSettings extends FieldSettingsBase
{
    public $DateFormat;


    public function __construct()
    {


    }

    public function SetDateFormat($dateFormat)
    {
        $this->DateFormat=$dateFormat;
        return $this;
    }

    public function GetType()
    {
        return 'Date';
    }

    public function InitializeFromOptions($options)
    {
        $this->DateFormat=$this->GetStringValue($options,'DateFormat');
        parent::InitializeFromOptions($options); // TODO: Change the autogenerated stub
    }


}