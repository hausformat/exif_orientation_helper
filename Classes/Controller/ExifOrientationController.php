<?php
namespace Bash\ExifOrientationHelper\Controller;

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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @author Ruben Schmidmeister <ruben.schmidmeister@icloud.com
 */
class ExifOrientationController
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\MessageInterface
     */
    public function mainAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $params = $request->getQueryParams();

        $resourceFactory = ResourceFactory::getInstance();

        if (isset($params[ 'file_reference_uid' ])) {
            $fileReference = $resourceFactory->getFileReferenceObject($params[ 'file_reference_uid' ]);
            $file = $fileReference->getOriginalFile();
        }

        if (isset($params[ 'file_uid' ])) {
            $file = $resourceFactory->getFileObject($params[ 'file_uid' ]);
        }

        if (!isset($file)) {
            die('You must either pass file_reference_uid or file_uid');
        }

        /** @var ExifOrientationService $exifService */
        $exifService = GeneralUtility::makeInstance(ExifOrientationService::class);
        $exifService->applyOrientation($file);

        return $response->withHeader('location', $params[ 'return_url' ]);
    }
}
