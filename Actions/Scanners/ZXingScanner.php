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
        $includes = [];
        $includes[] = "<script type='text/javascript' src='{$facade->buildUrlToVendorFile($path . '/camera.js')}'></script>";
        $includes[] = "<script type='text/javascript' src='https://unpkg.com/@zxing/library@latest'></script>";
        $includes[] = "<link rel='stylesheet' type='text/css' href='{$facade->buildUrlToVendorFile($path . '/style.css')}'></script>";
        return $includes;
    }

    public function buildJsScannerInit(FacadeInterface $facade): string
    {
        $checkMark = $this->getCameraId() . '_image'; 
        $wrapper = <<<JS
        <div id="{$this->getCameraId()}" style="display: none;">
            <div id = "{$checkMark}" style="display: none; z-index: 15; position: fixed; top: 30%; left: 30%; height: 100vh, width: 100vw">
                <i class="fa fa-check-circle-o" aria-hidden="true" style="color: limegreen; font-size: 200px;"></i>
            </div>
        </div>

JS;
        $wrapper = preg_replace( "/(\r|\n)/", "", $wrapper);
        
        return <<<JS

        $(function() {
            $('body').append('{$wrapper}');
        });

        var codeReader = new ZXing.BrowserMultiFormatReader();

        camera.init('{$this->getCameraId()}', {
            showTakePhoto: false,
            onStreamStart: function(deviceId) {
                codeReader.decodeOnceFromVideoDevice(deviceId).then((result) => {
                    console.log('ScanResult', result.text);
                    {$this->getScanAction()->buildJsScanFunctionName($facade)}(result.text, 1);
                    var image = document.getElementById('{$checkMark}');
                    image.style.display = "inline-block";
                    setTimeout(function(){
                        image.style.display = "none";
                        camera.close();
                    }, 2000);
                }).catch((err) => {
                	console.error(err)
                })
            },
            onStreamEnd: function() {
                codeReader.stopStreams();
            },
        });

        
JS;
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