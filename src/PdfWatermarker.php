<?php

namespace gutti3k\PdfWatermarker;

use setasign\Fpdi\Fpdi;
use gutti3k\PdfWatermarker\Support\Pdf;
use gutti3k\PdfWatermarker\Support\Position;
use gutti3k\PdfWatermarker\Contracts\Watermark;
use gutti3k\PdfWatermarker\Contracts\Watermarker;

class PdfWatermarker implements Watermarker
{
    protected const DPI = 96;
    protected const MM_IN_INCH = 25.4;

    protected $watermark;
    protected $totalPages;
    protected $specificPages = [];
    protected $position;
    protected $asBackground = false;
    protected $resolution = self::DPI;

    /** @var Fpdi */
    protected $fpdi;

    public function __construct(Pdf $file, Watermark $watermark, $resolution = self::DPI)
    {
        $this->fpdi = new Fpdi();
        $this->totalPages = $this->fpdi->setSourceFile($file->getRealPath());
        $this->watermark = $watermark;
        $this->position = new Position(Position::MIDDLE_CENTER);
        $this->resolution = $resolution;
    }

    /**
     * Set page range.
     *
     * @param int $startPage - the first page to be watermarked
     * @param int $endPage - (optional) the last page to be watermarked
     */
    public function setPageRange($startPage = 1, $endPage = null)
    {
        $endPage = is_null($endPage) ? $this->totalPages : $endPage;

        foreach (range($startPage, $endPage) as $pageNumber) {
            $this->specificPages[] = $pageNumber;
        }
    }

    /**
     * Apply the watermark below the PDF's content.
     */
    public function setAsBackground()
    {
        $this->asBackground = true;
    }

    /**
     * Apply the watermark over the PDF's content.
     */
    public function setAsOverlay()
    {
        $this->asBackground = false;
    }

    /**
     * Set the Position of the Watermark
     *
     * @param Position $position
     */
    public function setPosition(Position $position)
    {
        $this->position = $position;
    }

    /**
     * Loop through the pages while applying the watermark.
     */
    protected function process()
    {
        foreach (range(1, $this->totalPages) as $pageNumber) {
            $this->importPage($pageNumber);

            if (in_array($pageNumber, $this->specificPages) || empty($this->specificPages)) {
                $this->watermarkPage($pageNumber);
            } else {
                $this->watermarkPage($pageNumber, false);
            }
        }
    }

    /**
     * Import page.
     *
     * @param int $pageNumber - page number
     */
    protected function importPage($pageNumber)
    {
        $templateId = $this->fpdi->importPage($pageNumber);
        $templateDimension = $this->fpdi->getTemplateSize($templateId);

        if ($templateDimension['width'] > $templateDimension['height']) {
            $orientation = "L";
        } else {
            $orientation = "P";
        }

        $this->fpdi->addPage($orientation, array($templateDimension['width'], $templateDimension['height']));
    }

    /**
     * Apply the watermark to a specific page.
     *
     * @param int $pageNumber - page number
     * @param bool $watermark_visible - (optional) Make the watermark visible. True by default.
     */
    protected function watermarkPage($pageNumber, $watermark_visible = true)
    {
        $templateId = $this->fpdi->importPage($pageNumber);

        $templateDimension = $this->fpdi->getTemplateSize($templateId);

        list($wWidth, $wHeight) = $this->resizeToFit($this->watermark, $templateDimension['width'], $templateDimension['height']);

        // All params in milimeters.
        $watermarkCoords = $this->calculateWatermarkCoordinates(
            $wWidth,
            $wHeight,
            $templateDimension['width'],
            $templateDimension['height']
        );

        if ($watermark_visible) {
            if ($this->asBackground) {
                $this->fpdi->Image($this->watermark->getFilePath(), $watermarkCoords[0], $watermarkCoords[1], $wWidth, $wHeight); // -$this->resolution);
                $this->fpdi->useTemplate($templateId);
            } else {
                $this->fpdi->useTemplate($templateId);
                $this->fpdi->Image($this->watermark->getFilePath(), $watermarkCoords[0], $watermarkCoords[1], $wWidth, $wHeight); // -$this->resolution);
            }
        } else {
            $this->fpdi->useTemplate($templateId);
        }
    }

    /**
     * Calculate the coordinates of the watermark's position.
     *
     * @param int $wWidth - watermark's width
     * @param int $wHeight - watermark's height
     * @param int $tWidth - page width
     * @param int $Height -page height
     *
     * @return array - coordinates of the watermark's position
     */
    protected function calculateWatermarkCoordinates($wWidth, $wHeight, $tWidth, $tHeight)
    {
        switch ($this->position->getName()) {
            case 'TopLeft':
                $x = 0;
                $y = 0;
                break;
            case 'TopCenter':
                $x = ($tWidth - $wWidth) / 2;
                $y = 0;
                break;
            case 'TopRight':
                $x = $tWidth - $wWidth;
                $y = 0;
                break;
            case 'MiddleLeft':
                $x = 0;
                $y = ($tHeight - $wHeight) / 2;
                break;
            case 'MiddleRight':
                $x = $tWidth - $wWidth;
                $y = ($tHeight - $wHeight) / 2;
                break;
            case 'BottomLeft':
                $x = 0;
                $y = $tHeight - $wHeight;
                break;
            case 'BottomCenter':
                $x = ($tWidth - $wWidth) / 2;
                $y = $tHeight - $wHeight;
                break;
            case 'BottomRight':
                $x = $tWidth - $wWidth;
                $y = $tHeight - $wHeight;
                break;
            case 'MiddleCenter':
            default:
                $x = ($tWidth - $wWidth) / 2;
                $y = ($tHeight - $wHeight) / 2;
                break;
        }

        $x += $this->position->getOffsetX();
        $y += $this->position->getOffsetY();

        return array($x, $y);
    }

    /**
     * @param string $fileName
     * @return void
     */
    public function save($fileName = 'document.pdf')
    {
        $this->process();
        $this->fpdi->Output($fileName, 'F');
    }

    /**
     * @param string $fileName
     * @return void
     */
    public function download($fileName = 'document.pdf')
    {
        $this->process();
        $this->fpdi->Output($fileName, 'D');
    }

    /**
     * @param string $fileName
     * @return void
     */
    public function stream($fileName = 'document.pdf')
    {
        $this->process();
        $this->fpdi->Output($fileName, 'I');
    }

    /**
     * @param string $fileName
     * @return void
     */
    public function string($fileName = 'document.pdf')
    {
        $this->process();
        return $this->fpdi->Output($fileName, 'S');
    }

    /**
     * Scale the image to include it inside the container without warping.
     * @param $watermark watermark image of the Watermark class.
     * @param $maxWidth maximum width of the container in mm.
     * @param $maxHeight maximun height of the container in mm.
     * @return array(width, height) in mm.
     */
    public function resizeToFit(Watermark $watermark, $maxWidth, $maxHeight)
    {
        $width = $this->pixelsToMM($watermark->getWidth()); // to milimeters
        $height = $this->pixelsToMM($watermark->getHeight()); // to milimeters

        $widthScale = $maxWidth / $width; // milimeters
        $heightScale = $maxHeight / $height; // milimeters

        $scale = min($widthScale, $heightScale);

        return array(
            round($scale * $width),
            round($scale * $height),
        );
    }

    public function rotateImagen($filename, $rotate = false, $degrees = 90)
    {
        if ($rotate) {
            logger('girar');
            $degrees = $degrees;
            $image = imagecreatefrompng($filename);
            imagealphablending($image, false);
            imagesavealpha($image, true);
            $imgRotate = imagerotate($image, $degrees, imageColorAllocateAlpha($image, 0, 0, 0, 127));
            imagealphablending($imgRotate, false);
            imagesavealpha($imgRotate, true);
            imagepng($imgRotate, $filename);
            imagedestroy($image);
            imagedestroy($imgRotate);
        }
    }
}
