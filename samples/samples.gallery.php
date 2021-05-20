<?php
/**
 * @category Gallery: Standard Gallery
 * @tutorial Provides an easy way to setup a photo gallery.
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 */

require_once "ext/gallery/webcore.gallery.php";
class GallerySampleWidget extends WidgetBase
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
        
        $this->model = new Gallery('gal', 'My Gallery');
        $this->view  = new HtmlGalleryView($this->model);
        $this->view->setFrameWidth('inherit');
    }
    
    public static function createInstance()
    {
        return new GallerySampleWidget();
    }
    
    private static function getImages()
    {
        $imgList = new IndexedCollection();
        $dir     = "images/";
        if (is_dir($dir))
        {
            if ($dh = opendir($dir))
            {
                $i = 0;
                while (($file = readdir($dh)) !== false)
                {
                    $fileName = $dir . $file;
                    
                    if (filetype($fileName) == 'file' && strstr($file, '.thumb.jpg') == false && ImageHelper::isImage($fileName))
                    {
                        $i++;
                        $imgEntity = new GalleryImage($fileName, "Image " . $i);
                        $imgEntity->createThumbnail();
                        $imgList->addItem($imgEntity);
                    }
                }
                
                closedir($dh);
            }
        }
        
        return $imgList;
    }
    
    public function handleRequest()
    {
        $imgList = self::getImages();
        $this->model->dataBind($imgList);
    }
}

$sample = new GallerySampleWidget();
$sample->handleRequest();

?>
