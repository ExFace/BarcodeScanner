<?php
namespace exface\BarcodeScanner\Actions;

use exface\Core\Actions\CustomFacadeScript;
use exface\Core\CommonLogic\Constants\Icons;
use exface\Core\Facades\AbstractAjaxFacade\Elements\AbstractJqueryElement;
use exface\Core\Exceptions\Actions\ActionConfigurationError;
use exface\Core\Interfaces\Facades\FacadeInterface;
use exface\Core\Interfaces\WidgetInterface;
use exface\BarcodeScanner\Interfaces\JsScannerWrapperInterface;
use exface\BarcodeScanner\Actions\Scanners\OnScanJsScanner;
use exface\BarcodeScanner\Actions\Scanners\QuaggaJsScanner;
use exface\Core\CommonLogic\UxonObject;
use exface\Core\Exceptions\UnexpectedValueException;
use exface\BarcodeScanner\Actions\Scanners\ZXingScanner;

abstract class AbstractScanAction extends CustomFacadeScript
{
    const SCANNER_TYPE_ONSCANJS = 'hardware';
    const SCANNER_TYPE_QUAGGA = 'quagga';
    const SCANNER_TYPE_CAMERA = 'camera';
    
    private $scanner = null;
    
    private $scannerUxon = null;
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Actions\CustomFacadeScript::init()
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
    public function getScannerType() : string
    {
        if ($this->scanner !== null) {
            return $this->scanner->getType();
        } else {
            if ($this->scannerUxon instanceof UxonObject) {
                $value = mb_strtolower($this->scannerUxon->getProperty('type'));
                if ($value !== self::SCANNER_TYPE_ONSCANJS && $value !== self::SCANNER_TYPE_CAMERA && $value !== self::SCANNER_TYPE_QUAGGA) {
                    throw new ActionConfigurationError($this, 'Invalid scanner type "' . $value . '" in action "' . $this->getAliasWithNamespace() . '"!');
                }
                return $value;
            } else {
                return self::SCANNER_TYPE_ONSCANJS;
            }
        }
    }
    
    /**
     * 
     * @return JsScannerWrapperInterface
     */
    protected function getScanner() : JsScannerWrapperInterface
    {
        if ($this->scanner === null) {
            $scannerClass = $this::getScannerClassFromType($this->getScannerType());
            $this->scanner = new $scannerClass($this, $this->scannerUxon);
        }
        return $this->scanner;
    }
    
    /**
     * Configuration for the scanner
     * 
     * @uxon-property scanner
     * @uxon-type \exface\BarcodeScanner\Actions\Scanners\AbstractJsScanner
     * @uxon-template {"type": "hardware"}
     * @uxon-default {"type": "hardware"}
     * 
     * @param UxonObject $uxon
     * @return AbstractScanAction
     */
    public function setScanner(UxonObject $uxon) : AbstractScanAction
    {
        $this->scannerUxon = $uxon;
        $this->scanner = null;
        
        return $this;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Actions\CustomFacadeScript::buildScriptHelperFunctions()
     */
    public function buildScriptHelperFunctions(FacadeInterface $facade) : string
    {        
        return $this->getScanner()->buildJsScannerInit($facade) . $this->buildJsScanFunction($facade);
    }
    
    /**
     * 
     * @return WidgetInterface
     */
    public function getInputWidget() : WidgetInterface
    {
        return $this->getWidgetDefinedIn()->getInputWidget();
    }
    
    /**
     * 
     * @return AbstractJqueryElement
     */
    public function getInputElement(FacadeInterface $facade) : AbstractJqueryElement
    {
        $element = $facade->getElement($this->getInputWidget());
        if (! ($element instanceof AbstractJqueryElement)){
            throw new ActionConfigurationError($this, 'Facade "' . $facade->getAlias() . '" not supported! The BarcodeScanner actions only work with facades based on AbstractJqueryElements.');
        }
        return $element;
    }
    
    /**
     * 
     * @return AbstractJqueryElement
     */
    protected function getButtonElement(FacadeInterface $facade) : AbstractJqueryElement
    {
        $element = $facade->getElement($this->getWidgetDefinedIn());
        if (! ($element instanceof AbstractJqueryElement)){
            throw new ActionConfigurationError($this, 'Facade "' . $facade->getAlias() . '" not supported! The BarcodeScanner actions only work with facades based on AbstractJqueryElements.');
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
    protected function buildJsScanFunction(FacadeInterface $facade) : string
    {
        return <<<JS

				function {$this->buildJsScanFunctionName($facade)}(barcode, qty, overwrite){
					{$this->buildJsScanFunctionBody($facade, 'barcode', 'qty', 'overwrite')}
				}

JS;
    }
					
    public function buildJsScanFunctionName(FacadeInterface $facade) : string
    {
        return $this->getButtonElement($facade)->buildJsFunctionPrefix() . 'onScan';
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
    protected abstract function buildJsScanFunctionBody(FacadeInterface $tempalte, $js_var_barcode, $js_var_qty, $js_var_overwrite) : string;
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Actions\CustomFacadeScript::getIncludes()
     */
    public function getIncludes(FacadeInterface $facade) : array
    {
        return $this->getScanner()->getIncludes($facade);
    }
    
    /**
     * 
     * @param string $scannerType
     * @throws UnexpectedValueException
     * @return string
     */
    public static function getScannerClassFromType(string $scannerType) : string
    {
        switch ($scannerType) {
            case self::SCANNER_TYPE_ONSCANJS:
                return OnScanJsScanner::class;
            case self::SCANNER_TYPE_QUAGGA:
                return QuaggaJsScanner::class;
            case self::SCANNER_TYPE_CAMERA:
                return ZXingScanner::class;
            default:
                throw new UnexpectedValueException('Invalid scanner type "' . $scannerType . '"!');
        }
    }
    
    public function buildScript(FacadeInterface $facade, WidgetInterface $widget)
    {
        $script = parent::buildScript($facade, $widget);
        $script .= $this->getScanner()->buildJsScan();
        return $script;
    }
}