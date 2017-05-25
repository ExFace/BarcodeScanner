<?php
namespace exface\BarcodeScanner\Actions;

class ScanToCount extends AbstractScanAction
{

    private $barcode_prefixes = '';

    // TODO get the value from the app config as soon as configs are possible
    private $barcode_suffixes = '';

    // TODO get the value from the app config as soon as configs are possible
    private $search_barcode_in_column_id = '';

    private $increment_value_in_column_id = '';

    private $detect_longpress_after_sequential_scans = 5;

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
        // TODO Make it possible to specify, which column to use for comparison - currently it is always the next column to the right
        $output = "
				function incrementCellValue(barcode, qty, overwrite){
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
						var incrementColIdx = table.column('" . $this->getIncrementValueInColumnId() . ":name').index();
						var row = table.row(rowIdx).nodes().to$();
						var cell = table.cell({row: rowIdx, column: incrementColIdx});
						row.trigger('click');
						row.addClass('selected');
						$('html, body').animate({ scrollTop: row.offset().top-80 }, 200);
						cell.nodes().to$().fadeOut(200, function(){ $(this).fadeIn(120); });
						if (overwrite || !cell.data()){
							cell.data(qty);
						} else {
							cell.data(parseInt(cell.data()) + qty);
						}
						var diff = table.cell({row: rowIdx, column: incrementColIdx+1}).data() - table.cell({row: rowIdx, column: incrementColIdx}).data();
						if(diff === 0) cell.nodes().to$().removeClass('warning').removeClass('error').addClass('ok');
						else if(diff < 0) cell.nodes().to$().removeClass('ok').removeClass('error').addClass('warning');
						else if(diff > 0) cell.nodes().to$().removeClass('warning').removeClass('ok').addClass('error');
					}
				}
							
				jQuery.fn.center = function ()
			    {
			        this.css('position','fixed');
			        this.css('top', ($(window).height() / 2) - (this.outerHeight() / 2));
			        this.css('left', ($(window).width() / 2) - (this.outerWidth() / 2));
			        return this;
			    }
							
				function showKeyPad(barcode, qty, target, xpos){
					
				}
				
				$(document)." . ($this->getApp()->getWorkbench()->ui()->getTemplateFromRequest() instanceof \exface\JQueryMobileTemplate\Template\jQueryMobile ? "on('pageshow', '#" . $table->getJqmPageId() . "'," : "ready(") . " function(){
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
				
				$('#" . $table->getId() . "').on( 'draw.dt', function () {
					" . $table->getId() . "_table.column('" . $this->getIncrementValueInColumnId() . ":name').nodes().to$().numpad();
				} );
				";
        
        if ($this->getTemplate()->is('exface.JQueryMobile')) {
            $output .= "
				$(document).on('pagehide', '#" . $table->getJqmPageId() . "', function(){
					$(document).scannerDetection(false);
				});
				";
        }
        
        return $output;
    }
}
?>