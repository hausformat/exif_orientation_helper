<?php
namespace Bash\ExifOrientationHelper\Service;

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

use Bash\ExifOrientationHelper\Imaging\RawJpeg;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * @author Ruben Schmidmeister <ruben.schmidmeister@icloud.com
 */
class ExifOrientationService
{
    const JPEG_MIME_TYPE = 'image/jpeg';

    /**
     * @var ResourceFactory
     */
    private $resourceFactory;

    /**
     * @param \TYPO3\CMS\Core\Resource\ResourceFactory $resourceFactory
     */
    public function __construct(ResourceFactory $resourceFactory)
    {
        $this->resourceFactory = $resourceFactory;
    }

    /**
     * Applies the orientation found in the EXIF data for $file and removes exif data.
     * Replaces $file with the processed output.
     *
     * @param \TYPO3\CMS\Core\Resource\FileInterface $file
     * @api
     */
    public function applyOrientation(FileInterface $file)
    {
        if ($file->getMimeType() !== self::JPEG_MIME_TYPE) {
            return;
        }

        $storage = $file->getStorage();
        $path = $file->getForLocalProcessing();

        $image = GeneralUtility::makeInstance(RawJpeg::class, $path);

        if (!$image->hasOrientation()) {
            return;
        }

        $this->rotateImage($image);
        $this->flipImage($image);

        GeneralUtility::makeInstance(Dispatcher::class)
            ->dispatch(__CLASS__, 'process', [$file, $image]);

        $output = $this->writeImage($image);

        $storage->replaceFile($file, $output);
    }

    /**
     * Returns if the file has an EXIF Orientation that can be "applied".
     *
     * @api
     * @param \TYPO3\CMS\Core\Resource\FileInterface $file
     * @return bool
     */
    public function canApplyOrientation(FileInterface $file)
    {
        if ($file->getMimeType() !== self::JPEG_MIME_TYPE) {
            return false;
        }

        $image = GeneralUtility::makeInstance(RawJpeg::class, $file->getForLocalProcessing());

        return $image->hasOrientation();
    }

    /**
     * @param \Bash\ExifOrientationHelper\Imaging\RawJpeg $image
     *
     * @return string
     */
    private function writeImage(RawJpeg $image)
    {
        $path = $this->getOutputFile();

        $image->write($path);

        return $path;
    }

    /**
     * @param \Bash\ExifOrientationHelper\Imaging\RawJpeg $image
     */
    private function rotateImage(RawJpeg $image)
    {
        if ($image->getOrientation() > 4) {
            $image->rotate(90);
        }
    }

    /**
     * @param \Bash\ExifOrientationHelper\Imaging\RawJpeg $image
     */
    private function flipImage(RawJpeg $image)
    {
        $orientation = $image->getOrientation();

        if ($orientation === 3 || $orientation === 6) {
            $image->flip(IMG_FLIP_BOTH);
        }

        if ($orientation === 2 || $orientation === 5) {
            $image->flip(IMG_FLIP_VERTICAL);
        }

        if ($orientation === 4 || $orientation === 7) {
            $image->flip(IMG_FLIP_HORIZONTAL);
        }
    }

    /**
     * @return string
     */
    private function getOutputFile()
    {
        return GeneralUtility::tempnam('exif-orientation-helper-', '.jpg');
    }
}
