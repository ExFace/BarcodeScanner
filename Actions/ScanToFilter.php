<?php namespace exface\BarcodeScanner\Actions;

class ScanToFilter extends AbstractScanAction {
	private $filter_id = null;
	
	public function print_helper_functions(){
		$table =  $this->get_template()->get_element($this->get_called_by_widget()->get_input_widget());
		$output = "
				$(document)." . ($this->get_app()->get_workbench()->ui()->get_template_from_request() instanceof  \exface\JQueryMobileTemplate\Template\jQueryMobile ? "on('pageshow', '#" . $table->get_jqm_page_id() . "'," : "ready(" ) . " function(){
					$(document).scannerDetection({
							timeBeforeScanTest: 200,
							avgTimeByChar: 40,
							scanButtonKeyCode: 116,
							endChar: [9,13],
							startChar: [120],
							ignoreIfFocusOn: 'input',
							onComplete:	function(barcode, qty){ 
								" . $this->get_app()->get_workbench()->ui()->get_template()->get_element_by_widget_id($this->get_filter_id(), $this->get_called_by_widget()->get_page()->get_id())->build_js_value_setter('barcode') . "; 
								$('#{$table->get_id()}').one('draw.dt', function(){ 
									if ({$table->get_id()}_table.rows()[0].length === 1){
										{$table->get_id()}_table.row(0).nodes().to$().trigger('taphold'); 
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
	
	public function get_filter_id() {
		return $this->filter_id;
	}
	
	public function set_filter_id($value) {
		$this->filter_id = $value;
	}  
}
?>