<?php
require_once "webcore.view.form.php";

/**
 * Provides a standard Calendar object renderer.
 * Event managers and columns are rendered by using callbacks.
 * Requires std.calendarview.js and std.calendarview.css
 * Add, remove or modify callbacks using the renderCallbacks KeyedCollection
 *
 * @package WebCore
 * @subpackage View
 */
class HtmlCalendarView extends HtmlFormView
{
    /**
     * Creates an instance of this class
     *
     * @param Calendar $model
     */
    public function __construct(&$model)
    {
        parent::__construct($model);
        $this->cssClass       = 'calendarview';
        $this->isAsynchronous = true;
        $this->frameWidth     = 'auto';
        
        $callbacks =& $this->renderCallbacks->getArrayReference();
        
        $callbacks['Calendar']    = array(
            'HtmlCalendarRenderCallbacks',
            'renderCalendar'
        );
        $callbacks['Appointment'] = array(
            'HtmlCalendarRenderCallbacks',
            'renderAppointment'
        );
    }
    
    protected function registerDependencies()
    {
        self::registerCommonDependecies();
        $rootPath = HttpContext::getLibraryRoot();
        
        $cssPath = $rootPath . 'css/std.calendarview.css';
        $jsPath  = $rootPath . 'js/std.calendarview.js';
        
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_CSS_FILE, __CLASS__, 'HtmlCalendarView.Css', $cssPath);
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlCalendarView.Js', $jsPath);
    }
}

/**
 * Contains static callback methods to render standard framework HTML controls in a Calendar
 *
 * @package WebCore
 * @subpackage View
 */
class HtmlCalendarRenderCallbacks extends HtmlFormRenderCallbacks
{
    /**
     * Renders the calendar container and form
     *
     * @param Calendar $model
     * @param HtmlCalendarView $view
     */
    public static function renderCalendar(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        $tw->openForm();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('name', $model->getName());
        $tw->addAttribute('class', $view->getCssClass());
        $tw->addAttribute('method', 'post');
        $tw->addAttribute('action', HttpContext::getInfo()->getScriptVirtualPath());
        
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
        
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . 'Container');
        $tw->addAttribute('class', $view->getCssClass() . '-container');
        
        self::renderControls($model, $view);
        
        self::renderMonthTable($model, $view);
        
        $tw->openScript();
        $tw->addAttribute('type', 'text/javascript');
        $tw->writeRaw('window.addEvent("domready", function(){');
        $tw->writeRaw('new Calendar("' . HtmlViewBase::getHtmlId($model) . '","' . $view->getCssClass() . '", true, false, undefined,');
        $tw->writeRaw('{ initialDate: Date.parse("' . $model->getInitialDate("Y-m-d") . '"), viewMode:"' . $model->getViewMode() . '" }');
        $tw->writeRaw(');');
        $tw->writeRaw('});');
        $tw->closeScript();
        
        $tw->closeDiv();
        
        self::renderPostBackFlag($model, $view);
        self::renderPersistor($model->getInitialDatePersistor(), $view);
        self::renderPersistor($model->getViewModePersistor(), $view);
        $tw->closeDiv();
        $tw->closeForm();
    }
    
    /**
     * Renders the Navigation and View Change controls for the calendar
     *
     * @param Calendar $model
     * @param HtmlCalendarView $view
     */
    protected static function renderControls(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . 'Controls');
        $tw->addAttribute('class', $view->getCssClass() . '-controls');
        
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . 'CalendarNavigation');
        $tw->addAttribute('class', $view->getCssClass() . '-nav');
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . 'Previous');
        $tw->addAttribute('class', $view->getCssClass() . '-button ' . $view->getCssClass() . '-button-previous');
        $tw->addAttribute('title', Resources::getValue(Resources::SRK_CALENDAR_NAV_PREVIOUS));
        $tw->closeDiv(true);
        
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . 'Next');
        $tw->addAttribute('class', $view->getCssClass() . '-button ' . $view->getCssClass() . '-button-next');
        $tw->addAttribute('title', Resources::getValue(Resources::SRK_CALENDAR_NAV_NEXT));
        $tw->closeDiv(true);
        
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . 'Today');
        $tw->addAttribute('class', $view->getCssClass() . '-button ' . $view->getCssClass() . '-button-today');
        $tw->addAttribute('title', Resources::getValue(Resources::SRK_CALENDAR_NAV_TODAY));
        $tw->closeDiv(true);
        $tw->closeDiv();
        
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . 'Title');
        $tw->addAttribute('class', $view->getCssClass() . '-title');
        $tw->writeContent($model->getInitialDate('F, Y'));
        $tw->closeDiv();
        
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . 'Views');
        $tw->addAttribute('class', $view->getCssClass() . '-views');
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . 'ViewDay');
        $tw->addAttribute('class', $view->getCssClass() . '-button ' . $view->getCssClass() . '-view-day');
        $tw->addAttribute('title', Resources::getValue(Resources::SRK_CALENDAR_VIEW_DAY));
        $tw->closeDiv(true);
        
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . 'ViewWeek');
        $tw->addAttribute('class', $view->getCssClass() . '-button ' . $view->getCssClass() . '-view-week');
        $tw->addAttribute('title', Resources::getValue(Resources::SRK_CALENDAR_VIEW_WEEK));
        $tw->closeDiv(true);
        
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . 'ViewMonth');
        $tw->addAttribute('class', $view->getCssClass() . '-button ' . $view->getCssClass() . '-view-month');
        $tw->addAttribute('title', Resources::getValue(Resources::SRK_CALENDAR_VIEW_MONTH));
        $tw->closeDiv(true);
        $tw->closeDiv();
        
        $tw->closeDiv();
    }
    
    /**
     * Renders the Month View for the calendar
     *
     * @param Calendar $model
     * @param HtmlCalendarView $view
     */
    protected static function renderMonthTable(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openTable();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . 'Table');
        $tw->addAttribute('class', $view->getCssClass() . '-table');
        $tw->addAttribute('cellpadding', '0');
        $tw->addAttribute('cellspacing', '0');
        
        self::renderColumns($model, $view);
        self::renderHeaders($model, $view);
        self::renderBody($model, $view);
        
        $tw->closeTable();
    }
    
    /**
     * Renders the columns. These are used to set the different wid
     *
     * @param Calendar $model
     * @param HtmlCalendarView $view
     */
    protected static function renderColumns(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openCol();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . 'Col-1');
        $tw->addAttribute('column', '0');
        $tw->closeCol(true);
        
        for ($i = 0; $i < 7; $i++)
        {
            $tw->openCol();
            $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . 'Col' . $i);
            $tw->addAttribute('class', $view->getCssClass() . '-column');
            $tw->addAttribute('column', $i + 1);
            $tw->closeCol(true);
        }
    }
    
    /**
     * Renders the day headers for the calendar
     *
     * @param Calendar $model
     * @param HtmlCalendarView $view
     */
    protected static function renderHeaders(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openThead();
        $tw->openTr();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . 'Headers');
        
        $tw->openTh();
        $tw->addAttribute('class', $view->getCssClass() . '-week-header');
        $tw->closeTh(true);
        
        $weekdays = explode(",", Resources::getValue(Resources::SRK_DAY_NAMES));
        for ($i = 0; $i < 7; $i++)
        {
            $tw->openTh();
            $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . 'Header' . $i);
            $tw->addAttribute('class', $view->getCssClass() . '-column-header');
            $tw->writeContent($weekdays[$i]);
            $tw->closeTh();
        }
        
        $tw->closeTr();
        $tw->closeThead();
    }
    
    /**
     * Renders the days of the calendar
     *
     * @param Calendar $model
     * @param HtmlCalendarView $view
     */
    protected static function renderBody(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $initialDate        = $model->getInitialDate();
        $currentCalendarDay = $model->getFirstDay();
        $endCalendarAt      = $model->getLastDay();
        
        $month       = $model->getMonth();
        $currentWeek = date('W', $currentCalendarDay);
        $today       = strtotime(date('Y-m-d'));
        
        $columnNumber = 0;
        
        $appointmentsPerDay = new KeyedCollection(); // KeyedCollection of IndexedCollections
        
        /**
         * @var Appointment
         */
        $appointment = null;
        
        foreach ($model->getAppointments() as $appointment)
        {
            $appointmentStartDate = $appointment->getStartDate('Y-m-d');
            if ($appointmentsPerDay->keyExists($appointmentStartDate) === false)
                $appointmentsPerDay->setValue($appointmentStartDate, new IndexedCollection());
            
            $appointmentsPerDay->getItem($appointmentStartDate)->addItem($appointment);
        }
        
        $tw->openTbody();
        
        while ($currentCalendarDay <= $endCalendarAt)
        {
            $tdCssClasses     = array();
            $headerCssClasses = array();
            
            $currentCalendarDayMonthDay  = date('j', $currentCalendarDay);
            $currentCalendarDayMonth     = date('n', $currentCalendarDay);
            $currentCalendarDayFormatted = date('Y-m-d', $currentCalendarDay);
            
            $isToday       = ($currentCalendarDay == $today);
            $isCurrentDate = ($currentCalendarDay == $initialDate);
            $isSameMonth   = ($month == $currentCalendarDayMonth);
            
            if ($columnNumber == 0)
            {
                $tw->openTr();
                $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . 'Week' . $currentWeek);
                $tw->addAttribute('class', $view->getCssClass() . '-week');
                
                $tw->openTd();
                $tw->addAttribute('class', $view->getCssClass() . '-week-value');
                $tw->writeContent($currentWeek);
                $tw->closeTd();
                
                ++$currentWeek;
            }
            
            $headerCssClasses[] = $view->getCssClass() . '-day-header';
            $tdCssClasses[]     = $view->getCssClass() . '-day';
            
            if ($isToday)
                $headerCssClasses[] = $view->getCssClass() . '-day-header-today';
            if ($isCurrentDate)
                $tdCssClasses[] = $view->getCssClass() . '-day-selected';
            if ($isSameMonth === false)
            {
                $tdCssClasses[]     = $view->getCssClass() . '-day-offmonth';
                $headerCssClasses[] = $view->getCssClass() . '-day-header-offmonth';
            }
            
            $tw->openTd();
            $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . 'Day' . $currentCalendarDayFormatted);
            $tw->addAttribute('class', implode(' ', $tdCssClasses));
            $tw->addAttribute('date', $currentCalendarDayFormatted);
            $tw->addAttribute('column', $columnNumber);
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-day-container');
            
            $tw->openDiv();
            $tw->addAttribute('class', implode(' ', $headerCssClasses));
            $tw->writeContent($currentCalendarDayMonthDay);
            
            $tw->closeDiv();
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-day-content');
            
            if ($appointmentsPerDay->keyExists($currentCalendarDayFormatted))
            {
                foreach ($appointmentsPerDay->getValue($currentCalendarDayFormatted) as $appointment)
                {
                    self::renderAppointment($appointment, $view);
                }
            }
            
            $tw->closeDiv();
            $tw->closeDiv();
            $tw->closeTd();
            
            if ($columnNumber == 6)
                $tw->closeTr();
            
            $currentCalendarDay = strtotime('+1 day', $currentCalendarDay);
            $columnNumber       = ($columnNumber + 1) % 7;
        }
        
        $tw->closeTbody();
    }
    
    /**
     * Renders an appointment
     *
     * @param Appointment $model
     * @param HtmlCalendarView $view
     */
    public static function renderAppointment(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-appointment');
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-appointment-header');
        $tw->addAttribute('tooltip', $model->getDescription() === '' ? Resources::getValue(Resources::SRK_APPOINTMENT_TOOLTIP_EMPTY) : '');
        $tw->writeContent($model->getTitle(), false, true, false);
        $tw->closeDiv();
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-appointment-content');
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-appointment-description');
        
        $tw->writeContent($model->getDescription(), false, true, false);
        $tw->closeDiv();
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-appointment-fields');
        
        $tw->openUl();
        
        foreach ($model->getAdditionalFields() as $key => $value)
        {
            $tw->openLi();
            $tw->openSpan();
            $tw->addAttribute('class', $view->getCssClass() . '-appointment-fields-title');
            $tw->writeContent($key . ':');
            $tw->closeSpan();
            
            $tw->openSpan();
            $tw->addAttribute('class', $view->getCssClass() . '-appointment-field-description');
            $tw->writeContent($value, false, true, false);
            $tw->closeSpan();
            $tw->closeLi();
        }
        
        $tw->closeUl();
        $tw->closeDiv();
        
        $tw->closeDiv();
        $tw->closeDiv();
    }
}
?>