<?php namespace exface\BarcodeScanner\Actions;

use exface\Core\Actions\CustomTemplateScript;
use exface\Core\DataTypes\BooleanDataType;

class AbstractScanAction extends CustomTemplateScript {
	private $use_file_upload = false;
	private $use_camera = false;
	private $switch_camera = false;
	private $viewfinder_width = '640';
	private $viewfinder_height = '480';
	private $barcode_types = 'ean, ean_8';
	
	protected function init(){
		$this->set_script_language('javascript');
		$this->set_icon_name('barcode');
	}
	
	public function get_use_file_upload() {
		return $this->use_file_upload;
	}
	
	public function set_use_file_upload($value) {
		$this->use_file_upload = BooleanDataType::parse($value);
		return $this;
	} 
	
	public function get_use_camera() {
		return $this->use_camera;
	}
	
	public function set_use_camera($value) {
		$this->use_camera = BooleanDataType::parse($value);
		return $this;
	}  
	
	public function get_barcode_types() {
		return $this->barcode_types;
	}
	
	public function set_barcode_types($value) {
		$this->barcode_types = $value;
		return $this;
	} 
	
	public function get_switch_camera() {
		return $this->switch_camera;
	}
	
	public function set_switch_camera($value) {
		$this->switch_camera = BooleanDataType::parse($value);
		return $this;
	}
	
	public function get_viewfinder_width() {
		return $this->viewfinder_width;
	}
	
	public function set_viewfinder_width($value) {
		$this->viewfinder_width = $value;
		return $this;
	}
	
	public function get_viewfinder_height() {
		return $this->viewfinder_height;
	}
	
	public function set_viewfinder_height($value) {
		$this->viewfinder_height = $value;
		return $this;
	}
	
	    
	
	protected function build_js_camera_init(){
		$result = '';
		$button = $this->get_app()->get_workbench()->ui()->get_template()->get_element($this->get_called_by_widget());
		
		$readers = explode(',', $this->get_barcode_types());
		for($i=0; $i<count($readers); $i++){
			$readers[$i] = trim($readers[$i]) . '_reader';
		}
		$readers_init = json_encode($readers);
		
		$camera = $this->get_switch_camera() ? 'user' : 'environment';
		
		if ($this->get_use_file_upload()){
			$result = <<<JS

$(function() {
	$('#{$button->get_id()}').after($('<input style="visibility:hidden; display:inline; width: 0px;"type="file" id="{$button->get_id()}_file" accept="image/*;capture=camera"/>'));
	
    var App = {
        init: function() {
            App.attachListeners();
        },
        config: {
            reader: "ean",
            length: 10
        },
        attachListeners: function() {
            var self = this;

            $("#{$button->get_id()}_file").on("change", function(e) {
                if (e.target.files && e.target.files.length) {
                    App.decode(URL.createObjectURL(e.target.files[0]));
                }
            });

            $(".controls button").on("click", function(e) {
                var input = document.querySelector(".controls input[type=file]");
                if (input.files && input.files.length) {
                    App.decode(URL.createObjectURL(input.files[0]));
                }
            });

            $(".controls .reader-config-group").on("change", "input, select", function(e) {
                e.preventDefault();
                var target = $(e.target),
                    value = target.attr("type") === "checkbox" ? target.prop("checked") : target.val(),
                    name = target.attr("name"),
                    state = self._convertNameToState(name);

                console.log("Value of "+ state + " changed to " + value);
                self.setState(state, value);
            });

        },
        _accessByPath: function(obj, path, val) {
            var parts = path.split('.'),
                depth = parts.length,
                setter = (typeof val !== "undefined") ? true : false;

            return parts.reduce(function(o, key, i) {
                if (setter && (i + 1) === depth) {
                    o[key] = val;
                }
                return key in o ? o[key] : {};
            }, obj);
        },
        _convertNameToState: function(name) {
            return name.replace("_", ".").split("-").reduce(function(result, value) {
                return result + value.charAt(0).toUpperCase() + value.substring(1);
            });
        },
        detachListeners: function() {
            $(".controls input[type=file]").off("change");
            $(".controls .reader-config-group").off("change", "input, select");
            $(".controls button").off("click");

        },
        decode: function(src) {
            var self = this,
                config = $.extend({}, self.state, {src: src});
			{$button->build_js_busy_icon_show()}
            setTimeout(function() {
			    {$button->build_js_busy_icon_hide()}
			}, 5000);
            Quagga.decodeSingle(config, function(result) { $(document).scannerDetection(result.codeResult.code); {$button->build_js_busy_icon_hide()}});
        },
        setState: function(path, value) {
            var self = this;

            if (typeof self._accessByPath(self.inputMapper, path) === "function") {
                value = self._accessByPath(self.inputMapper, path)(value);
            }

            self._accessByPath(self.state, path, value);

            console.log(JSON.stringify(self.state));
            App.detachListeners();
            App.init();
        },
        inputMapper: {
            inputStream: {
                size: function(value){
                    return parseInt(value);
                }
            },
            numOfWorkers: function(value) {
                return parseInt(value);
            },
            decoder: {
                readers: function(value) {
                    return [value + "_reader"];
                }
            }
        },
        state: {
            inputStream: {
                size: 800
            },
            locator: {
                patchSize: "medium",
                halfSample: false
            },
            numOfWorkers: 8,
            decoder: {
                readers: {$readers_init}
            },
            locate: true,
            src: null
        }
    };
    
    App.init();
}); 
			
JS;
		} elseif ($this->get_use_camera()) {
			$dialog = <<<JS
<div class="modal" id="{$button->get_id()}_scanner">\
	<style>\
		#interactive.viewport {position: relative;}\
		#interactive.viewport > canvas, #interactive.viewport > video { max-width: 100%; width: 100%;}\
		canvas.drawing, canvas.drawingBuffer {position: absolute;left: 0;top: 0;}\
	</style>\
	<div class="modal-dialog modal-lg">\
		<div class="modal-content">\
			<div class="modal-header">\
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
				<h4 class="modal-title">Scanner</h4>\
			</div>\
			<div class="modal-body" style="text-align:center;">\
				<div id="interactive" class="viewport"></div>\
			</div>\
			<div class="modal-footer">\
        		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
      		</div>\
		</div><!-- /.modal-content -->\
	</div><!-- /.modal-dialog -->\
</div><!-- /.modal -->\	
JS;
			$result = <<<JS
	
$(function() {
	$('body').append('{$dialog}');
	
	$("#{$button->get_id()}").on("click", function(e) {
       $('#{$button->get_id()}_scanner').modal('show');
		Quagga.init({
				inputStream: {
	                type : "LiveStream",
	                constraints: {
	                    width: {$this->get_viewfinder_width()},
	                    height: {$this->get_viewfinder_height()},
	                    facingMode: "{$camera}"
	                }
	            },
	            locator: {
	                patchSize: "medium",
	                halfSample: true
	            },
	            numOfWorkers: 4,
	            decoder: {
	            	readers: [{"format":"ean_reader","config":{}}]
	            },
	            locate: true
			}, 
			function(err) {
				if (err) {
					console.log(err);
					return;
				}
				Quagga.start();
			}
		);
    });
       		
    $('#{$button->get_id()}_scanner').on('hide.bs.modal', function(){
    	if (Quagga){
    		Quagga.stop();	
    	}
    });

	Quagga.onProcessed(function(result) {
        var drawingCtx = Quagga.canvas.ctx.overlay,
            drawingCanvas = Quagga.canvas.dom.overlay;

        if (result) {
            if (result.boxes) {
                drawingCtx.clearRect(0, 0, parseInt(drawingCanvas.getAttribute("width")), parseInt(drawingCanvas.getAttribute("height")));
                result.boxes.filter(function (box) {
                    return box !== result.box;
                }).forEach(function (box) {
                    Quagga.ImageDebug.drawPath(box, {x: 0, y: 1}, drawingCtx, {color: "green", lineWidth: 2});
                });
            }

            if (result.box) {
                Quagga.ImageDebug.drawPath(result.box, {x: 0, y: 1}, drawingCtx, {color: "#00F", lineWidth: 2});
            }

            if (result.codeResult && result.codeResult.code) {
                Quagga.ImageDebug.drawPath(result.line, {x: 'x', y: 'y'}, drawingCtx, {color: 'red', lineWidth: 3});
            }
        }
    });

    Quagga.onDetected(function(result) {    		
    	if (result.codeResult.code){
			$('#scanner_input').val(result.codeResult.code);
    		$('#livestream_scanner').modal('hide');
    	}
    });

    Quagga.onDetected(function(result) {    		
    	if (result.codeResult.code){
    		$(document).scannerDetection(result.codeResult.code);
    		window.scrollTo(0, 0);
    		$('#{$button->get_id()}_scanner').modal('hide');
    	}
    });
});		
			
JS;
		} 
		
		return $result;
	}

}
?>