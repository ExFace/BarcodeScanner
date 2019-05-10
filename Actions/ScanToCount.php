<?php
namespace exface\BarcodeScanner\Actions;

use exface\Core\Interfaces\Facades\FacadeInterface;

/**
 * This action will increment a numeric value in a data column with every scan.
 * 
 * It is supposed to be used with data widgets. The widget must have a column
 * containing scancodes and another column with corresponding values. With every
 * scan, the value corresponding to the scanned barcode will be incremented
 * by `value_increment` (1 by default).
 * 
 * Here is an example:
 * 
 * ```
 * {
 *  "widget_type": "DataTable",
 *  "columns": [
 *      {"attribute_alias": "barcode"},
 *      {"attribute_alias": "counter"}
 *  ],
 *  "buttons": [
 *      {
 *          "hidden": true,
 *          "action": {
 *              "alias": "exface.BarcodeScanner.ScanToCount",
 *              "scancode_attribute_alias": "barcode",
 *              "value_attribute_alias": "counter"
 *          }
 *      }
 *  ]
 * }
 * 
 * ```
 * 
 * @author Andrej Kabachnik
 *
 */
class ScanToCount extends ScanToSelect
{

    private $value_attribute_alias = '';
    
    private $value_increment = 1;

    public function getValueAttributeAlias()
    {
        return $this->value_attribute_alias;
    }

    /**
     * Alias of the attribute to count (or a data column name).
     * 
     * Each scan will increase the number in that column by the scan quantity.
     * 
     * @uxon-property value_attribute_alias
     * @uxon-type metamodel:attribute
     * 
     * @param string $value
     * @return \exface\BarcodeScanner\Actions\ScanToCount
     */
    public function setValueAttributeAlias(string $value)
    {
        $this->value_attribute_alias = $value;
        return $this;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\BarcodeScanner\Actions\ScanToSelect::buildJsScanFunctionBody()
     */
    protected function buildJsScanFunctionBody(FacadeInterface $facade, $js_var_barcode, $js_var_qty, $js_var_overwrite) : string
    {
        // TODO Make it possible to specify, which column to use for comparison - currently it is always the next column to the right
        $tableElement = $this->getInputElement($facade);
        $js = parent::buildJsScanFunctionBody($facade, $js_var_barcode, $js_var_qty, $js_var_overwrite) . <<<JS

        var valOld = {$tableElement->buildJsValueGetter($this->getValueAttributeAlias())};
        var valNew;
        if ({$js_var_overwrite} || !valOld){
			valNew = {$js_var_qty} * ({$this->getValueIncrement()});
		} else {
			valNew = parseInt(valOld) + {$js_var_qty} * ({$this->getValueIncrement()});
		}
        console.log('counting: ', valOld, valNew);
        void {$tableElement->buildJsValueSetter('valNew', $this->getValueAttributeAlias())};

JS;
        
        return $js;
    }
    
    /**
     * 
     * @return float
     */
    public function getValueIncrement() : float
    {
        return $this->value_increment;
    }
    
    /**
     * 
     * @uxon-property value_increment
     * @uxon-type number
     * @uxon-default 1
     * 
     * @param float $value
     * @return ScanToCount
     */
    public function setValueIncrement($value) : ScanToCount
    {
        $this->value_increment = $value;
        return $this;
    }
}