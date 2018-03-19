<?php
namespace exface\BarcodeScanner\Actions;

use exface\Core\Factories\WidgetLinkFactory;
use exface\Core\Interfaces\Widgets\WidgetLinkInterface;
use exface\Core\CommonLogic\UxonObject;
use exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement;

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

    /**
     * 
     * {@inheritDoc}
     * @see \exface\BarcodeScanner\Actions\ScanToSelect::buildJsSelectByIndex()
     */
    protected function buildJsSelectByIndex(AbstractJqueryElement $inputElement, $js_var_rowIdx, $js_var_barcode, $js_var_qty, $js_var_overwrite)
    {
        if ($link = $this->getButtonWidgetLink()){
            $call_action = $inputElement->getTemplate()->getElement($link->getWidget())->buildJsClickFunctionName() . '();';
        }
        
        return parent::buildJsSelectByIndex($inputElement, $js_var_rowIdx, $js_var_barcode, $js_var_qty, $js_var_overwrite) . $call_action;
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
     * @param WidgetLinkInterface|UxonObject|string $widget_link_string_or_uxon
     * @return \exface\BarcodeScanner\Actions\ScanToPressButton
     */
    public function setButton($widget_link_string_or_uxon)
    {
        $this->button_widget_link = WidgetLinkFactory::createFromAnything($this->getWorkbench(), $widget_link_string_or_uxon);
        return $this;
    }
}
?>