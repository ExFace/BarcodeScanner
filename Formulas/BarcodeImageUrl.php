<?php
namespace exface\BarcodeScanner\Formulas;

use exface\Core\Exceptions\FormulaError;
use exface\BarcodeScanner\Facades\HttpBarcodeFacade;
use exface\Core\CommonLogic\Selectors\FacadeSelector;
use exface\Core\Formulas\WorkbenchURL;
use exface\Core\CommonLogic\Model\Formula;
use exface\Core\DataTypes\ImageUrlDataType;
use exface\Core\Factories\DataTypeFactory;
use exface\Core\Factories\FacadeFactory;

/**
  * Produces an URL to load an image showing a barcode of the given type with the givne value
 * 
 * E.g. 
 * - `=exface.BarcodeScanner.BarcodeImageUrl('ean-128', '12345678')` => https://myserver.com/mypath/api/barcode/ean-128/12345678
 *
 * @author Ralf Mulansky
 *      
 */
class BarcodeImageUrl extends Formula
{
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\CommonLogic\Model\Formula::run()
     */
    public function run(string $type = null, string $value = null)
    {
        if (! $type) {
            throw new FormulaError('No barcode type was given!');
        }/*
        if (! $value) {
            throw new FormulaError('No value to display as barcode!');
        }*/
        $facade = FacadeFactory::createFromString(HttpBarcodeFacade::class, $this->getWorkbench());
        $url = $this->getWorkbench()->getUrl() . $facade->getUrlRouteDefault() . '/' . $type . '/' . $value;
        return $url;
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\Core\CommonLogic\Model\Formula::getDataType()
     */
    public function getDataType()
    {
        return DataTypeFactory::createFromPrototype($this->getWorkbench(), ImageUrlDataType::class);
    }
    
}