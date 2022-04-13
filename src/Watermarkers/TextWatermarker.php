<?php

namespace Gutti3k\PdfWatermarker\Watermarkers;

use Gutti3k\PdfWatermarker\Support\Pdf;
use Gutti3k\PdfWatermarker\PdfWatermarker as Watermarker;
use Gutti3k\PdfWatermarker\Watermarks\TextWatermark;
use Gutti3k\PdfWatermarker\Watermarkers\Exceptions\InvalidColorException;
use Gutti3k\PdfWatermarker\Watermarkers\Exceptions\InvalidFontFileException;
use Gutti3k\PdfWatermarker\Watermarkers\Exceptions\InvalidInputFileException;

class TextWatermarker extends BaseWatermarketer
{

    protected $text;
    protected $font;
    protected $size = 10;
    protected $angle = 0;
    protected $color = '#00000000';

    /**
     * Set the wtaermark text
     *
     * @param string $text
     * @return ImageWatermarker
     */
    public function text($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Set the TTF font path
     *
     * @param string $font
     * @return ImageWatermarker
     */
    public function font($font)
    {
        $this->font = $font;
        return $this;
    }

    /**
     * Set the font size
     *
     * @param float $size
     * @return ImageWatermarker
     */
    public function size($size)
    {
        $this->size = (float) $size;
        return $this;
    }

    /**
     * Set the text angle
     *
     * @param float $angle
     * @return ImageWatermarker
     */
    public function angle($angle)
    {
        $this->angle = (float) $angle;
        return $this;
    }

    /**
     * Set the text color in #RRGGBBAA format
     *
     * @param string $color
     * @return ImageWatermarker
     */
    public function color($color)
    {
        $this->color = $color;
        return $this;
    }

    protected function watermarker(): Watermarker
    {
        if (!is_readable($this->input)) {
            throw new InvalidInputFileException('The specified input file is not valid');
        }

        if (!is_readable($this->font)) {
            throw new InvalidFontFileException('The specified font file is not valid');
        }

        if (!preg_match(TextWatermark::COLOR_PATTERN, $this->color)) {
            throw new InvalidColorException('The specified color format is not valid');
        }

        $pdf = new Pdf($this->input);

        $watermark = new TextWatermark($this->text, $this->font, $this->size, $this->angle, $this->color);

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
