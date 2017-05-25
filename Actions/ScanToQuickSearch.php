<?php
namespace exface\BarcodeScanner\Actions;

class ScanToQuickSearch extends AbstractScanAction
{

    public function printHelperFunctions()
    {
        $table = $this->getTemplate()->getElement($this->getCalledByWidget()->getInputWidget());
        if ($this->getTemplate()->is('exface.JQueryMobile')) {
            $document_event = "on('pageshow', '#" . $table->getJqmPageId() . "',";
            $single_result_action_script = "{$table->getId()}_table.row(0).nodes().to$().trigger('taphold');";
        } else {
            $document_event = "ready(";
            $single_result_action_script = "
				var pos = {$table->getId()}_table.row(0).nodes().to$().position();
				var e = new jQuery.Event('contextmenu')
				e.pageX = Math.floor(window.innerWidth/2);
				e.pageY = pos.top + 120;
				{$table->getId()}_table.row(0).nodes().to$().trigger(e);
			";
        }
        $output = $this->buildJsCameraInit() . "
				$(document)." . $document_event . " function(){
					$(document).scannerDetection({
							timeBeforeScanTest: 200,
							avgTimeByChar: 40,
							scanButtonKeyCode: 116,
							endChar: [9,13],
							startChar: [120],
							ignoreIfFocusOn: 'input',
							onComplete:	function(barcode, qty){
								$('#" . $table->getId() . "_quickSearch').val(barcode); 
								$('#{$table->getId()}').one('draw.dt', function(){ 
									if ({$table->getId()}_table.rows()[0].length === 1){
										{$single_result_action_script} 
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
}
?>