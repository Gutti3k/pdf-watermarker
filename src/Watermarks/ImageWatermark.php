<?php

namespace Gutti3k\PdfWatermarker\Watermarks;

use Gutti3k\PdfWatermarker\Contracts\Watermark;

class ImageWatermark implements Watermark
{
    protected $height;
    protected $width;
    protected $tmpFile;

    public function __construct($file)
    {
        $imagetype = exif_imagetype($file);
        $this->tmpFile = sys_get_temp_dir() . '/' . uniqid() . '.png';

        switch ($imagetype) {

            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($file);
                imageinterlace($image, false);
                imagejpeg($image, $this->tmpFile);
                imagedestroy($image);
                break;

            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($file);
                imageinterlace($image, false);
                imagesavealpha($image, true);
                imagepng($image, $this->tmpFile);
                imagedestroy($image);
                break;
            default:
                throw new \Exception("Unsupported image type: " . $imagetype);
                break;
        };

        $size = getimagesize($this->tmpFile);
        $this->width = $size[0]; // pixels
        $this->height = $size[1]; // pixels
    }

    /**
     * Return the path to the tmp file
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->tmpFile;
    }

    /**
     * Returns the watermark's height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Returns the watermark's width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }
}
