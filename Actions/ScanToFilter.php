<?php
namespace exface\BarcodeScanner\Actions;

use exface\Core\Exceptions\Actions\ActionConfigurationError;

/**
 * Places the scanned code in the filter widget specified by the filter_id property and performs a search.
 * 
 * If the search returns a single result, the corresponding context menu is triggered automatically.
 * 
 * @author Andrej Kabachnik
 *
 */
class ScanToFilter extends ScanToQuickSearch
{

    private $filter_id = null;

    public function getFilterId()
    {
        if (is_null($this->filter_id)){
            throw new ActionConfigurationError($this, 'No filter widget specified for action ' . $this->getAlias . ': please set the "filter_id" property of the action!');
        }
        return $this->filter_id;
    }

    /**
     * Specifies the widget id of the filter to fill with the scanned code.
     * 
     * @uxon-property filter_id
     * @uxon-type string
     * 
     * @param string $value
     * @return \exface\BarcodeScanner\Actions\ScanToFilter
     */
    public function setFilterId($value)
    {
        $this->filter_id = $value;
        return $this;
    }
    
    protected function buildJsScanFunctionBody($js_var_barcode, $js_var_qty, $js_var_overwrite)
    {
        return "

                                " . $this->getTemplate()->getElementByWidgetId($this->getFilterId(), $this->getCalledByWidget()->getPage())->buildJsValueSetter($js_var_barcode) . "; 
								{$this->buildJsSingleResultHandler()}
								{$this->getInputElement()->buildJsRefresh()}; 

";
    }
}
?>