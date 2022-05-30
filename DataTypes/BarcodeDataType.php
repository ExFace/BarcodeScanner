<?php

namespace exface\BarcodeScanner\DataTypes;

use exface\Core\CommonLogic\DataTypes\AbstractDataType;

class BarcodeDataType extends AbstractDataType
{
    private const types = [
        'UPC-A' => 'upc-a',
        'UPC-E' => 'upc-e',
        'EAN-8' => 'ean-8',
        'EAN-13' => 'ean-13',
        'EAN-13-PAD' => 'ean-13-pad',
        'EAN-13-NOPAD' => 'ean-13-nopad',
        'EAN-128' => 'ean-128',
        'CODE-39' => 'code-39',
        'CODE-39-ASCII' => 'code-39-ascii',
        'CODE-93' => 'code-93',
        'CODE-93-ASCII' => 'code-93-ascii',
        'CODE-128' => 'code-128',
        'CODABAR' => 'codabar',
        'ITF' => 'itf',
        'QR' => 'qr',
        'QR-L' => 'qr-l',
        'QR-M' => 'qr-m',
        'QR-Q' => 'qr-q',
        'QR-H' => 'qr-h',
        'DMTX' => 'dmtx',
        'DMTX-S' => 'dmtx-s',
        'DMTX-R' => 'dmtx-r',
        'GS1-DMTX' => 'gs1-dmtx',
        'GS1-DMTX-S' => 'gs1-dmtx-s',
        'GS1-DMTX-r' => 'gs1-dmtx-r'        
    ];
    
    public const FORMAT_PNG = 'png';
    
    public const FORMAT_GIF = 'gif';
    
    public const FORMAT_JPEG = 'jpeg';
    
    public const FORMAT_JPG = 'jpg';
    
    public const FORMAT_SVG = 'svg';
    
    public function isValidType(string $type) : bool
    {
        if ($this::types[strtoupper($type)] === null) {
            return false;
        }
        return true;
    }
}