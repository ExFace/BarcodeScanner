<?php
namespace exface\BarcodeScanner\Actions;

use exface\Core\Actions\CustomTemplateScript;
use exface\Core\DataTypes\BooleanDataType;
use exface\Core\CommonLogic\Constants\Icons;
use exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement;
use exface\Core\Exceptions\Actions\ActionConfigurationError;

abstract class AbstractScanAction extends CustomTemplateScript
{
    private $use_keyboard_scanner = true;
    
    private $use_file_upload = false;

    private $use_camera = false;

    private $switch_camera = false;

    private $viewfinder_width = '640';

    private $viewfinder_height = '480';

    private $barcode_types = 'ean, ean_8';
    
    // TODO get the value from the app config as soon as configs are possible
    private $barcode_prefixes = '';
    
    // TODO get the value from the app config as soon as configs are possible
    private $barcode_suffixes = '';
    
    // TODO get the value from the app config as soon as configs are possible
    private $detect_longpress_after_sequential_scans = 5;
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Actions\CustomTemplateScript::init()
     */
    protected function init()
    {
        $this->setScriptLanguage('javascript');
        $this->setIcon(Icons::BARCODE);
        $this->setName($this->getApp()->getTranslator()->Translate('ACTION.DEFAULT_NAME'));
    }
    
    /**
     * 
     * @return string
     */
    public function getBarcodePrefixes()
    {
        return $this->barcode_prefixes;
    }
    
    /**
     * Sets a comma-separated list of JS character codes for barcode prefixes.
     * 
     * Prefixes are special characters, that are being sent by some scanners to mark 
     * the end of the barcode read. Suffixes will be stripped off the end of the 
     * barcode!
     * 
     * @uxon-property barcode_prefixes
     * @uxon-type string
     * 
     * @param string $value
     * @return \exface\BarcodeScanner\Actions\AbstractScanAction
     */
    public function setBarcodePrefixes($value)
    {
        $this->barcode_prefixes = $value;
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getBarcodeSuffixes()
    {
        return $this->barcode_suffixes;
    }
    
    /**
     * Sets a comma-separated list of JS character codes for barcode suffixes.
     * 
     * Suffixes are special characters, that are being sent by some scanners to mark 
     * the end of the barcode read. Suffixes will be stripped off the end of the 
     * barcode!
     * 
     * @uxon-property barcode_suffixes
     * @uxon-type string
     * 
     * @param string $value
     * @return \exface\BarcodeScanner\Actions\AbstractScanAction
     */
    public function setBarcodeSuffixes($value)
    {
        $this->barcode_suffixes = $value;
        return $this;
    }
    
    /**
     * Returns the number of sequential scans, that indicate a long press of the scanner button.
     *
     * @return int
     */
    public function getDetectLongpressAfterSequentialScans()
    {
        return $this->detect_longpress_after_sequential_scans;
    }
    
    /**
     * Sets the number of sequential scans, that indicate a long press of the scanner button.
     * 
     * In this case the GUI is supposed to open a number input dialog to allow the user to type the desired quantity.
     *
     * @uxon-property detect_longpress_after_sequential_scans
     * @uxon-type number
     * 
     * @param integer $value
     * @return AbstractScanAction
     */
    public function setDetectLongpressAfterSequentialScans($value)
    {
        $this->detect_longpress_after_sequential_scans = $value;
        return $this;
    }
    
    /**
     * Returns TRUE if keyboard-scanners should be used and FALSE otherwise.
     *  
     * @return boolean
     */
    public function getUseKeyboardScanner()
    {
        return $this->use_keyboard_scanner;
    }
    
    /**
     * Set to FALSE to make the action ignore input via external scanners (built-in, bluetooth, USB, etc.).
     * 
     * This option is TRUE by default.
     * 
     * @uxon-property use_keyboard_scanner 
     * @uxon-type boolean
     * 
     * @param boolean $value
     * @return \exface\BarcodeScanner\Actions\AbstractScanAction
     */
    public function setUseKeyboardScanner($value)
    {
        $this->use_keyboard_scanner = BooleanDataType::cast($value);
        return $this;
    }

    public function getUseFileUpload()
    {
        return $this->use_file_upload;
    }

    /**
     * Set to TRUE to enable uploading images with barcodes to trigger the action - FALSE by default.
     * 
     * This option strongly depends on the device and the template used. 
     * 
     * @uxon-property use_file_upload
     * @uxon-type boolean
     * 
     * @param boolean $value
     * @return \exface\BarcodeScanner\Actions\AbstractScanAction
     */
    public function setUseFileUpload($value)
    {
        $this->use_file_upload = BooleanDataType::cast($value);
        return $this;
    }

    public function getUseCamera()
    {
        return $this->use_camera;
    }

    /**
     * Set to TRUE to enable scanning barcodes with the built-in camera of your device - FALSE by default.
     * 
     * This option strongly depends on the device and the template used. 
     * 
     * @uxon-property use_camera
     * @uxon-type boolean
     * 
     * @param boolean $value
     * @return \exface\BarcodeScanner\Actions\AbstractScanAction
     */
    public function setUseCamera($value)
    {
        $this->use_camera = BooleanDataType::cast($value);
        return $this;
    }

    /**
     * Returns a comma separated list of allowed barcode types or NULL if all types are allowed.
     * 
     * @return string
     */
    public function getBarcodeTypes()
    {
        return $this->barcode_types;
    }

    /**
     * Specifies a list of allowed barcode types (other barcodes will be ignored).
     * 
     * @uxon-property barcode_types
     * @uxon-type string
     * 
     * @param string $value
     * @return \exface\BarcodeScanner\Actions\AbstractScanAction
     */
    public function setBarcodeTypes($value)
    {
        $this->barcode_types = $value;
        return $this;
    }

    public function getSwitchCamera()
    {
        return $this->switch_camera;
    }

    public function setSwitchCamera($value)
    {
        $this->switch_camera = BooleanDataType::cast($value);
        return $this;
    }

    public function getCameraViewfinderWidth()
    {
        return $this->viewfinder_width;
    }

    public function setCameraViewfinderWidth($value)
    {
        $this->viewfinder_width = $value;
        return $this;
    }

    public function getCameraViewfinderHeight()
    {
        return $this->viewfinder_height;
    }

    public function setCameraViewfinderHeight($value)
    {
        $this->viewfinder_height = $value;
        return $this;
    }
    
    public function buildScriptHelperFunctions()
    {
        $output = '';
        
        if ($this->getUseCamera() || $this->getUseFileUpload()){
            $output .= $this->buildJsInitQuagga();
        } 
        
        // Add ScannerDetection in any case, as the camera scanner
        // will simply trigger it (the camera behaves as a keyboard
        // scanner)
        $output .= $this->buildJsInitScannerDetection();
        
        return $output . $this->buildJsScanFunction();
    }
    
    /**
     * 
     * @return AbstractJqueryElement
     */
    protected function getInputElement()
    {
        $element = $this->getTemplate()->getElement($this->getCalledByWidget()->getInputWidget());
        if (! ($element instanceof AbstractJqueryElement)){
            throw new ActionConfigurationError($this, 'Template "' . $this->getTemplate()->getAlias() . '" not supported! The BarcodeScanner actions only work with templates based on AbstractJqueryElements.');
        }
        return $element;
    }
    
    /**
     * 
     * @return AbstractJqueryElement
     */
    protected function getButtonElement()
    {
        $element = $this->getTemplate()->getElement($this->getCalledByWidget());
        if (! ($element instanceof AbstractJqueryElement)){
            throw new ActionConfigurationError($this, 'Template "' . $this->getTemplate()->getAlias() . '" not supported! The BarcodeScanner actions only work with templates based on AbstractJqueryElements.');
        }
        return $element;
    }
    
    /**
     * Returns the JS function to be called when a barcode scan is detected.
     * 
     * This method basically wraps buildJsScanFunctionBody in a JS function.
     * To implement a specific scan action, override buildSjScanFunction body,
     * rather than this method.
     * 
     * @return string
     */
    protected function buildJsScanFunction()
    {
        return <<<JS

				function {$this->buildJsScanFunctionName()}(barcode, qty, overwrite){
					{$this->buildJsScanFunctionBody('barcode', 'qty', 'overwrite')}
				}

JS;
    }
					
    protected function buildJsScanFunctionName()
    {
        return $this->getButtonElement()->buildJsFunctionPrefix() . 'onScan';
    }
    
    /**
     * Returns JS code to be called once a barcode scan is detected.
     * 
     * Override this method to implement a scan action.
     * 
     * @param string $js_var_barcode
     * @param string $js_var_qty
     * @param string $js_var_overwrite
     * @return string
     */
    protected abstract function buildJsScanFunctionBody($js_var_barcode, $js_var_qty, $js_var_overwrite);
    
    /**
     * Initializes the listener for scan events coming from keyboard scanners
     * (e.g. build-in, bluetooth or USB scanners).
     * 
     * @return string
     */
    protected function buildJsInitScannerDetection()
    {
        $input_element = $this->getInputElement();
        
        $js = "

                $(document)." . ($this->getTemplate()->is('exface.JQueryMobileTemplate') ? "on('pageshow', '#" . $input_element->getJqmPageId() . "'," : "ready(") . " function(){
						$(document).scannerDetection({
							timeBeforeScanTest: 200,
							scanButtonLongPressThreshold: " . $this->getDetectLongpressAfterSequentialScans() . ",
							" . ($this->getBarcodePrefixes() ? 'startChar: [' . $this->getBarcodePrefixes() . '],' : '') . "
							" . ($this->getBarcodeSuffixes() ? 'endChar: [' . $this->getBarcodeSuffixes() . '],' : '') . "
							avgTimeByChar: 40,
							scanButtonKeyCode: 116,
							startChar: [120],
							ignoreIfFocusOn: 'input',
							onComplete:	{$this->buildJsScanFunctionName()},
							//onScanButtonLongPressed: showKeyPad,
							//onReceive: function(string){console.log(string);}
					});
				});

";
        
        if ($this->getTemplate()->is('exface.JQueryMobileTemplate')) {
            $js .= "
				$(document).on('pagehide', '#" . $input_element->getJqmPageId() . "', function(){
					$(document).scannerDetection(false);
				});
				";
        }
        
        return $js;
    }

    /**
     * Initializes the camera/image scanner
     * 
     * @return string
     */
    protected function buildJsInitQuagga()
    {
        $result = '';
        $button = $this->getApp()->getWorkbench()->ui()->getTemplate()->getElement($this->getCalledByWidget());
        
        $readers = explode(',', $this->getBarcodeTypes());
        for ($i = 0; $i < count($readers); $i ++) {
            $readers[$i] = trim($readers[$i]) . '_reader';
        }
        $readers_init = json_encode($readers);
        
        $camera = $this->getSwitchCamera() ? 'user' : 'environment';
        
        if ($this->getUseFileUpload()) {
            $result = <<<JS

$(function() {
	$('#{$button->getId()}').after($('<input style="visibility:hidden; display:inline; width: 0px;"type="file" id="{$button->getId()}_file" accept="image/*;capture=camera"/>'));
	
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

            $("#{$button->getId()}_file").on("change", function(e) {
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
			{$button->buildJsBusyIconShow()}
            setTimeout(function() {
			    {$button->buildJsBusyIconHide()}
			}, 5000);
            Quagga.decodeSingle(config, function(result) { $(document).scannerDetection(result.codeResult.code); {$button->buildJsBusyIconHide()}});
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
        } elseif ($this->getUseCamera()) {
            $dialog = <<<JS
<div class="modal" id="{$button->getId()}_scanner">\
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
	
	$("#{$button->getId()}").on("click", function(e) {
       $('#{$button->getId()}_scanner').modal('show');
		Quagga.init({
				inputStream: {
	                type : "LiveStream",
	                constraints: {
	                    width: {$this->getCameraViewfinderWidth()},
	                    height: {$this->getCameraViewfinderHeight()},
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
       		
    $('#{$button->getId()}_scanner').on('hide.bs.modal', function(){
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
    		$(document).scannerDetection(result.codeResult.code);
    		window.scrollTo(0, 0);
    		$('#{$button->getId()}_scanner').modal('hide');
    	}
    });
});		
			
JS;
        }
        
        return $result;
    }
}
?>