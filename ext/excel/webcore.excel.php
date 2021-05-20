<?php
/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2009 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

require_once 'webcore.excel.worksheet.php';
require_once 'webcore.excel.document.php';

/**
 * IExcelWriter
 *
 * @package    WebCore
 * @subpackage Excel
 */
interface IExcelWriter extends IObject
{
    public function output();
    
    /**
     * Save Workbook to file
     *
     * @param 	string 		$pFileName
     */
    public function save($pFilename = null);
}

/**
 * IExcelReader
 *
 * @package    WebCore
 * @subpackage Excel
 */
interface IExcelReader extends IObject
{
    /**
     * Loads PHPExcel from file
     *
     * @param string $pFileName
     */
    public function load($pFilename);
}

/**
 * ExcelReader
 *
 * @package    WebCore
 * @subpackage Excel
 */
abstract class ExcelReaderBase extends ObjectBase implements IExcelReader
{
    /**
     * Loads PHPExcel from file
     *
     * @param string $pFileName
     */
    public function load($pFilename)
    {
        if (!file_exists($pFilename))
            throw new Exception("Could not open " . $pFilename . " for reading! File does not exist.");
    }
}

/**
 * IExcelReadFilter
 *
 * @package    WebCore
 * @subpackage Excel
 */
interface IExcelReadFilter extends IObject
{
    /**
     * Should this cell be read?
     *
     * @param 	$column		String column index
     * @param 	$row			Row index
     * @param	$worksheetName	Optional worksheet name
     * @return	boolean
     */
    public function readCell($column, $row, $worksheetName = '');
}

/**
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelReader_DefaultReadFilter extends ObjectBase implements IExcelReadFilter
{
    /**
     * Should this cell be read?
     *
     * @param 	$column		String column index
     * @param 	$row			Row index
     * @param	$worksheetName	Optional worksheet name
     * @return	boolean
     */
    public function readCell($column, $row, $worksheetName = '')
    {
        return true;
    }
}

/**
 * Workbook
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWorkbook extends ObjectBase
{
    /**
	 * Document properties
	 *
	 * @var ExcelDocumentProperties
	 */
	private $_properties;

	/**
	 * Document security
	 *
	 * @var ExcelDocumentSecurity
	 */
	private $_security;

	/**
	 * Collection of Worksheet objects
	 *
	 * @var ExcelWorksheet[]
	 */
	private $_workSheetCollection = array();

	/**
	 * Active sheet index
	 *
	 * @var int
	 */
	private $_activeSheetIndex = 0;

	/**
	 * Named ranges
	 *
	 * @var ExcelNamedRange[]
	 */
	private $_namedRanges = array();

	/**
	 * CellXf supervisor
	 *
	 * @var ExcelStyle
	 */
	private $_cellXfSupervisor;

	/**
	 * CellXf collection
	 *
	 * @var ExcelStyle[]
	 */
	private $_cellXfCollection = array();

	/**
	 * CellStyleXf collection
	 *
	 * @var ExcelStyle[]
	 */
	private $_cellStyleXfCollection = array();

	/**
	 * Create a new PHPExcel with one Worksheet
	 */
	public function __construct()
	{
		// Initialise worksheet collection and add one worksheet
		$this->_workSheetCollection = array();
		$this->_workSheetCollection[] = new ExcelWorksheet($this);
		$this->_activeSheetIndex = 0;

		// Create document properties
		$this->_properties = new ExcelDocumentProperties();

		// Create document security
		$this->_security = new ExcelDocumentSecurity();

		// Set named ranges
		$this->_namedRanges = array();

		// Create the cellXf supervisor
		$this->_cellXfSupervisor = new ExcelStyle(true);
		$this->_cellXfSupervisor->bindParent($this);

		// Create the default style
		$this->addCellXf(new ExcelStyle);
		$this->addCellStyleXf(new ExcelStyle);
	}

	/**
	 * Get properties
	 *
	 * @return ExcelDocumentProperties
	 */
	public function getProperties()
	{
		return $this->_properties;
	}

	/**
	 * Set properties
	 *
	 * @param ExcelDocumentProperties	$pValue
	 */
	public function setProperties(ExcelDocumentProperties $pValue)
	{
		$this->_properties = $pValue;
	}

	/**
	 * Get security
	 *
	 * @return ExcelDocumentSecurity
	 */
	public function getSecurity()
	{
		return $this->_security;
	}

	/**
	 * Set security
	 *
	 * @param ExcelDocumentSecurity	$pValue
	 */
	public function setSecurity(ExcelDocumentSecurity $pValue)
	{
		$this->_security = $pValue;
	}

	/**
	 * Get active sheet
	 *
	 * @return ExcelWorksheet
	 */
	public function getActiveSheet()
	{
		return $this->_workSheetCollection[$this->_activeSheetIndex];
	}

    /**
     * Create sheet and add it to this workbook
     *
     * @return ExcelWorksheet
     */
    public function createSheet($iSheetIndex = null)
    {
        $newSheet = new ExcelWorksheet($this);
        $this->addSheet($newSheet, $iSheetIndex);
        return $newSheet;
    }

    /**
     * Add sheet
     *
     * @param ExcelWorksheet $pSheet
	 * @param int|null $iSheetIndex Index where sheet should go (0,1,..., or null for last)
     * @return ExcelWorksheet
     * @throws Exception
     */
    public function addSheet($pSheet = null, $iSheetIndex = null)
    {
        if(is_null($iSheetIndex))
        {
            $this->_workSheetCollection[] = $pSheet;
        }
        else
        {
            // Insert the sheet at the requested index
            array_splice(
                $this->_workSheetCollection,
                $iSheetIndex,
                0,
                array($pSheet)
                );

			// Adjust active sheet index if necessary
			if ($this->_activeSheetIndex >= $iSheetIndex) {
				++$this->_activeSheetIndex;
			}

        }
		return $pSheet;
    }

	/**
	 * Remove sheet by index
	 *
	 * @param int $pIndex Active sheet index
	 * @throws Exception
	 */
	public function removeSheetByIndex($pIndex = 0)
	{
		if ($pIndex > count($this->_workSheetCollection) - 1) {
			throw new Exception("Sheet index is out of bounds.");
		} else {
			array_splice($this->_workSheetCollection, $pIndex, 1);
		}
	}

	/**
	 * Get sheet by index
	 *
	 * @param int $pIndex Sheet index
	 * @return ExcelWorksheet
	 * @throws Exception
	 */
	public function getSheet($pIndex = 0)
	{
		if ($pIndex > count($this->_workSheetCollection) - 1) {
			throw new Exception("Sheet index is out of bounds.");
		} else {
			return $this->_workSheetCollection[$pIndex];
		}
	}

	/**
	 * Get all sheets
	 *
	 * @return ExcelWorksheet[]
	 */
	public function getAllSheets()
	{
		return $this->_workSheetCollection;
	}

	/**
	 * Get sheet by name
	 *
	 * @param string $pName Sheet name
	 * @return ExcelWorksheet
	 * @throws Exception
	 */
	public function getSheetByName($pName = '')
	{
		$worksheetCount = count($this->_workSheetCollection);
		for ($i = 0; $i < $worksheetCount; ++$i) {
			if ($this->_workSheetCollection[$i]->getTitle() == $pName) {
				return $this->_workSheetCollection[$i];
			}
		}

		return null;
	}

	/**
	 * Get index for sheet
	 *
	 * @param ExcelWorksheet $pSheet
	 * @return Sheet index
	 * @throws Exception
	 */
	public function getIndex(ExcelWorksheet $pSheet)
	{
		foreach ($this->_workSheetCollection as $key => $value) {
			if ($value->getHashCode() == $pSheet->getHashCode()) {
				return $key;
			}
		}
	}

    /**
	 * Set index for sheet by sheet name.
	 *
	 * @param string $sheetName Sheet name to modify index for
	 * @param int $newIndex New index for the sheet
	 * @return New sheet index
	 * @throws Exception
	 */
    public function setIndexByName($sheetName, $newIndex)
    {
        $oldIndex = $this->getIndex($this->getSheetByName($sheetName));
        $pSheet = array_splice(
            $this->_workSheetCollection,
            $oldIndex,
            1
            );
        array_splice(
            $this->_workSheetCollection,
            $newIndex,
            0,
            $pSheet
            );
        return $newIndex;
    }

	/**
	 * Get sheet count
	 *
	 * @return int
	 */
	public function getSheetCount()
	{
		return count($this->_workSheetCollection);
	}

	/**
	 * Get active sheet index
	 *
	 * @return int Active sheet index
	 */
	public function getActiveSheetIndex()
	{
		return $this->_activeSheetIndex;
	}

	/**
	 * Set active sheet index
	 *
	 * @param int $pIndex Active sheet index
	 * @throws Exception
	 * @return ExcelWorksheet
	 */
	public function setActiveSheetIndex($pIndex = 0)
	{
		if ($pIndex > count($this->_workSheetCollection) - 1) {
			throw new Exception("Active sheet index is out of bounds.");
		} else {
			$this->_activeSheetIndex = $pIndex;
		}
		return $this->getActiveSheet();
	}

	/**
	 * Get sheet names
	 *
	 * @return string[]
	 */
	public function getSheetNames()
	{
		$returnValue = array();
		$worksheetCount = $this->getSheetCount();
		for ($i = 0; $i < $worksheetCount; ++$i) {
			array_push($returnValue, $this->getSheet($i)->getTitle());
		}

		return $returnValue;
	}

	/**
	 * Add external sheet
	 *
	 * @param ExcelWorksheet $pSheet External sheet to add
	 * @param int|null $iSheetIndex Index where sheet should go (0,1,..., or null for last)
	 * @throws Exception
	 * @return ExcelWorksheet
	 */
	public function addExternalSheet(ExcelWorksheet $pSheet, $iSheetIndex = null) {
		if (!is_null($this->getSheetByName($pSheet->getTitle()))) {
			throw new Exception("Workbook already contains a worksheet named '{$pSheet->getTitle()}'. Rename the external sheet first.");
		}

		// count how many cellXfs there are in this workbook currently, we will need this below
		$countCellXfs = count($this->_cellXfCollection);

		// copy all the shared cellXfs from the external workbook and append them to the current
		foreach ($pSheet->getParent()->getCellXfCollection() as $cellXf) {
			$this->addCellXf(clone $cellXf);
		}

		// move sheet to this workbook
		$pSheet->rebindParent($this);

		// update the cellXfs
		foreach ($pSheet->getCellCollection(false) as $cell) {
			$cell->setXfIndex( $cell->getXfIndex() + $countCellXfs );
		}

		return $this->addSheet($pSheet, $iSheetIndex);
	}

	/**
	 * Get named ranges
	 *
	 * @return ExcelNamedRange[]
	 */
	public function getNamedRanges() {
		return $this->_namedRanges;
	}

	/**
	 * Add named range
	 *
	 * @param ExcelNamedRange $namedRange
	 * @return PHPExcel
	 */
	public function addNamedRange(ExcelNamedRange $namedRange) {
		$this->_namedRanges[$namedRange->getWorksheet()->getTitle().'!'.$namedRange->getName()] = $namedRange;
		return true;
	}

	/**
	 * Get named range
	 *
	 * @param string $namedRange
	 */
	public function getNamedRange($namedRange, ExcelWorksheet $pSheet = null) {
		if ($namedRange != '' && !is_null($namedRange)) {
			if (!is_null($pSheet)) {
				$key = $pSheet->getTitle().'!'.$namedRange;
				if (isset($this->_namedRanges[$key])) {
					return $this->_namedRanges[$key];
				}
			}
			$returnCount = 0;
			foreach($this->_namedRanges as $_namedRange) {
				if ($_namedRange->getName() == $namedRange) {
					if ((!is_null($pSheet)) && ($_namedRange->getWorksheet()->getTitle() == $pSheet->getTitle())) {
						return $_namedRange;
					} else {
						$returnCount++;
						$returnValue = $_namedRange;
					}
				}
			}
			if ($returnCount == 1) {
				return $returnValue;
			}
		}

		return null;
	}

	/**
	 * Remove named range
	 *
	 * @param string $namedRange
	 * @return PHPExcel
	 */
	public function removeNamedRange($namedRange, ExcelWorksheet $pSheet = null) {
		if ($namedRange != '' && !is_null($namedRange)) {
			if (!is_null($pSheet)) {
				$key = $pSheet->getTitle().'!'.$namedRange;
				if (isset($this->_namedRanges[$key])) {
					unset($this->_namedRanges[$key]);
				}
			}
			foreach($this->_namedRanges as $_namedRange) {
				if ($_namedRange->getName() == $namedRange) {
					if ((!is_null($pSheet)) && ($_namedRange->getWorksheet()->getTitle() == $pSheet->getTitle())) {
						$key = $pSheet->getTitle().'!'.$namedRange;
						if (isset($this->_namedRanges[$key])) {
							unset($this->_namedRanges[$key]);
						}
					}
				}
			}
		}
		return $this;
	}

	/**
	 * Get worksheet iterator
	 *
	 * @return ExcelWorksheetIterator
	 */
	public function getWorksheetIterator() {
		return new ExcelWorksheetIterator($this);
	}

	/**
	 * Copy workbook (!= clone!)
	 *
	 * @return PHPExcel
	 */
	public function copy() {
		$copied = clone $this;

		$worksheetCount = count($this->_workSheetCollection);
		for ($i = 0; $i < $worksheetCount; ++$i) {
			$this->_workSheetCollection[$i] = $this->_workSheetCollection[$i]->copy();
			$this->_workSheetCollection[$i]->rebindParent($this);
		}

		return $copied;
	}

	/**
	 * Implement PHP __clone to create a deep clone, not just a shallow copy.
	 */
	public function __clone() {
		foreach($this as $key => $val) {
			if (is_object($val) || (is_array($val))) {
				$this->{$key} = unserialize(serialize($val));
			}
		}
	}

	/**
	 * Get the workbook collection of cellXfs
	 *
	 * @return ExcelStyle[]
	 */
	public function getCellXfCollection()
	{
		return $this->_cellXfCollection;
	}

	/**
	 * Get cellXf by index
	 *
	 * @param int $index
	 * @return ExcelStyle
	 */
	public function getCellXfByIndex($pIndex = 0)
	{
		return $this->_cellXfCollection[$pIndex];
	}

	/**
	 * Get cellXf by hash code
	 *
	 * @param string $pValue
	 * @return ExcelStyle|false
	 */
	public function getCellXfByHashCode($pValue = '')
	{
		foreach ($this->_cellXfCollection as $cellXf) {
			if ($cellXf->getHashCode() == $pValue) {
				return $cellXf;
			}
		}
		return false;
	}

	/**
	 * Get default style
	 *
	 * @return ExcelStyle
	 * @throws Exception
	 */
	public function getDefaultStyle()
	{
		if (isset($this->_cellXfCollection[0])) {
			return $this->_cellXfCollection[0];
		}
		throw new Exception('No default style found for this workbook');
	}

	/**
	 * Add a cellXf to the workbook
	 *
	 * @param ExcelStyle
	 */
	public function addCellXf(ExcelStyle $style)
	{
		$this->_cellXfCollection[] = $style;
		$style->setIndex(count($this->_cellXfCollection) - 1);
	}

	/**
	 * Remove cellXf by index. It is ensured that all cells get their xf index updated.
	 *
	 * @param int $pIndex Index to cellXf
	 * @throws Exception
	 */
	public function removeCellXfByIndex($pIndex = 0)
	{
		if ($pIndex > count($this->_cellXfCollection) - 1) {
			throw new Exception("CellXf index is out of bounds.");
		} else {
			// first remove the cellXf
			array_splice($this->_cellXfCollection, $pIndex, 1);

			// then update cellXf indexes for cells
			foreach ($this->_workSheetCollection as $worksheet) {
				foreach ($worksheet->getCellCollection(false) as $cell) {
					$xfIndex = $cell->getXfIndex();
					if ($xfIndex > $pIndex ) {
						// decrease xf index by 1
						$cell->setXfIndex($xfIndex - 1);
					} else if ($xfIndex == $pIndex) {
						// set to default xf index 0
						$cell->setXfIndex(0);
					}
				}
			}
		}
	}

	/**
	 * Get the cellXf supervisor
	 *
	 * @return ExcelStyle
	 */
	public function getCellXfSupervisor()
	{
		return $this->_cellXfSupervisor;
	}

	/**
	 * Get the workbook collection of cellStyleXfs
	 *
	 * @return ExcelStyle[]
	 */
	public function getCellStyleXfCollection()
	{
		return $this->_cellStyleXfCollection;
	}

	/**
	 * Get cellStyleXf by index
	 *
	 * @param int $pIndex
	 * @return ExcelStyle
	 */
	public function getCellStyleXfByIndex($pIndex = 0)
	{
		return $this->_cellStyleXfCollection[$pIndex];
	}

	/**
	 * Get cellStyleXf by hash code
	 *
	 * @param string $pValue
	 * @return ExcelStyle|false
	 */
	public function getCellStyleXfByHashCode($pValue = '')
	{
		foreach ($this->_cellXfStyleCollection as $cellStyleXf) {
			if ($cellStyleXf->getHashCode() == $pValue) {
				return $cellStyleXf;
			}
		}
		return false;
	}

	/**
	 * Add a cellStyleXf to the workbook
	 *
	 * @param ExcelStyle $pStyle
	 */
	public function addCellStyleXf(ExcelStyle $pStyle)
	{
		$this->_cellStyleXfCollection[] = $pStyle;
		$pStyle->setIndex(count($this->_cellStyleXfCollection) - 1);
	}

	/**
	 * Remove cellStyleXf by index
	 *
	 * @param int $pIndex
	 * @throws Exception
	 */
	public function removeCellStyleXfByIndex($pIndex = 0)
	{
		if ($pIndex > count($this->_cellStyleXfCollection) - 1) {
			throw new Exception("CellStyleXf index is out of bounds.");
		} else {
			array_splice($this->_cellStyleXfCollection, $pIndex, 1);
		}
	}

	/**
	 * Eliminate all unneeded cellXf and afterwards update the xfIndex for all cells
	 * and columns in the workbook
	 */
	public function garbageCollect()
	{
    	// how many references are there to each cellXf ?
		$countReferencesCellXf = array();
		foreach ($this->_cellXfCollection as $index => $cellXf) {
			$countReferencesCellXf[$index] = 0;
		}

		foreach ($this->getWorksheetIterator() as $sheet) {

			// from cells
			foreach ($sheet->getCellCollection(false) as $cell) {
				++$countReferencesCellXf[$cell->getXfIndex()];
			}

			// from row dimensions
			foreach ($sheet->getRowDimensions() as $rowDimension) {
				if ($rowDimension->getXfIndex() !== null) {
					++$countReferencesCellXf[$rowDimension->getXfIndex()];
				}
			}

			// from column dimensions
			foreach ($sheet->getColumnDimensions() as $columnDimension) {
				++$countReferencesCellXf[$columnDimension->getXfIndex()];
			}
		}

		// remove cellXfs without references and create mapping so we can update xfIndex
		// for all cells and columns
		$countNeededCellXfs = 0;
		foreach ($this->_cellXfCollection as $index => $cellXf) {
			if ($countReferencesCellXf[$index] > 0 || $index == 0) { // we must never remove the first cellXf
				++$countNeededCellXfs;
			} else {
				unset($this->_cellXfCollection[$index]);
			}
			$map[$index] = $countNeededCellXfs - 1;
		}
		$this->_cellXfCollection = array_values($this->_cellXfCollection);

		// update the index for all cellXfs
		foreach ($this->_cellXfCollection as $i => $cellXf) {
			echo $cellXf->setIndex($i);
		}

		// make sure there is always at least one cellXf (there should be)
		if (count($this->_cellXfCollection) == 0) {
			$this->_cellXfCollection[] = new ExcelStyle();
		}

		// update the xfIndex for all cells, row dimensions, column dimensions
		foreach ($this->getWorksheetIterator() as $sheet) {

			// for all cells
			foreach ($sheet->getCellCollection(false) as $cell) {
				$cell->setXfIndex( $map[$cell->getXfIndex()] );
			}

			// for all row dimensions
			foreach ($sheet->getRowDimensions() as $rowDimension) {
				if ($rowDimension->getXfIndex() !== null) {
					$rowDimension->setXfIndex( $map[$rowDimension->getXfIndex()] );
				}
			}

			// for all column dimensions
			foreach ($sheet->getColumnDimensions() as $columnDimension) {
				$columnDimension->setXfIndex( $map[$columnDimension->getXfIndex()] );
			}
		}

		// also do garbage collection for all the sheets
		foreach ($this->getWorksheetIterator() as $sheet) {
			$sheet->garbageCollect();
		}
	}
}
?>