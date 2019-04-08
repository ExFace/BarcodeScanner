<?php
namespace exface\BarcodeScanner\Actions;

use exface\Core\Factories\WidgetLinkFactory;
use exface\Core\Interfaces\Widgets\WidgetLinkInterface;
use exface\Core\CommonLogic\UxonObject;
use exface\Core\Interfaces\Facades\FacadeInterface;

/**
 * Selects the data item(s) having the scanned code in the specified column and presses a button afterwards.
 * 
 * The button is specified via widget link placed in the "button" property.
 * 
 * @author Andrej Kabachnik
 *
 */
class ScanToPressButton extends ScanToSelect
{
    private $press_button_widget_link = null;
    
    protected function buildJsScanFunctionBody(FacadeInterface $facade, $js_var_barcode, $js_var_qty, $js_var_overwrite) : string
    {
        if ($link = $this->getButtonWidgetLink()){
            $call_action = $this->getInputElement($facade)->getFacade()->getElement($link->getTargetWidget())->buildJsClickFunctionName() . '();';
        }
        
        return parent::buildJsScanFunctionBody($facade, $js_var_barcode, $js_var_qty, $js_var_overwrite) . $call_action;
    }
    
    /**
     * Returns a widget link to the button to be pressed after a successfull scan.
     * 
     * @return WidgetLinkInterface
     */
    public function getButtonWidgetLink()
    {
        return $this->button_widget_link;
    }
    
    /**
     * Specifies a widget link to the button, that should be pressed after scanning.
     * 
     * @uxon-property button
     * @uxon-type \exface\Core\CommonLogic\WidgetLink
     * 
     * @param UxonObject|string $string_or_uxon
     * @return \exface\BarcodeScanner\Actions\ScanToPressButton
     */
    public function setButton($string_or_uxon)
    {
        $this->button_widget_link = WidgetLinkFactory::createFromWidget($this->getWidgetDefinedIn(), $string_or_uxon);
        return $this;
    }
}
?>