<?php
namespace exface\BarcodeScanner\Actions;

use exface\Core\Exceptions\Actions\ActionConfigurationError;
use exface\Core\Interfaces\Facades\FacadeInterface;
use exface\Core\Facades\AbstractAjaxFacade\Elements\AbstractJqueryElement;
use exface\Core\Exceptions\Actions\ActionRuntimeError;

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

    protected function buildJsScanFunctionBody(FacadeInterface $facade, $js_var_barcode, $js_var_qty, $js_var_overwrite) : string
    {
        $inputElement = $this->getInputElement($facade);
        if (method_exists($inputElement, 'buildJsSelectRowByValue') === false) {
            $errorText = 'Cannot use selecting scan actions with facade element "' . get_class($inputElement) . '": missing method buildJsSelectRowByValue()!';
            $this->getWorkbench()->getLogger()->logException(new ActionRuntimeError($this, $errorText));
            return "console.error('{$errorText}')";
        }
        
        $col = $inputElement->getWidget()->getColumnByAttributeAlias($this->getSearchBarcodeInColumnId());
        return "

                    var scannedString = " . $js_var_barcode . ";
                    {$inputElement->buildJsSelectRowByValue($col, 'scannedString', $inputElement->buildJsShowMessageError("'Barcode \"' + scannedString + '\" not found!'"))}

";
            
    }
}