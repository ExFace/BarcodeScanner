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

    /**
     * 
     * @throws ActionConfigurationError
     * @return string
     */
    public function getTargetWidgetId() : string
    {
        if ($this->targetWidgetId === null){
            throw new ActionConfigurationError($this, 'No target widget specified for action ' . $this->getAlias . ': please set the "filter_id" property of the action!');
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
        return <<<JS

    {$facade->getElementByWidgetId($this->getTargetWidgetId(), $this->getWidgetDefinedIn()->getPage())->buildJsValueSetter($js_var_barcode)}

JS;
    }
}
?>