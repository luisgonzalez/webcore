<?php
/**
 * @package WebCore
 * @subpackage View
 * @version 1.0
 * 
 * Provides views of controls in a form.
 *
 * @todo Improve on the Rich text area control formatting. Right now tooltip breaks and the css class sucks.
 * @todo Wow, I just realized how much the RTF editor sucks! Won't even handle postbacks correctly.
 * 
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.view.php";

/**
 * Provides a standard Form object renderer.
 * Controls, fields and containers are rendered by using callbacks.
 * Add, remove or modify callbacks using the renderCallbacks KeyedCollection
 *
 * @package WebCore
 * @subpackage View
 */
class HtmlFormView extends HtmlViewBase
{
    /**
     * Creates a new instance of this class
     *
     * @param Form $model
     */
    public function __construct(&$model)
    {
        parent::__construct($model);
        $this->cssClass = 'formview';
        $callbacks =& $this->renderCallbacks->getArrayReference();
        
        // Setup the callbacks for each renderable model
        $callbacks['Form']                 = array(
            'HtmlFormRenderCallbacks',
            'renderForm'
        );
        $callbacks['FormSection']          = array(
            'HtmlFormRenderCallbacks',
            'renderFormSection'
        );
        $callbacks['TextField']            = array(
            'HtmlFormRenderCallbacks',
            'renderTextField'
        );
        $callbacks['PasswordField']        = array(
            'HtmlFormRenderCallbacks',
            'renderTextField'
        );
        $callbacks['EmailField']           = array(
            'HtmlFormRenderCallbacks',
            'renderTextField'
        );
        $callbacks['MoneyField']           = array(
            'HtmlFormRenderCallbacks',
            'renderTextField'
        );
        $callbacks['IntegerField']         = array(
            'HtmlFormRenderCallbacks',
            'renderTextField'
        );
        $callbacks['DateTextField']         = array(
            'HtmlFormRenderCallbacks',
            'renderTextField'
        );
        $callbacks['DecimalField']         = array(
            'HtmlFormRenderCallbacks',
            'renderTextField'
        );
        $callbacks['PhoneNumberField']     = array(
            'HtmlFormRenderCallbacks',
            'renderTextField'
        );
        $callbacks['Button']               = array(
            'HtmlFormRenderCallbacks',
            'renderButton'
        );
        $callbacks['Persistor']            = array(
            'HtmlFormRenderCallbacks',
            'renderPersistor'
        );
        $callbacks['FileField']            = array(
            'HtmlFormRenderCallbacks',
            'renderFileField'
        );
        $callbacks['TextArea']             = array(
            'HtmlFormRenderCallbacks',
            'renderTextArea'
        );
        $callbacks['CheckBox']             = array(
            'HtmlFormRenderCallbacks',
            'renderCheckBox'
        );
        $callbacks['ComboBox']             = array(
            'HtmlFormRenderCallbacks',
            'renderComboBox'
        );
        $callbacks['DateField']            = array(
            'HtmlFormRenderCallbacks',
            'renderDateField'
        );
        $callbacks['CompoundListField']    = array(
            'HtmlFormRenderCallbacks',
            'renderCompoundListField'
        );
        $callbacks['TimeField']            = array(
            'HtmlFormRenderCallbacks',
            'renderTimeField'
        );
        $callbacks['DateTimeField']        = array(
            'HtmlFormRenderCallbacks',
            'renderDateTimeField'
        );
        $callbacks['LabelField']           = array(
            'HtmlFormRenderCallbacks',
            'renderLabelField'
        );
        $callbacks['TextBlock']            = array(
            'HtmlFormRenderCallbacks',
            'renderTextBlock'
        );
        $callbacks['TabContainer']         = array(
            'HtmlFormRenderCallbacks',
            'renderTabContainer'
        );
        $callbacks['TabPage']              = array(
            'HtmlFormRenderCallbacks',
            'renderTabPage'
        );
        $callbacks['MonthYearComboBox']    = array(
            'HtmlFormRenderCallbacks',
            'renderMonthYearComboBox'
        );
        $callbacks['RichTextArea']         = array(
            'HtmlFormRenderCallbacks',
            'renderRichTextArea'
        );
        $callbacks['MultiSelector'] = array(
            'HtmlFormRenderCallbacks',
            'renderMultiSelector'
        );
        $callbacks['CheckBoxGroup'] = array(
            'HtmlFormRenderCallbacks',
            'renderCheckBoxGroup'
        );
        $this->registerDependencies();
    }
    
    /**
     * Registers model resources and dependencies on the client-side
     *
     */
    protected function registerDependencies()
    {
        self::registerCommonFormsDependecies($this->getModel());
    }
}

/**
 * Contains static callback methods to render standard framework HTML controls in a Form
 *
 * @package WebCore
 * @subpackage View
 */
class HtmlFormRenderCallbacks extends HtmlRenderCallbacks
{
    /**
     * Renders the main form
     *
     * @param Form $model
     * @param HtmlFormView $view
     */
    public static function renderForm(&$model, &$view)
    {
        if ($model->getVisible() === false)
            return;
        
        $tw = HtmlWriter::getInstance();
        
        // The form tag
        $tw->openForm();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('name', $model->getName());
        $tw->addAttribute('class', $view->getCssClass());
        $tw->addAttribute('action', HttpContext::getInfo()->getScriptVirtualPath());
        $tw->addAttribute('method', 'post');
        $tw->addAttribute('enctype', 'multipart/form-data');
        
        // The form's label
        if ($model->getCaption() != '')
        {
            $tw->openDiv();
            $tw->addAttribute('class', $view->getMasterCssClass() . '-caption');
            $tw->writeContent($model->getCaption());
            $tw->closeDiv();
        }
        
        // The form's content section
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . '_content');
        $tw->addAttribute('class', $view->getCssClass() . '-content');
        if ($view->getBodyHeight() > 0)
        {
            $tw->addAttribute('style', 'height: ' . $view->getBodyHeight() . 'px;');
        }
        
        // The form's child controls
        self::renderFieldContainerChildren($model, $view);
        
        parent::renderMessages($model, $view);
        
        // Add the postback hidden field to signal postbacks
        self::renderPostBackFlag($model, $view);
        $tw->closeDiv();
        
        // The form's button section
        $buttonControls = $model->getChildren()->getControlNames(false, 'ButtonModelBase');
        
        if (count($buttonControls) > 0)
        {
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-buttonpanel-container');
            
            $tw->openDiv();
            $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . '_buttonPanel');
            $tw->addAttribute('class', $view->getCssClass() . '-buttonpanel');
            
            foreach ($buttonControls as $buttonName)
                self::renderFormButton($model, $view, $buttonName);
            
            $tw->closeDiv(); // Close the button panel
            
            // Add the loading bar to replace the buttons with.
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-loadingbar');
            $tw->writeContent(' ');
            $tw->closeDiv(true); // Close the ajax bar
            
            $tw->closeDiv(); // Close the bottom container
        }
        
        $tw->closeForm();
        
        $enableAsync = ($view->getIsAsynchronous() == true) ? 'true' : 'false';
        $javascript  = "var js_" . HtmlViewBase::getHtmlId($model) . " = null;" . "\r\n"
            . "window.addEvent('domready', function () { js_" . HtmlViewBase::getHtmlId($model) . " = new HtmlFormView('" . HtmlViewBase::getHtmlId($model) . "', '" . $view->getCssClass() . "', $enableAsync); });";
                        
        self::renderInitializationScript($javascript);
    }
    
    /**
     * Renders a form's section
     *
     * @param FormSection $model
     * @param HtmlFormView $view
     */
    public static function renderFormSection(&$model, &$view)
    {
        if ($model->getVisible() === false)
            return;
        
        $tw = HtmlWriter::getInstance();
        
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('class', $view->getCssClass() . '-formsection');
        
        // The container's header
        if ($model->getCaption() != '')
        {
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-formsection-caption');
            $tw->writeContent($model->getCaption());
            $tw->closeDiv();
        }
        
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . '_content');
        $tw->addAttribute('class', $view->getCssClass() . '-formsection-content');
        
        self::renderFieldContainerChildren($model, $view);
        
        $tw->closeDiv(true); // close content
        
        // The form section's button panel
        $buttonControls = $model->getChildren()->getControlNames(false, 'ButtonModelBase');
        
        if (count($buttonControls) > 0)
        {
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-formsection-buttonpanel');
            
            foreach ($buttonControls as $buttonName)
                self::renderFormButton($model, $view, $buttonName);
            
            $tw->closeDiv();
        }
        
        $tw->closeDiv(); // close main container
    }
    
    /**
     * Renders a TextField
     *
     * @param TextField $model
     * @param HtmlFormView $view
     */
    public static function renderTextField(&$model, &$view)
    {
        self::openFieldLayout($model, $view);
        
        $tw = HtmlWriter::getInstance();
        
        // Render the html-equivalent input
        $tw->openInput();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('name', $model->getBindingMemberName());
        $valueString = $model->getValue() . '';
        $validator   = $model->getValidator();
        if (ObjectIntrospector::isA($validator, 'NumericFieldValidator') && $valueString != '' && is_numeric($valueString))
        {
            if ($validator->getIsMoney())
                $valueString = '$' . number_format($valueString, 2);
        }
        $tw->addAttribute('value', $valueString);
        $tw->addAttribute('class', $view->getCssClass() . '-textfield');
        if (ObjectIntrospector::isA($model, 'PasswordField'))
        {
            $tw->addAttribute('type', 'password');
        }
        else
        {
            $tw->addAttribute('type', 'text');
        }
        $tw->addAttribute('maxlength', $model->getMaxChars());
        if ($model->getIsReadOnly() === true)
            $tw->addAttribute('disabled', 'disabled');
        
        $tw->closeInput(false);
        
        self::closeFieldLayout($model, $view);
    }
    
    /**
     * Renders a DateField
     *
     * @param DateField $model
     * @param HtmlFormView $view
     */
    public static function renderDateField(&$model, &$view)
    {
        self::openFieldLayout($model, $view);
        
        $tw = HtmlWriter::getInstance();
        
        // Render the html-equivalent input
        $tw->openInput();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('name', $model->getBindingMemberName());
        $tw->addAttribute('value', $model->getValue());
        $tw->addAttribute('class', $view->getCssClass() . '-calendar');
        $tw->addAttribute('type', 'text');
        $tw->addAttribute('maxlength', 20);
        if ($model->getIsReadOnly() === true)
            $tw->addAttribute('disabled', 'disabled');

        $tw->closeInput();
        
        self::closeFieldLayout($model, $view);
    }
    
    /**
     * Renders a TextArea
     *
     * @param TextArea $model
     * @param HtmlFormView $view
     */
    public static function renderTextArea(&$model, &$view)
    {
        self::openFieldLayout($model, $view);
        
        $tw = HtmlWriter::getInstance();
        
        // Render the html-equivalent input
        $tw->openTextarea(true);
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('name', $model->getBindingMemberName());
        $tw->addAttribute('class', $view->getCssClass() . '-textarea');
        $tw->addAttribute('type', 'text');
        
        if ($model->getIsReadOnly() === true)
            $tw->addAttribute('disabled', 'disabled');
        
        if (trim($model->getValue()) != '')
            $tw->writeContent($model->getValue(), false, false, false);
        
        $tw->closeTextarea(true);
        
        self::closeFieldLayout($model, $view);
    }
    
    /**
     * Renders a RichTextArea
     *
     * @param RichTextArea $model
     * @param HtmlFormView $view
     */
    public static function renderRichTextArea(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openTable();
        $tw->addAttribute('summary', '');
        $tw->openTr();
        
        // Render the label
        $tw->openTd();
        $tw->addAttribute('class', 'richtextlabel');
        
        $tw->openDiv();
        $tw->addAttribute('style', 'float: left;');
        
        if ($model->getIsRequired() && !$model->getIsReadOnly())
        {
            $tw->openSpan();
            $tw->addAttribute('title', Resources::getValue(Resources::SRK_FIELD_REQUIRED));
            $tw->writeContent('*', false, false, false);
            $tw->closeSpan();
        }
        
        $tw->openLabel();
        $tw->addAttribute('for', HtmlViewBase::getHtmlId($model));
        $tw->writeContent($model->getCaption());
        $tw->closeLabel();
        $tw->closeDiv();
        
        self::renderFieldError($model, $view);
        
        $tw->closeTd();
        $tw->closeTr();
        
        
        if ($model->getHelpMessage() != '')
        {
            $tw->openTr();
            $tw->openTd();
            $tw->addAttribute('class', 'richtexthelp');
            
            $tw->openDiv();
            $tw->writeContent($model->getHelpMessage());
            $tw->closeDiv();
            
            $tw->closeTd();
            $tw->closeTr();
        }
        
        $tw->openTr();
        $tw->openTd();
        $tw->addAttribute('class', $view->getCssClass() . '-richtext-container');
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-richtext-container');
        
        // Render the html-equivalent input
        $tw->openTextarea(true);
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('name', $model->getBindingMemberName());
        $tw->addAttribute('class', $view->getCssClass() . '-richtext');
        $tw->addAttribute('type', 'text');
        //$tw->addAttribute('style', 'width: 100%;');
        if ($model->getIsReadOnly() === true)
            $tw->addAttribute('disabled', 'disabled');
        
        if (trim($model->getValue()) != '')
            $tw->writeContent($model->getValue(), false, false, false);
        
        $tw->closeTextarea(true);
        $tw->closeDiv();
        
        $tw->closeTd();
        $tw->closeTr();
        $tw->closeTable();
        
    }
    
    /**
     * Renders a CheckBox
     *
     * @param CheckBox $model
     * @param HtmlFormView $view
     */
    public static function renderCheckBox(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        // @todo use self::openFieldLayout($model, $view);  

        $tw->openTable();
        $tw->addAttribute('summary', '');
        
        $tw->openTr();
        // Render the label
        $tw->openTd();
        $tw->addAttribute('class', 'fieldlabel');        
        $tw->writeContent(' ');
        $tw->closeTd();
        $tw->openTd();        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-field-container');
        // Render the html-equivalent input
        $tw->openInput();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . '_ctrl');
        $tw->addAttribute('name', $model->getBindingMemberName() . '_ctrl');
        $tw->addAttribute('value', $model->getValue());
        $tw->addAttribute('class', $view->getCssClass() . '-checkbox');
        $tw->addAttribute('type', 'checkbox');
        $tw->addAttribute('onchange', "if (this.checked) { \$('" . HtmlViewBase::getHtmlId($model) . "').set('value', '" . $model->getCheckedValue() . "'); } else { \$('" . HtmlViewBase::getHtmlId($model) . "').set('value', '" . $model->getUncheckedValue() . "'); } ");
        if ($model->getIsChecked())
            $tw->addAttribute('checked', 'checked');
        if ($model->getIsReadOnly() === true)
            $tw->addAttribute('disabled', 'disabled');
        $tw->closeInput();
        
        // supporting hidden field
        $tw->openInput();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('name', $model->getBindingMemberName());
        $tw->addAttribute('value', $model->getValue());
        $tw->addAttribute('type', 'hidden');
        $tw->closeInput();
        
        $tw->openLabel();
        $tw->addAttribute('class', $view->getCssClass() . '-checkbox-caption');
        $tw->addAttribute('for', HtmlViewBase::getHtmlId($model) . '_ctrl');
        $tw->writeContent($model->getCaption());
        $tw->closeLabel();
        $tw->closeDiv();
        
        if ($model->getHelpMessage() != '')
        {
            $tw->openTr();
            $tw->openTd();
            $tw->addAttribute('class', 'fieldlabel');
            $tw->writeContent(' ');
            $tw->closeTd();
            $tw->openTd();
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-checkbox-helpmessage');
            $tw->writeContent($model->getHelpMessage(), true, true, false);
            $tw->closeDiv();
            $tw->closeTd();
            $tw->closeTr();
        }
        self::renderFieldError($model, $view);
        $tw->closeTd();
        $tw->closeTr();
        $tw->closeTable();
    }
    
    /**
     * Renders a Persistor
     *
     * @param Persistor $model
     * @param HtmlFormView $view
     */
    public static function renderPersistor(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openInput();
        
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('name', $model->getBindingMemberName());
        $tw->addAttribute('value', $model->getValue());
        $tw->addAttribute('type', 'hidden');
        
        $tw->closeInput();
    }
    
    /**
     * Renders a ComboBox
     *
     * @param ComboBox $model
     * @param HtmlFormView $view
     */
    public static function renderComboBox(&$model, &$view)
    {
        self::openFieldLayout($model, $view);
        
        $tw = HtmlWriter::getInstance();
        
        $tw->openSelect();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('name', $model->getBindingMemberName());
        $tw->addAttribute('class', $view->getCssClass() . '-select');
        if ($model->getMultiline() === true)
            $tw->addAttribute('size', '4');
        if ($model->getEventValue() != '')
            $tw->addAttribute('eventname', $model->getEventName());
        if ($model->getIsReadOnly() === true)
            $tw->addAttribute('disabled', 'disabled');
        
        $currentOptgroup = '';
        $isOptgroupOpen  = false;
        
        foreach ($model->getOptions() as $option)
        {
            if ($currentOptgroup != $option['category'])
            {
                if ($isOptgroupOpen === true)
                    $tw->closeOptgroup();
                
                if ($option['category'] != '')
                {
                    $tw->openOptgroup();
                    $tw->addAttribute('label', $option['category']);
                    $isOptgroupOpen  = true;
                    $currentOptgroup = $option['category'];
                }
                else
                {
                    $isOptgroupOpen  = false;
                    $currentOptgroup = $option['category'];
                }
            }
            
            $tw->openOption();
            $tw->addAttribute('value', $option['value']);
            if ($option['value'] == $model->getValue())
            {
                $tw->addAttribute('selected', 'selected');
            }
            $tw->writeContent($option['display'], false, false);
            $tw->closeOption();
        }
        
        if ($isOptgroupOpen === true)
            $tw->closeOptgroup();
        
        $tw->closeSelect();
        
        self::closeFieldLayout($model, $view);
    }
    
    /**
     * Renders a CheckBoxGroup
     *
     * @param CheckBoxGroup $model
     * @param HtmlFormView $view
     */
    public static function renderCheckBoxGroup(&$model, &$view)
    {
        self::openFieldLayout($model, $view);
        
        $values = explode("|", $model->getValue());
        $tw = HtmlWriter::getInstance();
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-checkboxgroup');
        
        $tw->openInput();
        $tw->addAttribute('class', $view->getCssClass() . '-checkboxgroup-hidden');
        $tw->addAttribute('name', $model->getBindingMemberName());
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('type', 'hidden');
        $tw->closeInput();
        
        $currentOptgroup = '';
        $isOptgroupOpen  = false;
        
        foreach ($model->getOptions() as $option)
        {
            if ($currentOptgroup != $option['category'])
            {
                if ($isOptgroupOpen === true)
                    $tw->closeDiv();
                
                if ($option['category'] != '')
                {
                    $tw->openDiv();
                    $tw->addAttribute('label', $option['category']);
                    $isOptgroupOpen  = true;
                    $currentOptgroup = $option['category'];
                }
                else
                {
                    $isOptgroupOpen  = false;
                    $currentOptgroup = $option['category'];
                }
            }
            
            $tw->openDiv();
            $tw->openInput();
            $tw->addAttribute('class', $view->getCssClass() . '-checkboxgroup-item');
            $tw->addAttribute('type', 'checkbox');
            $tw->addAttribute('value', $option['value']);
            
            if (in_array($option['value'], $values))
                $tw->addAttribute('checked', 'checked');
            
            $tw->closeInput();
            $tw->openSpan();
            $tw->writeContent($option['display'], false, false);
            $tw->closeSpan();
            $tw->closeDiv();
        }
        
        if ($isOptgroupOpen === true)
            $tw->closeDiv();
        
        $tw->closeDiv();
        
        self::closeFieldLayout($model, $view);
    }
    
    /**
     * Renders a Button model
     *
     * @param Button $model
     * @param HtmlFormView $view
     */
    public static function renderButton(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        // Render the button itself.
        $tw->openA();
        $tw->addAttribute('href', HtmlRenderCallbacks::HREF_NO_ACTION);
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        
        if ($model->getIsEnabled() === false)
        {
            $tw->addAttribute('class', $view->getMasterCssClass() . '-button-disabled');
        }
        else
        {
            $tw->addAttribute('class', $view->getMasterCssClass() . '-button ' . $view->getCssClass() . '-button');
            $tw->addAttribute('eventname', $model->getEventName());
            $tw->addAttribute('name', $model->getName());
            $tw->addAttribute('value', '1');
        }
        
        $tw->writeContent($model->getCaption());
        $tw->closeA();
    }
    
    /**
     * Renders a FileField
     *
     * @param FileField $model
     * @param HtmlFormView $view
     */
    public static function renderFileField(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        self::openFieldLayout($model, $view);
        
        // Render the html-equivalent input
        if (is_null($model->getValue()) === false)
        {
            if (ObjectIntrospector::isA($model->getValue(), 'PostedFile'))
            {
                $fieldName = $model->getValue()->getFileName();
                $fieldName = str_replace(" ", "_", $fieldName);
                $content   = $model->getValue()->readAll();
                $model->setValue($content);
            }
            
            if (strlen($model->getValue()) > 0)
            {
                $hash = md5($model->getValue());
                file_put_contents(HttpContext::getTempDir() . $hash, $model->getValue());
                
                $tw->openDiv();
                $tw->addAttribute('class', $view->getCssClass() . '-filefield-container');
                
                if ($model->getAllowPreview())
                {
                    $tw->openA();
                    $tw->addAttribute('href', self::HREF_NO_ACTION);
                    
                    $tw->openImg();
                    $tw->addAttribute('src', getenv("PHP_SELF") . '?_fileName=' . urlencode($fieldName) . '&_file_img_handler=' . $hash);
                    $tw->addAttribute('id', 'img' . $model->getName());
                    $tw->addAttribute('alt', $model->getBindingMemberName());
                    $tw->addAttribute('class', $view->getCssClass() . '-filefield-image');
                    $tw->closeImg();
                    
                    $tw->closeA();
                }
                else
                {
                    $tw->openA();
                    $tw->addAttribute('href', getenv("PHP_SELF") . '?_fileName=' . urlencode($fieldName) . '&_file=' . $hash);
                    $tw->writeContent(Resources::getValue(Resources::SRK_FIELD_DOWNLOAD));
                    $tw->closeA();
                }
                
                $tw->closeDiv();
            }
        }
        
        if ($model->getIsReadOnly() === false)
        {
            $tw->openInput();
            $tw->addAttribute('id', 'fileField_' . $model->getName());
            $tw->addAttribute('class', $view->getCssClass() . "-filefield");
            $tw->addAttribute('name', $model->getBindingMemberName());
            $tw->addAttribute('type', 'file');
            $tw->addAttribute('eventname', $model->getEventName());
            $tw->closeInput();
        }
        
        self::closeFieldLayout($model, $view);
    }
    
    /**
     * Helper function that provides a simple, table-based layout for regular fields.
     * Use the closeFieldLayout method after writing the control.
     *
     * @param FieldModelBase $model
     * @param HtmlFormView $view
     */
    public static function openFieldLayout(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openTable();
        $tw->addAttribute('summary', '');
        $tw->openTr();
        
        // Render the label
        $tw->openTd();
        $tw->addAttribute('class', 'fieldlabel');
        
        if ($model->getIsRequired() && !$model->getIsReadOnly())
        {
            $tw->openSpan();
            $tw->addAttribute('title', Resources::getValue(Resources::SRK_FIELD_REQUIRED));
            $tw->writeContent('*', false, false, false);
            $tw->closeSpan();
        }
        
        $tw->openLabel();
        $tw->addAttribute('for', HtmlViewBase::getHtmlId($model));
        $tw->writeContent($model->getCaption(), true, true, false);
        $tw->closeLabel();
        $tw->closeTd();
        $tw->openTd();
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-field-container');
        $tw->addAttribute('tooltip', $model->getHelpMessage());
    }
    
    /**
     * Helper function that must be preceeded by an opendFieldLayout call.
     *
     * @param FieldModelBase $model
     * @param HtmlFormView $view
     */
    public static function closeFieldLayout(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->closeDiv();
        self::renderFieldError($model, $view);
        $tw->closeTd();
        $tw->closeTr();
        $tw->closeTable();
    }
    
    /**
     * Renders a List compound field
     *
     * @param CompoundListField $model
     * @param HtmlFormView $view
     */
    public static function renderCompoundListField(&$model, &$view)
    {
        self::openFieldLayout($model, $view);
        
        $tw = HtmlWriter::getInstance();
        
        $tw->openInput();
        $tw->addAttribute("type", "hidden");
        $tw->addAttribute("name", $model->getName() . "_items");
        $tw->addAttribute('id', 'compoundListField_' . $model->getName() . "_items");
        $tw->addAttribute('value', $model->getValue());
        $tw->closeInput();
        
        $tw->openInput();
        $tw->addAttribute("type", "text");
        $tw->addAttribute("name", $model->getName());
        $tw->addAttribute('id', 'compoundListField_' . $model->getName());
        $tw->addAttribute('class', $view->getCssClass() . '-compoundlist');
        
        $tw->closeInput();
        
        self::closeFieldLayout($model, $view);
    }
    
    /**
     * Renders a TimeField
     *
     * @param TimeField $model
     * @param HtmlFormView $view
     */
    public static function renderTimeField(&$model, &$view)
    {
        self::openFieldLayout($model, $view);
        
        $tw = HtmlWriter::getInstance();
        
        // Render the html-equivalent input
        $tw->openInput();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('name', $model->getBindingMemberName());
        $tw->addAttribute('value', $model->getValue());
        $tw->addAttribute('class', $view->getCssClass() . '-time');
        $tw->addAttribute('type', 'text');
        $tw->addAttribute('maxlength', 20);
        if ($model->getIsReadOnly() === true)
            $tw->addAttribute('disabled', 'disabled');
        
        $tw->closeInput();
        
        self::closeFieldLayout($model, $view);
    }
    
    /**
     * Renders a TimeField
     *
     * @param DateTimeField $model
     * @param HtmlFormView $view
     */
    public static function renderDateTimeField(&$model, &$view)
    {
        self::openFieldLayout($model, $view);
        
        $tw = HtmlWriter::getInstance();
        
        // Render the html-equivalent input
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-datetime');
        
        $tw->openDiv();
        $tw->openInput();
        $tw->addAttribute('id', $model->getName() . '_date');
        $tw->addAttribute('value', $model->getValue('Y-m-d'));
        $tw->addAttribute('class', $view->getCssClass() . '-calendar');
        $tw->addAttribute('type', 'text');
        $tw->addAttribute('maxlength', 20);

        if ($model->getIsReadOnly() === true)
            $tw->addAttribute('disabled', 'disabled');
        $tw->closeInput(false);
        $tw->closeDiv();
        
        $tw->openDiv();
        $tw->openInput();
        $tw->addAttribute('id', $model->getName() . '_time');
        $tw->addAttribute('value', $model->getValue('h:i A'));
        $tw->addAttribute('class', $view->getCssClass() . '-time');
        $tw->addAttribute('type', 'text');
        $tw->addAttribute('maxlength', 20);
        if ($model->getIsReadOnly() === true)
            $tw->addAttribute('disabled', 'disabled');

        $tw->closeInput(false);
        $tw->closeDiv();
        
        $tw->openInput();
        $tw->addAttribute('id', $model->getName());
        $tw->addAttribute('name', $model->getName());
        $tw->addAttribute('value', $model->getValue());
        $tw->addAttribute('class', $view->getCssClass() . '-datetimefield');
        $tw->addAttribute('type', 'hidden');
        $tw->closeInput(false);
        
        $tw->closeDiv();
        
        self::closeFieldLayout($model, $view);
    }
    
    /**
     * Renders a LabelField
     *
     * @param LabelField $model
     * @param HtmlFormView $view
     */
    public static function renderLabelField(&$model, &$view)
    {
        self::openFieldLayout($model, $view);
        $isUrl = false;
        
        if (substr($model->getValue(), 0, 7) == "http://")
            $isUrl = true;
        
        $tw = HtmlWriter::getInstance();
        
        if ($isUrl)
        {
            $tw->openSpan();
            $tw->openA();
            $tw->addAttribute('href', $model->getValue());
            $tw->addAttribute('target', '_blank');
            $tw->writeContent($model->getValue());
            $tw->closeA();
            $tw->closeSpan();
        }
        else
        {
            $tw->openSpan();
            if ($model->getHtmlEncode() === true)
                $tw->writeRaw(nl2br(htmlentities($model->getValue())));
            else
                $tw->writeRaw($model->getValue());
            $tw->closeSpan();
        }
        
        self::closeFieldLayout($model, $view);
    }
    
    /**
     * Renders a TextBlock
     *
     * @param TextBlock $model
     * @param HtmlFormView $view
     */
    public static function renderTextBlock(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openSpan();
        $tw->addAttribute('class', $view->getCssClass() . '-textblock');
        
        if ($model->getHtmlEncode() === true)
            $tw->writeRaw(nl2br(htmlentities($model->getText())));
        else
            $tw->writeRaw($model->getText());
        
        $tw->closeSpan();
    }
    
    /**
     * Renders a TabContainer
     *
     * @param TabContainer $model
     * @param HtmlFormView $view
     */
    public static function renderTabContainer(&$model, &$view)
    {
        if ($model->getVisible() === false)
            return;
        
        $tw = HtmlWriter::getInstance();
        
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('class', $view->getCssClass() . '-tabview');
        
        $tw->openInput();
        $tw->addAttribute('type', 'hidden');
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . '_activeTabPage');
        $tw->addAttribute('name', $model->getActiveTabPagePersistor()->getName());
        $tw->addAttribute('value', $model->getActiveTabPagePersistor()->getValue());
        $tw->closeInput();
        
        $tw->openUl();
        $tw->addAttribute('class', $view->getCssClass() . '-tabtitle-container');
        
        foreach ($model->getTabPageNames() as $tabName)
        {
            $tw->openLi();
            $tw->addAttribute('container', $tabName);
            $tw->addAttribute('class', $view->getCssClass() . '-tabtitle');
            $tw->writeContent($model->getContainer($tabName)->getCaption());
            $tw->closeLi();
        }
        
        $tw->closeUl();
        
        self::renderFieldContainerChildren($model, $view);
        
        $tw->closeDiv(true);
    }
    
    /**
     * Renders a TabPage
     *
     * @param TabPage $model
     * @param HtmlFormView $view
     */
    public static function renderTabPage(&$model, &$view)
    {
        if ($model->getVisible() === false)
            return;
        
        $tw = HtmlWriter::getInstance();
        
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('class', $view->getCssClass() . '-tabpage');
        
        self::renderFieldContainerChildren($model, $view);
        
        $tw->closeDiv(true);
    }
    
    /**
     * Renders a MonthYearComboBox
     *
     * @param MonthYearComboBox $model
     * @param HtmlFormView $view
     */
    public static function renderMonthYearComboBox(&$model, &$view)
    {
        if ($model->getVisible() === false)
            return;
        
        $tw = HtmlWriter::getInstance();
        
        self::openFieldLayout($model, $view);
        
        $children = $model->getChildren()->getControlNames(true);
        
        $first = true;
        foreach ($children as $controlName)
        {
            $currentControl = $model->getChildren()->getControl($controlName, true);
            
            if ($currentControl->getVisible() === false)
                continue;
            
            $currentSelect = $first ? 'month' : 'year';

            $tw->openSelect();
            $tw->addAttribute('id', 'select_' . $currentControl->getName());
            $tw->addAttribute('name', $currentControl->getName());
            $tw->addAttribute('class', $view->getCssClass() . '-select ' . $view->getCssClass() . '-select-' . $currentSelect);
            
            foreach ($currentControl->getOptions() as $option)
            {
                $tw->openOption();
                $tw->addAttribute('value', $option['value']);
                
                if ($option['value'] == $currentControl->getValue())
                    $tw->addAttribute('selected', 'selected');
                
                $tw->writeContent($option['display'], false, false, false);
                $tw->closeOption();
            }
            
            $tw->closeSelect();
            
            $first = false;
        }
        
        self::closeFieldLayout($model, $view);
    }
    
    /**
     * Renders the multiselector control
     *
     * @param MultiSelector $model
     * @param HtmlFormView $view
     */
    public static function renderMultiSelector(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openDiv();
        $tw->addAttribute('id', 'multiselector_' . $model->getName());
        $tw->addAttribute('class', $view->getCssClass() . '-multiselector');
        
        $tw->openInput();
        $tw->addAttribute("type", "hidden");
        $tw->addAttribute("name", $model->getName());
        $tw->addAttribute('id', 'multiselector_' . $model->getName() . '_value');
        $tw->addAttribute('class', $view->getCssClass() . '-multiselector-input');
        $tw->addAttribute('value', $model->getChildren()->getControl($model->getName() . '_items', false)->getValue());
        $tw->closeInput(false);
        
        $tw->openTable();
        $tw->addAttribute('class', $view->getCssClass() . '-multiselector-table');
        $tw->openTr();
        $tw->openTd();
        $tw->addAttribute('class', $view->getCssClass() . '-multiselector-selectcontainer');

        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-multiselector-select-title');
        $tw->writeContent(Resources::getValue(Resources::SRK_MULTISELECTOR_AVAILABLE));
        
        $tw->closeDiv();
        
        $tw->openDiv();
        $tw->addAttribute('id', 'multiselector_' . $model->getName() . 'Available');
        $tw->addAttribute('class', $view->getCssClass() . '-multiselector-select');
        
        foreach ($model->getDataSource() as $key => $value)
        {
            if ($model->getValue()->containsValue($key) === false)
            {
                $tw->openDiv();
                $tw->addAttribute('class', $view->getCssClass() . '-multiselector-option');
                $tw->addAttribute('optionValue', $key);
                $tw->writeContent($value);
                $tw->closeDiv();
            }
        }
        
        $tw->closeDiv();
        $tw->closeTd();
        
        $tw->openTd();
        $tw->addAttribute('class', $view->getCssClass() . '-multiselector-buttoncontainer');
        
        $tw->openDiv();
        $tw->openA();
        $tw->addAttribute('id', 'multiselector_' . $model->getName() . 'ButtonAdd');
        $tw->addAttribute('class', $view->getMasterCssClass() . '-button ' . $view->getCssClass() . '-button');
        $tw->addAttribute('eventname', '~');
        $tw->writeContent('>');
        $tw->closeA();
        $tw->closeDiv();
        
        $tw->openDiv();
        $tw->openA();
        $tw->addAttribute('id', 'multiselector_' . $model->getName() . 'ButtonRemove');
        $tw->addAttribute('class', $view->getMasterCssClass() . '-button ' . $view->getCssClass() . '-button');
        $tw->addAttribute('eventname', '~');
        $tw->writeContent('<');
        $tw->closeA();
        $tw->closeDiv();
        
        $tw->closeTd();
        
        $tw->openTd();
        $tw->addAttribute('class', $view->getCssClass() . '-multiselector-selectcontainer');
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-multiselector-select-title');
        $tw->writeContent(Resources::getValue(Resources::SRK_MULTISELECTOR_SELECTED));
        $tw->closeDiv();
        
        $tw->openDiv();
        $tw->addAttribute('id', 'multiselector_' . $model->getName() . 'Selected');
        $tw->addAttribute('class', $view->getCssClass() . '-multiselector-select');
        
        foreach ($model->getDataSource() as $key => $value)
        {
            if ($model->getValue()->containsValue($key) === true)
            {
                $tw->openDiv();
                $tw->addAttribute('class', $view->getCssClass() . '-multiselector-option');
                $tw->addAttribute('optionValue', $key);
                $tw->writeContent($value);
                $tw->closeDiv();
            }
        }
        
        $tw->closeDiv();
        $tw->closeTd();
        $tw->closeTr();
        $tw->closeTable();
        
        $tw->closeDiv();
        
        $tw->openScript();
        $tw->addAttribute('defer', 'defer');
        $tw->addAttribute('type', 'text/javascript');
        $tw->writeRaw("window.addEvent('domready',function() { new Multiselector('multiselector_" . $model->getName() . "','" . $view->getCssClass() . "'); } );");
        $tw->closeScript();
    }
}
?>