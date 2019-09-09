<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use Akeneo\Crowdin\Client;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BaseService
{

    /** @var Client */
    protected $client;

    public function __construct()
    {
        $apiService = GeneralUtility::makeInstance(ApiCredentialsService::class);
        $project = $apiService->get();
        $this->client = new Client($project->getIdentifier(), $project->getPassword());
    }

    /**
     * Unzip an language zip file
     *
     * @param string $file path to zip file
     * @param string $path path to extract to
     * @throws \RuntimeException
     */
    protected function unzip(string $file, string $path)
    {
        $zip = zip_open($file);
        if (is_resource($zip)) {
            if (!is_dir($path)) {
                GeneralUtility::mkdir_deep($path);
            }
            while (($zipEntry = zip_read($zip)) !== false) {
                $zipEntryName = zip_entry_name($zipEntry);
                if (strpos($zipEntryName, '/') !== false) {
                    $zipEntryPathSegments = explode('/', $zipEntryName);
                    $fileName = array_pop($zipEntryPathSegments);
                    // It is a folder, because the last segment is empty, let's create it
                    if (empty($fileName)) {
                        GeneralUtility::mkdir_deep($path . implode('/', $zipEntryPathSegments));
                    } else {
                        $absoluteTargetPath = GeneralUtility::getFileAbsFileName($path . implode('/', $zipEntryPathSegments) . '/' . $fileName);
                        if (trim($absoluteTargetPath) !== '') {
                            $return = GeneralUtility::writeFile(
                                $absoluteTargetPath,
                                zip_entry_read($zipEntry, zip_entry_filesize($zipEntry))
                            );
                            if ($return === false) {
                                throw new \RuntimeException('Could not write file ' . $zipEntryName, 1520170845);
                            }
                        } else {
                            throw new \RuntimeException('Could not write file ' . $zipEntryName, 1520170846);
                        }
                    }
                } else {
                    throw new \RuntimeException('Extension directory missing in zip file!', 1520170847);
                }
            }
        } else {
            throw new \RuntimeException('Unable to open zip file ' . $file, 1520170848);
        }
    }

    public function getProjectIdentifier(): string
    {
        return $this->client->getProjectIdentifier();
    }
}