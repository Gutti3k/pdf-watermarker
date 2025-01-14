<?php

namespace Gutti3k\PdfWatermarker\Contracts;

use Gutti3k\PdfWatermarker\Support\Position;

interface Watermarker
{
    /**
     * Set page range.
     *
     * @param int $startPage - the first page to be watermarked
     * @param int|null $endPage - (optional) the last page to be watermarked
     */
    public function setPageRange($start, $end = null);

    /**
     * Set the Position of the Watermark
     *
     * @param Position $position
     * @return void
     */
    public function setPosition(Position $position);

    /**
     * Set the watermark as background.
     *
     * @return void
     */
    public function setAsBackground();

    /**
     * Set the watermark as overlay.
     *
     * @return void
     */
    public function setAsOverlay();

    /**
     * Save the PDF.
     *
     * @param $file
     * @return void
     */
    public function save($file);

    /**
     * Download the PDF.
     *
     * @param $file
     * @return void
     */
    public function download($file);

    /**
     * Streams the PDF contents to the browser.
     *
     * @param $file
     * @return void
     */
    public function stream($file);

    /**
     * Return the PDF content as a string.
     *
     * @param $file
     * @return void
     */
    public function string($fileName);
}
