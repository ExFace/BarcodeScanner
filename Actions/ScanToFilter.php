<?php
namespace exface\BarcodeScanner\Actions;

class ScanToFilter extends AbstractScanAction
{

    private $filter_id = null;

    public function printHelperFunctions()
    {
        $table = $this->getTemplate()->getElement($this->getCalledByWidget()->getInputWidget());
        $output = "
				$(document)." . ($this->getApp()->getWorkbench()->ui()->getTemplateFromRequest() instanceof \exface\JQueryMobileTemplate\Template\jQueryMobile ? "on('pageshow', '#" . $table->getJqmPageId() . "'," : "ready(") . " function(){
					$(document).scannerDetection({
							timeBeforeScanTest: 200,
							avgTimeByChar: 40,
							scanButtonKeyCode: 116,
							endChar: [9,13],
							startChar: [120],
							ignoreIfFocusOn: 'input',
							onComplete:	function(barcode, qty){ 
								" . $this->getApp()->getWorkbench()->ui()->getTemplate()->getElementByWidgetId($this->getFilterId(), $this->getCalledByWidget()->getPage()->getAliasWithNamespace())->buildJsValueSetter('barcode') . "; 
								$('#{$table->getId()}').one('draw.dt', function(){ 
									if ({$table->getId()}_table.rows()[0].length === 1){
										{$table->getId()}_table.row(0).nodes().to$().trigger('taphold'); 
									}
								});
								{$table->getId()}_table.draw(); 
							}
					});
				});
				";
        if ($this->getTemplate()->is('exface.JQueryMobile')) {
            $output .= "
				$(document).on('pagehide', '#" . $table->getJqmPageId() . "', function(){
					$(document).scannerDetection(false);
				});
				";
        }
        
        return $output;
    }

    public function getFilterId()
    {
        return $this->filter_id;
    }

    public function setFilterId($value)
    {
        $this->filter_id = $value;
    }
}
?>