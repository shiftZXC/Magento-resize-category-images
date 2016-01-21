<?php
/**
* @category   Technooze/Modules/magento-how-tos
* @package    Technooze_Timage
* @author     Damodar Bashyal (http://dltr.org/)
* @link       http://j.mp/resizeImage
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * ref: /lib/Varien/Image.php
*/
class Technooze_Timage_Helper_Data extends Mage_Core_Helper_Abstract
{
    var
        $width = null,
        $height = null,
        $rawImg = '',
        $img = false,
        $cacheDir = '',
        $croppedCacheDir = '',
        $croppedImage = '',
        $cachedImage = '',
        $cachedImageUrl = '',
        $ext = '',
        $bgColor = array(255, 255, 255),
        $imageObj = '',
        $baseUrl = '',
        $placeHolder = false,

        // image settings
        $keepTransparency = true,
        $aspectRatio = true,
        $constrainOnly = true,
        $keepFrame = true,
        $quality
        ;

    /**
     * Reset all previous data
     */
    protected function _reset()
    {
        $this->width = null;
        $this->height = null;
        $this->rawImg = '';
        $this->img = false;
        $this->cachedImage = '';
        $this->croppedImage = '';
        $this->cachedImageUrl = '';
        $this->ext = '';
        $this->bgColor = array(255, 255, 255);
        $this->imageObj = '';
        $this->baseUrl = $this->getBaseUrl();
        $this->keepTransparency = true;
        $this->aspectRatio = true;
        $this->constrainOnly = true;
        $this->keepFrame = true;
        $this->quality = null;
        return $this;
    }

    /**
     * @return string
     */
    private function getBaseUrl()
    {
        $baseUrl = Mage::getStoreConfig('web/unsecure/base_media_url');
        return preg_replace('#^https?://#', '//', $baseUrl);
    }

    /**
     * @param string $img
     * @return $this
     */
    public function init($img = '')
    {
        $this->_reset();

        if(empty($this->placeHolder))
        {
            $this->placeHolder = Mage::getDesign()->getSkinUrl('images/catalog/product/placeholder/image.jpg');
        }

        if($img)
        {
            $this->rawImg = $img;
        }

        $this->imagePath($this->rawImg);

        $path_parts = pathinfo($this->img);

        $this->ext = $path_parts['extension'];

        $this->cacheDir();

        return $this;
    }

    /**
     * @param null|int $width
     * @return $this
     */
    public function setWidth($width=null)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @param null|int $height
     * @return $this
     */
    public function setHeight($height=null)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @param null|int $width
     * @param null|int $height
     * @return string
     */
    public function resize($width=null, $height=null)
    {
        if($width)
        {
            $this->width = $width;
        }

        if($height)
        {
            $this->height = $height;
        }

        $this->cacheIt();

        return $this->cachedImageUrl();
    }

    /**
     * @return string
     */
    public function cachedImageUrl()
    {
        $img = str_replace(array(BP . DS . 'media', BP), '', $this->cachedImage);
        $img = trim(str_replace('\\', '/', $img), '/');

        return $this->baseUrl . $img;
    }

    /**
     * @return string|void
     */
    public function getCroppedCache()
    {
        $this->croppedImage = $this->croppedCacheDir . md5($this->img . $this->width . $this->height) . '.' .$this->ext;

        if(file_exists($this->croppedImage))
        {
            return $this->croppedImage;
        }

        $this->cropIt();
    }

    /**
     * @return string|void
     */
    public function cacheIt()
    {
        $this->cachedImage = $this->cacheDir . md5($this->img . $this->width . $this->height) . '.' .$this->ext;

        if(file_exists($this->cachedImage))
        {
            return $this->cachedImage;
        }

        $this->resizer();
    }

    /**
     * Set image quality, values in percentage from 0 to 100
     * @param $quality
     * @return $this
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;
        return $this;
    }

    /**
     * Guarantee, that image picture width/height will not be distorted.
     * Applicable before calling resize()
     * It is true by default.
     * @param bool $bool
     * @return $this
     */
    public function keepAspectRatio($bool=true)
    {
        $this->aspectRatio = $bool;
        return $this;
    }

    /**
     * Guarantee, that image will have dimensions, set in $width/$height
     * Applicable before calling resize()
     * Not applicable, if keepAspectRatio(false)
     * @param bool $bool
     * @return $this
     */
    public function keepFrame($bool=true)
    {
        $this->keepFrame = $bool;
        return $this;
    }

    /**
     * Guarantee, that image picture will not be bigger, than it was.
     * Applicable before calling resize()
     * It is false by default
     * @param bool $bool
     * @return $this
     */
    public function constrainOnly($bool=false)
    {
        $this->constrainOnly = $bool;
        return $this;
    }

    /**
     * Guarantee, that image will not lose transparency if any.
     * Applicable before calling resize()
     * It is true by default.
     *
     * $alphaOpacity - TODO, not used for now
     */
    public function keepTransparency($flag, $alphaOpacity = null)
    {
        $this->keepTransparency = $flag;
        return $this;
    }

    /**
     * @param int $top
     * @param int $left
     * @param int $right
     * @param int $bottom
     */
    private function cropIt($top=0, $left=0, $right=0, $bottom=0){
        if ( empty($this->imageObj) ) {
            $this->imageObj = new Varien_Image($this->img);
        }
        try {
            $this->imageObj->crop($top, $left, $right, $bottom);
            $this->imageObj->save($this->croppedImage);
            $this->img = $this->croppedImage;
        } catch(Exception $e){
            Mage::throwException($e->getMessage());
        }
    }

    /**
     * Crop an image.
     *
     * @param int $top. Default value is 0
     * @param int $left. Default value is 0
     * @param int $right. Default value is 0
     * @param int $bottom. Default value is 0
     * @access public
     * @return $this
     */
    public function crop($top=0, $left=0, $right=0, $bottom=0)
    {
        $cache = $this->getCroppedCache();
        if($cache){
            $this->img = $cache;
        } else {
            try{
                $width = $this->width;
                $height = $this->height;
                $origWidth = $this->getOriginalWidth();
                $origHeight = $this->getOriginalHeight();
                $cropHeightTrim = $cropWidthTrim = 0;

                if($width && $height){

                    $origRatio = $origWidth / $origHeight;
                    $cropRatio = $width / $height;

                    if ($origRatio >= $cropRatio) { // trim width
                        $cropWidth = $origHeight * $cropRatio;
                        $cropWidthTrim = 0 - ($cropWidth - $origWidth) / 2;
                    } else { // trim height
                        $cropHeight = $origWidth / $cropRatio;
                        $cropHeightTrim = 0 - ($cropHeight - $origHeight) / 2;
                    }
                }

                if(!$top && !$left && !$right && !$bottom){
                    if ($cropWidthTrim) {
                        $right = $left = $cropWidthTrim;
                    } elseif ($cropHeightTrim) {
                        $top = $bottom = $cropHeightTrim;
                    }
                }
                $this->cropIt($top, $left, $right, $bottom);
            } catch(Exception $e){
                Mage::throwException($e->getMessage());
            }
        }
        return $this;
    }

    public function resizer()
    {
        if ( empty($this->imageObj) ) {
            $this->imageObj = new Varien_Image($this->img);
        }
        try{
            $this->imageObj->quality($this->quality);
            $this->imageObj->constrainOnly($this->aspectRatio);
            $this->imageObj->keepAspectRatio($this->aspectRatio);
            $this->imageObj->keepFrame($this->keepFrame);
            $this->imageObj->keepTransparency($this->keepTransparency);
            $this->imageObj->backgroundColor($this->bgColor);
            $this->imageObj->resize($this->width, $this->height);
            $this->imageObj->save($this->cachedImage);
        } catch(Exception $e){
            Mage::throwException($e->getMessage());
        }
    }

    /**
     * @param string $img
     */
    public function imagePath($img='')
    {
        $unsecure_media_url = Mage::getStoreConfig('web/unsecure/base_media_url');
        $secure_media_url = Mage::getStoreConfig('web/secure/base_media_url');
		$img = str_replace(array(
            $unsecure_media_url, // unsecure media url
            $secure_media_url, // secure media url
            str_replace(array('http:', 'https:'), '', $unsecure_media_url), // unsecure media url without https?
            str_replace(array('http:', 'https:'), '', $secure_media_url), // secure media url without https?
        ), '', $img);
        $img = trim(str_replace('/', DS, $img), DS);
        $this->img = BP . DS . 'media' . DS . $img;

        /** 
         * First check this file on FS
         * If it doesn't exist - try to download it from DB
         */
        $filename = str_replace("media" . DS, "", $img);
        if(!file_exists($filename)) {
            Mage::helper('core/file_storage_database')->saveFileToFilesystem($filename);
        }
        
        if((!file_exists($this->img) || !is_file($this->img)) && !empty($this->placeHolder))
        {
            if($adminPlaceholder = Mage::getStoreConfig("catalog/placeholder/image_placeholder")){
                
                $adminPlaceholder = Mage::getBaseDir('media') .DS."catalog".DS."product".DS."placeholder".DS. $adminPlaceholder;
                
                if(file_exists($adminPlaceholder) && is_file($adminPlaceholder)){
                    $this->img = $adminPlaceholder;
                }
            } else {
                $this->img = Mage::getBaseDir('skin') .DS."frontend".DS."base".DS."default".DS."images".DS."catalog".DS."product".DS."placeholder".DS."image.jpg";
            }
            
            $this->placeHolder = false;
        }
    }

    public function cacheDir()
    {
        $cache = BP . DS . 'media' . DS . 'catalog' . DS . 'cache' . DS;
        $cropCache = $cache . 'cropped' . DS;

        if(!is_dir($cache))
        {
            mkdir($cache, 0777, true);
        }

        if(!is_dir($cropCache))
        {
            mkdir($cropCache, 0777, true);
        }

        $this->cacheDir = $cache;
        $this->croppedCacheDir = $cropCache;
    }

    /**
     * Retrieve original image width
     *
     * @return int|null
     */
    public function getOriginalWidth()
    {
        return $this->imageObj->getOriginalWidth();
    }

    /**
     * Retrieve original image height
     *
     * @return int|null
     */
    public function getOriginalHeight()
    {
        return $this->imageObj->getOriginalHeight();
    }
}
