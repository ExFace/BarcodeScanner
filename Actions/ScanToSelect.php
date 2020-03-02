<?php
namespace exface\BarcodeScanner\Actions;

use exface\Core\Exceptions\Actions\ActionConfigurationError;
use exface\Core\Interfaces\Facades\FacadeInterface;
use exface\Core\Exceptions\Actions\ActionRuntimeError;
use exface\Core\Interfaces\WidgetInterface;
use exface\Core\Interfaces\Widgets\iShowData;

/**
 * Selects the data item(s) having the scanned code in the specified column.
 * 
 * Use the action `exface.BarcodeScanner.ScanToPressButton` if you want to perform an action right after selection!
 * 
 * Example:
 * 
 * ```
 * {
 *  "widget_type": "DataTable",
 *  "columns": [
 *      {"attribute_alias": "barcode"}
 *  ],
 *  "buttons": [
 *      {
 *          "hidden": true,
 *          "action": {
 *              "alias": "exface.BarcodeScanner.ScanToSelect",
 *              "scancode_attribute_alias": "barcode"
 *          }
 *      }
 *  ]
 * }
 * 
 * ```
 * 
 * @author Andrej
 *
 */
class ScanToSelect extends AbstractScanAction
{
    private $scancode_attribute_alias = '';
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\BarcodeScanner\Actions\AbstractScanAction::getInputWidget()
     */
    public function getInputWidget() : WidgetInterface
    {
        $widget = parent::getInputWidget();
        if (! ($widget instanceof iShowData)) {
            throw new ActionConfigurationError($this, 'Cannot use action "' . $this->getAliasWithNamespace() . '" with widget type "' . $widget->getWidgetType() . '": only Data widgets supported!');
        }
        return $widget;
    }

    public function getScancodeAttributeAlias()
    {
        if (is_null($this->scancode_attribute_alias)){
            throw new ActionConfigurationError($this, 'No column to search for the scanned barcode is specified: please set the "scancode_attribute_alias" property for the action!');
        }
        return $this->scancode_attribute_alias;
    }

    /**
     * The attribute alias (or data column name) of the column in which to search for the scanned code.
     * 
     * @uxon-property scancode_attribute_alias
     * @uxon-type metamodel:attribute
     * 
     * @param string $value
     * @return \exface\BarcodeScanner\Actions\ScanToSelect
     */
    public function setScancodeAttributeAlias(string $value)
    {
        $this->scancode_attribute_alias = $value;
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
        
        $col = $inputElement->getWidget()->getColumnByAttributeAlias($this->getScancodeAttributeAlias());
        return "

                    var scannedString = " . $js_var_barcode . ";
                    {$inputElement->buildJsSelectRowByValue($col, 'scannedString', $inputElement->buildJsShowMessageError("'Barcode \"' + scannedString + '\" not found!'"))}

";
            
    }
}