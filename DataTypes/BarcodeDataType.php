<?php

namespace exface\BarcodeScanner\DataTypes;

use exface\Core\CommonLogic\DataTypes\AbstractDataType;
use exface\Core\CommonLogic\DataTypes\EnumStaticDataTypeTrait;
use exface\Core\Interfaces\DataTypes\EnumDataTypeInterface;

class BarcodeDataType extends AbstractDataType implements EnumDataTypeInterface
{
    use EnumStaticDataTypeTrait;
    
    const UPC_A = 'upc-a';
    const UPC_E = 'upc-e';
    const EAN_8 = 'ean-8';
    const EAN_13 = 'ean-13';
    const EAN_13_PAD = 'ean-13-pad';
    const EAN_13_NOPAD = 'ean-13-nopad';
    const EAN_128 = 'ean-128';
    const CODE_39 = 'code-39';
    const CODE_39_ASCII = 'code-39-ascii';
    const CODE_93 = 'code-93';
    const CODE_93_ASCII = 'code-93-ascii';
    const CODE_128 = 'code-128';
    const CODABAR = 'codabar';
    const ITF = 'itf';
    const QR = 'qr';
    const QR_L = 'qr-l';
    const QR_M = 'qr-m';
    const QR_Q = 'qr-q';
    const QR_H = 'qr-h';
    const DMTX = 'dmtx';
    const DMTX_S = 'dmtx-s';
    const DMTX_R = 'dmtx-r';
    const GS1_DMTX = 'gs1-dmtx';
    const GS1_DMTX_S = 'gs1-dmtx-s';
    const GS1_DMTX_r = 'gs1-dmtx-r';
    
    private $labels = [];

    /**
     *
     * {@inheritDoc}
     * @see \exface\Core\Interfaces\DataTypes\EnumDataTypeInterface::getLabels()
     */
    public function getLabels()
    {
        if (empty($this->labels)) {            
            foreach (static::getValuesStatic() as $const => $val) {
                $this->labels[$val] = $val;
            }
        }
        return $this->labels;
    }
}