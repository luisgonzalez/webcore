<?php
/**
 * @package WebCore
 * @subpackage Globalization
 * @version 1.0
 * 
 * Contains classes that facilitate the culture-related information.
 * 
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

/**
 * Abstract implementation to facilitate culture information.
 * @todo This should be implemented as a helper class to access RESOURCES and NOT settings!
 * @package WebCore
 * @subpackage Globalization
 */
class CultureInfo extends ObjectBase
{
    const RESOURCES_DIR = 'resources/';
    const DICT_DATETIME = 'dateTimeFormat';
    const DICT_NUMBER = 'numberFormat';
    
    const CULTURE_ENUS = "en-US";
    const CULTURE_ESMX = "es-MX";

    const DEFAULTFILENAME = 'cultureInfo.xml';

    // SRK stands for Static Resource Key
    const SRK_FIELD_REQUIRED = 'webcore.field.error.required';
    const SRK_FIELD_BADEMAIL = 'webcore.field.error.bademail';
    const SRK_FIELD_BADNUMBER = 'webcore.field.error.badnumber';
    const SRK_FIELD_BADPHONENUMBER = 'webcore.field.error.badphone';
    const SRK_FIELD_BADDATE = 'webcore.field.error.baddate';
    const SRK_FIELD_MAXIMUM = 'webcore.field.error.maximum';
    const SRK_FIELD_MINIMUM = 'webcore.field.error.minimum';
    const SRK_FIELD_NODECIMALS = 'webcore.field.error.nodecimals';
    const SRK_FIELD_DOWNLOAD = 'webcore.field.download';
    const SRK_FORM_ERRORTITLE = 'webcore.form.error.title';
    const SRK_FORM_ERRORGENERIC = 'webcore.form.error.text';
    const SRK_FORM_ERRORCONTINUE = 'webcore.form.error.continue';
    const SRK_FORM_INFOTITLE = 'webcore.form.info.title';
    const SRK_FORM_INFOCONTINUE = 'webcore.form.info.continue';
    const SRK_UPLOAD_ERR_OK = 'webcore.postedfile.error.ok';
    const SRK_UPLOAD_ERR_CANT_WRITE = 'webcore.postedfile.error.cantwrite';
    const SRK_UPLOAD_ERR_EXTENSION = 'webcore.postedfile.error.extension';
    const SRK_UPLOAD_ERR_FORM_SIZE = 'webcore.postedfile.error.formsize';
    const SRK_UPLOAD_ERR_INI_SIZE = 'webcore.postedfile.error.inisize';
    const SRK_UPLOAD_ERR_NO_FILE = 'webcore.postedfile.error.nofile';
    const SRK_UPLOAD_ERR_NO_TMP_DIR = 'webcore.postedfile.error.notmpdir';
    const SRK_UPLOAD_ERR_PARTIAL = 'webcore.postedfile.error.partial';
    const SRK_MEMBERSHIP_ERR_LOGIN = 'webcore.membership.error.login';
    const SRK_MONTH_NAMES = 'webcore.monthnames';
    const SRK_MONTH_SHORTNAMES = 'webcore.monthshortnames';
    const SRK_DAY_NAMES = 'webcore.daynames';
    const SRK_DAY_SHORTNAMES = 'webcore.dayshortnames';
    const SRK_LOGIN_FORM_CAPTION = 'webcore.login.form.caption';
    const SRK_LOGIN_USER_CAPTION = 'webcore.login.user.caption';
    const SRK_LOGIN_USER_TOOLTIP = 'webcore.login.user.tooltip';
    const SRK_LOGIN_PASSWORD_CAPTION = 'webcore.login.password.caption';
    const SRK_LOGIN_PASSWORD_TOOLTIP = 'webcore.login.password.tooltip';
    const SRK_LOGIN_BUTTON_CAPTION = 'webcore.login.button.caption';
    const SRK_GRID_PAGINATOR_FOOTER = 'webcore.grid.paginator.footer';
    const SRK_GRID_TOOLBAR_SEARCH = 'webcore.grid.toolbar.search';
    const SRK_GRID_TOOLBAR_FILTER = 'webcore.grid.toolbar.filter';
    const SRK_GRID_TOOLBAR_GROUP = 'webcore.grid.toolbar.group';
    const SRK_GRID_TOOLBAR_TOOLS = 'webcore.grid.toolbar.tools';
    const SRK_REPEATER_PAGER_FIRST = 'webcore.grid.pager.first';
    const SRK_REPEATER_PAGER_PREV = 'webcore.grid.pager.prev';
    const SRK_REPEATER_PAGER_NEXT = 'webcore.grid.pager.next';
    const SRK_REPEATER_PAGER_LAST = 'webcore.grid.pager.last';
    const SRK_GRID_PAGER_PAGE = 'webcore.grid.pager.page';
    const SRK_REPEATER_PAGER_RECORDS = 'webcore.grid.pager.records';
    const SRK_REPEATER_PAGER_NORECORDS = 'webcore.grid.pager.norecords';
    const SRK_GRID_PAGER_OFPAGE = 'webcore.grid.pager.ofpage';
    const SRK_GRID_COMMAND_VIEW = 'webcore.grid.command.view';
    const SRK_GRID_COMMAND_EDIT = 'webcore.grid.command.edit';
    const SRK_GRID_COMMAND_DELETE = 'webcore.grid.command.delete';
    const SRK_GRID_COMMAND_SELECT = 'webcore.grid.command.select';
    const SRK_GRID_NOFILTER = 'webcore.grid.filtering.nofilter';
    const SRK_GRID_NOSEARCH = 'webcore.grid.searching.nosearch';
    const SRK_GRID_ACTION_PRINT = 'webcore.grid.actions.print';
    const SRK_CAPTION_DATE = 'webcore.caption.date';
    const SRK_CAPTION_TIME = 'webcore.caption.time';
    const SRK_CAPTION_HOUR = 'webcore.caption.hour';
    const SRK_CAPTION_PASSWORD = 'webcore.caption.password';
    const SRK_CAPTION_EMAIL = 'webcore.caption.email';
    const SRK_CAPTION_ADD = 'webcore.caption.add';
    const SRK_CAPTION_DELETE = 'webcore.caption.delete';
    const SRK_CAPTION_SAVE = 'webcore.caption.save';
    const SRK_CAPTION_CANCEL = 'webcore.caption.cancel';
    const SRK_CAPTION_EDIT = 'webcore.caption.edit';
    const SRK_CONFIRM_DELETE = 'webcore.confirm.delete';
    const SRK_OPER_STARTSWITH = 'webcore.grid.searchdialog.startswith';
    const SRK_OPER_ENDSWITH = 'webcore.grid.searchdialog.endswith';
    const SRK_OPER_CONTAINS = 'webcore.grid.searchdialog.contains';
    const SRK_OPER_EQUALS = 'webcore.grid.searchdialog.equals';
    const SRK_OPER_BETWEEN = 'webcore.grid.searchdialog.between';
    const SRK_SEARCH_DIALOG_CAPTION = 'webcore.grid.searchdialog.caption';
    const SRK_SEARCH_DIALOG_MSG = 'webcore.grid.searchdialog.message';
    const SRK_SEARCH_DIALOG_OK = 'webcore.grid.searchdialog.okbutton';
    const SRK_SEARCH_DIALOG_CANCEL = 'webcore.grid.searchdialog.cancelbutton';
    const SRK_PRINTVIEW_PRINT = 'webcore.printview.printbutton';
    const SRK_PRINTVIEW_TITLE = 'webcore.printview.pagetitle';
    const SRK_PRINTVIEW_SUBTITLE = 'webcore.printview.subtitle';
    const SRK_PRINTVIEW_CSVEXPORT = 'webcore.printview.csvexportbutton';
    const SRK_PRINTVIEW_BIFFEXPORT = 'webcore.printview.biffexportbutton';
    const SRK_PRINTVIEW_OXMLEXPORT = 'webcore.printview.oxmlexportbutton';
    const SRK_PRINTVIEW_PDFEXPORT = 'webcore.printview.pdfexportbutton';
    const SRK_CALENDAR_NAV_PREVIOUS = 'webcore.calendar.navprevious';
    const SRK_CALENDAR_NAV_NEXT = 'webcore.calendar.navnext';
    const SRK_CALENDAR_NAV_TODAY = 'webcore.calendar.navtoday';
    const SRK_CALENDAR_VIEW_DAY = 'webcore.calendar.viewday';
    const SRK_CALENDAR_VIEW_WEEK = 'webcore.calendar.viewweek';
    const SRK_CALENDAR_VIEW_MONTH = 'webcore.calendar.viewmonth';
    const SRK_APPOINTMENT_TOOLTIP_EMPTY = 'webcore.appointment.tooltipempty';
    const SRK_MULTISELECTOR_AVAILABLE = 'webcore.multiselector.available';
    const SRK_MULTISELECTOR_SELECTED = 'webcore.multiselector.selected';

    private static $frameworkResources = array(
        CultureInfo::CULTURE_ENUS => array(
            self::SRK_FIELD_REQUIRED => 'This field is required.',
            self::SRK_FORM_ERRORTITLE => 'Error.',
            self::SRK_FORM_INFOTITLE => 'Information.',
            self::SRK_FORM_ERRORGENERIC => 'You must correct the errors in the marked fields in order to continue.',
            self::SRK_FORM_ERRORCONTINUE => 'OK',
            self::SRK_FORM_INFOCONTINUE => 'Continue',
            self::SRK_UPLOAD_ERR_OK => 'The file was uploaded successufuly.',
            self::SRK_UPLOAD_ERR_CANT_WRITE => 'Error writing the file to the server. Insufficient permissions.',
            self::SRK_UPLOAD_ERR_EXTENSION => 'The extension of the uploaded file is unacceptable.',
            self::SRK_UPLOAD_ERR_FORM_SIZE => 'The file you provided is larger than the allowed limit.',
            self::SRK_UPLOAD_ERR_INI_SIZE => 'The file you provided is larger than the allowed limit.',
            self::SRK_UPLOAD_ERR_NO_FILE => 'You did not provide a valid file to upload.',
            self::SRK_UPLOAD_ERR_NO_TMP_DIR => 'The server is having technical difficulties. Try again later.',
            self::SRK_UPLOAD_ERR_PARTIAL => 'The file was partially uploaded. Please try again.',
            self::SRK_FIELD_BADEMAIL => 'The email address is not in the correct format.',
            self::SRK_FIELD_BADNUMBER => 'The number format could not be recognized.',
            self::SRK_FIELD_BADDATE => 'The date is not recognized. Please specify the date in the format: YYYY-MM-DD',
            self::SRK_FIELD_MAXIMUM => 'The number is out of the allowed range. The maximum value is ',
            self::SRK_FIELD_MINIMUM => 'The number is out of the allowed range. The minimum value is ',
            self::SRK_FIELD_NODECIMALS => 'The number must be an integer. No decimals are allowed.',
            self::SRK_FIELD_BADPHONENUMBER => 'The phone number you provided is invalid.',
            self::SRK_MEMBERSHIP_ERR_LOGIN => 'Invalid username or password.',
            self::SRK_MONTH_NAMES => 'January,February,March,April,May,June,July,August,September,October,November,December',
            self::SRK_MONTH_SHORTNAMES => 'JAN,FEB,MAR,APR,MAY,JUN,JUL,AUG,SEP,OCT,NOV,DEC',
            self::SRK_DAY_NAMES => 'Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            self::SRK_DAY_SHORTNAMES => 'Sun,Mon,Tue,Wed,Thr,Fri,Sat',
            self::SRK_LOGIN_FORM_CAPTION => 'Sign In',
            self::SRK_LOGIN_USER_CAPTION => 'Username',
            self::SRK_LOGIN_USER_TOOLTIP => 'Your username',
            self::SRK_LOGIN_PASSWORD_CAPTION => 'Password',
            self::SRK_LOGIN_PASSWORD_TOOLTIP => 'Your password',
            self::SRK_LOGIN_BUTTON_CAPTION => 'Sign In',
            self::SRK_GRID_PAGINATOR_FOOTER => 'Page <b>%s</b> of <b>%s</b>',
            self::SRK_FIELD_DOWNLOAD => 'Download file',
            self::SRK_GRID_TOOLBAR_TOOLS => 'Tools',
            self::SRK_GRID_TOOLBAR_SEARCH => 'Search',
            self::SRK_GRID_TOOLBAR_GROUP => 'Group',
            self::SRK_GRID_TOOLBAR_FILTER => 'Filter',
            self::SRK_REPEATER_PAGER_FIRST => 'First Page',
            self::SRK_REPEATER_PAGER_LAST => 'Last Page',
            self::SRK_REPEATER_PAGER_PREV => 'Previous Page',
            self::SRK_REPEATER_PAGER_NEXT => 'Next Page',
            self::SRK_GRID_PAGER_PAGE => 'Page ',
            self::SRK_REPEATER_PAGER_RECORDS => 'Displaying records <b>%s</b> to <b>%s</b> of <b>%s</b>',
            self::SRK_REPEATER_PAGER_NORECORDS => 'There are no records under this view.',
            self::SRK_GRID_PAGER_OFPAGE => ' of ',
            self::SRK_GRID_COMMAND_VIEW => 'View Details',
            self::SRK_GRID_COMMAND_EDIT => 'Edit Item',
            self::SRK_GRID_COMMAND_DELETE => 'Delete Item',
            self::SRK_GRID_COMMAND_SELECT => 'Select',
            self::SRK_GRID_NOFILTER => '(No quick filter)',
            self::SRK_GRID_NOSEARCH => '(No search filter)',
            self::SRK_GRID_ACTION_PRINT => 'Print Preview ...',
            self::SRK_CAPTION_DATE => 'date',
            self::SRK_CAPTION_TIME => 'time',
            self::SRK_CAPTION_HOUR => 'hour',
            self::SRK_CAPTION_PASSWORD => 'password',
            self::SRK_CAPTION_EMAIL => 'email',
            self::SRK_CAPTION_ADD => 'Add',
            self::SRK_CAPTION_DELETE => 'Delete',
            self::SRK_CAPTION_SAVE => 'Save',
            self::SRK_CAPTION_CANCEL => 'Cancel',
            self::SRK_CAPTION_EDIT => 'Edit',
            self::SRK_CONFIRM_DELETE => 'Are you sure you want to delete this entry permanently?',
            self::SRK_OPER_STARTSWITH => 'starts with',
            self::SRK_OPER_ENDSWITH => 'ends with',
            self::SRK_OPER_CONTAINS => 'contains',
            self::SRK_OPER_EQUALS => 'equals',
            self::SRK_OPER_BETWEEN => 'between',
            self::SRK_SEARCH_DIALOG_CAPTION => 'Search',
            self::SRK_SEARCH_DIALOG_MSG => 'Enter the search criteria below.',
            self::SRK_SEARCH_DIALOG_OK => 'OK',
            self::SRK_SEARCH_DIALOG_CANCEL => 'Cancel',
            self::SRK_PRINTVIEW_PRINT => 'Send to Printer',
            self::SRK_PRINTVIEW_TITLE => 'Print Preview - ',
            self::SRK_PRINTVIEW_SUBTITLE => 'Data as of ',
            self::SRK_PRINTVIEW_CSVEXPORT => 'Export as CSV',
            self::SRK_PRINTVIEW_BIFFEXPORT => 'Export as Excel 97-2003',
            self::SRK_PRINTVIEW_OXMLEXPORT => 'Export as Excel 2007',
            self::SRK_PRINTVIEW_PDFEXPORT => 'Export as PDF',
            self::SRK_CALENDAR_NAV_PREVIOUS => 'Previous',
            self::SRK_CALENDAR_NAV_NEXT => 'Next',
            self::SRK_CALENDAR_NAV_TODAY => 'Today',
            self::SRK_CALENDAR_VIEW_DAY => 'Day View',
            self::SRK_CALENDAR_VIEW_WEEK => 'Week View',
            self::SRK_CALENDAR_VIEW_MONTH => 'Month View',
            self::SRK_APPOINTMENT_TOOLTIP_EMPTY => 'Double click to expand...',
            self::SRK_MULTISELECTOR_AVAILABLE => 'Available',
            self::SRK_MULTISELECTOR_SELECTED => 'Selected'),

        CultureInfo::CULTURE_ESMX => array(
            self::SRK_FIELD_REQUIRED => 'Este campo es obligatorio.',
            self::SRK_FORM_ERRORTITLE => 'Error.',
            self::SRK_FORM_INFOTITLE => 'Información.',
            self::SRK_FORM_ERRORGENERIC => 'Existen errores en la captura. Corrija los campos marcados con error para continuar.',
            self::SRK_FORM_ERRORCONTINUE => 'Aceptar',
            self::SRK_FORM_INFOCONTINUE => 'Continuar',
            self::SRK_UPLOAD_ERR_OK => 'El archivo fue cargado existosamente.',
            self::SRK_UPLOAD_ERR_CANT_WRITE => 'Privilegios insuficientes al guardar el archivo en el servidor.',
            self::SRK_UPLOAD_ERR_EXTENSION => 'La extensión del archivo cargado es inaceptable.',
            self::SRK_UPLOAD_ERR_FORM_SIZE => 'El tamaño del archivo proporcionado excede el límite permitido.',
            self::SRK_UPLOAD_ERR_INI_SIZE => 'El tamaño del archivo proporcionado excede el límite permitido.',
            self::SRK_UPLOAD_ERR_NO_FILE => 'No proporcionó un archivo a cargar válido.',
            self::SRK_UPLOAD_ERR_NO_TMP_DIR => 'El servidor tiene dificultades técnicas por el momento. Intente más tarde.',
            self::SRK_UPLOAD_ERR_PARTIAL => 'El archivo fue cargado parcialmente. Intente de nuevo.',
            self::SRK_FIELD_BADEMAIL => 'Proporcione un correo electrónico en un formato válido',
            self::SRK_FIELD_BADNUMBER => 'El número que proporcionó se encuentra en un formato irreconocible.',
            self::SRK_FIELD_BADDATE => 'La fecha no es reconocida. Especifique la fecha en el formato: AAAA-MM-DD',
            self::SRK_FIELD_MAXIMUM => 'El número que proporcionó es muy grande. El máximo es ',
            self::SRK_FIELD_MINIMUM => 'El número que proporcionó es muy pequeño. El mínimo es ',
            self::SRK_FIELD_NODECIMALS => 'El número debe ser entero. No proporcione decimales.',
            self::SRK_FIELD_BADPHONENUMBER => 'El número telefónico que proporcionó es inválido.',
            self::SRK_MEMBERSHIP_ERR_LOGIN => 'Usuario o contraseña incorrecta.',
            self::SRK_MONTH_NAMES => 'Enero,Febrero,Marzo,Abril,Mayo,Junio,Julio,Agosto,Septiembre,Octubre,Noviembre,Diciembre',
            self::SRK_MONTH_SHORTNAMES => 'ENE,FEB,MAR,ABR,MAY,JUN,JUL,AGO,SEP,OCT,NOV,DIC',
            self::SRK_DAY_NAMES => 'Domingo,Lunes,Martes,Miércoles,Jueves,Viernes,Sábado',
            self::SRK_DAY_SHORTNAMES => 'Dom,Lun,Mar,Mie,Jue,Vie,Sab',
            self::SRK_LOGIN_FORM_CAPTION => 'Inicio de Sesión',
            self::SRK_LOGIN_USER_CAPTION => 'Nombre de Usuario',
            self::SRK_LOGIN_USER_TOOLTIP => 'Su nombre de usuario',
            self::SRK_LOGIN_PASSWORD_CAPTION => 'Contraseña',
            self::SRK_LOGIN_PASSWORD_TOOLTIP => 'Su contraseña',
            self::SRK_LOGIN_BUTTON_CAPTION => 'Entrar',
            self::SRK_GRID_PAGINATOR_FOOTER => 'P&aacute;gina <b>%s</b> de <b>%s</b>',
            self::SRK_FIELD_DOWNLOAD => 'Descargar archivo',
            self::SRK_GRID_TOOLBAR_TOOLS => 'Herramientas',
            self::SRK_GRID_TOOLBAR_SEARCH => 'Buscar',
            self::SRK_GRID_TOOLBAR_GROUP => 'Agrupar',
            self::SRK_GRID_TOOLBAR_FILTER => 'Filtrar',
            self::SRK_REPEATER_PAGER_FIRST => 'Primera Página',
            self::SRK_REPEATER_PAGER_LAST => 'Última Página',
            self::SRK_REPEATER_PAGER_PREV => 'Página Anterior',
            self::SRK_REPEATER_PAGER_NEXT => 'Página Siguiente',
            self::SRK_GRID_PAGER_PAGE => 'Página ',
            self::SRK_GRID_PAGER_OFPAGE => ' de ',
            self::SRK_GRID_COMMAND_VIEW => 'Ver Detalles',
            self::SRK_GRID_COMMAND_EDIT => 'Editar Registro',
            self::SRK_GRID_COMMAND_DELETE => 'Borrar Registro',
            self::SRK_GRID_COMMAND_SELECT => 'Seleccionar',
            self::SRK_GRID_NOFILTER => '(Sin filtro rápido)',
            self::SRK_GRID_NOSEARCH => '(Sin filtro de búsqueda)',
            self::SRK_GRID_ACTION_PRINT => 'Vista de Impresión ...',
            self::SRK_REPEATER_PAGER_RECORDS => 'Registros <b>%s</b> al <b>%s</b> de <b>%s</b>',
            self::SRK_REPEATER_PAGER_NORECORDS => 'No existen registros bajo esta vista.',
            self::SRK_CAPTION_DATE => 'fecha',
            self::SRK_CAPTION_TIME => 'tiempo',
            self::SRK_CAPTION_HOUR => 'hora',
            self::SRK_CAPTION_PASSWORD => 'contraseña',
            self::SRK_CAPTION_EMAIL => 'correo electrónico',
            self::SRK_CAPTION_ADD => 'Agregar',
            self::SRK_CAPTION_DELETE => 'Eliminar',
            self::SRK_CAPTION_SAVE => 'Guardar',
            self::SRK_CAPTION_CANCEL => 'Cancelar',
            self::SRK_CAPTION_EDIT => 'Editar',
            self::SRK_CONFIRM_DELETE => '¿Desea eliminar el registro permanentemente?',
            self::SRK_OPER_STARTSWITH => 'comienza con',
            self::SRK_OPER_ENDSWITH => 'termina con',
            self::SRK_OPER_CONTAINS => 'contiene',
            self::SRK_OPER_EQUALS => 'es igual a',
            self::SRK_OPER_BETWEEN => 'entre',
            self::SRK_SEARCH_DIALOG_CAPTION => 'Búsqueda',
            self::SRK_SEARCH_DIALOG_MSG => 'Introduzca los criterios de búsqueda.',
            self::SRK_SEARCH_DIALOG_OK => 'Aceptar',
            self::SRK_SEARCH_DIALOG_CANCEL => 'Cancelar',
            self::SRK_PRINTVIEW_PRINT => 'Enviar a Impresora',
            self::SRK_PRINTVIEW_TITLE => 'Vista de Impresión - ',
            self::SRK_PRINTVIEW_SUBTITLE => 'Fecha de Generación: ',
            self::SRK_PRINTVIEW_CSVEXPORT => 'Exportar como CSV',
            self::SRK_PRINTVIEW_BIFFEXPORT => 'Exportar a Excel 97-2003',
            self::SRK_PRINTVIEW_OXMLEXPORT => 'Exportar a Excel 2007',
            self::SRK_PRINTVIEW_PDFEXPORT => 'Exportar a PDF',
            self::SRK_CALENDAR_NAV_PREVIOUS => 'Anterior',
            self::SRK_CALENDAR_NAV_NEXT => 'Siguiente',
            self::SRK_CALENDAR_NAV_TODAY => 'Hoy',
            self::SRK_CALENDAR_VIEW_DAY => 'Ver Día',
            self::SRK_CALENDAR_VIEW_WEEK => 'Ver Semana',
            self::SRK_CALENDAR_VIEW_MONTH => 'Ver Mes',
            self::SRK_APPOINTMENT_TOOLTIP_EMPTY => 'Doble clic para expandir...',
            self::SRK_MULTISELECTOR_AVAILABLE => 'Dispnibles',
            self::SRK_MULTISELECTOR_SELECTED => 'Seleccionados'));
    
    /**
     *
     * @var string Holds the path for the language resource
     */
    protected static $storeName;

    /**
     *
     * @var DateTimeFormatInfo holds the reference for the DateTimeFormatInfo Object
     */
    protected static $dateTimeFormatInfo;

    /**
     *
     * @var NumberFormatInfo holds the reference for the NumberFormatInfo Object
     */
    protected static $numberFormatInfo;

    /**
     *
     * @var string This var holds the current culture code
     */
    protected static $cultureCode;

    /**
     *
     * @var array Holds the reference for the current dictionary
     */
    protected static $dictionary;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $culture
     */
    private function __construct()
    {
        
    }
    
    /**
     * Holds the reference for the DateTimeFormatInfo Object
     * @return DateTimeFormatInfo
     */
    public function getDateTimeFormatInfo()
    {
        return self::$dateTimeFormatInfo;
    }

    /**
     * Holds the reference for the DateTimeFormatInfo Object
     * @return DateTimeFormatInfo
     */
    public function getNumberFormatInfo()
    {
        return self::$numberFormatInfo;
    }

    /**
     *
     * @param string $cultureCode The culture code to be used withing the helper
     */
    public static function setCulture($cultureCode)
    {
        self::$cultureCode = $cultureCode;
        self::$dateTimeFormatInfo = DateTimeFormatInfo::getInstance();
        self::$numberFormatInfo = NumberFormatInfo::getInstance();
    }

    /**
     * Gets the current culture code
     * @return string
     */
    public static function getCulture()
    {
        return self::$cultureCode;
    }

    /**
     * Creates a default settings file
     * @param string $fileName The filename the settings will be written to.
     */
    private static function createDefault($fileName)
    {
        $defaultResources         = self::$frameworkResources;
        self::$dictionary = new KeyedCollectionWrapper($defaultResources, false);
        self::save($fileName);
    }

    /**
     * sets a value for a given key
     * @param string $keyName
     * @param mixed $value
     * @param string $culture
     */
    public static function setValueByCulture($keyName, $value, $culture)
    {
        $arr =& self::getCollection()->getArrayReference();
        $arr[$culture][$keyName] = $value;
    }

    /**
     * Load the persistent collection given the storeName.
     *
     * @param string $storeName The storename (or filename)
     */
    public static function load($storeName = '')
    {
        $data = '';
        if ($storeName === '')
        {
            self::$storeName = HttpContext::getDocumentRoot() . self::RESOURCES_DIR . self::$cultureCode."/". self::DEFAULTFILENAME;
        }
        else
        {
            self::$storeName = $storeName;
        }

        if (file_exists(self::$storeName) === false) self::createDefault(self::$storeName);
        $data = file_get_contents(self::$storeName);
        //$data = utf8_encode($data);
        self::$dictionary = XmlSerializer::deserialize($data, 'KeyedCollectionWrapper');
    }
    /**
     * Saves the persistent collection given the storeName.
     *
     * @param string $storeName The storename (or filename)
     */
    public static function save($storeName = '')
    {
        if (self::$dictionary == null)
            self::load($storeName);

        if ($storeName === '')
            $fileName = HttpContext::getDocumentRoot()  . self::RESOURCES_DIR . self::$cultureCode . "/" . self::DEFAULTFILENAME;
        else
            $fileName = $storeName;

        $data = XmlSerializer::serialize(self::$dictionary);
        $data = utf8_decode($data);
        file_put_contents($fileName, $data);
    }

    /**
     * Gets the store name from wich the collection was loaded.
     *
     * @return string
     */
    public static function getStoreName()
    {
        return self::$storeName;
    }

    /**
     * Sets a value for a given key.
     *
     * @param string $keyName
     * @param string $value
     */
    public static function setValue($keyName, $value)
    {
        $arr =& self::getCollection()->getArrayReference();
        $arr[self::getCulture()][$keyName] = $value;
    }

    /**
     * Gets the value of a given key.
     *
     * @param string $keyName
     * @return string
     */
    public static function getValue($keyName)
    {
        if (key_exists($key, self::$dictionary) == false)
            return null;

        return self::$dictionary[$key];
    }

    /**
     * Determines if the given key has a value.
     *
     * @param string $keyName
     * @return bool
     */
    public static function hasValue($keyName)
    {
        $arr =& self::getCollection()->getArrayReference();
        if (array_key_exists(self::getCulture(), $arr))
        {
            if (array_key_exists($keyName, $arr[self::getCulture()]))
                return true;
        }
        return false;
    }

    /**
     * Gets all the keys in the collection as an array.
     *
     * @return array
     */
    public static function getKeys()
    {
        $arr =& self::getCollection()->getArrayReference();
        return array_keys($arr[self::getCulture()]);
    }
    
    /**
     * Gets the underlying collection by reference.
     *
     * @return CollectionBase
     */
    public static function &getCollection()
    {
        if (self::$dictionary == null)
            self::load();

        return self::$dictionary;
    }
}

/**
 * Facilitates Date and Time formats and information by culture
 * 
 * @package WebCore
 * @subpackage Globalization
 */
class DateTimeFormatInfo extends ObjectBase implements ISingleton
{
    const DICT_DATETIME_MONTHSNAMES = 'monthNames';
    const DICT_DATETIME_DAYSNAMES = 'dayNames';
    const DICT_DATETIME_SHORTDATE = 'shortDate';
    const DICT_DATETIME_LONGDATE = 'longDate';
    const DICT_DATETIME_SHORTTIME = 'shortTime';
    const DICT_DATETIME_LONGTIME = 'longTime';
    const DICT_DATETIME_SHORTDATETIME = 'shortDateTime';
    const DICT_DATETIME_LONGDATETIME = 'longDateTime';
    const DICT_DATETIME_FIRSTDAYWEEK = 'firstDayWeek';

    /**
     *
     * @var DateTimeFormatInfo
     */
    private static $instance;

    /**
     * Creates a new instance of this class
     *
     * @param string $culture
     */
    private function __construct()
    {
        
    }

    /**
     *
     * @return DateTimeFormatInfo
     */
    public static function getInstance()
    {
        if(!(self::$instance instanceof  self))
        {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
    public static function  isLoaded()
    {
        return self::$instance instanceof self;
    }


    /**
     * Returns an array with months names
     *
     * @return array
     */
    public function getMonthsNames()
    {
        $defaultArray = array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        );
        $val          = $this->getValue(NumberFormatInfo::DICT_DATETIME_MONTHSNAMES);
        return is_null($val) ? $defaultArray : $val;
    }
    
    /**
     * Returns month name
     *
     * @param int $month
     * @return string
     */
    public function getMonthName($month)
    {
        $months = $this->getMonthsNames();
        
        return $months[$month];
    }
    
    /**
     * Formats a date
     *
     * @todo
     */
    public function getFormatValue($format)
    {
        return $format;
    }
    
    /**
     * Formats a datetime
     *
     * @param float $timeSpan
     * @param string $format
     * @return string
     */
    public function format($timeSpan, $format)
    {
        $stringFormat = $this->getFormatValue($format);
        
        return date($stringFormat, $timeSpan);
    }
    
    /**
     * Returns an array with days names
     *
     * @return array
     */
    public function getDaysNames()
    {
        $defaultArray = array(
            'Sunday',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday'
        );
        $val          = $this->getValue(NumberFormatInfo::DICT_DATETIME_DAYSNAMES);
        return is_null($val) ? $defaultArray : $val;
    }
    
    /**
     * Returns day name
     *
     * @param int $month
     * @return string
     */
    public function getDayName($day)
    {
        $days = $this->getDaysNames();
        
        return $days[$day];
    }
    
    /**
     * Returns first day of week
     * @todo
     * @return int
     */
    public function getFirstDayOfWeek()
    {
        return 1;
    }
    
    /**
     * Parses a date string in this culture-specific format and converts it
     * into a DateTime object
     * 
     * @param string $str
     * @return DateTime
     */
    public function parse($str)
    {
        return new DateTime($str);
    }
}

/**
 * Facilitates numbers and currencies formats and information by culture
 * 
 * @package WebCore
 * @subpackage Globalization
 */
class NumberFormatInfo extends ObjectBase implements ISingleton
{
    const DICT_NUMBER_FORMAT = 'numberFormat';
    const DICT_NUMBER_CURRENCYFORMAT = 'currencyFormat';
    const DICT_NUMBER_CURRENCYSYMBOL = 'currencySymbol';
    const DICT_NUMBER_DECIMALDIGIT = 'decimalDigit';

    /**
     *
     * @var NumberFormatInfo
     */
    private static $instance;
    
    /**
     * Creates a new instance of this class
     *
     */
    private function __construct()
    {
        
    }

    /**
     *
     * @return NumberFormatInfo
     */
    public static function getInstance()
    {
        if(!(self::$instance instanceof  self))
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     *
     * @return boolean
     */
    public static function  isLoaded()
    {
        return self::$instance instanceof self;
    }
    
    /**
     * Formats a number
     *
     * @todo
     */
    public function format($number)
    {
        return $number;
    }
    
    /**
     * Formats amount to currency
     *
     * @param float $amount
     * @return string
     */
    public function formatCurrency($amount)
    {
        $currencySymbol = $this->getCurrencySymbol();
        $decimalDigit   = $this->getDecimalDigits();
        
        return $currencySymbol . $amount;
    }
    
    /**
     * Returns Currency Symbol
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        $val = $this->getValue(NumberFormatInfo::DICT_NUMBER_CURRENCYSYMBOL);
        return is_null($val) ? '$' : $val;
    }
    
    /**
     * Returns Decimal Digit
     *
     * @return string
     */
    public function getDecimalDigit()
    {
        $val = $this->getValue(NumberFormatInfo::DICT_NUMBER_DECIMALDIGIT);
        return is_null($val) ? '.' : $val;
    }
    
    /**
     * Parses a number string in this culture-specific format and converts it
     * into a floating point number.
     * 
     * @param string $str
     * @return float
     */
    public function parse($str)
    {
        //@todo
    }
}
?>