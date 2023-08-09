<?php

declare(strict_types=1);

namespace NickVulkers\CleanMedia\Model;

class GetDuplicates
{
    /**
     * @param array $paths
     * @param bool $isRecursive
     * @return array
     */
    public function execute(array $paths, bool $isRecursive = true): array
    {
        $duplicates = [];

        foreach ($paths as $path) {
            $path = rtrim($path, DIRECTORY_SEPARATOR);

            if (is_dir($path)) {
                $duplicates = array_merge_recursive($duplicates, $this->scanDir($path, $isRecursive));
            }
        }

        return array_filter($duplicates, function ($data) {
            return count($data) > 1;
        });
    }

    /**
     * @param string $path
     * @param bool $isRecursive
     * @return array
     */
    private function scanDir(string $path, bool $isRecursive = true): array
    {
        $dir = opendir($path);

        if (!$dir) {
            return [];
        }

        $duplicates = [];

        while (($fileName = readdir($dir)) !== false) {
            if (in_array($fileName, array('.', '..'))) {
                continue;
            }

            $fullPath = $path . DIRECTORY_SEPARATOR . $fileName;

            if (is_dir($fullPath) && $isRecursive) {
                $duplicates = array_merge_recursive($duplicates, $this->scanDir($fullPath));
            } else {
                if (is_file($fullPath)) {
                    $hash = hash_file('md5', $fullPath);
                    $files[$hash] = $fullPath;
                }
            }
        }

        closedir($dir);

        return $duplicates;
    }
}
