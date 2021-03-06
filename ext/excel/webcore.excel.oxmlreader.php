<?php
require_once 'webcore.excel.php';
require_once 'style/Borders.php';
require_once 'style/NumberFormat.php';
require_once 'worksheet/Drawing.php';
require_once 'webcore.excel.utils.php';
require_once 'webcore.excel.referencehelper.php';

/**
 * ExcelReader_Excel2007
 *
 * @package    WebCore
 * @subpackage Excel
 */
class OXmlReader extends ExcelReaderBase
{
    /**
     * Read data only?
     *
     * @var boolean
     */
    private $_readDataOnly = false;
    
    /**
     * Restict which sheets should be loaded?
     *
     * @var array
     */
    private $_loadSheetsOnly = null;
    
    /**
     * ExcelReader_IReadFilter instance
     *
     * @var ExcelReader_IReadFilter
     */
    private $_readFilter = null;
    
    /**
     * Read data only?
     *
     * @return boolean
     */
    public function getReadDataOnly()
    {
        return $this->_readDataOnly;
    }
    
    /**
     * Set read data only
     *
     * @param boolean $pValue
     */
    public function setReadDataOnly($pValue = false)
    {
        $this->_readDataOnly = $pValue;
    }
    
    /**
     * Get which sheets to load
     *
     * @return mixed
     */
    public function getLoadSheetsOnly()
    {
        return $this->_loadSheetsOnly;
    }
    
    /**
     * Set which sheets to load
     *
     * @param mixed $value
     */
    public function setLoadSheetsOnly($value = null)
    {
        $this->_loadSheetsOnly = is_array($value) ? $value : array(
            $value
        );
    }
    
    /**
     * Set all sheets to load
     */
    public function setLoadAllSheets()
    {
        $this->_loadSheetsOnly = null;
    }
    
    /**
     * Read filter
     *
     * @return ExcelReader_IReadFilter
     */
    public function getReadFilter()
    {
        return $this->_readFilter;
    }
    
    /**
     * Set read filter
     *
     * @param ExcelReader_IReadFilter $pValue
     */
    public function setReadFilter(ExcelReader_IReadFilter $pValue)
    {
        $this->_readFilter = $pValue;
    }
    
    /**
     * Create a new ExcelReader_Excel2007 instance
     */
    public function __construct()
    {
        $this->_readFilter = new ExcelReader_DefaultReadFilter();
    }
    
    /**
     * Loads PHPExcel from file
     *
     * @param 	string 		$pFilename
     * @throws 	Exception
     */
    public function load($pFilename)
    {
        parent::load($pFilename);
        
        // Initialisations
        $excel = new ExcelWorkbook();
        $excel->removeSheetByIndex(0);
        $zip = new ZipArchive;
        $zip->open($pFilename);
        
        $rels = simplexml_load_string($zip->getFromName("_rels/.rels")); //~ http://schemas.openxmlformats.org/package/2006/relationships");
        foreach ($rels->Relationship as $rel)
        {
            switch ($rel["Type"])
            {
                case "http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties":
                    $xmlCore = simplexml_load_string($zip->getFromName("{$rel['Target']}"));
                    $xmlCore->registerXPathNamespace("dc", "http://purl.org/dc/elements/1.1/");
                    $xmlCore->registerXPathNamespace("dcterms", "http://purl.org/dc/terms/");
                    $xmlCore->registerXPathNamespace("cp", "http://schemas.openxmlformats.org/package/2006/metadata/core-properties");
                    $docProps = $excel->getProperties();
                    $docProps->setCreator((string) self::array_item($xmlCore->xpath("dc:creator")));
                    $docProps->setLastModifiedBy((string) self::array_item($xmlCore->xpath("cp:lastModifiedBy")));
                    $docProps->setCreated(strtotime(self::array_item($xmlCore->xpath("dcterms:created")))); //! respect xsi:type
                    $docProps->setModified(strtotime(self::array_item($xmlCore->xpath("dcterms:modified")))); //! respect xsi:type
                    $docProps->setTitle((string) self::array_item($xmlCore->xpath("dc:title")));
                    $docProps->setDescription((string) self::array_item($xmlCore->xpath("dc:description")));
                    $docProps->setSubject((string) self::array_item($xmlCore->xpath("dc:subject")));
                    $docProps->setKeywords((string) self::array_item($xmlCore->xpath("cp:keywords")));
                    $docProps->setCategory((string) self::array_item($xmlCore->xpath("cp:category")));
                    break;
                
                case "http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument":
                    $dir          = dirname($rel["Target"]);
                    $relsWorkbook = simplexml_load_string($zip->getFromName("$dir/_rels/" . basename($rel["Target"]) . ".rels")); //~ http://schemas.openxmlformats.org/package/2006/relationships");
                    $relsWorkbook->registerXPathNamespace("rel", "http://schemas.openxmlformats.org/package/2006/relationships");
                    
                    $sharedStrings = array();
                    $xpath         = self::array_item($relsWorkbook->xpath("rel:Relationship[@Type='http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings']"));
                    $xmlStrings    = simplexml_load_string($zip->getFromName("$dir/$xpath[Target]")); //~ http://schemas.openxmlformats.org/spreadsheetml/2006/main");
                    if (isset($xmlStrings) && isset($xmlStrings->si))
                    {
                        foreach ($xmlStrings->si as $val)
                        {
                            if (isset($val->t))
                            {
                                $sharedStrings[] = ExcelShared_String::ControlCharacterOOXML2PHP((string) $val->t);
                            }
                            elseif (isset($val->r))
                            {
                                $sharedStrings[] = $this->_parseRichText($val);
                            }
                        }
                    }
                    
                    $worksheets = array();
                    foreach ($relsWorkbook->Relationship as $ele)
                    {
                        if ($ele["Type"] == "http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet")
                        {
                            $worksheets[(string) $ele["Id"]] = $ele["Target"];
                        }
                    }
                    
                    $styles     = array();
                    $cellStyles = array();
                    $xpath      = self::array_item($relsWorkbook->xpath("rel:Relationship[@Type='http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles']"));
                    $xmlStyles  = simplexml_load_string($zip->getFromName("$dir/$xpath[Target]")); //~ http://schemas.openxmlformats.org/spreadsheetml/2006/main");
                    $numFmts    = $xmlStyles->numFmts[0];
                    if (isset($numFmts))
                    {
                        $numFmts->registerXPathNamespace("sml", "http://schemas.openxmlformats.org/spreadsheetml/2006/main");
                    }
                    if (!$this->_readDataOnly)
                    {
                        foreach ($xmlStyles->cellXfs->xf as $xf)
                        {
                            $numFmt = ExcelStyleNumberFormat::FORMAT_GENERAL;
                            
                            if ($xf["numFmtId"])
                            {
                                if (isset($numFmts))
                                {
                                    $tmpNumFmt = self::array_item($numFmts->xpath("sml:numFmt[@numFmtId=$xf[numFmtId]]"));
                                    
                                    if (isset($tmpNumFmt["formatCode"]))
                                    {
                                        $numFmt = (string) $tmpNumFmt["formatCode"];
                                    }
                                }
                                
                                if ((int) $xf["numFmtId"] < 165)
                                {
                                    $numFmt = ExcelStyleNumberFormat::builtInFormatCode((int) $xf["numFmtId"]);
                                }
                            }
                            //$numFmt = str_replace('mm', 'i', $numFmt);
                            //$numFmt = str_replace('h', 'H', $numFmt);
                            
                            $styles[] = (object) array(
                                "numFmt" => $numFmt,
                                "font" => $xmlStyles->fonts->font[intval($xf["fontId"])],
                                "fill" => $xmlStyles->fills->fill[intval($xf["fillId"])],
                                "border" => $xmlStyles->borders->border[intval($xf["borderId"])],
                                "alignment" => $xf->alignment,
                                "protection" => $xf->protection,
                                "applyAlignment" => (isset($xf["applyAlignment"]) && ((string) $xf["applyAlignment"] == 'true' || (string) $xf["applyAlignment"] == '1')),
                                "applyBorder" => (isset($xf["applyBorder"]) && ((string) $xf["applyBorder"] == 'true' || (string) $xf["applyBorder"] == '1')),
                                "applyFill" => (isset($xf["applyFill"]) && ((string) $xf["applyFill"] == 'true' || (string) $xf["applyFill"] == '1')),
                                "applyFont" => (isset($xf["applyFont"]) && ((string) $xf["applyFont"] == 'true' || (string) $xf["applyFont"] == '1')),
                                "applyNumberFormat" => (isset($xf["applyNumberFormat"]) && ((string) $xf["applyNumberFormat"] == 'true' || (string) $xf["applyNumberFormat"] == '1')),
                                "applyProtection" => (isset($xf["applyProtection"]) && ((string) $xf["applyProtection"] == 'true' || (string) $xf["applyProtection"] == '1'))
                            );
                        }
                        
                        foreach ($xmlStyles->cellStyleXfs->xf as $xf)
                        {
                            $numFmt = ExcelStyleNumberFormat::FORMAT_GENERAL;
                            if ($numFmts && $xf["numFmtId"])
                            {
                                $tmpNumFmt = self::array_item($numFmts->xpath("sml:numFmt[@numFmtId=$xf[numFmtId]]"));
                                if (isset($tmpNumFmt["formatCode"]))
                                {
                                    $numFmt = (string) $tmpNumFmt["formatCode"];
                                }
                                else if ((int) $xf["numFmtId"] < 165)
                                {
                                    $numFmt = ExcelStyleNumberFormat::builtInFormatCode((int) $xf["numFmtId"]);
                                }
                            }
                            
                            $cellStyles[] = (object) array(
                                "numFmt" => $numFmt,
                                "font" => $xmlStyles->fonts->font[intval($xf["fontId"])],
                                "fill" => $xmlStyles->fills->fill[intval($xf["fillId"])],
                                "border" => $xmlStyles->borders->border[intval($xf["borderId"])],
                                "alignment" => $xf->alignment,
                                "protection" => $xf->protection,
                                "applyAlignment" => true,
                                "applyBorder" => true,
                                "applyFill" => true,
                                "applyFont" => true,
                                "applyNumberFormat" => true,
                                "applyProtection" => true
                            );
                        }
                    }
                    
                    $dxfs = array();
                    if (!$this->_readDataOnly)
                    {
                        foreach ($xmlStyles->dxfs->dxf as $dxf)
                        {
                            $style = new ExcelStyle;
                            $this->_readStyle($style, $dxf);
                            $dxfs[] = $style;
                        }
                        
                        foreach ($xmlStyles->cellStyles->cellStyle as $cellStyle)
                        {
                            if (intval($cellStyle['builtinId']) == 0)
                            {
                                if (isset($cellStyles[intval($cellStyle['xfId'])]))
                                {
                                    // Set default style
                                    $style = new ExcelStyle;
                                    $this->_readStyle($style, $cellStyles[intval($cellStyle['xfId'])]);
                                    ExcelStyle::setDefaultStyle($style);
                                }
                            }
                        }
                    }
                    
                    $xmlWorkbook = simplexml_load_string($zip->getFromName("{$rel['Target']}")); //~ http://schemas.openxmlformats.org/spreadsheetml/2006/main");
                    
                    // Set base date
                    if ($xmlWorkbook->workbookPr)
                    {
                        ExcelShared_Date::setExcelCalendar(ExcelShared_Date::CALENDAR_WINDOWS_1900);
                        if (isset($xmlWorkbook->workbookPr['date1904']))
                        {
                            $date1904 = (string) $xmlWorkbook->workbookPr['date1904'];
                            if ($date1904 == "true" || $date1904 == "1")
                            {
                                ExcelShared_Date::setExcelCalendar(ExcelShared_Date::CALENDAR_MAC_1904);
                            }
                        }
                    }
                    
                    $sheetId = 0;
                    foreach ($xmlWorkbook->sheets->sheet as $eleSheet)
                    {
                        // Check if sheet should be skipped
                        if (isset($this->_loadSheetsOnly) && !in_array((string) $eleSheet["name"], $this->_loadSheetsOnly))
                        {
                            continue;
                        }
                        
                        // Load sheet
                        $docSheet = $excel->createSheet();
                        $docSheet->setTitle((string) $eleSheet["name"]);
                        $fileWorksheet = $worksheets[(string) self::array_item($eleSheet->attributes("http://schemas.openxmlformats.org/officeDocument/2006/relationships"), "id")];
                        $xmlSheet      = simplexml_load_string($zip->getFromName("$dir/$fileWorksheet")); //~ http://schemas.openxmlformats.org/spreadsheetml/2006/main");
                        
                        $sharedFormulas = array();
                        
                        if (isset($xmlSheet->sheetViews) && isset($xmlSheet->sheetViews->sheetView))
                        {
                            if (isset($xmlSheet->sheetViews->sheetView['zoomScale']))
                            {
                                $docSheet->getSheetView()->setZoomScale(intval($xmlSheet->sheetViews->sheetView['zoomScale']));
                            }
                            
                            if (isset($xmlSheet->sheetViews->sheetView['zoomScaleNormal']))
                            {
                                $docSheet->getSheetView()->setZoomScaleNormal(intval($xmlSheet->sheetViews->sheetView['zoomScaleNormal']));
                            }
                            
                            if (isset($xmlSheet->sheetViews->sheetView['showGridLines']))
                            {
                                $docSheet->setShowGridLines($xmlSheet->sheetViews->sheetView['showGridLines'] ? true : false);
                            }
                            
                            if (isset($xmlSheet->sheetViews->sheetView->pane))
                            {
                                if (isset($xmlSheet->sheetViews->sheetView->pane['topLeftCell']))
                                {
                                    $docSheet->freezePane((string) $xmlSheet->sheetViews->sheetView->pane['topLeftCell']);
                                }
                                else
                                {
                                    $xSplit = 0;
                                    $ySplit = 0;
                                    
                                    if (isset($xmlSheet->sheetViews->sheetView->pane['xSplit']))
                                    {
                                        $xSplit = 1 + intval($xmlSheet->sheetViews->sheetView->pane['xSplit']);
                                    }
                                    
                                    if (isset($xmlSheet->sheetViews->sheetView->pane['ySplit']))
                                    {
                                        $ySplit = 1 + intval($xmlSheet->sheetViews->sheetView->pane['ySplit']);
                                    }
                                    
                                    $docSheet->freezePaneByColumnAndRow($xSplit, $ySplit);
                                }
                            }
                        }
                        
                        if (isset($xmlSheet->sheetPr) && isset($xmlSheet->sheetPr->outlinePr))
                        {
                            if (isset($xmlSheet->sheetPr->outlinePr['summaryRight']) && $xmlSheet->sheetPr->outlinePr['summaryRight'] == false)
                            {
                                $docSheet->setShowSummaryRight(false);
                            }
                            else
                            {
                                $docSheet->setShowSummaryRight(true);
                            }
                            
                            if (isset($xmlSheet->sheetPr->outlinePr['summaryBelow']) && $xmlSheet->sheetPr->outlinePr['summaryBelow'] == false)
                            {
                                $docSheet->setShowSummaryBelow(false);
                            }
                            else
                            {
                                $docSheet->setShowSummaryBelow(true);
                            }
                        }
                        
                        if (isset($xmlSheet->sheetFormatPr))
                        {
                            if (isset($xmlSheet->sheetFormatPr['customHeight']) && ((string) $xmlSheet->sheetFormatPr['customHeight'] == '1' || strtolower((string) $xmlSheet->sheetFormatPr['customHeight']) == 'true') && isset($xmlSheet->sheetFormatPr['defaultRowHeight']))
                            {
                                $docSheet->getDefaultRowDimension()->setRowHeight((float) $xmlSheet->sheetFormatPr['defaultRowHeight']);
                            }
                            if (isset($xmlSheet->sheetFormatPr['defaultColWidth']))
                            {
                                $docSheet->getDefaultColumnDimension()->setWidth((float) $xmlSheet->sheetFormatPr['defaultColWidth']);
                            }
                        }
                        
                        if (isset($xmlSheet->cols) && !$this->_readDataOnly)
                        {
                            foreach ($xmlSheet->cols->col as $col)
                            {
                                for ($i = intval($col["min"]) - 1; $i < intval($col["max"]); ++$i)
                                {
                                    if ($col["bestFit"])
                                    {
                                        $docSheet->getColumnDimension(ExcelCell::stringFromColumnIndex($i))->setAutoSize(true);
                                    }
                                    if ($col["hidden"])
                                    {
                                        $docSheet->getColumnDimension(ExcelCell::stringFromColumnIndex($i))->setVisible(false);
                                    }
                                    if ($col["collapsed"])
                                    {
                                        $docSheet->getColumnDimension(ExcelCell::stringFromColumnIndex($i))->setCollapsed(true);
                                    }
                                    if ($col["outlineLevel"] > 0)
                                    {
                                        $docSheet->getColumnDimension(ExcelCell::stringFromColumnIndex($i))->setOutlineLevel(intval($col["outlineLevel"]));
                                    }
                                    $docSheet->getColumnDimension(ExcelCell::stringFromColumnIndex($i))->setWidth(floatval($col["width"]));
                                    
                                    if (intval($col["max"]) == 16384)
                                    {
                                        break;
                                    }
                                }
                            }
                        }
                        
                        if (isset($xmlSheet->printOptions) && !$this->_readDataOnly)
                        {
                            if ($xmlSheet->printOptions['gridLinesSet'] == 'true' && $xmlSheet->printOptions['gridLinesSet'] == '1')
                            {
                                $docSheet->setShowGridlines(true);
                            }
                            
                            if ($xmlSheet->printOptions['gridLines'] == 'true' || $xmlSheet->printOptions['gridLines'] == '1')
                            {
                                $docSheet->setPrintGridlines(true);
                            }
                            
                            if ($xmlSheet->printOptions['horizontalCentered'])
                            {
                                $docSheet->getPageSetup()->setHorizontalCentered(true);
                            }
                            if ($xmlSheet->printOptions['verticalCentered'])
                            {
                                $docSheet->getPageSetup()->setVerticalCentered(true);
                            }
                        }
                        
                        foreach ($xmlSheet->sheetData->row as $row)
                        {
                            if ($row["ht"] && !$this->_readDataOnly)
                            {
                                $docSheet->getRowDimension(intval($row["r"]))->setRowHeight(floatval($row["ht"]));
                            }
                            if ($row["hidden"] && !$this->_readDataOnly)
                            {
                                $docSheet->getRowDimension(intval($row["r"]))->setVisible(false);
                            }
                            if ($row["collapsed"])
                            {
                                $docSheet->getRowDimension(intval($row["r"]))->setCollapsed(true);
                            }
                            if ($row["outlineLevel"] > 0)
                            {
                                $docSheet->getRowDimension(intval($row["r"]))->setOutlineLevel(intval($row["outlineLevel"]));
                            }
                            
                            foreach ($row->c as $c)
                            {
                                $r               = (string) $c["r"];
                                $cellDataType    = (string) $c["t"];
                                $value           = null;
                                $calculatedValue = null;
                                
                                // Read cell?
                                if (!is_null($this->getReadFilter()))
                                {
                                    $coordinates = ExcelCell::coordinateFromString($r);
                                    
                                    if (!$this->getReadFilter()->readCell($coordinates[0], $coordinates[1], $docSheet->getTitle()))
                                    {
                                        break;
                                    }
                                }
                                
                                // Read cell!
                                switch ($cellDataType)
                                {
                                    case "s":
                                        if ((string) $c->v != '')
                                        {
                                            $value = $sharedStrings[intval($c->v)];
                                            
                                            if ($value instanceof ExcelRichText)
                                            {
                                                $value = clone $value;
                                            }
                                        }
                                        else
                                        {
                                            $value = '';
                                        }
                                        
                                        break;
                                    case "b":
                                        $value = (string) $c->v;
                                        if ($value == '0')
                                        {
                                            $value = false;
                                        }
                                        else if ($value == '1')
                                        {
                                            $value = true;
                                        }
                                        else
                                        {
                                            $value = (bool) $c->v;
                                        }
                                        
                                        break;
                                    case "inlineStr":
                                        $value = $this->_parseRichText($c->is);
                                        
                                        break;
                                    case "e":
                                        if (!isset($c->f))
                                        {
                                            $value = (string) $c->v;
                                        }
                                        else
                                        {
                                            $value = "={$c->f}";
                                        }
                                        
                                        break;
                                    
                                    default:
                                        if (!isset($c->f))
                                        {
                                            $value = (string) $c->v;
                                        }
                                        else
                                        {
                                            // Formula
                                            $value           = "={$c->f}";
                                            $calculatedValue = isset($c->v) ? (string) $c->v : null;
                                            $cellDataType    = 'f';
                                            
                                            // Shared formula?
                                            if (isset($c->f['t']) && strtolower((string) $c->f['t']) == 'shared')
                                            {
                                                $instance = (string) $c->f['si'];
                                                
                                                if (!isset($sharedFormulas[(string) $c->f['si']]))
                                                {
                                                    $sharedFormulas[$instance] = array(
                                                        'master' => $r,
                                                        'formula' => $value
                                                    );
                                                }
                                                else
                                                {
                                                    $master  = ExcelCell::coordinateFromString($sharedFormulas[$instance]['master']);
                                                    $current = ExcelCell::coordinateFromString($r);
                                                    
                                                    $difference    = array(
                                                        0,
                                                        0
                                                    );
                                                    $difference[0] = ExcelCell::columnIndexFromString($current[0]) - ExcelCell::columnIndexFromString($master[0]);
                                                    $difference[1] = $current[1] - $master[1];
                                                    
                                                    $helper = ExcelReferenceHelper::getInstance();
                                                    $x      = $helper->updateFormulaReferences($sharedFormulas[$instance]['formula'], 'A1', $difference[0], $difference[1]);
                                                    
                                                    $value = $x;
                                                }
                                            }
                                        }
                                        
                                        break;
                                }
                                
                                // Check for numeric values
                                if (is_numeric($value) && $cellDataType != 's')
                                {
                                    if ($value == (int) $value)
                                        $value = (int) $value;
                                    elseif ($value == (float) $value)
                                        $value = (float) $value;
                                    elseif ($value == (double) $value)
                                        $value = (double) $value;
                                }
                                
                                // Rich text?
                                if ($value instanceof ExcelRichText && $this->_readDataOnly)
                                {
                                    $value = $value->getPlainText();
                                }
                                
                                // Assign value
                                if ($cellDataType != '')
                                {
                                    $docSheet->setCellValueExplicit($r, $value, $cellDataType);
                                }
                                else
                                {
                                    $docSheet->setCellValue($r, $value);
                                }
                                if (!is_null($calculatedValue))
                                {
                                    $docSheet->getCell($r)->setCalculatedValue($calculatedValue);
                                }
                                
                                // Style information?
                                if ($c["s"] && !$this->_readDataOnly)
                                {
                                    if (isset($styles[intval($c["s"])]))
                                    {
                                        $this->_readStyle($docSheet->getStyle($r), $styles[intval($c["s"])]);
                                    }
                                    
                                    if ($cellDataType != 's' && ExcelShared_Date::isDateTimeFormat($docSheet->getStyle($r)->getNumberFormat()))
                                    {
                                        if (preg_match("/^([0-9.,-]+)$/", $value))
                                        {
                                            $docSheet->setCellValue($r, (float) $value);
                                        }
                                    }
                                }
                                
                                // Set rich text parent
                                if ($value instanceof ExcelRichText && !$this->_readDataOnly)
                                {
                                    $value->setParent($docSheet->getCell($r));
                                }
                            }
                        }
                        
                        $conditionals = array();
                        if (!$this->_readDataOnly)
                        {
                            foreach ($xmlSheet->conditionalFormatting as $conditional)
                            {
                                foreach ($conditional->cfRule as $cfRule)
                                {
                                    if (((string) $cfRule["type"] == ExcelStyle_Conditional::CONDITION_NONE || (string) $cfRule["type"] == ExcelStyle_Conditional::CONDITION_CELLIS || (string) $cfRule["type"] == ExcelStyle_Conditional::CONDITION_CONTAINSTEXT) && isset($dxfs[intval($cfRule["dxfId"])]))
                                    {
                                        $conditionals[(string) $conditional["sqref"]][intval($cfRule["priority"])] = $cfRule;
                                    }
                                }
                            }
                            
                            foreach ($conditionals as $ref => $cfRules)
                            {
                                ksort($cfRules);
                                $conditionalStyles = array();
                                foreach ($cfRules as $cfRule)
                                {
                                    $objConditional = new ExcelStyle_Conditional();
                                    $objConditional->setConditionType((string) $cfRule["type"]);
                                    $objConditional->setOperatorType((string) $cfRule["operator"]);
                                    
                                    if ((string) $cfRule["text"] != '')
                                    {
                                        $objConditional->setText((string) $cfRule["text"]);
                                    }
                                    
                                    if (count($cfRule->formula) > 1)
                                    {
                                        foreach ($cfRule->formula as $formula)
                                        {
                                            $objConditional->addCondition((string) $formula);
                                        }
                                    }
                                    else
                                    {
                                        $objConditional->addCondition((string) $cfRule->formula);
                                    }
                                    $objConditional->setStyle(clone $dxfs[intval($cfRule["dxfId"])]);
                                    $conditionalStyles[] = $objConditional;
                                }
                                
                                // Extract all cell references in $ref
                                $aReferences = ExcelCell::extractAllCellReferencesInRange($ref);
                                foreach ($aReferences as $reference)
                                {
                                    $docSheet->getStyle($reference)->setConditionalStyles($conditionalStyles);
                                }
                            }
                        }
                        
                        $aKeys = array(
                            "sheet",
                            "objects",
                            "scenarios",
                            "formatCells",
                            "formatColumns",
                            "formatRows",
                            "insertColumns",
                            "insertRows",
                            "insertHyperlinks",
                            "deleteColumns",
                            "deleteRows",
                            "selectLockedCells",
                            "sort",
                            "autoFilter",
                            "pivotTables",
                            "selectUnlockedCells"
                        );
                        if (!$this->_readDataOnly)
                        {
                            foreach ($aKeys as $key)
                            {
                                $method = "set" . ucfirst($key);
                                $docSheet->getProtection()->$method($xmlSheet->sheetProtection[$key] == "true");
                            }
                        }
                        
                        if (!$this->_readDataOnly)
                        {
                            $docSheet->getProtection()->setPassword((string) $xmlSheet->sheetProtection["password"], true);
                            if ($xmlSheet->protectedRanges->protectedRange)
                            {
                                foreach ($xmlSheet->protectedRanges->protectedRange as $protectedRange)
                                {
                                    $docSheet->protectCells((string) $protectedRange["sqref"], (string) $protectedRange["password"], true);
                                }
                            }
                        }
                        
                        if ($xmlSheet->autoFilter && !$this->_readDataOnly)
                        {
                            $docSheet->setAutoFilter((string) $xmlSheet->autoFilter["ref"]);
                        }
                        
                        if ($xmlSheet->mergeCells->mergeCell && !$this->_readDataOnly)
                        {
                            foreach ($xmlSheet->mergeCells->mergeCell as $mergeCell)
                            {
                                $docSheet->mergeCells((string) $mergeCell["ref"]);
                            }
                        }
                        
                        if (!$this->_readDataOnly)
                        {
                            $docPageMargins = $docSheet->getPageMargins();
                            $docPageMargins->setLeft(floatval($xmlSheet->pageMargins["left"]));
                            $docPageMargins->setRight(floatval($xmlSheet->pageMargins["right"]));
                            $docPageMargins->setTop(floatval($xmlSheet->pageMargins["top"]));
                            $docPageMargins->setBottom(floatval($xmlSheet->pageMargins["bottom"]));
                            $docPageMargins->setHeader(floatval($xmlSheet->pageMargins["header"]));
                            $docPageMargins->setFooter(floatval($xmlSheet->pageMargins["footer"]));
                        }
                        
                        if (!$this->_readDataOnly)
                        {
                            $docPageSetup = $docSheet->getPageSetup();
                            
                            if (isset($xmlSheet->pageSetup["orientation"]))
                            {
                                $docPageSetup->setOrientation((string) $xmlSheet->pageSetup["orientation"]);
                            }
                            if (isset($xmlSheet->pageSetup["paperSize"]))
                            {
                                $docPageSetup->setPaperSize(intval($xmlSheet->pageSetup["paperSize"]));
                            }
                            if (isset($xmlSheet->pageSetup["scale"]))
                            {
                                $docPageSetup->setScale(intval($xmlSheet->pageSetup["scale"]));
                            }
                            if (isset($xmlSheet->pageSetup["fitToHeight"]) && intval($xmlSheet->pageSetup["fitToHeight"]) > 0)
                            {
                                $docPageSetup->setFitToHeight(intval($xmlSheet->pageSetup["fitToHeight"]));
                            }
                            if (isset($xmlSheet->pageSetup["fitToWidth"]) && intval($xmlSheet->pageSetup["fitToWidth"]) > 0)
                            {
                                $docPageSetup->setFitToWidth(intval($xmlSheet->pageSetup["fitToWidth"]));
                            }
                        }
                        
                        if (!$this->_readDataOnly)
                        {
                            $docHeaderFooter = $docSheet->getHeaderFooter();
                            $docHeaderFooter->setDifferentOddEven($xmlSheet->headerFooter["differentOddEven"] == 'true');
                            $docHeaderFooter->setDifferentFirst($xmlSheet->headerFooter["differentFirst"] == 'true');
                            $docHeaderFooter->setScaleWithDocument($xmlSheet->headerFooter["scaleWithDoc"] == 'true');
                            $docHeaderFooter->setAlignWithMargins($xmlSheet->headerFooter["alignWithMargins"] == 'true');
                            $docHeaderFooter->setOddHeader((string) $xmlSheet->headerFooter->oddHeader);
                            $docHeaderFooter->setOddFooter((string) $xmlSheet->headerFooter->oddFooter);
                            $docHeaderFooter->setEvenHeader((string) $xmlSheet->headerFooter->evenHeader);
                            $docHeaderFooter->setEvenFooter((string) $xmlSheet->headerFooter->evenFooter);
                            $docHeaderFooter->setFirstHeader((string) $xmlSheet->headerFooter->firstHeader);
                            $docHeaderFooter->setFirstFooter((string) $xmlSheet->headerFooter->firstFooter);
                        }
                        
                        if ($xmlSheet->rowBreaks->brk && !$this->_readDataOnly)
                        {
                            foreach ($xmlSheet->rowBreaks->brk as $brk)
                            {
                                if ($brk["man"])
                                {
                                    $docSheet->setBreak("A$brk[id]", ExcelWorksheet::BREAK_ROW);
                                }
                            }
                        }
                        if ($xmlSheet->colBreaks->brk && !$this->_readDataOnly)
                        {
                            foreach ($xmlSheet->colBreaks->brk as $brk)
                            {
                                if ($brk["man"])
                                {
                                    $docSheet->setBreak(ExcelCell::stringFromColumnIndex($brk["id"]) . "1", ExcelWorksheet::BREAK_COLUMN);
                                }
                            }
                        }
                        
                        if ($xmlSheet->dataValidations && !$this->_readDataOnly)
                        {
                            foreach ($xmlSheet->dataValidations->dataValidation as $dataValidation)
                            {
                                // Uppercase coordinate
                                $range = strtoupper($dataValidation["sqref"]);
                                
                                // Extract all cell references in $range
                                $aReferences = ExcelCell::extractAllCellReferencesInRange($range);
                                foreach ($aReferences as $reference)
                                {
                                    // Create validation
                                    $docValidation = $docSheet->getCell($reference)->getDataValidation();
                                    $docValidation->setType((string) $dataValidation["type"]);
                                    $docValidation->setErrorStyle((string) $dataValidation["errorStyle"]);
                                    $docValidation->setOperator((string) $dataValidation["operator"]);
                                    $docValidation->setAllowBlank($dataValidation["allowBlank"] != 0);
                                    $docValidation->setShowDropDown($dataValidation["showDropDown"] == 0);
                                    $docValidation->setShowInputMessage($dataValidation["showInputMessage"] != 0);
                                    $docValidation->setShowErrorMessage($dataValidation["showErrorMessage"] != 0);
                                    $docValidation->setErrorTitle((string) $dataValidation["errorTitle"]);
                                    $docValidation->setError((string) $dataValidation["error"]);
                                    $docValidation->setPromptTitle((string) $dataValidation["promptTitle"]);
                                    $docValidation->setPrompt((string) $dataValidation["prompt"]);
                                    $docValidation->setFormula1((string) $dataValidation->formula1);
                                    $docValidation->setFormula2((string) $dataValidation->formula2);
                                }
                            }
                        }
                        
                        // Add hyperlinks
                        $hyperlinks = array();
                        if (!$this->_readDataOnly)
                        {
                            // Locate hyperlink relations
                            if ($zip->locateName(dirname("$dir/$fileWorksheet") . "/_rels/" . basename($fileWorksheet) . ".rels"))
                            {
                                $relsWorksheet = simplexml_load_string($zip->getFromName(dirname("$dir/$fileWorksheet") . "/_rels/" . basename($fileWorksheet) . ".rels")); //~ http://schemas.openxmlformats.org/package/2006/relationships");
                                foreach ($relsWorksheet->Relationship as $ele)
                                {
                                    if ($ele["Type"] == "http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink")
                                    {
                                        $hyperlinks[(string) $ele["Id"]] = (string) $ele["Target"];
                                    }
                                }
                            }
                            
                            // Loop trough hyperlinks
                            if ($xmlSheet->hyperlinks)
                            {
                                foreach ($xmlSheet->hyperlinks->hyperlink as $hyperlink)
                                {
                                    // Link url
                                    $linkRel = $hyperlink->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
                                    
                                    if (isset($linkRel['id']))
                                    {
                                        $docSheet->getCell($hyperlink['ref'])->getHyperlink()->setUrl($hyperlinks[(string) $linkRel['id']]);
                                    }
                                    if (isset($hyperlink['location']))
                                    {
                                        $docSheet->getCell($hyperlink['ref'])->getHyperlink()->setUrl('sheet://' . (string) $hyperlink['location']);
                                    }
                                    
                                    // Tooltip
                                    if (isset($hyperlink['tooltip']))
                                    {
                                        $docSheet->getCell($hyperlink['ref'])->getHyperlink()->setTooltip((string) $hyperlink['tooltip']);
                                    }
                                }
                            }
                        }
                        
                        // Add comments
                        $comments    = array();
                        $vmlComments = array();
                        if (!$this->_readDataOnly)
                        {
                            // Locate comment relations
                            if ($zip->locateName(dirname("$dir/$fileWorksheet") . "/_rels/" . basename($fileWorksheet) . ".rels"))
                            {
                                $relsWorksheet = simplexml_load_string($zip->getFromName(dirname("$dir/$fileWorksheet") . "/_rels/" . basename($fileWorksheet) . ".rels")); //~ http://schemas.openxmlformats.org/package/2006/relationships");
                                foreach ($relsWorksheet->Relationship as $ele)
                                {
                                    if ($ele["Type"] == "http://schemas.openxmlformats.org/officeDocument/2006/relationships/comments")
                                    {
                                        $comments[(string) $ele["Id"]] = (string) $ele["Target"];
                                    }
                                    if ($ele["Type"] == "http://schemas.openxmlformats.org/officeDocument/2006/relationships/vmlDrawing")
                                    {
                                        $vmlComments[(string) $ele["Id"]] = (string) $ele["Target"];
                                    }
                                }
                            }
                            
                            // Loop trough comments
                            foreach ($comments as $relName => $relPath)
                            {
                                // Load comments file
                                $relPath      = ExcelShared_File::realpath(dirname("$dir/$fileWorksheet") . "/" . $relPath);
                                $commentsFile = simplexml_load_string($zip->getFromName($relPath));
                                
                                // Utility variables
                                $authors = array();
                                
                                // Loop trough authors
                                foreach ($commentsFile->authors->author as $author)
                                {
                                    $authors[] = (string) $author;
                                }
                                
                                // Loop trough contents
                                foreach ($commentsFile->commentList->comment as $comment)
                                {
                                    $docSheet->getComment((string) $comment['ref'])->setAuthor($authors[(string) $comment['authorId']]);
                                    $docSheet->getComment((string) $comment['ref'])->setText($this->_parseRichText($comment->text));
                                }
                            }
                            
                            // Loop trough VML comments
                            foreach ($vmlComments as $relName => $relPath)
                            {
                                // Load VML comments file
                                $relPath         = ExcelShared_File::realpath(dirname("$dir/$fileWorksheet") . "/" . $relPath);
                                $vmlCommentsFile = simplexml_load_string($zip->getFromName($relPath));
                                $vmlCommentsFile->registerXPathNamespace('v', 'urn:schemas-microsoft-com:vml');
                                
                                $shapes = $vmlCommentsFile->xpath('//v:shape');
                                foreach ($shapes as $shape)
                                {
                                    $shape->registerXPathNamespace('v', 'urn:schemas-microsoft-com:vml');
                                    
                                    if (isset($shape['style']))
                                    {
                                        $style     = (string) $shape['style'];
                                        $fillColor = strtoupper(substr((string) $shape['fillcolor'], 1));
                                        $column    = null;
                                        $row       = null;
                                        
                                        $clientData = $shape->xpath('.//x:ClientData');
                                        if (is_array($clientData))
                                        {
                                            $clientData = $clientData[0];
                                            
                                            if (isset($clientData['ObjectType']) && (string) $clientData['ObjectType'] == 'Note')
                                            {
                                                $temp = $clientData->xpath('.//x:Row');
                                                if (is_array($temp))
                                                    $row = $temp[0];
                                                
                                                $temp = $clientData->xpath('.//x:Column');
                                                if (is_array($temp))
                                                    $column = $temp[0];
                                            }
                                        }
                                        
                                        if (!is_null($column) && !is_null($row))
                                        {
                                            // Set comment properties
                                            $comment = $docSheet->getCommentByColumnAndRow($column, $row + 1);
                                            $comment->getFillColor()->setRGB($fillColor);
                                            
                                            // Parse style
                                            $styleArray = explode(';', str_replace(' ', '', $style));
                                            foreach ($styleArray as $stylePair)
                                            {
                                                $stylePair = explode(':', $stylePair);
                                                
                                                if ($stylePair[0] == 'margin-left')
                                                    $comment->setMarginLeft($stylePair[1]);
                                                if ($stylePair[0] == 'margin-top')
                                                    $comment->setMarginTop($stylePair[1]);
                                                if ($stylePair[0] == 'width')
                                                    $comment->setWidth($stylePair[1]);
                                                if ($stylePair[0] == 'height')
                                                    $comment->setHeight($stylePair[1]);
                                                if ($stylePair[0] == 'visibility')
                                                    $comment->setVisible($stylePair[1] == 'visible');
                                                
                                            }
                                        }
                                    }
                                }
                            }
                            
                            // Header/footer images
                            if ($xmlSheet->legacyDrawingHF && !$this->_readDataOnly)
                            {
                                if ($zip->locateName(dirname("$dir/$fileWorksheet") . "/_rels/" . basename($fileWorksheet) . ".rels"))
                                {
                                    $relsWorksheet   = simplexml_load_string($zip->getFromName(dirname("$dir/$fileWorksheet") . "/_rels/" . basename($fileWorksheet) . ".rels")); //~ http://schemas.openxmlformats.org/package/2006/relationships");
                                    $vmlRelationship = '';
                                    
                                    foreach ($relsWorksheet->Relationship as $ele)
                                    {
                                        if ($ele["Type"] == "http://schemas.openxmlformats.org/officeDocument/2006/relationships/vmlDrawing")
                                        {
                                            $vmlRelationship = self::dir_add("$dir/$fileWorksheet", $ele["Target"]);
                                        }
                                    }
                                    
                                    if ($vmlRelationship != '')
                                    {
                                        // Fetch linked images
                                        $relsVML  = simplexml_load_string($zip->getFromName(dirname($vmlRelationship) . '/_rels/' . basename($vmlRelationship) . '.rels')); //~ http://schemas.openxmlformats.org/package/2006/relationships");
                                        $drawings = array();
                                        foreach ($relsVML->Relationship as $ele)
                                        {
                                            if ($ele["Type"] == "http://schemas.openxmlformats.org/officeDocument/2006/relationships/image")
                                            {
                                                $drawings[(string) $ele["Id"]] = self::dir_add($vmlRelationship, $ele["Target"]);
                                            }
                                        }
                                        
                                        // Fetch VML document
                                        $vmlDrawing = simplexml_load_string($zip->getFromName($vmlRelationship));
                                        $vmlDrawing->registerXPathNamespace('v', 'urn:schemas-microsoft-com:vml');
                                        
                                        $hfImages = array();
                                        
                                        $shapes = $vmlDrawing->xpath('//v:shape');
                                        foreach ($shapes as $shape)
                                        {
                                            $shape->registerXPathNamespace('v', 'urn:schemas-microsoft-com:vml');
                                            $imageData = $shape->xpath('//v:imagedata');
                                            $imageData = $imageData[0];
                                            
                                            $imageData = $imageData->attributes('urn:schemas-microsoft-com:office:office');
                                            $style     = self::toCSSArray((string) $shape['style']);
                                            
                                            $hfImages[(string) $shape['id']] = new ExcelWorksheet_HeaderFooterDrawing();
                                            if (isset($imageData['title']))
                                            {
                                                $hfImages[(string) $shape['id']]->setName((string) $imageData['title']);
                                            }
                                            
                                            $hfImages[(string) $shape['id']]->setPath("zip://$pFilename#" . $drawings[(string) $imageData['relid']], false);
                                            $hfImages[(string) $shape['id']]->setResizeProportional(false);
                                            $hfImages[(string) $shape['id']]->setWidth($style['width']);
                                            $hfImages[(string) $shape['id']]->setHeight($style['height']);
                                            $hfImages[(string) $shape['id']]->setOffsetX($style['margin-left']);
                                            $hfImages[(string) $shape['id']]->setOffsetY($style['margin-top']);
                                            $hfImages[(string) $shape['id']]->setResizeProportional(true);
                                        }
                                        
                                        $docSheet->getHeaderFooter()->setImages($hfImages);
                                    }
                                }
                            }
                            
                        }
                        
                        if ($zip->locateName(dirname("$dir/$fileWorksheet") . "/_rels/" . basename($fileWorksheet) . ".rels"))
                        {
                            $relsWorksheet = simplexml_load_string($zip->getFromName(dirname("$dir/$fileWorksheet") . "/_rels/" . basename($fileWorksheet) . ".rels")); //~ http://schemas.openxmlformats.org/package/2006/relationships");
                            $drawings      = array();
                            foreach ($relsWorksheet->Relationship as $ele)
                            {
                                if ($ele["Type"] == "http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing")
                                {
                                    $drawings[(string) $ele["Id"]] = self::dir_add("$dir/$fileWorksheet", $ele["Target"]);
                                }
                            }
                            if ($xmlSheet->drawing && !$this->_readDataOnly)
                            {
                                foreach ($xmlSheet->drawing as $drawing)
                                {
                                    $fileDrawing = $drawings[(string) self::array_item($drawing->attributes("http://schemas.openxmlformats.org/officeDocument/2006/relationships"), "id")];
                                    $relsDrawing = simplexml_load_string($zip->getFromName(dirname($fileDrawing) . "/_rels/" . basename($fileDrawing) . ".rels")); //~ http://schemas.openxmlformats.org/package/2006/relationships");
                                    $images      = array();
                                    
                                    if ($relsDrawing && $relsDrawing->Relationship)
                                    {
                                        foreach ($relsDrawing->Relationship as $ele)
                                        {
                                            if ($ele["Type"] == "http://schemas.openxmlformats.org/officeDocument/2006/relationships/image")
                                            {
                                                $images[(string) $ele["Id"]] = self::dir_add($fileDrawing, $ele["Target"]);
                                            }
                                        }
                                    }
                                    $xmlDrawing = simplexml_load_string($zip->getFromName($fileDrawing))->children("http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing");
                                    
                                    if ($xmlDrawing->oneCellAnchor)
                                    {
                                        foreach ($xmlDrawing->oneCellAnchor as $oneCellAnchor)
                                        {
                                            if ($oneCellAnchor->pic->blipFill)
                                            {
                                                $blip       = $oneCellAnchor->pic->blipFill->children("http://schemas.openxmlformats.org/drawingml/2006/main")->blip;
                                                $xfrm       = $oneCellAnchor->pic->spPr->children("http://schemas.openxmlformats.org/drawingml/2006/main")->xfrm;
                                                $outerShdw  = $oneCellAnchor->pic->spPr->children("http://schemas.openxmlformats.org/drawingml/2006/main")->effectLst->outerShdw;
                                                $objDrawing = new ExcelWorksheet_Drawing;
                                                $objDrawing->setName((string) self::array_item($oneCellAnchor->pic->nvPicPr->cNvPr->attributes(), "name"));
                                                $objDrawing->setDescription((string) self::array_item($oneCellAnchor->pic->nvPicPr->cNvPr->attributes(), "descr"));
                                                $objDrawing->setPath("zip://$pFilename#" . $images[(string) self::array_item($blip->attributes("http://schemas.openxmlformats.org/officeDocument/2006/relationships"), "embed")], false);
                                                $objDrawing->setCoordinates(ExcelCell::stringFromColumnIndex($oneCellAnchor->from->col) . ($oneCellAnchor->from->row + 1));
                                                $objDrawing->setOffsetX(ExcelShared_Drawing::EMUToPixels($oneCellAnchor->from->colOff));
                                                $objDrawing->setOffsetY(ExcelShared_Drawing::EMUToPixels($oneCellAnchor->from->rowOff));
                                                $objDrawing->setResizeProportional(false);
                                                $objDrawing->setWidth(ExcelShared_Drawing::EMUToPixels(self::array_item($oneCellAnchor->ext->attributes(), "cx")));
                                                $objDrawing->setHeight(ExcelShared_Drawing::EMUToPixels(self::array_item($oneCellAnchor->ext->attributes(), "cy")));
                                                if ($xfrm)
                                                {
                                                    $objDrawing->setRotation(ExcelShared_Drawing::angleToDegrees(self::array_item($xfrm->attributes(), "rot")));
                                                }
                                                if ($outerShdw)
                                                {
                                                    $shadow = $objDrawing->getShadow();
                                                    $shadow->setVisible(true);
                                                    $shadow->setBlurRadius(ExcelShared_Drawing::EMUTopixels(self::array_item($outerShdw->attributes(), "blurRad")));
                                                    $shadow->setDistance(ExcelShared_Drawing::EMUTopixels(self::array_item($outerShdw->attributes(), "dist")));
                                                    $shadow->setDirection(ExcelShared_Drawing::angleToDegrees(self::array_item($outerShdw->attributes(), "dir")));
                                                    $shadow->setAlignment((string) self::array_item($outerShdw->attributes(), "algn"));
                                                    $shadow->getColor()->setRGB(self::array_item($outerShdw->srgbClr->attributes(), "val"));
                                                    $shadow->setAlpha(self::array_item($outerShdw->srgbClr->alpha->attributes(), "val") / 1000);
                                                }
                                                $objDrawing->setWorksheet($docSheet);
                                            }
                                        }
                                    }
                                    if ($xmlDrawing->twoCellAnchor)
                                    {
                                        foreach ($xmlDrawing->twoCellAnchor as $twoCellAnchor)
                                        {
                                            if ($twoCellAnchor->pic->blipFill)
                                            {
                                                $blip       = $twoCellAnchor->pic->blipFill->children("http://schemas.openxmlformats.org/drawingml/2006/main")->blip;
                                                $xfrm       = $twoCellAnchor->pic->spPr->children("http://schemas.openxmlformats.org/drawingml/2006/main")->xfrm;
                                                $outerShdw  = $twoCellAnchor->pic->spPr->children("http://schemas.openxmlformats.org/drawingml/2006/main")->effectLst->outerShdw;
                                                $objDrawing = new ExcelWorksheet_Drawing;
                                                $objDrawing->setName((string) self::array_item($twoCellAnchor->pic->nvPicPr->cNvPr->attributes(), "name"));
                                                $objDrawing->setDescription((string) self::array_item($twoCellAnchor->pic->nvPicPr->cNvPr->attributes(), "descr"));
                                                $objDrawing->setPath("zip://$pFilename#" . $images[(string) self::array_item($blip->attributes("http://schemas.openxmlformats.org/officeDocument/2006/relationships"), "embed")], false);
                                                $objDrawing->setCoordinates(ExcelCell::stringFromColumnIndex($twoCellAnchor->from->col) . ($twoCellAnchor->from->row + 1));
                                                $objDrawing->setOffsetX(ExcelShared_Drawing::EMUToPixels($twoCellAnchor->from->colOff));
                                                $objDrawing->setOffsetY(ExcelShared_Drawing::EMUToPixels($twoCellAnchor->from->rowOff));
                                                $objDrawing->setResizeProportional(false);
                                                
                                                $objDrawing->setWidth(ExcelShared_Drawing::EMUToPixels(self::array_item($xfrm->ext->attributes(), "cx")));
                                                $objDrawing->setHeight(ExcelShared_Drawing::EMUToPixels(self::array_item($xfrm->ext->attributes(), "cy")));
                                                
                                                if ($xfrm)
                                                {
                                                    $objDrawing->setRotation(ExcelShared_Drawing::angleToDegrees(self::array_item($xfrm->attributes(), "rot")));
                                                }
                                                if ($outerShdw)
                                                {
                                                    $shadow = $objDrawing->getShadow();
                                                    $shadow->setVisible(true);
                                                    $shadow->setBlurRadius(ExcelShared_Drawing::EMUTopixels(self::array_item($outerShdw->attributes(), "blurRad")));
                                                    $shadow->setDistance(ExcelShared_Drawing::EMUTopixels(self::array_item($outerShdw->attributes(), "dist")));
                                                    $shadow->setDirection(ExcelShared_Drawing::angleToDegrees(self::array_item($outerShdw->attributes(), "dir")));
                                                    $shadow->setAlignment((string) self::array_item($outerShdw->attributes(), "algn"));
                                                    $shadow->getColor()->setRGB(self::array_item($outerShdw->srgbClr->attributes(), "val"));
                                                    $shadow->setAlpha(self::array_item($outerShdw->srgbClr->alpha->attributes(), "val") / 1000);
                                                }
                                                $objDrawing->setWorksheet($docSheet);
                                            }
                                        }
                                    }
                                    
                                }
                            }
                        }
                        
                        // Loop trough definedNames
                        if ($xmlWorkbook->definedNames)
                        {
                            foreach ($xmlWorkbook->definedNames->definedName as $definedName)
                            {
                                // Extract range
                                $extractedRange = (string) $definedName;
                                $extractedRange = preg_replace('/\'(\w+)\'\!/', '', $extractedRange);
                                $extractedRange = str_replace('$', '', $extractedRange);
                                
                                // Valid range?
                                if (stripos((string) $definedName, '#REF!') !== false || $extractedRange == '')
                                {
                                    continue;
                                }
                                
                                // Some definedNames are only applicable if we are on the same sheet...
                                if ($definedName['localSheetId'] == $sheetId)
                                {
                                    // Switch on type
                                    switch ((string) $definedName['name'])
                                    {
                                        
                                        case '_xlnm._FilterDatabase':
                                            $docSheet->setAutoFilter($extractedRange);
                                            break;
                                        
                                        case '_xlnm.Print_Titles':
                                            // Split $extractedRange
                                            $extractedRange = explode(',', $extractedRange);
                                            
                                            // Set print titles
                                            if (isset($extractedRange[0]))
                                            {
                                                $range = explode(':', $extractedRange[0]);
                                                
                                                if (ExcelWorksheet::extractSheetTitle($range[0]) != '')
                                                    $range[0] = ExcelWorksheet::extractSheetTitle($range[0]);
                                                $range[0] = str_replace('$', '', $range[0]);
                                                if (ExcelWorksheet::extractSheetTitle($range[1]) != '')
                                                    $range[1] = ExcelWorksheet::extractSheetTitle($range[1]);
                                                $range[1] = str_replace('$', '', $range[1]);
                                                
                                                $docSheet->getPageSetup()->setColumnsToRepeatAtLeft($range);
                                            }
                                            if (isset($extractedRange[1]))
                                            {
                                                $range = explode(':', $extractedRange[1]);
                                                
                                                if (ExcelWorksheet::extractSheetTitle($range[0]) != '')
                                                    $range[0] = ExcelWorksheet::extractSheetTitle($range[0]);
                                                $range[0] = str_replace('$', '', $range[0]);
                                                if (ExcelWorksheet::extractSheetTitle($range[1]) != '')
                                                    $range[1] = ExcelWorksheet::extractSheetTitle($range[1]);
                                                $range[1] = str_replace('$', '', $range[1]);
                                                
                                                $docSheet->getPageSetup()->setRowsToRepeatAtTop($range);
                                            }
                                            
                                            break;
                                        
                                        case '_xlnm.Print_Area':
                                            $docSheet->getPageSetup()->setPrintArea($extractedRange);
                                            break;
                                        
                                        default:
                                            $excel->addNamedRange(new ExcelNamedRange((string) $definedName['name'], $docSheet, $extractedRange, true));
                                            break;
                                    }
                                }
                                else
                                {
                                    // "Global" definedNames
                                    $locatedSheet       = null;
                                    $extractedSheetName = '';
                                    if (strpos((string) $definedName, '!') !== false)
                                    {
                                        // Extract sheet name
                                        $extractedSheetName = ExcelWorksheet::extractSheetTitle((string) $definedName);
                                        
                                        // Locate sheet
                                        $locatedSheet = $excel->getSheetByName($extractedSheetName);
                                    }
                                    
                                    if (!is_null($locatedSheet))
                                    {
                                        $excel->addNamedRange(new ExcelNamedRange((string) $definedName['name'], $locatedSheet, $extractedRange, false));
                                    }
                                }
                            }
                        }
                        
                        // Garbage collect...
                        $docSheet->garbageCollect();
                        
                        // Next sheet id
                        ++$sheetId;
                    }
                    
                    if (!$this->_readDataOnly)
                    {
                        $excel->setActiveSheetIndex(intval($xmlWorkbook->bookView->workbookView["activeTab"]));
                    }
                    break;
            }
            
        }
        
        return $excel;
    }
    
    private function _readColor($color)
    {
        if (isset($color["rgb"]))
        {
            return $color["rgb"];
        }
        else if (isset($color["indexed"]))
        {
            return ExcelStyle_Color::indexedColor($color["indexed"])->getARGB();
        }
    }
    
    private function _readStyle($docStyle, $style)
    {
        // format code
        if ($style->applyNumberFormat)
            $docStyle->getNumberFormat()->setFormatCode($style->numFmt);
        
        // font
        if (isset($style->font) && ($style->applyFont || $style instanceof SimpleXMLElement))
        {
            $docStyle->getFont()->setName((string) $style->font->name["val"]);
            $docStyle->getFont()->setSize((string) $style->font->sz["val"]);
            if (isset($style->font->b))
            {
                $docStyle->getFont()->setBold(!isset($style->font->b["val"]) || $style->font->b["val"] == 'true');
            }
            if (isset($style->font->i))
            {
                $docStyle->getFont()->setItalic(!isset($style->font->i["val"]) || $style->font->i["val"] == 'true');
            }
            if (isset($style->font->strike))
            {
                $docStyle->getFont()->setStriketrough(!isset($style->font->strike["val"]) || $style->font->strike["val"] == 'true');
            }
            $docStyle->getFont()->getColor()->setARGB($this->_readColor($style->font->color));
            
            if (isset($style->font->u) && !isset($style->font->u["val"]))
            {
                $docStyle->getFont()->setUnderline(ExcelStyleFont::UNDERLINE_SINGLE);
            }
            else if (isset($style->font->u) && isset($style->font->u["val"]))
            {
                $docStyle->getFont()->setUnderline((string) $style->font->u["val"]);
            }
            
            if (isset($style->font->vertAlign) && isset($style->font->vertAlign["val"]))
            {
                $vertAlign = strtolower((string) $style->font->vertAlign["val"]);
                if ($vertAlign == 'superscript')
                {
                    $docStyle->getFont()->setSuperScript(true);
                }
                if ($vertAlign == 'subscript')
                {
                    $docStyle->getFont()->setSubScript(true);
                }
            }
        }
        
        // fill
        if (isset($style->fill) && ($style->applyFill || $style instanceof SimpleXMLElement))
        {
            if ($style->fill->gradientFill)
            {
                $gradientFill = $style->fill->gradientFill[0];
                $docStyle->getFill()->setFillType((string) $gradientFill["type"]);
                $docStyle->getFill()->setRotation(floatval($gradientFill["degree"]));
                $gradientFill->registerXPathNamespace("sml", "http://schemas.openxmlformats.org/spreadsheetml/2006/main");
                $docStyle->getFill()->getStartColor()->setARGB($this->_readColor(self::array_item($gradientFill->xpath("sml:stop[@position=0]"))->color));
                $docStyle->getFill()->getEndColor()->setARGB($this->_readColor(self::array_item($gradientFill->xpath("sml:stop[@position=1]"))->color));
            }
            elseif ($style->fill->patternFill)
            {
                $patternType = (string) $style->fill->patternFill["patternType"] != '' ? (string) $style->fill->patternFill["patternType"] : 'solid';
                $docStyle->getFill()->setFillType($patternType);
                if ($style->fill->patternFill->fgColor)
                {
                    $docStyle->getFill()->getStartColor()->setARGB($this->_readColor($style->fill->patternFill->fgColor));
                }
                if ($style->fill->patternFill->bgColor)
                {
                    $docStyle->getFill()->getEndColor()->setARGB($this->_readColor($style->fill->patternFill->bgColor));
                }
            }
        }
        
        // border
        if (isset($style->border) && ($style->applyBorder || $style instanceof SimpleXMLElement))
        {
            if ($style->border["diagonalUp"] == 'true')
            {
                $docStyle->getBorders()->setDiagonalDirection(ExcelStyleBorders::DIAGONAL_UP);
            }
            elseif ($style->border["diagonalDown"] == 'true')
            {
                $docStyle->getBorders()->setDiagonalDirection(ExcelStyleBorders::DIAGONAL_DOWN);
            }
            $docStyle->getBorders()->setOutline($style->border["outline"] == 'true');
            $this->_readBorder($docStyle->getBorders()->getLeft(), $style->border->left);
            $this->_readBorder($docStyle->getBorders()->getRight(), $style->border->right);
            $this->_readBorder($docStyle->getBorders()->getTop(), $style->border->top);
            $this->_readBorder($docStyle->getBorders()->getBottom(), $style->border->bottom);
            $this->_readBorder($docStyle->getBorders()->getDiagonal(), $style->border->diagonal);
            $this->_readBorder($docStyle->getBorders()->getVertical(), $style->border->vertical);
            $this->_readBorder($docStyle->getBorders()->getHorizontal(), $style->border->horizontal);
        }
        
        // alignment
        if (isset($style->alignment) && ($style->applyAlignment || $style instanceof SimpleXMLElement))
        {
            $docStyle->getAlignment()->setHorizontal((string) $style->alignment["horizontal"]);
            $docStyle->getAlignment()->setVertical((string) $style->alignment["vertical"]);
            
            $textRotation = 0;
            if ((int) $style->alignment["textRotation"] <= 90)
            {
                $textRotation = (int) $style->alignment["textRotation"];
            }
            else if ((int) $style->alignment["textRotation"] > 90)
            {
                $textRotation = 90 - (int) $style->alignment["textRotation"];
            }
            
            $docStyle->getAlignment()->setTextRotation(intval($textRotation));
            $docStyle->getAlignment()->setWrapText((string) $style->alignment["wrapText"] == "true" || (string) $style->alignment["wrapText"] == "1");
            $docStyle->getAlignment()->setShrinkToFit((string) $style->alignment["shrinkToFit"] == "true" || (string) $style->alignment["shrinkToFit"] == "1");
            $docStyle->getAlignment()->setIndent(intval((string) $style->alignment["indent"]) > 0 ? intval((string) $style->alignment["indent"]) : 0);
        }
        
        // protection
        if (isset($style->protection) && $style->applyProtection)
        {
            if (isset($style->protection['locked']))
            {
                if ((string) $style->protection['locked'] == 'true')
                {
                    $docStyle->getProtection()->setLocked(ExcelStyle_Protection::PROTECTION_PROTECTED);
                }
                else
                {
                    $docStyle->getProtection()->setLocked(ExcelStyle_Protection::PROTECTION_UNPROTECTED);
                }
            }
            
            if (isset($style->protection['hidden']))
            {
                if ((string) $style->protection['hidden'] == 'true')
                {
                    $docStyle->getProtection()->setHidden(ExcelStyle_Protection::PROTECTION_PROTECTED);
                }
                else
                {
                    $docStyle->getProtection()->setHidden(ExcelStyle_Protection::PROTECTION_UNPROTECTED);
                }
            }
        }
    }
    
    private function _readBorder($docBorder, $eleBorder)
    {
        if (isset($eleBorder["style"]))
        {
            $docBorder->setBorderStyle((string) $eleBorder["style"]);
        }
        if (isset($eleBorder->color))
        {
            $docBorder->getColor()->setARGB($this->_readColor($eleBorder->color));
        }
    }
    
    private function _parseRichText($is = null)
    {
        $value = new ExcelRichText();
        
        if (isset($is->t))
        {
            $value->createText(ExcelShared_String::ControlCharacterOOXML2PHP((string) $is->t));
        }
        else
        {
            foreach ($is->r as $run)
            {
                $objText = $value->createTextRun(ExcelShared_String::ControlCharacterOOXML2PHP((string) $run->t));
                
                if (isset($run->rPr))
                {
                    if (isset($run->rPr->rFont["val"]))
                    {
                        $objText->getFont()->setName((string) $run->rPr->rFont["val"]);
                    }
                    
                    if (isset($run->rPr->sz["val"]))
                    {
                        $objText->getFont()->setSize((string) $run->rPr->sz["val"]);
                    }
                    
                    if (isset($run->rPr->color))
                    {
                        $objText->getFont()->setColor(new ExcelStyle_Color($this->_readColor($run->rPr->color)));
                    }
                    
                    if ((isset($run->rPr->b["val"]) && ((string) $run->rPr->b["val"] == 'true' || (string) $run->rPr->b["val"] == '1')) || (isset($run->rPr->b) && !isset($run->rPr->b["val"])))
                    {
                        $objText->getFont()->setBold(true);
                    }
                    
                    if ((isset($run->rPr->i["val"]) && ((string) $run->rPr->i["val"] == 'true' || (string) $run->rPr->i["val"] == '1')) || (isset($run->rPr->i) && !isset($run->rPr->i["val"])))
                    {
                        $objText->getFont()->setItalic(true);
                    }
                    
                    if (isset($run->rPr->vertAlign) && isset($run->rPr->vertAlign["val"]))
                    {
                        $vertAlign = strtolower((string) $run->rPr->vertAlign["val"]);
                        if ($vertAlign == 'superscript')
                        {
                            $objText->getFont()->setSuperScript(true);
                        }
                        if ($vertAlign == 'subscript')
                        {
                            $objText->getFont()->setSubScript(true);
                        }
                    }
                    
                    if (isset($run->rPr->u) && !isset($run->rPr->u["val"]))
                    {
                        $objText->getFont()->setUnderline(ExcelStyleFont::UNDERLINE_SINGLE);
                    }
                    else if (isset($run->rPr->u) && isset($run->rPr->u["val"]))
                    {
                        $objText->getFont()->setUnderline((string) $run->rPr->u["val"]);
                    }
                    
                    if ((isset($run->rPr->strike["val"]) && ((string) $run->rPr->strike["val"] == 'true' || (string) $run->rPr->strike["val"] == '1')) || (isset($run->rPr->strike) && !isset($run->rPr->strike["val"])))
                    {
                        $objText->getFont()->setStriketrough(true);
                    }
                }
            }
        }
        
        return $value;
    }
    
    private static function array_item($array, $key = 0)
    {
        return (isset($array[$key]) ? $array[$key] : null);
    }
    
    private static function dir_add($base, $add)
    {
        return preg_replace('~[^/]+/\.\./~', '', dirname($base) . "/$add");
    }
    
    private static function toCSSArray($style)
    {
        $style = str_replace("\r", "", $style);
        $style = str_replace("\n", "", $style);
        
        $temp = explode(';', $style);
        
        $style = array();
        foreach ($temp as $item)
        {
            $item = explode(':', $item);
            
            if (strpos($item[1], 'px') !== false)
            {
                $item[1] = str_replace('px', '', $item[1]);
            }
            if (strpos($item[1], 'pt') !== false)
            {
                $item[1] = str_replace('pt', '', $item[1]);
                $item[1] = ExcelSharedFont::fontSizeToPixels($item[1]);
            }
            if (strpos($item[1], 'in') !== false)
            {
                $item[1] = str_replace('in', '', $item[1]);
                $item[1] = ExcelSharedFont::inchSizeToPixels($item[1]);
            }
            if (strpos($item[1], 'cm') !== false)
            {
                $item[1] = str_replace('cm', '', $item[1]);
                $item[1] = ExcelSharedFont::centimeterSizeToPixels($item[1]);
            }
            
            $style[$item[0]] = $item[1];
        }
        
        return $style;
    }
}
?>