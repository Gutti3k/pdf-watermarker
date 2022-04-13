<?php

namespace gutti3k\PdfWatermarker\Watermarkers;

use gutti3k\PdfWatermarker\Support\Pdf;
use gutti3k\PdfWatermarker\PdfWatermarker as Watermarker;
use gutti3k\PdfWatermarker\Watermarks\ImageWatermark;
use gutti3k\PdfWatermarker\Watermarkers\Exceptions\InvalidInputFileException;
use gutti3k\PdfWatermarker\Watermarkers\Exceptions\InvalidWatermarkFileException;

class ImageWatermarker extends BaseWatermarketer
{
    protected $watermark;

    /**
     * Set the watermark image
     *
     * @param string $filename
     * @return ImageWatermarker
     */
    public function watermark($filename)
    {
        $this->watermark = $filename;
        return $this;
    }

    protected function watermarker(): Watermarker
    {
        if (!is_readable($this->input)) {
            throw new InvalidInputFileException('The specified input file is not valid');
        }

        if (!is_readable($this->watermark)) {
            throw new InvalidWatermarkFileException('The specified watermark file is not valid');
        }

        $pdf = new Pdf($this->input);

        $watermark = new ImageWatermark($this->watermark);

        $watermarker = new Watermarker($pdf, $watermark, $this->resolution);

        if ($this->position) {
            $watermarker->setPosition($this->position);
        }

        if ($this->asBackground) {
            $watermarker->setAsBackground();
        }

        if ($this->asOverlay) {
            $watermarker->setAsOverlay();
        }

        $watermarker->setPageRange($this->pageRangeFrom, $this->pageRangeTo);

        return $watermarker;
    }
}
