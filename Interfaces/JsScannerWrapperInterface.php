<?php
namespace exface\BarcodeScanner\Interfaces;

use exface\Core\Interfaces\Facades\FacadeInterface;
use exface\BarcodeScanner\Actions\AbstractScanAction;
use exface\Core\Interfaces\iCanBeConvertedToUxon;

interface JsScannerWrapperInterface extends iCanBeConvertedToUxon
{
    public function buildJsScannerInit(FacadeInterface $facade) : string;
    
    public function buildJsScan() : string;
    
    public function getIncludes(FacadeInterface $facade) : array;
    
    public function getScanAction() : AbstractScanAction;
    
    public function getType() : string;
}