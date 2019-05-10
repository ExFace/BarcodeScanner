<?php
namespace exface\BarcodeScanner\Actions;

use exface\Core\Interfaces\Widgets\WidgetLinkInterface;
use exface\Core\CommonLogic\UxonObject;
use exface\Core\Interfaces\Facades\FacadeInterface;
use exface\Core\Facades\AbstractAjaxFacade\AbstractAjaxFacade;

/**
 * Selects data item(s) with the scanned code in the specified column and presses a button afterwards.
 * 
 * The button widget needs a static id, which is to be placed in `press_button_id`.
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
 *          "action": "my.App.ActionForSelectedItem",
 *          "id": "scan_action_button"
 *      },
 *      {
 *          "hidden": true,
 *          "action": {
 *              "alias": "exface.BarcodeScanner.ScanToCount",
 *              "scancode_attribute_alias": "barcode",
 *              "press_button_id": "scan_action_button"
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
class ScanToPressButton extends ScanToSelect
{
    private $press_button_id = null;
    
    protected function buildJsScanFunctionBody(FacadeInterface $facade, $js_var_barcode, $js_var_qty, $js_var_overwrite) : string
    {
        $buttonScript = $this->getPressButtonElement($facade)->buildJsClickFunctionName() . '();';
        return parent::buildJsScanFunctionBody($facade, $js_var_barcode, $js_var_qty, $js_var_overwrite) . $buttonScript;
    }
    
    /**
     * Returns a widget link to the button to be pressed after a successfull scan.
     * 
     * @return WidgetLinkInterface
     */
    protected function getPressButtonElement(AbstractAjaxFacade $facade)
    {
        $elem = $this->getInputElement($facade)->getFacade()->getElementByWidgetId($this->press_button_id);
        return $elem;
    }
    
    /**
     * Specifies a widget link to the button, that should be pressed after scanning.
     * 
     * @uxon-property press_button_id
     * @uxon-type uxon:$..id
     * 
     * @param UxonObject|string $string_or_uxon
     * @return \exface\BarcodeScanner\Actions\ScanToPressButton
     */
    public function setPressButtonId(string $id)
    {
        $this->press_button_id = $id;
        return $this;
    }
}
?>