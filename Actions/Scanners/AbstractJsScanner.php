<?php
namespace exface\BarcodeScanner\Actions\Scanners;

use exface\Core\Interfaces\Facades\FacadeInterface;
use exface\BarcodeScanner\Interfaces\JsScannerWrapperInterface;
use exface\Core\DataTypes\StringDataType;
use exface\BarcodeScanner\Actions\AbstractScanAction;
use exface\Core\CommonLogic\Traits\ImportUxonObjectTrait;
use exface\Core\Interfaces\iCanBeConvertedToUxon;
use exface\Core\CommonLogic\UxonObject;

abstract class AbstractJsScanner implements JsScannerWrapperInterface
{
    use ImportUxonObjectTrait;
    
    private $scanAction = null;
    
    public function __construct(AbstractScanAction $scanAction, UxonObject $uxon = null)
    {
        $this->scanAction = $scanAction;
        if ($uxon !== null) {
            $this->importUxonObject($uxon, ['type']);
        }
    }
    
    /**
     * 
     * @param string $pathRelativeToVendorFolder
     * @return string
     */
    protected function buildUrlIncludePath(string $pathRelativeToVendorFolder, FacadeInterface $facade) : string
    {
        if (StringDataType::startsWith($pathRelativeToVendorFolder, 'https:', false) || StringDataType::startsWith($pathRelativeToVendorFolder, 'http:', false)) {
            return $pathRelativeToVendorFolder;
        }
        
        return $facade->buildUrlToVendorFile($pathRelativeToVendorFolder);
    }
    
    /**
     * The type of the scanner to be used: integrated or connected `hardware` (defualt) or built-in `camera`.
     * 
     * @uxon-property type
     * @uxon-type [hardware,camera]
     * @uxon-default hardware
     * 
     * @return string
     */
    abstract public function getType() : string;
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\BarcodeScanner\Interfaces\JsScannerWrapperInterface::getScanAction()
     */
    public function getScanAction() : AbstractScanAction
    {
        return $this->scanAction;
    }
    
    /**
     * 
     * @return iCanBeConvertedToUxon::getUxonSchemaClass()
     */
    public static function getUxonSchemaClass() : ?string
    {
        return JsScannerUxonSchema::class;
    }
}