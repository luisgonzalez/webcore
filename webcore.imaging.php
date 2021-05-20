<?php
/**
 * @package WebCore
 * @subpackage Imaging
 * @version experimental
 *
 * At this point, only facilitates the creation of thumbnails.
 * Future versions will facilitate image manipulation to generate things like captcha
 *
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

/**
 * GD Image Helper
 *
 * @package WebCore
 * @subpackage Imaging
 */
class ImageHelper extends HelperBase
{
    /**
     * Checks if file is a valid image
     *
     * @param string $fileName
     * @return bool
     */
    public static function isImage($fileName)
    {
        if (is_readable($fileName) == false)
            return false;
        
        if (imagecreatefromstring(file_get_contents($fileName)) == false)
            return false;
        
        return true;
    }

    /**
     * Will create a Image from a resource as a file to the given
     * path
     * @param resource $resource
     * @param string $mimetype
     * @return imagefile
     */
    public static function convertResourceToImage($resource, $mimetype, $path)
    {
        switch($mimetype)
        {
            case 'image/jpeg':
                return imagejpeg($resource, $path);
                break;
            case 'image/gif':
                return imagegif($resource , $path);
                break;
            case 'image/png':
                return imagepng($resource, $path);
                break;
        }

        return false;
    }
    /**
     * Checks if a resource is a valid image
     *
     * @param string $resource
     * @return bool
     */
    public static function isResourceImage($resource)
    {

        if (imagecreatefromstring($resource) == false)
            return false;

        return true;
    }

    /**
     * Create a thumbnail from a resource
     *
     * @param string $resource
     * @param int $width
     * @param int $height
     *
     * @return resource
     */
    public static function createThumbnailFromResource($resource, $width = 320, $height = 240)
    {
        $imgResource = imagecreatefromstring($resource);
        if ($imgResource == false)
            return false;

        return ImageHelper::createThumbnail($imgResource, $width, $height);
    }
    
    /**
     * Create a thumbnail from a file
     *
     * @param string $fileName
     * @param int $width
     * @param int $height
     *
     * @return resource
     */
    public static function createThumbnailFromFile($fileName, $width = 320, $height = 240)
    {
        $imgResource = imagecreatefromstring(file_get_contents($fileName));
        
        if ($imgResource == false)
            return false;
        
        return ImageHelper::createThumbnail($imgResource, $width, $height);
    }
    
    /**
     * Create a thumbnail from a resource
     *
     * @param resource $image
     * @param int $width
     * @param int $height
     *
     * @return resource
     */
    public static function createThumbnail($image, $width = 320, $height = 240)
    {
        $oldWidth  = imagesx($image);
        $oldHeight = imagesy($image);
        
        if ($oldWidth > $oldHeight)
            $height = $oldHeight * ($width / $oldWidth);
        else
            $width = $oldWidth * ($height / $oldHeight);
        
        $thumb = imagecreatetruecolor($width, $height);
        imagecopyresampled($thumb, $image, 0, 0, 0, 0, $width, $height, $oldWidth, $oldHeight);
        
        return $thumb;
    }
    
    /**
     * Creates a dummy image to use when original image is missing
     *
     * @param string $message
     * @return resource
     */
    public static function createDummy($message = "MISSING FILE")
    {
        $thumb      = imagecreate(strlen($message) * 12, 20);
        $background = imagecolorallocate($thumb, 210, 210, 210);
        $textColor  = imagecolorallocate($thumb, 255, 0, 0);
        imagefill($thumb, 0, 0, $background);
        imagestring($thumb, 20, 7, 2, $message, $textColor);
        
        return $thumb;
    }
}
?>