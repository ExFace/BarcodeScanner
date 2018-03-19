<?php
namespace exface\BarcodeScanner\Actions;

use exface\Core\Interfaces\Templates\TemplateInterface;
use exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement;

/**
 * Places the scanned code in the quick search of the parent widget and performs a search.
 * 
 * If the search returns a single result, the corresponding context menu is triggered automatically.
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
    protected function buildJsScanFunctionBody(TemplateInterface $template, $js_var_barcode, $js_var_qty, $js_var_overwrite) : string
    {
        $input_element = $this->getInputElement($template);
        return "

                                $('#" . $input_element->getId() . "_quickSearch').val(" . $js_var_barcode . "); 
								{$this->buildJsSingleResultHandler($input_element)} 
								{$input_element->buildJsRefresh()}; 

";
    }
    
    /**
     * Returns a JS script triggering the context menu on the result row if there is only a single search result.
     * 
     * @return string
     */
    protected function buildJsSingleResultHandler(AbstractJqueryElement $inputElement) : string
    {
        $input_element_id = $inputElement->getId();
        
        if ($inputElement->getTemplate()->is('exface.JQueryMobileTemplate')) {
            $js = "{$input_element_id}_table.row(0).nodes().to$().trigger('taphold');";
        } else {
            $js = "
				var pos = {$input_element_id}_table.row(0).nodes().to$().position();
				var e = new jQuery.Event('contextmenu')
				e.pageX = Math.floor(window.innerWidth/2);
				e.pageY = pos.top + 120;
				{$input_element_id}_table.row(0).nodes().to$().trigger(e);
			";
        }
        
        $js = "
                                $('#{$input_element_id}').one('draw.dt', function(){ 
								    if ({$input_element_id}_table.rows()[0].length === 1){
                                        $js;
								    }
							    });
";
        
        return $js;
    }
}
?>