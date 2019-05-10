<?php
namespace exface\BarcodeScanner\Actions;

use exface\Core\Interfaces\Facades\FacadeInterface;
use exface\Core\Interfaces\Widgets\iHaveQuickSearch;
use exface\Core\Exceptions\Actions\ActionConfigurationError;
use exface\Core\Interfaces\WidgetInterface;

/**
 * Places the scanned code in the quick search of the input widget and performs a search.
 * 
 * In many cases, it may be a good idea to make one of the other buttons auto-execute it's action
 * when the search returns a single result: set the `bind_to_single_result` to `true` for the
 * button with the action to be performed after the scan.
 * 
 * Example:
 * 
 * ```
 * {
 *  "widget_type": "DataTable",
 *  "buttons": [
 *      {
 *          "action": "exface.BarcodeScanner.ScanToQuickSearch",
 *          "hidden": true
 *      },
 *      {
 *          "action_alias": "my.App.SomeImportantAction",
 *          "visibility": "promoted",
 *          "bind_to_single_result": true
 *      }
 *  ]
 * }
 * 
 * ```
 *
 * @author Andrej Kabachnik
 *
 */
class ScanToQuickSearch extends AbstractScanAction
{    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\BarcodeScanner\Actions\AbstractScanAction::buildJsScanFunctionBody()
     */
    protected function buildJsScanFunctionBody(FacadeInterface $facade, $js_var_barcode, $js_var_qty, $js_var_overwrite) : string
    {
        $inputWidget = $this->getInputWidget();
        if ($inputWidget instanceof iHaveQuickSearch) {
            $quickSearchElement = $facade->getElement($inputWidget->getQuickSearchWidget());
        } else {
            throw new ActionConfigurationError($this, 'Cannot use action "' . $this->getAliasWithNamespace() . '" with widget "' . $inputWidget->getWidgetType() . '": the widget must support quick search (iHaveQuickSearch interface).');
        }
        $input_element = $this->getInputElement($facade);
        return "

                                {$quickSearchElement->buildJsValueSetter($js_var_barcode)}; 
								{$input_element->buildJsRefresh()}; 

";
    }
}