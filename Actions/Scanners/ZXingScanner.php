<?php
namespace exface\BarcodeScanner\Actions\Scanners;

use exface\Core\Interfaces\Facades\FacadeInterface;
use exface\BarcodeScanner\Actions\AbstractScanAction;
use exface\Core\CommonLogic\UxonObject;

class ZXingScanner extends AbstractJsScanner
{
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Interfaces\iCanBeConvertedToUxon::exportUxonObject()
     */
    public function exportUxonObject()
    {
        // TODO add other properties!
        return new UxonObject([
            'type' => 'camera'
        ]);
    }

    /**
     * 
     * {@inheritDoc}
     * @see \exface\BarcodeScanner\Actions\Scanners\AbstractJsScanner::getType()
     */
    public function getType() : string
    {
        return AbstractScanAction::SCANNER_TYPE_CAMERA;
    }

    /**
     * 
     * {@inheritDoc}
     * @see \exface\BarcodeScanner\Interfaces\JsScannerWrapperInterface::getIncludes()
     */
    public function getIncludes(FacadeInterface $facade): array
    {
        $path = "exface/Core/Facades/AbstractAjaxFacade/js/camera";
        $config = $this->getScanAction()->getWorkbench()->getApp('exface.BarcodeScanner')->getConfig();
        $includes = [];
        $includes[] = $facade->buildUrlToVendorFile($path . '/camera.js');
        $includes[] = $this->buildUrlIncludePath($config->getOption('LIBS.ZXING.JS'), $facade);
        $includes[] = "<link rel='stylesheet' type='text/css' href='{$facade->buildUrlToVendorFile($path . '/style.css')}'></link>";
        return $includes;
    }

    public function buildJsScannerInit(FacadeInterface $facade): string
    {
        $input_element = $this->getScanAction()->getInputElement($facade);
        $checkMark = $this->getCameraId() . '_image'; 
        $wrapper = <<<JS
        <div id="{$this->getCameraId()}" style="display: none;">
            <div id = "{$checkMark}" style="display: none; z-index: 2003; position: fixed; height: 100vh; width: 100vw">
                <i class="fa fa-check-circle-o" aria-hidden="true" style="position: fixed; top: calc(50% - 125px); left: calc(50% - 75px); color: limegreen; font-size: 200px;"></i>
            </div>
        </div>

JS;
        $wrapper = preg_replace( "/(\r|\n)/", "", $wrapper);
        
        $initJS = <<<JS

        $('body').append('{$wrapper}');

        var codeReader = new ZXing.BrowserMultiFormatReader();

        camera.init('{$this->getCameraId()}', {
            onStreamStart: function(deviceId, videoId) {
                codeReader.decodeOnceFromVideoDevice(deviceId, videoId).then((result) => {
                    console.log('ScanResult', result.text);
                    {$this->getScanAction()->buildJsScanFunctionName($facade)}(result.text, 1);
                    var image = document.getElementById('{$checkMark}');
                    image.style.display = "inline-block";
                    setTimeout(function(){
                        image.style.display = "none";
                        camera.close();
                    }, 2000);
                }).catch(function(err){
                	console.error(err)
                })
            },
            onStreamEnd: function() {
                codeReader.stopStreams();
            },
            showTakePhoto: false,
            hints: {$facade->getWorkbench()->getApp('exface.BarcodeScanner')->getTranslator()->translate('ZXING.SCANNER.HINTS')}
        });

        
JS;
    
    // Do some facade-specific stuff
    switch (true) {
        // Facades built on jQueryMobile
        case ($facade->is('exface.JQueryMobileFacade.JQueryMobileFacade')):
        case ($facade->is('exface.NativeDroid2Facade.NativeDroid2Facade')):
            $js = <<<JS
            
                $(document).on('pageshow', '#{$input_element->getJqmPageId()}', function(){
                    {$initJS}
				});
				
JS;
                    break;
                    
                    // Facades built on SAP UI5
        case ($facade->is('exface.UI5Facade.UI5Facade')):
            $controller = $input_element->getController();
            $controller->addOnShowViewScript($initJS);
            $js = '';
            break;
            
            // Regular jQuery facades
        default:
            $js = <<<JS
            
                $(document).ready(function(){
                    {$initJS}
				});
				
JS;
    }
    
    return $js;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\BarcodeScanner\Interfaces\JsScannerWrapperInterface::buildJsScan()
     */
    public function buildJsScan() : string
    {
        return "camera.open()";
        
        
        /*
         * 
         * {$this->getScanAction()->buildJsScanFunctionName($facade)}(sScanned, iQty);
         */
    }
    
    protected function getCameraId() : String
    {
        return $this->getScanAction()->getWidgetDefinedIn()->getId() . '_camera';
    }
}