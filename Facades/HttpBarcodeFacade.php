<?php
namespace exface\BarcodeScanner\Facades;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use exface\Core\Facades\AbstractHttpFacade\AbstractHttpFacade;
use exface\Core\DataTypes\StringDataType;
use Intervention\Image\ImageManager;
use GuzzleHttp\Psr7\Response;
use exface\BarcodeScanner\DataTypes\BarcodeDataType;
use exface\Core\Exceptions\Facades\FacadeRuntimeError;
use exface\Core\Interfaces\Selectors\FacadeSelectorInterface;


/**
 * Facade to create barcode images from a given value and type.
 * 
 * ## Example
 * 
 * Use the following url `api/barcode/ean-128/1234567` to create an ean-128 barcode image with the value `1234567`.
 * Its possible to add various parameters to the url to further style the barcode.
 * See `https://github.com/kreativekorp/barcode` for all possible parameters.
 * 
 * 
 * @author Ralf Mulansky
 *
 */
class HttpBarcodeFacade extends AbstractHttpFacade
{    
    const FORMAT_PNG = 'png';
    
    const FORMAT_GIF = 'gif';
    
    const FORMAT_JPEG = 'jpeg';
    
    const FORMAT_JPG = 'jpg';
    
    const FORMAT_SVG = 'svg';
    
    /**
     * 
     * @param FacadeSelectorInterface $selector
     */
    public function __construct(FacadeSelectorInterface $selector)
    {
        parent::__construct($selector);
        require_once $this->getWorkbench()->getInstallationPath()
            . DIRECTORY_SEPARATOR . 'vendor'
            . DIRECTORY_SEPARATOR . 'exface'
            . DIRECTORY_SEPARATOR . 'BarcodeScanner'
            . DIRECTORY_SEPARATOR . 'CommonLogic'
            . DIRECTORY_SEPARATOR . 'barcode.php';
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\Core\Facades\AbstractHttpFacade\AbstractHttpFacade::getUrlRouteDefault()
     */
    public function getUrlRouteDefault(): string
    {
        return 'api/barcode';
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\Core\Facades\AbstractHttpFacade\AbstractHttpFacade::createResponse()
     */
    protected function createResponse(ServerRequestInterface $request) : ResponseInterface
    {   
        $uri = $request->getUri();
        $path = ltrim(StringDataType::substringAfter($uri->getPath(), $this->getUrlRouteDefault()), "/");
        
        $pathParts = explode('/', $path);
        $barcodeType = urldecode($pathParts[0]);
        $value = urldecode($pathParts[1]);
        if ($value == '') {
            $value = null;
        }
        
        if (! BarcodeDataType::isValidStaticValue($barcodeType)) {
            $this->getWorkbench()->getLogger()->logException(new FacadeRuntimeError("Cannot create barcode with type '{$barcodeType}' and value '{$value}'"));
            return new Response(404, $this->buildHeadersCommon());
        }
        
        $headers = array_merge($this->buildHeadersCommon(), [
            'Expires' => 0,
            'Cache-Control', 'must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'public'
        ]);
        
        // See if there are additional parameters 
        $params = [];
        parse_str($uri->getQuery() ?? '', $params);
        
        if ($format = $params['f']) {
            $format = strtolower($format);
            switch (true) {
                case $format == self::FORMAT_GIF:
                    $format = self::FORMAT_GIF;
                    $headers['Content-Type'] = 'image/' . $format;
                    break;
                case $format == self::FORMAT_SVG:
                    $format = self::FORMAT_SVG;
                    $headers['Content-Type'] = 'image/' . $format . '+xml';
                    break;
                case $format == self::FORMAT_PNG:
                    $format = self::FORMAT_PNG;
                    $headers['Content-Type'] = 'image/' . $format;
                    break;
                case $format == self::FORMAT_JPG:
                case $format == self::FORMAT_JPEG:
                    $format = self::FORMAT_JPEG;
                    $headers['Content-Type'] = 'image/' . $format;
                    break;
                default:
                    $this->getWorkbench()->getLogger()->logException(new FacadeRuntimeError("Cannot create barcode with format '{$format}'"));
                    return new Response(404, $this->buildHeadersCommon());
            unset ($params['f']);
            }              
        } else {
            $format = self::FORMAT_JPEG;
            $headers['Content-Type'] = 'image/' . $format;
        }
        $generator = new \barcode_generator();
        
        $image = $generator->output_image($format, $barcodeType, $value, $params);
        
        $response = new Response(200, $headers, $image);
        return $response;
    }
}