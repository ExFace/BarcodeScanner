<?php
namespace exface\BarcodeScanner\Actions;

use exface\Core\Exceptions\Actions\ActionConfigurationError;
use exface\Core\Interfaces\Facades\FacadeInterface;

/**
 * Places the scanned code in a specified value widget.
 * 
 * The target widget is specified by `target_widget_id` and can be any value 
 * widget on the page.
 * 
 * Example for setting a specific filter in a DataTable upon scan:
 * 
 * ```
 * {
 *  "widget_type": "DataTable",
 *  "filters": [
 *      {
 *          "attribute_alias": "barcode",
 *          "id": "scancode_filter"
 *      }
 *  ],
 *  "buttons": [
 *      {
 *          "hidden": true,
 *          "refresh_input": true,
 *          "action": {
 *              "alias": "exface.BarcodeScanner.ScanToSetValue",
 *              "target_widget_id": "scancode_filter"
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
class ScanToSetValue extends AbstractScanAction
{
    private $targetWidgetId = null;
    
    private $valueDelimiter = null;
    
    private $appendValues = false;

    /**
     * 
     * @throws ActionConfigurationError
     * @return string
     */
    public function getTargetWidgetId() : string
    {
        if ($this->targetWidgetId === null){
            throw new ActionConfigurationError($this, 'No target widget specified for action ' . $this->getAlias() . ': please set the "filter_id" property of the action!');
        }
        return $this->targetWidgetId;
    }

    /**
     * Specifies the widget id of the filter to fill with the scanned code.
     * 
     * @uxon-property target_widget_id
     * @uxon-type uxon:$..id
     * 
     * @param string $value
     * @return ScanToSetValue
     */
    public function setTargetWidgetId($value) : ScanToSetValue
    {
        $this->targetWidgetId = $value;
        return $this;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\BarcodeScanner\Actions\ScanToQuickSearch::buildJsScanFunctionBody()
     */
    protected function buildJsScanFunctionBody(FacadeInterface $facade, $js_var_barcode, $js_var_qty, $js_var_overwrite) : string
    {
        $targetElement = $facade->getElementByWidgetId($this->getTargetWidgetId(), $this->getWidgetDefinedIn()->getPage());
        $newValueJs = $js_var_barcode;
        if ($this->getAppendValues() === true) {
            $newValueJs = "{$targetElement->buildJsValueGetter()} + '{$this->getAppendValuesDelimiter()}' + {$newValueJs}";
        }
        return <<<JS

    var sScanVal = $newValueJs;
    {$targetElement->buildJsValueSetter('sScanVal')}

JS;
    }
    
    /**
     *
     * @return bool
     */
    public function getAppendValues() : bool
    {
        return $this->appendValue;
    }
    
    /**
     * Set to TRUE to append scanned values instead of replacing.
     * 
     * By defualt scanned values replace existing values of the target widget. If this property
     * is set to `true` they will be appended instead using the `append_values_delimiter` as 
     * separator.
     * 
     * @uxon-property append_values
     * @uxon-type boolean
     * @uxon-default false
     * 
     * @param bool $value
     * @return ScanToSetValue
     */
    public function setAppendValues(bool $value) : ScanToSetValue
    {
        $this->appendValue = $value;
        return $this;
    }
    
    /**
     *
     * @return string
     */
    public function getAppendValuesDelimiter() : string
    {
        return $this->valueDelimiter;
    }
    
    /**
     * Seperator to use when appending new values (if `append_values` is `true`)
     * 
     * @uxon-property append_values_delimiter
     * @uxon-type string
     * @uxon-default ,
     * 
     * @param string $value
     * @return ScanToSetValue
     */
    public function setAppendValuesDelimiter(string $value) : ScanToSetValue
    {
        $this->valueDelimiter = $value;
        return $this;
    }
    
}