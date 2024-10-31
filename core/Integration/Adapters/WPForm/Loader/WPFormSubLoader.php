<?php


namespace rnpdfimporter\core\Integration\Adapters\WPForm\Loader;
use rnpdfimporter\core\Integration\Adapters\WPForm\Entry\Retriever\WPFormEntryRetriever;
use rnpdfimporter\core\Loader;
use rnpdfimporter\pr\core\PRLoader;

class WPFormSubLoader extends Loader
{

    public $ItemId;
    public function __construct($prefix,$basePrefix,$dbVersion,$fileVersion,$mainFile,$config=null)
    {
        $this->ItemId=12;
        $this->ProcessorLoader=new WPFormProcessorLoader($this);
        $this->ProcessorLoader->Initialize();
        parent::__construct($prefix,$basePrefix,$dbVersion,$fileVersion,$mainFile,$config);
        \add_filter('wpforms_frontend_confirmation_message',array($this,'AddPDFLink'),10,2);

        $this->AddMenu('PDF Importer for WPFORM',$this->Prefix,'administrator','','rnpdfimporter\Pages\PDFList');
        $this->AddMenu('Our WPForms Plugins',$prefix.'_additional_plugins','administrator','','rnpdfimporter\Pages\AdditionalPlugins');
        add_action( 'admin_notices', array($this,'NewPluginNotice') );


        if($this->IsPR())
        {
            $this->PRLoader=new PRLoader($this);
        }
    }

    public function NewPluginNotice()
    {

        global $IsShowingAutomationNotice;
        if($IsShowingAutomationNotice==true)
            return;

        $IsShowingAutomationNotice=true;
        if(get_option('automation_dont_show_again',false)==true)
            return;

        ?>
        <style type="text/css">
            .sfReviewButton{
                display: inline-block;
                padding: 6px 12px;
                margin-bottom: 0;
                font-size: 14px;
                font-weight: 400;
                line-height: 1.42857143;
                text-align: center;
                white-space: nowrap;
                vertical-align: middle;
                -ms-touch-action: manipulation;
                touch-action: manipulation;
                cursor: pointer;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
                background-image: none;
                border: 1px solid transparent;
                border-radius: 4px;
                color: #fff;
                background-color: #5bc0de;
                border-color: #46b8da;
                text-decoration: none;
            }

            .sfReviewButton:hover{
                color: #fff;
                background-color: #31b0d5;
                border-color: #269abc;
            }
        </style>
        <div class="notice is-dismissible notice-info sfReviewNotice" style="clear:both; padding-bottom:0;">
            <div style="padding-top: 5px;">


                <table >
                    <tbody  style="width:calc(100% - 135px);">
                    <tr>
                        <td>
                            <img style="display: inline-block;width:128px;vertical-align: top;" src="<?php echo $this->URL?>images/adIcons/automation.png">
                        </td>
                        <td>
                            <div style="display: flex; vertical-align: top;margin-left: 5px;flex-direction: column;height: 100%">

                                <div style="padding-bottom: 1px;margin-bottom: 0;font-size: 16px;font-family: Verdana">
                                    Streamline your WPForms workflow with our new plugin: <span style="font-weight: bold">Automation for WPForms</span>
                                </div>
                                <ul style="list-style: circle;list-style-position: inside">
                                    <li>Add actions that WPForms alone can't do, like rejecting an entry when a value has been submitted before</li>
                                    <li>Create workflows that make your life easier, like sending your form to an approval process</li>
                                    <li>Do repetitive actions (like sending emails, updating an entry add notes etc) with a click of a button that you can add in the entries screen, another page or even directly in an email</li>
                                </ul>
                                <div>
                                    <a target="_blank" href="https://formwiz.rednao.com/downloads/automation-for-wpforms/" class="button button-primary">Check it out</a>
                                    <button id="closePluginNotice" class="button button-secondary">Close and don't show again</button>
                                </div>
                            </div>
                        </td>

                    </tr>

                    </tbody>
                </table>
                <div style="clear: both;"></div>
            </div>

        </div>

        <script type="text/javascript">
            jQuery(document).ready( function($) {

                jQuery('#closePluginNotice').click(function(e){
                    debugger;
                    e.preventDefault();
                    $.post( ajaxurl, {
                        action: 'pdf_importer_dont_show_again_notice',
                        nonce:'<?php echo wp_create_nonce('pdf_builder_dont_show_again')?>'
                    });
                    jQuery('.sfReviewNotice').remove();
                });
            });
        </script> <?php

    }

    public function GetRootURL()
    {
        return 'https://formwiz.rednao.com/';
    }

    public function AddPDFLink($message,$formData)
    {
        global $RNWPImporterCreatedEntry;
        if(!isset($RNWPImporterCreatedEntry['CreatedDocuments']))
            return $message;

        if(\strpos($message,'[wpformpdflink]')===false)
            return $message;

        $links=array();
        $UsedTemplates=[];
        foreach($RNWPImporterCreatedEntry['CreatedDocuments'] as $createdDocument)
        {
            if(in_array($createdDocument['TemplateId'],$UsedTemplates))
                continue;
            $data=array(
              'entryid'=>$RNWPImporterCreatedEntry['EntryId'],
              'templateid'=>$createdDocument['TemplateId'],
              'nonce'=>\wp_create_nonce($this->Prefix.'_'.$RNWPImporterCreatedEntry['EntryId'].'_'.$createdDocument['TemplateId'])
            );
            $url=admin_url('admin-ajax.php').'?data='.\json_encode($data).'&action='.$this->Prefix.'_public_create_pdf';
            $links[]='<a target="_blank" href="'.esc_attr($url).'">'.\esc_html($createdDocument['Name']).'.pdf</a>';
            $UsedTemplates[]=$createdDocument['TemplateId'];
        }

        $message=\str_replace('[wpformpdflink]',\implode($links),$message);

        return $message;


    }

    /**
     * @return WPFormEntryRetriever
     */
    public function CreateEntryRetriever()
    {
        return new WPFormEntryRetriever($this);
    }


    public function AddBuilderScripts()
    {
        $this->AddScript('wpformbuilder','js/dist/WPFormBuilder_bundle.js',array('jquery', 'wp-element','regenerator-runtime','@builder'));
    }

    public function GetPurchaseURL()
    {
        return 'https://formwiz.rednao.com/pdf-importer/';
    }


    public function AddAdvertisementParams($params)
    {
        if(\get_option($this->Prefix.'never_show_add',false)==true)
        {
            $params['Text']='';

        }else
        {
            $params['Text'] = 'Want to create a pdf instead of importing one?';
            $params['LinkText'] = 'Try PDF Builder for WPForms';
            $params['LinkURL'] = 'https://wordpress.org/plugins/pdf-builder-for-wpforms/';
            $params['Icon'] = $this->URL . 'images/adIcons/wpform.jpg';
        }
        return $params;
    }

    public function GetProductItemId()
    {
        return 16;
    }
}