<?php namespace exface\BarcodeScanner\Actions;

use exface\Core\Actions\CustomTemplateScript;

class AbstractScanAction extends CustomTemplateScript {
	private $use_camera_native_interface = false;
	private $use_camera_live_viewfinder = false;
	private $use_camera_barcode_types = 'ean, ean_8';
	
	protected function init(){
		$this->set_script_language('javascript');
		$this->set_icon_name('barcode');
	}
	
	public function get_use_camera_native_interface() {
		return $this->use_camera_native_interface;
	}
	
	public function set_use_camera_native_interface($value) {
		$this->use_camera_native_interface = $value;
		return $this;
	} 
	
	public function get_use_camera_live_viewfinder() {
		return $this->use_camera_live_viewfinder;
	}
	
	public function set_use_camera_live_viewfinder($value) {
		$this->use_camera_live_viewfinder = $value;
		return $this;
	}  
	
	public function get_use_camera_barcode_types() {
		return $this->use_camera_barcode_types;
	}
	
	public function set_use_camera_barcode_types($value) {
		$this->use_camera_barcode_types = $value;
		return $this;
	}  
	
	protected function build_js_camera_init(){
		$result = '';
		$button = $this->get_app()->get_workbench()->ui()->get_template()->get_element($this->get_called_by_widget());
		$readers = explode(',', $this->get_use_camera_barcode_types());
		for($i=0; $i<count($readers); $i++){
			$readers[$i] = trim($readers[$i]) . '_reader';
		}
		$readers_init = json_encode($readers);
		if ($this->get_use_camera_native_interface()){
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
		} elseif ($this->get_use_camera_live_viewfinder()) {
			$dialog = <<<JS
<div class="modal" id="{$button->get_id()}_scanner">\
	<div class="modal-dialog modal-lg">\
		<div class="modal-content">\
			<div class="modal-header">\
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
				<h4 class="modal-title">Scaner</h4>\
			</div>\
			<div class="modal-body" style="text-align:center;">\
				<div id="interactive" class="viewport"></div>\
			</div>\
			<div class="modal-footer">\
				\
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
       App.init();
    });
       		
    $('#{$button->get_id()}_scanner').on('hide.bs.modal', function(){
    	if (Quagga){
    		Quagga.stop();	
    	}
    });
       		
    var App = {
        init : function() {
            Quagga.init(this.state, function(err) {
                if (err) {
                    console.log(err);
                    return;
                }
                App.attachListeners();
                Quagga.start();
            });
        },
        attachListeners: function() {
            var self = this;
			
            $(".controls").on("click", "button.stop", function(e) {
                e.preventDefault();
                Quagga.stop();
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
            $(".controls").off("click", "button.stop");
            $(".controls .reader-config-group").off("change", "input, select");
        },
        setState: function(path, value) {
            var self = this;

            if (typeof self._accessByPath(self.inputMapper, path) === "function") {
                value = self._accessByPath(self.inputMapper, path)(value);
            }

            self._accessByPath(self.state, path, value);

            console.log(JSON.stringify(self.state));
            App.detachListeners();
            Quagga.stop();
            App.init();
        },
        inputMapper: {
            inputStream: {
                constraints: function(value){
                    var values = value.split('x');
                    return {
                        width: parseInt(values[0]),
                        height: parseInt(values[1])
                    }
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
                type : "LiveStream",
                constraints: {
                    width: 640,
                    height: 480,
                    facingMode: "environment" // or user
                }
            },
            locator: {
                patchSize: "medium",
                halfSample: true
            },
            numOfWorkers: 4,
            decoder: {
                readers : {$readers_init}
            },
            locate: true
        },
        lastResult : null
    };

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