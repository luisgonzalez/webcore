<?php
/**
 * ExcelWriterDocProps
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWriter_Excel2007_Theme extends ExcelWriterPart
{
    /**
     * Write theme to XML format
     *
     * @param 	ExcelWorkbook	$workbook
     * @return 	string 		XML Output
     * @throws 	Exception
     */
    public function writeTheme($workbook = null)
    {
        // Create XML writer
        $xDoc = null;
        if ($this->getParentWriter()->getUseDiskCaching())
        {
            $xDoc = new ExcelXmlWriter(ExcelXmlWriter::STORAGE_DISK, $this->getParentWriter()->getDiskCachingDirectory());
        }
        else
        {
            $xDoc = new ExcelXmlWriter(ExcelXmlWriter::STORAGE_MEMORY);
        }
        
        // XML header
        $xDoc->startDocument('1.0', 'UTF-8', 'yes');
        
        // a:theme
        $xDoc->startElement('a:theme');
        $xDoc->writeAttribute('xmlns:a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
        $xDoc->writeAttribute('name', 'Office Theme');
        
        // a:themeElements
        $xDoc->startElement('a:themeElements');
        
        {
            // a:clrScheme
            $xDoc->startElement('a:clrScheme');
            $xDoc->writeAttribute('name', 'Office');
            
            // a:dk1
            $xDoc->startElement('a:dk1');
            
            // a:sysClr
            $xDoc->startElement('a:sysClr');
            $xDoc->writeAttribute('val', 'windowText');
            $xDoc->writeAttribute('lastClr', '000000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:lt1
            $xDoc->startElement('a:lt1');
            
            // a:sysClr
            $xDoc->startElement('a:sysClr');
            $xDoc->writeAttribute('val', 'window');
            $xDoc->writeAttribute('lastClr', 'FFFFFF');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:dk2
            $xDoc->startElement('a:dk2');
            
            // a:sysClr
            $xDoc->startElement('a:srgbClr');
            $xDoc->writeAttribute('val', '1F497D');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:lt2
            $xDoc->startElement('a:lt2');
            
            // a:sysClr
            $xDoc->startElement('a:srgbClr');
            $xDoc->writeAttribute('val', 'EEECE1');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:accent1
            $xDoc->startElement('a:accent1');
            
            // a:sysClr
            $xDoc->startElement('a:srgbClr');
            $xDoc->writeAttribute('val', '4F81BD');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:accent2
            $xDoc->startElement('a:accent2');
            
            // a:sysClr
            $xDoc->startElement('a:srgbClr');
            $xDoc->writeAttribute('val', 'C0504D');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:accent3
            $xDoc->startElement('a:accent3');
            
            // a:sysClr
            $xDoc->startElement('a:srgbClr');
            $xDoc->writeAttribute('val', '9BBB59');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:accent4
            $xDoc->startElement('a:accent4');
            
            // a:sysClr
            $xDoc->startElement('a:srgbClr');
            $xDoc->writeAttribute('val', '8064A2');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:accent5
            $xDoc->startElement('a:accent5');
            
            // a:sysClr
            $xDoc->startElement('a:srgbClr');
            $xDoc->writeAttribute('val', '4BACC6');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:accent6
            $xDoc->startElement('a:accent6');
            
            // a:sysClr
            $xDoc->startElement('a:srgbClr');
            $xDoc->writeAttribute('val', 'F79646');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:hlink
            $xDoc->startElement('a:hlink');
            
            // a:sysClr
            $xDoc->startElement('a:srgbClr');
            $xDoc->writeAttribute('val', '0000FF');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:folHlink
            $xDoc->startElement('a:folHlink');
            
            // a:sysClr
            $xDoc->startElement('a:srgbClr');
            $xDoc->writeAttribute('val', '800080');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
        }
        
        {
            // a:fontScheme
            $xDoc->startElement('a:fontScheme');
            $xDoc->writeAttribute('name', 'Office');
            
            // a:majorFont
            $xDoc->startElement('a:majorFont');
            
            // a:latin
            $xDoc->startElement('a:latin');
            $xDoc->writeAttribute('typeface', 'Cambria');
            $xDoc->endElement();
            
            // a:ea 
            $xDoc->startElement('a:ea');
            $xDoc->writeAttribute('typeface', '');
            $xDoc->endElement();
            
            // a:cs
            $xDoc->startElement('a:cs');
            $xDoc->writeAttribute('typeface', '');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Jpan');
            $xDoc->writeAttribute('typeface', '?? ?????');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Hang');
            $xDoc->writeAttribute('typeface', '?? ??');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Hans');
            $xDoc->writeAttribute('typeface', '??');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Hant');
            $xDoc->writeAttribute('typeface', '????');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Arab');
            $xDoc->writeAttribute('typeface', 'Times New Roman');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Hebr');
            $xDoc->writeAttribute('typeface', 'Times New Roman');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Thai');
            $xDoc->writeAttribute('typeface', 'Tahoma');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Ethi');
            $xDoc->writeAttribute('typeface', 'Nyala');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Beng');
            $xDoc->writeAttribute('typeface', 'Vrinda');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Gujr');
            $xDoc->writeAttribute('typeface', 'Shruti');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Khmr');
            $xDoc->writeAttribute('typeface', 'MoolBoran');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Knda');
            $xDoc->writeAttribute('typeface', 'Tunga');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Guru');
            $xDoc->writeAttribute('typeface', 'Raavi');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Cans');
            $xDoc->writeAttribute('typeface', 'Euphemia');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Cher');
            $xDoc->writeAttribute('typeface', 'Plantagenet Cherokee');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Yiii');
            $xDoc->writeAttribute('typeface', 'Microsoft Yi Baiti');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Tibt');
            $xDoc->writeAttribute('typeface', 'Microsoft Himalaya');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Thaa');
            $xDoc->writeAttribute('typeface', 'MV Boli');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Deva');
            $xDoc->writeAttribute('typeface', 'Mangal');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Telu');
            $xDoc->writeAttribute('typeface', 'Gautami');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Taml');
            $xDoc->writeAttribute('typeface', 'Latha');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Syrc');
            $xDoc->writeAttribute('typeface', 'Estrangelo Edessa');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Orya');
            $xDoc->writeAttribute('typeface', 'Kalinga');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Mlym');
            $xDoc->writeAttribute('typeface', 'Kartika');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Laoo');
            $xDoc->writeAttribute('typeface', 'DokChampa');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Sinh');
            $xDoc->writeAttribute('typeface', 'Iskoola Pota');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Mong');
            $xDoc->writeAttribute('typeface', 'Mongolian Baiti');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Viet');
            $xDoc->writeAttribute('typeface', 'Times New Roman');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Uigh');
            $xDoc->writeAttribute('typeface', 'Microsoft Uighur');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:minorFont
            $xDoc->startElement('a:minorFont');
            
            // a:latin
            $xDoc->startElement('a:latin');
            $xDoc->writeAttribute('typeface', 'Calibri');
            $xDoc->endElement();
            
            // a:ea 
            $xDoc->startElement('a:ea');
            $xDoc->writeAttribute('typeface', '');
            $xDoc->endElement();
            
            // a:cs
            $xDoc->startElement('a:cs');
            $xDoc->writeAttribute('typeface', '');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Jpan');
            $xDoc->writeAttribute('typeface', '?? ?????');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Hang');
            $xDoc->writeAttribute('typeface', '?? ??');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Hans');
            $xDoc->writeAttribute('typeface', '??');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Hant');
            $xDoc->writeAttribute('typeface', '????');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Arab');
            $xDoc->writeAttribute('typeface', 'Arial');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Hebr');
            $xDoc->writeAttribute('typeface', 'Arial');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Thai');
            $xDoc->writeAttribute('typeface', 'Tahoma');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Ethi');
            $xDoc->writeAttribute('typeface', 'Nyala');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Beng');
            $xDoc->writeAttribute('typeface', 'Vrinda');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Gujr');
            $xDoc->writeAttribute('typeface', 'Shruti');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Khmr');
            $xDoc->writeAttribute('typeface', 'DaunPenh');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Knda');
            $xDoc->writeAttribute('typeface', 'Tunga');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Guru');
            $xDoc->writeAttribute('typeface', 'Raavi');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Cans');
            $xDoc->writeAttribute('typeface', 'Euphemia');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Cher');
            $xDoc->writeAttribute('typeface', 'Plantagenet Cherokee');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Yiii');
            $xDoc->writeAttribute('typeface', 'Microsoft Yi Baiti');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Tibt');
            $xDoc->writeAttribute('typeface', 'Microsoft Himalaya');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Thaa');
            $xDoc->writeAttribute('typeface', 'MV Boli');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Deva');
            $xDoc->writeAttribute('typeface', 'Mangal');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Telu');
            $xDoc->writeAttribute('typeface', 'Gautami');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Taml');
            $xDoc->writeAttribute('typeface', 'Latha');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Syrc');
            $xDoc->writeAttribute('typeface', 'Estrangelo Edessa');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Orya');
            $xDoc->writeAttribute('typeface', 'Kalinga');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Mlym');
            $xDoc->writeAttribute('typeface', 'Kartika');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Laoo');
            $xDoc->writeAttribute('typeface', 'DokChampa');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Sinh');
            $xDoc->writeAttribute('typeface', 'Iskoola Pota');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Mong');
            $xDoc->writeAttribute('typeface', 'Mongolian Baiti');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Viet');
            $xDoc->writeAttribute('typeface', 'Arial');
            $xDoc->endElement();
            
            // a:font
            $xDoc->startElement('a:font');
            $xDoc->writeAttribute('script', 'Uigh');
            $xDoc->writeAttribute('typeface', 'Microsoft Uighur');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
        }
        
        {
            // a:fmtScheme
            $xDoc->startElement('a:fmtScheme');
            $xDoc->writeAttribute('name', 'Office');
            
            // a:fillStyleLst
            $xDoc->startElement('a:fillStyleLst');
            
            // a:solidFill
            $xDoc->startElement('a:solidFill');
            
            // a:schemeClr
            $xDoc->startElement('a:schemeClr');
            $xDoc->writeAttribute('val', 'phClr');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:gradFill
            $xDoc->startElement('a:gradFill');
            $xDoc->writeAttribute('rotWithShape', '1');
            
            // a:gsLst
            $xDoc->startElement('a:gsLst');
            
            // a:gs
            $xDoc->startElement('a:gs');
            $xDoc->writeAttribute('pos', '0');
            
            // a:schemeClr
            $xDoc->startElement('a:schemeClr');
            $xDoc->writeAttribute('val', 'phClr');
            
            // a:tint
            $xDoc->startElement('a:tint');
            $xDoc->writeAttribute('val', '50000');
            $xDoc->endElement();
            
            // a:satMod
            $xDoc->startElement('a:satMod');
            $xDoc->writeAttribute('val', '300000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:gs
            $xDoc->startElement('a:gs');
            $xDoc->writeAttribute('pos', '35000');
            
            // a:schemeClr
            $xDoc->startElement('a:schemeClr');
            $xDoc->writeAttribute('val', 'phClr');
            
            // a:tint
            $xDoc->startElement('a:tint');
            $xDoc->writeAttribute('val', '37000');
            $xDoc->endElement();
            
            // a:satMod
            $xDoc->startElement('a:satMod');
            $xDoc->writeAttribute('val', '300000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:gs
            $xDoc->startElement('a:gs');
            $xDoc->writeAttribute('pos', '100000');
            
            // a:schemeClr
            $xDoc->startElement('a:schemeClr');
            $xDoc->writeAttribute('val', 'phClr');
            
            // a:tint
            $xDoc->startElement('a:tint');
            $xDoc->writeAttribute('val', '15000');
            $xDoc->endElement();
            
            // a:satMod
            $xDoc->startElement('a:satMod');
            $xDoc->writeAttribute('val', '350000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:lin
            $xDoc->startElement('a:lin');
            $xDoc->writeAttribute('ang', '16200000');
            $xDoc->writeAttribute('scaled', '1');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:gradFill
            $xDoc->startElement('a:gradFill');
            $xDoc->writeAttribute('rotWithShape', '1');
            
            // a:gsLst
            $xDoc->startElement('a:gsLst');
            
            // a:gs
            $xDoc->startElement('a:gs');
            $xDoc->writeAttribute('pos', '0');
            
            // a:schemeClr
            $xDoc->startElement('a:schemeClr');
            $xDoc->writeAttribute('val', 'phClr');
            
            // a:shade
            $xDoc->startElement('a:shade');
            $xDoc->writeAttribute('val', '51000');
            $xDoc->endElement();
            
            // a:satMod
            $xDoc->startElement('a:satMod');
            $xDoc->writeAttribute('val', '130000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:gs
            $xDoc->startElement('a:gs');
            $xDoc->writeAttribute('pos', '80000');
            
            // a:schemeClr
            $xDoc->startElement('a:schemeClr');
            $xDoc->writeAttribute('val', 'phClr');
            
            // a:shade
            $xDoc->startElement('a:shade');
            $xDoc->writeAttribute('val', '93000');
            $xDoc->endElement();
            
            // a:satMod
            $xDoc->startElement('a:satMod');
            $xDoc->writeAttribute('val', '130000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:gs
            $xDoc->startElement('a:gs');
            $xDoc->writeAttribute('pos', '100000');
            
            // a:schemeClr
            $xDoc->startElement('a:schemeClr');
            $xDoc->writeAttribute('val', 'phClr');
            
            // a:shade
            $xDoc->startElement('a:shade');
            $xDoc->writeAttribute('val', '94000');
            $xDoc->endElement();
            
            // a:satMod
            $xDoc->startElement('a:satMod');
            $xDoc->writeAttribute('val', '135000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:lin
            $xDoc->startElement('a:lin');
            $xDoc->writeAttribute('ang', '16200000');
            $xDoc->writeAttribute('scaled', '0');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:lnStyleLst
            $xDoc->startElement('a:lnStyleLst');
            
            // a:ln
            $xDoc->startElement('a:ln');
            $xDoc->writeAttribute('w', '9525');
            $xDoc->writeAttribute('cap', 'flat');
            $xDoc->writeAttribute('cmpd', 'sng');
            $xDoc->writeAttribute('algn', 'ctr');
            
            // a:solidFill
            $xDoc->startElement('a:solidFill');
            
            // a:schemeClr
            $xDoc->startElement('a:schemeClr');
            $xDoc->writeAttribute('val', 'phClr');
            
            // a:shade
            $xDoc->startElement('a:shade');
            $xDoc->writeAttribute('val', '95000');
            $xDoc->endElement();
            
            // a:satMod
            $xDoc->startElement('a:satMod');
            $xDoc->writeAttribute('val', '105000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:prstDash
            $xDoc->startElement('a:prstDash');
            $xDoc->writeAttribute('val', 'solid');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:ln
            $xDoc->startElement('a:ln');
            $xDoc->writeAttribute('w', '25400');
            $xDoc->writeAttribute('cap', 'flat');
            $xDoc->writeAttribute('cmpd', 'sng');
            $xDoc->writeAttribute('algn', 'ctr');
            
            // a:solidFill
            $xDoc->startElement('a:solidFill');
            
            // a:schemeClr
            $xDoc->startElement('a:schemeClr');
            $xDoc->writeAttribute('val', 'phClr');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:prstDash
            $xDoc->startElement('a:prstDash');
            $xDoc->writeAttribute('val', 'solid');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:ln
            $xDoc->startElement('a:ln');
            $xDoc->writeAttribute('w', '38100');
            $xDoc->writeAttribute('cap', 'flat');
            $xDoc->writeAttribute('cmpd', 'sng');
            $xDoc->writeAttribute('algn', 'ctr');
            
            // a:solidFill
            $xDoc->startElement('a:solidFill');
            
            // a:schemeClr
            $xDoc->startElement('a:schemeClr');
            $xDoc->writeAttribute('val', 'phClr');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:prstDash
            $xDoc->startElement('a:prstDash');
            $xDoc->writeAttribute('val', 'solid');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            
            
            // a:effectStyleLst
            $xDoc->startElement('a:effectStyleLst');
            
            // a:effectStyle
            $xDoc->startElement('a:effectStyle');
            
            // a:effectLst
            $xDoc->startElement('a:effectLst');
            
            // a:outerShdw
            $xDoc->startElement('a:outerShdw');
            $xDoc->writeAttribute('blurRad', '40000');
            $xDoc->writeAttribute('dist', '20000');
            $xDoc->writeAttribute('dir', '5400000');
            $xDoc->writeAttribute('rotWithShape', '0');
            
            // a:srgbClr
            $xDoc->startElement('a:srgbClr');
            $xDoc->writeAttribute('val', '000000');
            
            // a:alpha
            $xDoc->startElement('a:alpha');
            $xDoc->writeAttribute('val', '38000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:effectStyle
            $xDoc->startElement('a:effectStyle');
            
            // a:effectLst
            $xDoc->startElement('a:effectLst');
            
            // a:outerShdw
            $xDoc->startElement('a:outerShdw');
            $xDoc->writeAttribute('blurRad', '40000');
            $xDoc->writeAttribute('dist', '23000');
            $xDoc->writeAttribute('dir', '5400000');
            $xDoc->writeAttribute('rotWithShape', '0');
            
            // a:srgbClr
            $xDoc->startElement('a:srgbClr');
            $xDoc->writeAttribute('val', '000000');
            
            // a:alpha
            $xDoc->startElement('a:alpha');
            $xDoc->writeAttribute('val', '35000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:effectStyle
            $xDoc->startElement('a:effectStyle');
            
            // a:effectLst
            $xDoc->startElement('a:effectLst');
            
            // a:outerShdw
            $xDoc->startElement('a:outerShdw');
            $xDoc->writeAttribute('blurRad', '40000');
            $xDoc->writeAttribute('dist', '23000');
            $xDoc->writeAttribute('dir', '5400000');
            $xDoc->writeAttribute('rotWithShape', '0');
            
            // a:srgbClr
            $xDoc->startElement('a:srgbClr');
            $xDoc->writeAttribute('val', '000000');
            
            // a:alpha
            $xDoc->startElement('a:alpha');
            $xDoc->writeAttribute('val', '35000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:scene3d
            $xDoc->startElement('a:scene3d');
            
            // a:camera
            $xDoc->startElement('a:camera');
            $xDoc->writeAttribute('prst', 'orthographicFront');
            
            // a:rot
            $xDoc->startElement('a:rot');
            $xDoc->writeAttribute('lat', '0');
            $xDoc->writeAttribute('lon', '0');
            $xDoc->writeAttribute('rev', '0');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:lightRig
            $xDoc->startElement('a:lightRig');
            $xDoc->writeAttribute('rig', 'threePt');
            $xDoc->writeAttribute('dir', 't');
            
            // a:rot
            $xDoc->startElement('a:rot');
            $xDoc->writeAttribute('lat', '0');
            $xDoc->writeAttribute('lon', '0');
            $xDoc->writeAttribute('rev', '1200000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:sp3d
            $xDoc->startElement('a:sp3d');
            
            // a:bevelT
            $xDoc->startElement('a:bevelT');
            $xDoc->writeAttribute('w', '63500');
            $xDoc->writeAttribute('h', '25400');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:bgFillStyleLst
            $xDoc->startElement('a:bgFillStyleLst');
            
            // a:solidFill
            $xDoc->startElement('a:solidFill');
            
            // a:schemeClr
            $xDoc->startElement('a:schemeClr');
            $xDoc->writeAttribute('val', 'phClr');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:gradFill
            $xDoc->startElement('a:gradFill');
            $xDoc->writeAttribute('rotWithShape', '1');
            
            // a:gsLst
            $xDoc->startElement('a:gsLst');
            
            // a:gs
            $xDoc->startElement('a:gs');
            $xDoc->writeAttribute('pos', '0');
            
            // a:schemeClr
            $xDoc->startElement('a:schemeClr');
            $xDoc->writeAttribute('val', 'phClr');
            
            // a:tint
            $xDoc->startElement('a:tint');
            $xDoc->writeAttribute('val', '40000');
            $xDoc->endElement();
            
            // a:satMod
            $xDoc->startElement('a:satMod');
            $xDoc->writeAttribute('val', '350000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:gs
            $xDoc->startElement('a:gs');
            $xDoc->writeAttribute('pos', '40000');
            
            // a:schemeClr
            $xDoc->startElement('a:schemeClr');
            $xDoc->writeAttribute('val', 'phClr');
            
            // a:tint
            $xDoc->startElement('a:tint');
            $xDoc->writeAttribute('val', '45000');
            $xDoc->endElement();
            
            // a:shade
            $xDoc->startElement('a:shade');
            $xDoc->writeAttribute('val', '99000');
            $xDoc->endElement();
            
            // a:satMod
            $xDoc->startElement('a:satMod');
            $xDoc->writeAttribute('val', '350000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:gs
            $xDoc->startElement('a:gs');
            $xDoc->writeAttribute('pos', '100000');
            
            // a:schemeClr
            $xDoc->startElement('a:schemeClr');
            $xDoc->writeAttribute('val', 'phClr');
            
            // a:shade
            $xDoc->startElement('a:shade');
            $xDoc->writeAttribute('val', '20000');
            $xDoc->endElement();
            
            // a:satMod
            $xDoc->startElement('a:satMod');
            $xDoc->writeAttribute('val', '255000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:path
            $xDoc->startElement('a:path');
            $xDoc->writeAttribute('path', 'circle');
            
            // a:fillToRect
            $xDoc->startElement('a:fillToRect');
            $xDoc->writeAttribute('l', '50000');
            $xDoc->writeAttribute('t', '-80000');
            $xDoc->writeAttribute('r', '50000');
            $xDoc->writeAttribute('b', '180000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:gradFill
            $xDoc->startElement('a:gradFill');
            $xDoc->writeAttribute('rotWithShape', '1');
            
            // a:gsLst
            $xDoc->startElement('a:gsLst');
            
            // a:gs
            $xDoc->startElement('a:gs');
            $xDoc->writeAttribute('pos', '0');
            
            // a:schemeClr
            $xDoc->startElement('a:schemeClr');
            $xDoc->writeAttribute('val', 'phClr');
            
            // a:tint
            $xDoc->startElement('a:tint');
            $xDoc->writeAttribute('val', '80000');
            $xDoc->endElement();
            
            // a:satMod
            $xDoc->startElement('a:satMod');
            $xDoc->writeAttribute('val', '300000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:gs
            $xDoc->startElement('a:gs');
            $xDoc->writeAttribute('pos', '100000');
            
            // a:schemeClr
            $xDoc->startElement('a:schemeClr');
            $xDoc->writeAttribute('val', 'phClr');
            
            // a:shade
            $xDoc->startElement('a:shade');
            $xDoc->writeAttribute('val', '30000');
            $xDoc->endElement();
            
            // a:satMod
            $xDoc->startElement('a:satMod');
            $xDoc->writeAttribute('val', '200000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // a:path
            $xDoc->startElement('a:path');
            $xDoc->writeAttribute('path', 'circle');
            
            // a:fillToRect
            $xDoc->startElement('a:fillToRect');
            $xDoc->writeAttribute('l', '50000');
            $xDoc->writeAttribute('t', '50000');
            $xDoc->writeAttribute('r', '50000');
            $xDoc->writeAttribute('b', '50000');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
        }
        
        $xDoc->endElement();
        
        // a:objectDefaults
        $xDoc->writeElement('a:objectDefaults', null);
        
        // a:extraClrSchemeLst
        $xDoc->writeElement('a:extraClrSchemeLst', null);
        
        $xDoc->endElement();
        
        // Return
        return $xDoc->getData();
    }
}
?>