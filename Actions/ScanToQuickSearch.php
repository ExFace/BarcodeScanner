<?php namespace exface\BarcodeScanner\Actions;

class ScanToQuickSearch extends AbstractScanAction {
	
	public function print_helper_functions(){
		$table =  $this->get_template()->get_element($this->get_called_by_widget()->get_input_widget());
		if ($this->get_template()->is('exface.JQueryMobile')){
			$document_event = "on('pageshow', '#" . $table->get_jqm_page_id() . "',";
			$single_result_action_script = "{$table->get_id()}_table.row(0).nodes().to$().trigger('taphold');";
		} else {
			$document_event = "ready(";
			$single_result_action_script = "
				var pos = {$table->get_id()}_table.row(0).nodes().to$().position();
				var e = new jQuery.Event('contextmenu')
				e.pageX = Math.floor(window.innerWidth/2);
				e.pageY = pos.top + 120;
				{$table->get_id()}_table.row(0).nodes().to$().trigger(e);
			";
		}
		$output = $this->build_js_camera_init() . "
				$(document)." . $document_event . " function(){
					$(document).scannerDetection({
							timeBeforeScanTest: 200,
							avgTimeByChar: 40,
							scanButtonKeyCode: 116,
							endChar: [9,13],
							startChar: [120],
							ignoreIfFocusOn: 'input',
							onComplete:	function(barcode, qty){
								$('#" . $table->get_id() . "_quickSearch').val(barcode); 
								$('#{$table->get_id()}').one('draw.dt', function(){ 
									if ({$table->get_id()}_table.rows()[0].length === 1){
										{$single_result_action_script} 
									}
								});
								{$table->get_id()}_table.draw(); 
							}
					});
				});								
				";
				
		if ($this->get_template()->is('exface.JQueryMobile')){
			$output .= "
				$(document).on('pagehide', '#" . $table->get_jqm_page_id() . "', function(){
					$(document).scannerDetection(false);
				});
				";
		}
		
		return $output;
	}
}
?>