<?php
namespace exface\BarcodeScanner\Actions;

use exface\Core\Interfaces\Facades\FacadeInterface;
use exface\Core\Facades\AbstractAjaxFacade\Elements\AbstractJqueryElement;
use exface\Core\Facades\AbstractAjaxFacade\Elements\JqueryDataTablesTrait;

class ScanToCount extends ScanToSelect
{

    private $increment_value_in_column_id = '';

    public function getIncrementValueInColumnId()
    {
        return $this->increment_value_in_column_id;
    }

    /**
     * Id or name of the column, that contains the counted values.
     * 
     * Each scan will increase the number in that column by the scan quantity.
     * 
     * @uxon-property increment_value_in_column_id
     * @uxon-type string
     * 
     * @param string $value
     * @return \exface\BarcodeScanner\Actions\ScanToCount
     */
    public function setIncrementValueInColumnId($value)
    {
        $this->increment_value_in_column_id = $value;
        return $this;
    }
    
    protected function buildJsScanFunctionBody(FacadeInterface $facade, $js_var_barcode, $js_var_qty, $js_var_overwrite) : string
    {
        // TODO Make it possible to specify, which column to use for comparison - currently it is always the next column to the right
        $tableElement = $this->getInputElement($facade);
        $js = parent::buildJsScanFunctionBody($facade, $js_var_barcode, $js_var_qty, $js_var_overwrite) . <<<JS

        /*
        var table = {$tableElement->getId()}_table;
        var incrementColIdx = table.column('{$this->getIncrementValueInColumnId()}:name').index();
		var row = table.row(rowIdx).nodes().to$();
		var cell = table.cell({row: rowIdx, column: incrementColIdx});
		cell.nodes().to$().fadeOut(200, function(){ $(this).fadeIn(120); });
		if ({$js_var_overwrite} || !cell.data()){
			cell.data({$js_var_qty});
		} else {
			cell.data(parseInt(cell.data()) + {$js_var_qty});
		}
		var diff = table.cell({row: rowIdx, column: incrementColIdx+1}).data() - table.cell({row: rowIdx, column: incrementColIdx}).data();
		if(diff === 0) cell.nodes().to$().removeClass('warning').removeClass('error').addClass('ok');
		else if(diff < 0) cell.nodes().to$().removeClass('ok').removeClass('error').addClass('warning');
		else if(diff > 0) cell.nodes().to$().removeClass('warning').removeClass('ok').addClass('error');
        */

        var valOld = {$tableElement->buildJsValueGetter($this->getIncrementValueInColumnId())};
        var valNew;
        if ({$js_var_overwrite} || !valOld){
			valNew = {$js_var_qty};
		} else {
			valNew = parseInt(valOld) + {$js_var_qty};
		}
        console.log('counting: ', valOld, valNew);
        void {$tableElement->buildJsValueSetter('valNew', $this->getIncrementValueInColumnId())};

JS;
        
        return $js;
    }

    public function buildScriptHelperFunctions(FacadeInterface $facade) : string
    {
        $table = $facade->getElement($this->getWidgetDefinedIn()->getInputWidget());
        return parent::buildScriptHelperFunctions($facade) . "				
				/*$('#" . $table->getId() . "').on( 'draw.dt', function () {
					" . $table->getId() . "_table.column('" . $this->getIncrementValueInColumnId() . ":name').nodes().to$().numpad();
				} );*/
				";
    }
}
?>