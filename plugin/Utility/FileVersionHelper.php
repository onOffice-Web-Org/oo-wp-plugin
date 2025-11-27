<?php

/**
 *
 *    Copyright (C) 2025 onOffice GmbH
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=1);

namespace onOffice\WPlugin\Utility;

/**
 * Helper class for file versioning in WordPress script/style enqueuing
 */
class FileVersionHelper
{
    /**
     * Get file modification time or fallback to plugin version
     *
     * This method returns the file modification time for cache busting.
     * If the file doesn't exist (e.g., in test environments), it falls back
     * to the plugin version constant.
     *
     * @param string $filePath Absolute path to the file
     * @return string|int File modification time (int) or plugin version (string)
     */
    public static function getFileVersion(string $filePath)
    {
        return file_exists($filePath) ? filemtime($filePath) : ONOFFICE_PLUGIN_VERSION;
    }
}