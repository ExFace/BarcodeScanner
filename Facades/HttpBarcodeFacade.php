<?php
namespace exface\BarcodeScanner\Facades;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use exface\Core\Facades\AbstractHttpFacade\AbstractHttpFacade;
use exface\Core\DataTypes\StringDataType;
use Intervention\Image\ImageManager;
use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\stream_for;
use exface\BarcodeScanner\DataTypes\BarcodeDataType;
use exface\Core\Factories\DataTypeFactory;
use exface\Core\Exceptions\Facades\FacadeRuntimeError;

require (__DIR__.'\..\CommonLogic\barcode.php');

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
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri();
        $path = ltrim(StringDataType::substringAfter($uri->getPath(), $this->getUrlRouteDefault()), "/");
        
        $pathParts = explode('/', $path);
        $barcodeType = urldecode($pathParts[0]);
        $value = urldecode($pathParts[1]);
        if ($value == '') {
            $value = null;
        }
        $datatype = DataTypeFactory::createFromPrototype($this->getWorkbench(), BarcodeDataType::class);
        if (! $datatype->isValidType($barcodeType) || ! $value) {
            $this->getWorkbench()->getLogger()->logException(new FacadeRuntimeError("Cannot create barcode with type '{$barcodeType}' and value '{$value}'"));
            return new Response(404);
        }
        
        $headers = [
            'Expires' => 0,
            'Cache-Control', 'must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'public'
        ];
        
        // See if there are additional parameters 
        $params = [];
        parse_str($uri->getQuery() ?? '', $params);
        
        if ($format = $params['f']) {
            $format = strtolower($format);
            switch (true) {
                case $format == BarcodeDataType::FORMAT_GIF:
                    $format = BarcodeDataType::FORMAT_GIF;
                    $headers['Content-Type'] = 'image/' . $format;
                    break;
                case $format == BarcodeDataType::FORMAT_SVG:
                    $format = BarcodeDataType::FORMAT_SVG;
                    $headers['Content-Type'] = 'image/' . $format . '+xml';
                    break;
                case $format == BarcodeDataType::FORMAT_PNG:
                    $format = BarcodeDataType::FORMAT_PNG;
                    $headers['Content-Type'] = 'image/' . $format;
                    break;
                case $format == BarcodeDataType::FORMAT_JPG:
                case $format == BarcodeDataType::FORMAT_JPEG:
                    $format = BarcodeDataType::FORMAT_JPEG;
                    $headers['Content-Type'] = 'image/' . $format;
                    break;
                default:
                    $this->getWorkbench()->getLogger()->logException(new FacadeRuntimeError("Cannot create barcode with format '{$format}'"));
                    return new Response(404);
            unset ($params['f']);
            }              
        } else {
            $format = BarcodeDataType::FORMAT_JPEG;
            $headers['Content-Type'] = 'image/' . $format;
        }
        $generator = new \barcode_generator();
        
        $image = $generator->output_image($format, $barcodeType, $value, $params);
        
        $response = new Response(200, $headers, $image);
        return $response;
    }
}