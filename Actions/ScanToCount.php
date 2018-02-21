<?php
namespace exface\BarcodeScanner\Actions;

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
    
    protected function buildJsSelectByIndex($js_var_rowIdx, $js_var_barcode, $js_var_qty, $js_var_overwrite)
    {
        return "

                        var incrementColIdx = table.column('" . $this->getIncrementValueInColumnId() . ":name').index();
						var row = table.row(" . $js_var_rowIdx . ").nodes().to$();
						var cell = table.cell({row: " . $js_var_rowIdx . ", column: incrementColIdx});
						row.trigger('click');
						row.addClass('selected');
						$('html, body').animate({ scrollTop: row.offset().top-80 }, 200);
						cell.nodes().to$().fadeOut(200, function(){ $(this).fadeIn(120); });
						if (" . $js_var_overwrite . " || !cell.data()){
							cell.data(" . $js_var_qty . ");
						} else {
							cell.data(parseInt(cell.data()) + " . $js_var_qty . ");
						}
						var diff = table.cell({row: " . $js_var_rowIdx . ", column: incrementColIdx+1}).data() - table.cell({row: " . $js_var_rowIdx . ", column: incrementColIdx}).data();
						if(diff === 0) cell.nodes().to$().removeClass('warning').removeClass('error').addClass('ok');
						else if(diff < 0) cell.nodes().to$().removeClass('ok').removeClass('error').addClass('warning');
						else if(diff > 0) cell.nodes().to$().removeClass('warning').removeClass('ok').addClass('error');

";
    }

    public function buildScriptHelperFunctions()
    {
        $table = $this->getTemplate()->getElement($this->getTriggerWidget()->getInputWidget());
        return parent::buildScriptHelperFunctions() . "				
				$('#" . $table->getId() . "').on( 'draw.dt', function () {
					" . $table->getId() . "_table.column('" . $this->getIncrementValueInColumnId() . ":name').nodes().to$().numpad();
				} );
				";
    }
}
?>