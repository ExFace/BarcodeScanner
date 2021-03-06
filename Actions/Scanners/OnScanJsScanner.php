<?php
namespace exface\BarcodeScanner\Actions\Scanners;

use exface\BarcodeScanner\Actions\AbstractScanAction;
use exface\Core\Interfaces\Facades\FacadeInterface;
use exface\Core\CommonLogic\UxonObject;
use exface\Core\Interfaces\iCanBeConvertedToUxon;

class OnScanJsScanner extends AbstractJsScanner
{
    private $barcode_prefixes = [];
    
    private $barcode_suffixes = [9,13];
    
    private $barcodeScanEventPreventDefault = false;
    
    private $scanButtonKeyCode = null;
    
    private $barcodeScanDisaledIfFocus = true;
    
    private $detect_longpress_after_sequential_scans = 5;
    
    private $customKeyCodeMap = null;
    
    private $multiScanDelimiterKeyCode = null;
    
    private $multiScanDelimiterCharacter = EXF_LIST_SEPARATOR;
    
    private $preventEnterOnButtons = false;
    
    /**
     *
     * @return string
     */
    public function getBarcodePrefixKeyCodes() : array
    {
        $arr = $this->barcode_prefixes;
        
        if (($delimCode = $this->getMultiScanDelimiterKeyCode()) !== null) {
            if (($idx = array_search($delimCode, $arr)) !== false) {
                unset($arr[$idx]);
            }
        }
        
        return $arr;
    }
    
    /**
     * Sets a comma-separated list of JS character codes for barcode prefixes.
     *
     * Prefixes are special characters, that are being sent by some scanners to mark
     * the end of the barcode read. Suffixes will be stripped off the end of the
     * barcode!
     *
     * @uxon-property barcode_prefix_key_codes
     * @uxon-type string
     *
     * @param string $value
     * @return OnScanJsScanner
     */
    public function setBarcodePrefixKeyCodes(string $value) : OnScanJsScanner
    {
        $this->barcode_prefixes = explode(',', $value);
        return $this;
    }
    
    /**
     *
     * @return string[]
     */
    public function getBarcodeSuffixKeyCodes() : array
    {
        $arr = $this->barcode_suffixes;
        
        if (($delimCode = $this->getMultiScanDelimiterKeyCode()) !== null) {
            if (($idx = array_search($delimCode, $arr)) !== false) {
                unset($arr[$idx]);
            }
        }
        
        return $arr;
    }
    
    /**
     * Sets a comma-separated list of JS character codes for barcode suffixes.
     *
     * Suffixes are special characters, that are being sent by some scanners to mark
     * the end of the barcode read. Suffixes will be stripped off the end of the
     * barcode!
     *
     * @uxon-property barcode_suffix_key_codes
     * @uxon-type string
     * @uxon-default 9,13
     * @uxon-template 9,13
     *
     * @param string $value
     * @return OnScanJsScanner
     */
    public function setBarcodeSuffixKeyCodes(string $value) : OnScanJsScanner
    {
        $this->barcode_suffixes = explode(',', $value);
        return $this;
    }
    
    /**
     * Returns the number of sequential scans, that indicate a long press of the scanner button.
     *
     * @return int
     */
    public function getScanButtonLongPressTime()
    {
        return $this->detect_longpress_after_sequential_scans;
    }
    
    /**
     * If the scan-button is pressed for this number of milliseconds, a long-press is triggered.
     * 
     * This will only work if `scan_button_key_code` is properly configured.
     *
     * @uxon-property scan_button_long_press_time
     * @uxon-type integer
     * @uxon-default 500
     * @uxon-template 500
     *
     * @param int $value
     * @return OnScanJsScanner
     */
    public function setScanButtonLongPressTime(int $value) : OnScanJsScanner
    {
        $this->detect_longpress_after_sequential_scans = $value;
        return $this;
    }
    
    /**
     *
     * @return int
     */
    public function getScanButtonKeyCode() : ?int
    {
        return $this->scanButtonKeyCode;
    }
    
    /**
     * The key code of the scan-button (if the scanner sends a special key code for it)
     * 
     * @uxon-property scan_button_key_code
     * @uxon-type integer 
     * 
     * @param int $value
     * @return OnScanJsScanner
     */
    public function setScanButtonKeyCode(int $value) : OnScanJsScanner
    {
        $this->scanButtonKeyCode = $value;
        return $this;
    }
    
    /**
     *
     * @return bool
     */
    public function getScanEventPreventDefault() : bool
    {
        return $this->barcodeScanEventPreventDefault;
    }
    
    /**
     * Set to TRUE to prevent any other things to happen when a barcode scan is detected.
     *
     * For example, by default the enter-key is one of the `barcode_suffix_key_codes` and,
     * thus, every barcode scan, will shift the focus to the next focusable control. Setting
     * `barcode_scan_event_prevent_default` to `true` will prevent this.
     *
     * @uxon-property scan_event_prevent_default
     * @uxon-type boolean
     * @uxon-default false
     *
     * @param bool $value
     * @return OnScanJsScanner
     */
    public function setScanEventPreventDefault(bool $value) : OnScanJsScanner
    {
        $this->barcodeScanEventPreventDefault = $value;
        return $this;
    }
    
    public function getScannerDisabledIfFocusOnWidget() : bool
    {
        return $this->barcodeScanDisaledIfFocus;
    }
    
    /**
     * Set to FALSE to perform the action even if an input widget has explicit focus.
     *
     * Note: by default the scanned value will appear in the focused widget and get
     * processed by the action simultaniously.
     *
     * @uxon-property scanner_disabled_if_focus_on_widget
     * @uxon-type boolean
     * @uxon-default true
     *
     * @param bool $value
     * @return OnScanJsScanner
     */
    public function setScannerDisabledIfFocusOnWidget(bool $value) : OnScanJsScanner
    {
        $this->barcodeScanDisaledIfFocus = $value;
        return $this;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\BarcodeScanner\Interfaces\JsScannerWrapperInterface::buildJsScannerInit()
     */
    public function buildJsScannerInit(FacadeInterface $facade) : string
    {
        $input_element = $this->getScanAction()->getInputElement($facade);
        $js = '';
        
        $keyCodeMapper = '';
        if (($keyMap = $this->getCustomKeyCodes()) !== null) {
            $customKeyCases = '';
            foreach ($keyMap as $keyCode => $char) {
                $customKeyCases .= "case '$keyCode': return '$char';
                            ";
            }
            $keyCodeMapper = "keyCodeMapper: function(oEvent) {
                        switch (oEvent.which.toString()) {
                            {$customKeyCases}
                        }
                        return onScan.decodeKeyEvent(oEvent);
                    },";
        }
        
        $preprocessor = '';
        if ($this->getMultiScanDelimiterKeyCode() !== null) {
            $trimChar = preg_quote($this->getMultiScanDelimiterCharacter());
            $preprocessor = "sScanned = sScanned.replace(/^$trimChar+|$trimChar+$/g, '');";
        }
        
        $initJS = "
                    if (onScan.isAttachedTo(document)) {
                        onScan.detachFrom(document);
                    }
                    
                    onScan.attachTo(document, {
						scanButtonLongPressTime: " . $this->getScanButtonLongPressTime() . ",
						" . (empty($this->getBarcodePrefixKeyCodes()) === false ? 'prefixKeyCodes: [' . implode(',', $this->getBarcodePrefixKeyCodes()) . '],' : '') . "
						" . (empty($this->getBarcodeSuffixKeyCodes()) === false ? 'suffixKeyCodes: [' . implode(',', $this->getBarcodeSuffixKeyCodes()) . '],' : '') . "
						" . ($this->getScanButtonKeyCode() !== null ? 'scanButtonKeyCode: ' . $this->getScanButtonKeyCode() . ',' : '') . "
						" . ($this->getScannerDisabledIfFocusOnWidget() === true ? 'ignoreIfFocusOn: "input:not([type=\'button\']):not([type=\'submit\']):not([type=\'checkbox\'])",' : '') . "
						preventDefault: " . ($this->getScanEventPreventDefault() ? 'true' : 'false') . ",
                        stopPropagation: " . ($this->getScanEventPreventDefault() ? 'true' : 'false') . ",
                        captureEvents: true,
                        $keyCodeMapper
						onScan:	function(sScanned, iQty){
                            {$preprocessor}
                            {$this->getScanAction()->buildJsScanFunctionName($facade)}(sScanned, iQty);
                        },
                        {$this->buildJsOnKeyDetect()}
					});
					
";
        
        // Do some facade-specific stuff
        switch (true) {
            // Facades built on jQueryMobile
            case ($facade->is('exface.JQueryMobileFacade.JQueryMobileFacade')):
            case ($facade->is('exface.NativeDroid2Facade.NativeDroid2Facade')):
                $js = <<<JS
                
                $(document).on('pageshow', '#{$input_element->getJqmPageId()}', function(){
                    {$initJS}
				});
				
                $(document).on('pagehide', '#{$input_element->getJqmPageId()}', function(){
					onScan.detachFrom(document);
				});
				
JS;
                    break;
                    
            // Facades built on SAP UI5
            case ($facade->is('exface.UI5Facade.UI5Facade')):
                $controller = $input_element->getController();
                $controller->addOnShowViewScript($initJS);
                $controller->addOnHideViewScript("onScan.detachFrom(document);");
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
    
    protected function buildJsOnKeyDetect() : string
    {
        $js = '';
        if ($this->getPreventEnterOnButtons()) {
            $js .= <<<JS
                        
                        var oFocused = document.activeElement;
        				if (oEvent.which === 13 && (oFocused.matches("input[type='button']") || oFocused.matches("button")) && onScan.isScanInProgressFor(document)) {
        					oEvent.preventDefault();
                            oEvent.stopImmediatePropagation();
        				}

JS;
        }
        
        if ($js) {
            return "onKeyDetect: function(iKeyCode, oEvent) { $js },";
        }
        
        return '';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\BarcodeScanner\Interfaces\JsScannerWrapperInterface::getIncludes()
     */
    public function getIncludes(FacadeInterface $facade) : array
    {
        $config = $this->getScanAction()->getWorkbench()->getApp('exface.BarcodeScanner')->getConfig();
        return [
            $this->buildUrlIncludePath($config->getOption('LIBS.ONSCAN.JS'), $facade)
        ];
    }
    
    /**
     * 
     * @return iCanBeConvertedToUxon::exportUxonObject()
     */
    public function exportUxonObject()
    {
        return new UxonObject([
            'type' => 'hardware'
        ]);
    }
    
    public function getType() : string
    {
        return AbstractScanAction::SCANNER_TYPE_ONSCANJS;
    }
    
    /**
     *
     * @return string[]|NULL
     */
    public function getCustomKeyCodes() : ?array
    {
        $map = $this->customKeyCodeMap;
        if ($this->getMultiScanDelimiterKeyCode() !== null) {
            $map[$this->getMultiScanDelimiterKeyCode()] = $this->getMultiScanDelimiterCharacter();
        }
        return $map;
    }
    
    /**
     * Manually map key codes to specific characters.
     * 
     * @uxon-property custom_key_codes
     * @uxon-type object
     * @uxon-template {"":""}
     * 
     * @param <string,string> $value
     * @return OnScanJsScanner
     */
    public function setCustomKeyCodes(UxonObject $uxon) : OnScanJsScanner
    {
        $this->customKeyCodeMap = $uxon->toArray();
        return $this;
    }
    
    /**
     *
     * @return string|NULL
     */
    public function getMultiScanDelimiterKeyCode() : ?string
    {
        return $this->multiScanDelimiterKeyCode;
    }
    
    /**
     * If the scanner can scan multiple codes at once (i.e. RFID tags) this should be the delimiter key code.
     * 
     * If no `multi_scan_delimiter_key_code` is set, every scan is concidered to contain only one
     * code!
     * 
     * @uxon-property multi_scan_delimiter_key_code
     * @uxon-type string
     * 
     * @param string $value
     * @return OnScanJsScanner
     */
    public function setMultiScanDelimiterKeyCode(string $value) : OnScanJsScanner
    {
        $this->multiScanDelimiterKeyCode = $value;
        return $this;
    }
    
    /**
     *
     * @return string
     */
    public function getMultiScanDelimiterCharacter() : string
    {
        return $this->multiScanDelimiterCharacter;
    }
    
    /**
     * If the scanner can scan multiple codes at once (i.e. RFID tags) the delimiter key code will be translated into this character.
     * 
     * By default, it is a comma.
     * 
     * @uxon-property multi_scan_delimiter_character
     * @uxon-type string
     * @uxon-default ,
     * 
     * @param string $value
     * @return OnScanJsScanner
     */
    public function setMultiScanDelimiterCharacter(string $value) : OnScanJsScanner
    {
        $this->multiScanDelimiterCharacter = $value;
        return $this;
    }
    
    protected function getPreventEnterOnButtons() : bool
    {
        return $this->preventEnterOnButtons;
    }
    
    /**
     * Set to TRUE to prevent "enter" key events, that are potential barcode characters from "pressing" focused buttons.
     * 
     * If a barcode is being scanned while a button is focused and the scanner sends an "enter"
     * ley (key code 13), the button will get triggered. If the enter key code has some special
     * meaning within the barcode, and you do not want the default button-trigger behavior,
     * set this property to `true`. In this case enter key events will be silenced if a button
     * is focused.
     * 
     * @uxon-property prevent_enter_on_buttons
     * @uxon-type boolean
     * @uxon-default false
     * 
     * @param bool $trueOrFalse
     * @return OnScanJsScanner
     */
    public function setPreventEnterOnButtons(bool $trueOrFalse) : OnScanJsScanner
    {
        $this->preventEnterOnButtons = $trueOrFalse;
        return $this;
    }
}