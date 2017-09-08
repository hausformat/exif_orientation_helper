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
use TYPO3\CMS\Backend\Form\Element\InlineElementHookInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * @author Ruben Schmidmeister <ruben.schmidmeister@icloud.com
 */
class InlineRecord implements InlineElementHookInterface
{
    /**
     * Pre-processing to define which control items are enabled or disabled.
     *
     * @param string $parentUid        The uid of the parent (embedding) record (uid or NEW...)
     * @param string $foreignTable     The table (foreign_table) we create control-icons for
     * @param array  $childRecord      The current record of that foreign_table
     * @param array  $childConfig      TCA configuration of the current field of the child record
     * @param bool   $isVirtual        Defines whether the current records is only virtually shown and not physically part of the parent record
     * @param array  &$enabledControls (reference) Associative array with the enabled control items
     *
     * @return void
     */
    public function renderForeignRecordHeaderControl_preProcess($parentUid, $foreignTable, array $childRecord, array $childConfig, $isVirtual, array &$enabledControls)
    {
    }

    /**
     * Post-processing to define which control items to show. Possibly own icons can be added here.
     *
     * @param string $parentUid     The uid of the parent (embedding) record (uid or NEW...)
     * @param string $foreignTable  The table (foreign_table) we create control-icons for
     * @param array  $childRecord   The current record of that foreign_table
     * @param array  $childConfig   TCA configuration of the current field of the child record
     * @param bool   $isVirtual     Defines whether the current records is only virtually shown and not physically part of the parent record
     * @param array  &$controlItems (reference) Associative array with the currently available control items
     *
     * @return void
     */
    public function renderForeignRecordHeaderControl_postProcess($parentUid, $foreignTable, array $childRecord, array $childConfig, $isVirtual, array &$controlItems)
    {
        if ($foreignTable !== 'sys_file_reference') {
            return;
        }

        $uid = $childRecord[ 'uid' ];

        if (substr($uid, 0, 3) === 'NEW') {
            return;
        }

        /** @var LanguageService $languageService */
        $languageService = $GLOBALS[ 'LANG' ];
        $resourceFactory = ResourceFactory::getInstance();
        $fileReference = $resourceFactory->getFileReferenceObject($uid);

        $exifService = GeneralUtility::makeInstance(ExifOrientationService::class);

        if (!$exifService->canApplyOrientation($fileReference)) {
            return NULL;
        }

        /** @var IconFactory $iconFactory */
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        $icon = $iconFactory->getIcon(
            'apply-exif-orientation',
            Icon::SIZE_SMALL,
            NULL
        );

        $url = BackendUtility::getModuleUrl('exif_orientation_helper', [
            'file_reference_uid' => $childRecord[ 'uid' ],
            'return_url'         => $this->getReturnUrl(),
        ]);

        $title = $languageService->sL('LLL:EXT:exif_orientation_helper/Resources/Private/Language/locallang.xlf:button.tooltip');
        $confirmText = $languageService->sL('LLL:EXT:exif_orientation_helper/Resources/Private/Language/locallang.xlf:confirm.text');

        $controlItems[ 'exif_orientation_button' ] = '<a data-content="' . htmlentities($confirmText) . '" data-severity="warning" data-title="' . htmlentities($title) . '" href="' . htmlentities($url) . '" title="' . htmlentities($title) . '" class="btn btn-default t3js-modal-trigger">' . $icon->render() . '</a>';
    }

    /**
     * @return string
     */
    protected function getReturnUrl()
    {
        $arguments = GeneralUtility::_GET();

        $returnUrl = [
            'edit'      => $arguments[ 'edit' ],
            'returnUrl' => $arguments[ 'returnUrl' ],
        ];

        return BackendUtility::getModuleUrl('record_edit', $returnUrl);
    }
}
