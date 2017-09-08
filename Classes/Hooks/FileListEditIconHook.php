<?php
namespace Bash\ExifOrientationHelper\Hooks;

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

use Bash\ExifOrientationHelper\Service\ExifOrientationService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Filelist\FileListEditIconHookInterface;
use TYPO3\CMS\Lang\LanguageService;

/**
 * @author Ruben Schmidmeister <ruben.schmidmeister@icloud.com
 */
class FileListEditIconHook implements FileListEditIconHookInterface
{
    /**
     * Modifies edit icon array
     *
     * @param array                        $cells        Array of edit icons
     * @param \TYPO3\CMS\Filelist\FileList $parentObject Parent object
     *
     * @return void
     */
    public function manipulateEditIcons(&$cells, &$parentObject)
    {
        $fileOrFolderObject = $cells[ '__fileOrFolderObject' ];

        if (!($fileOrFolderObject instanceof File)) {
            return;
        }

        $button = $this->getButton($fileOrFolderObject);

        if ($button === NULL) {
            $button = $parentObject->spaceIcon;
        }

        $cells = array_merge(['exif_orientation_helper' => $button], $cells);
    }

    /**
     * @param \TYPO3\CMS\Core\Resource\File $fileOrFolderObject
     *
     * @return null|string
     */
    protected function getButton(File $fileOrFolderObject)
    {
        $exifService = GeneralUtility::makeInstance(ExifOrientationService::class);

        if (!$exifService->canApplyOrientation($fileOrFolderObject)) {
            return NULL;
        }

        /** @var LanguageService $languageService */
        $languageService = $GLOBALS[ 'LANG' ];

        /** @var IconFactory $iconFactory */
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $icon = $iconFactory->getIcon('apply-exif-orientation', Icon::SIZE_SMALL);

        $url = BackendUtility::getModuleUrl('exif_orientation_helper', [
            'file_uid'   => $fileOrFolderObject->getUid(),
            'return_url' => $this->getReturnUrl(),
        ]);

        $title = $languageService->sL('LLL:EXT:exif_orientation_helper/Resources/Private/Language/locallang.xlf:button.tooltip');
        $confirmText = $languageService->sL('LLL:EXT:exif_orientation_helper/Resources/Private/Language/locallang.xlf:confirm.text');

        return '<a data-content="' . htmlentities($confirmText) . '" data-severity="warning" data-title="' . htmlentities($title) . '" href="' . htmlentities($url) . '" title="' . htmlentities($title) . '" class="btn btn-default t3js-modal-trigger">' . $icon->render() . '</a>';
    }

    /**
     * @return string
     */
    protected function getReturnUrl()
    {
        $arguments = GeneralUtility::_GET();

        $returnUrl = [
            'id'        => $arguments[ 'id' ],
            'returnUrl' => $arguments[ 'returnUrl' ],
        ];

        return BackendUtility::getModuleUrl($arguments[ 'M' ], $returnUrl);
    }
}

