<?php
namespace Bash\ExifOrientationHelper\Imaging;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * @author Ruben Schmidmeister <ruben.schmidmeister@icloud.com
 */
class RawJpeg
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var resource
     */
    private $image;

    /**
     * @var array
     */
    private $data;

    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return array
     */
    public function getExifData()
    {
        if ($this->data === NULL) {
            $this->data = @read_exif_data($this->filename);
        }

        return $this->data;
    }

    /**
     * @return int
     */
    public function getOrientation()
    {
        $data = $this->getExifData();

        if ($data && isset($data[ 'Orientation' ])) {
            return $data[ 'Orientation' ];
        }

        return NULL;
    }

    /**
     * @return bool
     */
    public function hasOrientation()
    {
        return $this->getOrientation() !== NULL;
    }

    /**
     * @param int $angle
     */
    public function rotate($angle)
    {
        $this->image = imagerotate($this->getImage(), $angle, NULL);
    }

    /**
     * @param int $mode
     */
    public function flip($mode)
    {
        imageflip($this->getImage(), $mode);
    }

    /**
     * @param string $filename
     */
    public function write($filename)
    {
        touch($filename);
        imagejpeg($this->getImage(), $filename, 100);
    }

    /**
     * @return resource
     */
    private function getImage()
    {
        if ($this->image === NULL) {
            $this->image = imagecreatefromjpeg($this->filename);
        }

        return $this->image;
    }
}