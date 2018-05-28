<?php
namespace exface\BarcodeScanner\Actions;

use exface\Core\Exceptions\Actions\ActionConfigurationError;
use exface\Core\Interfaces\Templates\TemplateInterface;
use exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement;

/**
 * Selects the data item(s) having the scanned code in the specified column.
 * 
 * Use the action ScanToPressButton if you want to perform an action right after selection!
 * 
 * @author Andrej
 *
 */
class ScanToSelect extends AbstractScanAction
{

    private $search_barcode_in_column_id = '';

    public function getSearchBarcodeInColumnId()
    {
        if (is_null($this->search_barcode_in_column_id)){
            throw new ActionConfigurationError($this, 'No column to search for the scanned barcode is specified: please set the "search_barcode_in_column_id" property for the action!');
        }
        return $this->search_barcode_in_column_id;
    }

    public function setSearchBarcodeInColumnId($value)
    {
        $this->search_barcode_in_column_id = $value;
        return $this;
    }

    protected function buildJsScanFunctionBody(TemplateInterface $template, $js_var_barcode, $js_var_qty, $js_var_overwrite) : string
    {
        // TODO Make it possible to specify, which column to use for comparison - currently it is always the next column to the right
        $inputElement = $this->getInputElement($template);
        return "

                    var scannedString = " . $js_var_barcode . ";
					var table = " . $inputElement->getId() . "_table;
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
						if (rowIdx > -1) " . $js_var_qty . " = " . $js_var_qty . " + split - 1;
						split++;
					}
													
					if (rowIdx == -1){
						{$inputElement->buildJsShowMessageError("'Barcode \"' + scannedString + '\" not found!'")};
					} else {
						{$this->buildJsSelectByIndex($inputElement, 'rowIdx', $js_var_barcode, $js_var_qty, $js_var_overwrite)}
					}

";
            
    }
    
    /**
     * Returns a JS script that should perform the selection of item identified by the passed JS variable.
     * 
     * By default, the corresponding row is selected.
     * 
     * @return string
     */
    protected function buildJsSelectByIndex(AbstractJqueryElement $inputElement, $js_var_rowIdx, $js_var_barcode, $js_var_qty, $js_var_overwrite)
    {
        return "table.rows(" . $js_var_rowIdx . ").select();";
    }

}
?>