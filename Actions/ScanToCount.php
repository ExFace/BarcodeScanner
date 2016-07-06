<?php namespace exface\BarcodeScanner\Actions;

class ScanToCount extends AbstractScanAction {
	private $barcode_prefixes = ''; // TODO get the value from the app config as soon as configs are possible
	private $barcode_suffixes = ''; // TODO get the value from the app config as soon as configs are possible
	private $search_barcode_in_column_id = '';
	private $increment_value_in_column_id = '';
	private $detect_longpress_after_sequential_scans = 5;
	
	public function get_barcode_prefixes() {
		return $this->barcode_prefixes;
	}
	
	public function set_barcode_prefixes($value) {
		$this->barcode_prefixes = $value;
	}
	
	public function get_barcode_suffixes() {
		return $this->barcode_suffixes;
	}
	
	public function set_barcode_suffixes($value) {
		$this->barcode_suffixes = $value;
	}  

	public function get_search_barcode_in_column_id() {
		return $this->search_barcode_in_column_id;
	}
	
	public function set_search_barcode_in_column_id($value) {
		$this->search_barcode_in_column_id = $value;
	}
	
	public function get_increment_value_in_column_id() {
		return $this->increment_value_in_column_id;
	}
	
	public function set_increment_value_in_column_id($value) {
		$this->increment_value_in_column_id = $value;
	}    
	
	/**
	 * Returns the number of sequential scans, that indicate a long press of the scanner button. In this case
	 * the GUI is supposed to open a number input dialog to allow the user to type the desired quantity.
	 * @return int
	 */
	public function get_detect_longpress_after_sequential_scans() {
		return $this->detect_longpress_after_sequential_scans;
	}
	
	/**
	 * Sets the number of sequential scans, that indicate a long press of the scanner button. In this case
	 * the GUI is supposed to open a number input dialog to allow the user to type the desired quantity.
	 * @param int $value
	 */
	public function set_detect_longpress_after_sequential_scans($value) {
		$this->detect_longpress_after_sequential_scans = $value;
	}	
	
	public function print_helper_functions(){
		$table =  $this->get_template()->get_element($this->get_called_by_widget()->get_input_widget());
		// TODO Make it possible to specify, which column to use for comparison - currently it is always the next column to the right
		$output = "
				function incrementCellValue(barcode, qty, overwrite){
					var scannedString = barcode;
					var table = " . $table->get_id() . "_table;
					var rowIdx = -1;
					var split = 1;
					// Find the row with the barcode scanned. If not found, it might also be possible, that the scanned string
					// contains 2, 3 or more barcodes glued together, so try splitting it a look again. 
					while (rowIdx == -1 && split <= 10){
						if(barcode.length % split === 0){
							if (split > 1){
								barcode = barcode.substring(0, barcode.length / split);
							}
							rowIdx = table.column('" . $this->get_search_barcode_in_column_id() . ":name').data().indexOf(barcode);
						}
						if (rowIdx > -1) qty = qty + split - 1;
						split++;
					}
													
					if (rowIdx == -1){
						alert('Barcode \"' + scannedString + '\" not found!');
					} else {
						var incrementColIdx = table.column('" . $this->get_increment_value_in_column_id() . ":name').index();
						var row = table.row(rowIdx).nodes().to$();
						var cell = table.cell({row: rowIdx, column: incrementColIdx});
						row.trigger('click');
						row.addClass('selected');
						$('html, body').animate({ scrollTop: row.offset().top-80 }, 200);
						cell.nodes().to$().fadeOut(200, function(){ $(this).fadeIn(120); });
						if (overwrite || !cell.data()){
							cell.data(qty);
						} else {
							cell.data(parseInt(cell.data()) + qty);
						}
						var diff = table.cell({row: rowIdx, column: incrementColIdx+1}).data() - table.cell({row: rowIdx, column: incrementColIdx}).data();
						if(diff === 0) cell.nodes().to$().removeClass('warning').removeClass('error').addClass('ok');
						else if(diff < 0) cell.nodes().to$().removeClass('ok').removeClass('error').addClass('warning');
						else if(diff > 0) cell.nodes().to$().removeClass('warning').removeClass('ok').addClass('error');
					}
				}
							
				jQuery.fn.center = function ()
			    {
			        this.css('position','fixed');
			        this.css('top', ($(window).height() / 2) - (this.outerHeight() / 2));
			        this.css('left', ($(window).width() / 2) - (this.outerWidth() / 2));
			        return this;
			    }
							
				function showKeyPad(barcode, qty, target, xpos){
					
				}
				
				$(document)." . ($this->get_app()->exface()->ui()->get_template_from_request() instanceof  \exface\JQueryMobileTemplate\Template\jQueryMobile ? "on('pageshow', '#" . $table->get_jqm_page_id() . "'," : "ready(" ) . " function(){
						$(document).scannerDetection({
							timeBeforeScanTest: 200,
							scanButtonLongPressThreshold: " . $this->get_detect_longpress_after_sequential_scans() . ",
							" . ($this->get_barcode_prefixes() ? 'startChar: [' . $this->get_barcode_prefixes() . '],' : '') . "
							" . ($this->get_barcode_suffixes() ? 'endChar: [' . $this->get_barcode_suffixes() . '],' : '') . "
							avgTimeByChar: 40,
							scanButtonKeyCode: 116,
							startChar: [120],
							ignoreIfFocusOn: 'input',
							onComplete:	incrementCellValue,
							onScanButtonLongPressed: showKeyPad,
							//onReceive: function(string){console.log(string);}
					});
				});
				
				$('#" . $table->get_id() . "').on( 'draw.dt', function () {
					" . $table->get_id() . "_table.column('" . $this->get_increment_value_in_column_id() . ":name').nodes().to$().numpad();
				} );
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