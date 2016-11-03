<?php
namespace Bash\ExifOrientationHelper\Controller
{
    use Bash\ExifOrientationHelper\Service\ExifOrientationService;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use TYPO3\CMS\Core\Resource\ResourceFactory;
    use TYPO3\CMS\Core\Utility\GeneralUtility;

    class ExifOrientationController
    {
        public function mainAction(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
        {
            $params = $request->getQueryParams();

            $resourceFactory = ResourceFactory::getInstance();

            if (isset($params['file_reference_uid'])) {
                $fileReference = $resourceFactory->getFileReferenceObject($params['file_reference_uid']);
                $file = $fileReference->getOriginalFile();
            }

            if (isset($params['file_uid'])) {
                $file = $resourceFactory->getFileObject($params['file_uid']);
            }

            if (!isset($file)) {
                die('You must either pass file_reference_uid or file_uid');
            }

            /** @var ExifOrientationService $exifService */
            $exifService = GeneralUtility::makeInstance(ExifOrientationService::class, $resourceFactory);
            $exifService->applyRotation($file);

            return $response->withHeader('location', $params['return_url']);
        }
    }
}
