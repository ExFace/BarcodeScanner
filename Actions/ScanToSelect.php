<?php
namespace exface\BarcodeScanner\Actions;

use exface\Core\Factories\WidgetLinkFactory;

class ScanToSelect extends AbstractScanAction
{

    private $barcode_prefixes = '';

    // TODO get the value from the app config as soon as configs are possible
    private $barcode_suffixes = '';

    // TODO get the value from the app config as soon as configs are possible
    private $search_barcode_in_column_id = '';

    private $increment_value_in_column_id = '';

    private $detect_longpress_after_sequential_scans = 5;
    
    private $press_button_widget_link = null;

    public function getBarcodePrefixes()
    {
        return $this->barcode_prefixes;
    }

    public function setBarcodePrefixes($value)
    {
        $this->barcode_prefixes = $value;
    }

    public function getBarcodeSuffixes()
    {
        return $this->barcode_suffixes;
    }

    public function setBarcodeSuffixes($value)
    {
        $this->barcode_suffixes = $value;
    }

    public function getSearchBarcodeInColumnId()
    {
        return $this->search_barcode_in_column_id;
    }

    public function setSearchBarcodeInColumnId($value)
    {
        $this->search_barcode_in_column_id = $value;
    }

    public function getIncrementValueInColumnId()
    {
        return $this->increment_value_in_column_id;
    }

    public function setIncrementValueInColumnId($value)
    {
        $this->increment_value_in_column_id = $value;
    }

    /**
     * Returns the number of sequential scans, that indicate a long press of the scanner button.
     * In this case
     * the GUI is supposed to open a number input dialog to allow the user to type the desired quantity.
     *
     * @return int
     */
    public function getDetectLongpressAfterSequentialScans()
    {
        return $this->detect_longpress_after_sequential_scans;
    }

    /**
     * Sets the number of sequential scans, that indicate a long press of the scanner button.
     * In this case
     * the GUI is supposed to open a number input dialog to allow the user to type the desired quantity.
     *
     * @param int $value            
     */
    public function setDetectLongpressAfterSequentialScans($value)
    {
        $this->detect_longpress_after_sequential_scans = $value;
    }

    public function printHelperFunctions()
    {
        $table = $this->getTemplate()->getElement($this->getCalledByWidget()->getInputWidget());
        
        if ($link = $this->getPressButtonWidgetLink()){
            $call_action = $this->getTemplate()->getElement($link->getWidget())->buildJsClickFunctionName() . '();';
        }
        
        // TODO Make it possible to specify, which column to use for comparison - currently it is always the next column to the right
        $output = "
				function selectOnScan(barcode, qty, overwrite){
					var scannedString = barcode;
					var table = " . $table->getId() . "_table;
					var rowIdx = -1;
					var split = 1;
					// Find the row with the barcode scanned. If not found, it might also be possible, that the scanned string
					// contains 2, 3 or more barcodes glued together, so try splitting it a look again. 
					while (rowIdx == -1 && split <= 10){
						if(barcode.length % split === 0){
							if (split > 1){
								barcode = barcode.substring(0, barcode.length / split);
							}
							rowIdx = table.column('" . $this->getSearchBarcodeInColumnId() . ":name').data().indexOf(barcode);
						}
						if (rowIdx > -1) qty = qty + split - 1;
						split++;
					}
													
					if (rowIdx == -1){
						alert('Barcode \"' + scannedString + '\" not found!');
					} else {
						table.rows(rowIdx).select();
                        " . $call_action . "
					}
				}
				
				$(document)." . ($this->getTemplate()->is('exface.JQueryMobileTemplate') ? "on('pageshow', '#" . $table->getJqmPageId() . "'," : "ready(") . " function(){
						$(document).scannerDetection({
							timeBeforeScanTest: 200,
							scanButtonLongPressThreshold: " . $this->getDetectLongpressAfterSequentialScans() . ",
							" . ($this->getBarcodePrefixes() ? 'startChar: [' . $this->getBarcodePrefixes() . '],' : '') . "
							" . ($this->getBarcodeSuffixes() ? 'endChar: [' . $this->getBarcodeSuffixes() . '],' : '') . "
							avgTimeByChar: 40,
							scanButtonKeyCode: 116,
							startChar: [120],
							ignoreIfFocusOn: 'input',
							onComplete:	incrementCellValue,
							onScanButtonLongPressed: showKeyPad,
							//onReceive: function(string){console.log(string);}
					});
				});
				";
        
        if ($this->getTemplate()->is('exface.JQueryMobileTemplate')) {
            $output .= "
				$(document).on('pagehide', '#" . $table->getJqmPageId() . "', function(){
					$(document).scannerDetection(false);
				});
				";
        }
        
        return $output;
    }
    
    public function getPressButtonWidgetLink()
    {
        return $this->press_button_widget_link;
    }
    
    public function setPressButton($widget_link_string_or_uxon)
    {
        $this->press_button_widget_link = WidgetLinkFactory::createFromAnything($this->getWorkbench(), $widget_link_string_or_uxon);
        return $this;
    }
}
?>