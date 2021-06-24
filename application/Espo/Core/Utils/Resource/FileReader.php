<?php
/************************************************************************
 * This file is part of EspoCRM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2021 Yurii Kuznietsov, Taras Machyshyn, Oleksii Avramenko
 * Website: https://www.espocrm.com
 *
 * EspoCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * EspoCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EspoCRM. If not, see http://www.gnu.org/licenses/.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "EspoCRM" word.
 ************************************************************************/

namespace Espo\Core\Utils\Resource;

use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Metadata;

use RuntimeException;

/**
 * Reads resource files.
 */
class FileReader
{
    private $paths = [
        'corePath' => 'application/Espo/Resources/',
        'modulePath' => 'application/Espo/Modules/{*}/Resources/',
        'customPath' => 'custom/Espo/Custom/Resources/',
    ];

    private $fileManager;

    private $metadata;

    public function __construct(FileManager $fileManager, Metadata $metadata)
    {
        $this->fileManager = $fileManager;
        $this->metadata = $metadata;
    }

    /**
     * Read a resource file. Returns NULL if the file does not exists.
     *
     * @throws RuntimeException If the resource does not exist.
     */
    public function read(string $path, FileReaderParams $params): string
    {
        $exactPath = $this->findExactPath($path, $params);

        if (!$exactPath) {
            throw new RuntimeException("Resource file '{$path}' does not exist.");
        }

        return $this->fileManager->getContents($exactPath);
    }

    /**
     * Whether a resource file exists.
     */
    public function exists(string $path, FileReaderParams $params): bool
    {
        return $this->findExactPath($path, $params) !== null;
    }

    private function findExactPath(string $path, FileReaderParams $params): ?string
    {
        $customPath = $this->paths['customPath'] . $path;

        if ($this->fileManager->isFile($customPath)) {
            return $customPath;
        }

        $moduleName = null;

        if ($params->getScope()) {
            $moduleName = $this->metadata->getScopeModuleName($params->getScope());
        }

        if ($moduleName) {
            $modulePath = $this->buildModulePath($path, $moduleName);

            if ($this->fileManager->isFile($modulePath)) {
                return $modulePath;
            }
        }

        if ($params->getModuleName()) {
            $modulePath = $this->buildModulePath($path, $params->getModuleName());

            if ($this->fileManager->isFile($modulePath)) {
                return $modulePath;
            }
        }

        $corePath = $this->paths['corePath'] . $path;

        if ($this->fileManager->isFile($corePath)) {
            return $corePath;
        }

        return null;
    }

    private function buildModulePath(string $path, string $moduleName): string
    {
        return str_replace(
            '{*}',
            $moduleName,
            $this->paths['modulePath'] . $path
        );
    }
}