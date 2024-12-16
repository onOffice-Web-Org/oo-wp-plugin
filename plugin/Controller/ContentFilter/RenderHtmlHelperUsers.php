<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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

declare (strict_types=1);

namespace onOffice\WPlugin\Controller\ContentFilter;

use function is_user_logged_in;

class RenderHtmlHelperUsers
{
    /**
     * @param string $type
     * @param string $documentationLink
     * @param string $linkDetail
     * @param array $pDataDetail
     * @return string
     */
    public static function renderHtmlHelperUserIfEmptyId(string $type, string $documentationLink, string $linkDetail = null, array $pDataDetail): string
    {
        $title       = sprintf(__("%s list documentation", 'onoffice-for-wp-websites'), ucfirst($type));
        $linkDetail  = !empty($pDataDetail) ? $linkDetail : '<a href=' . esc_url($documentationLink) . '>' . esc_html($title) . '</a>';
        $description = sprintf(__("The plugin couldn't find any %s. Please make sure that you have published some %s, as described in the %s", 'onoffice-for-wp-websites'), $type, $type, $linkDetail);
        $html = '<div class="oo-detailview-helper">';
        $html .= '<p class="oo-detailview-helper-text oo-detailview-helper-text--default">' . sprintf(__("You have opened the detail page, but we do not know which %s to show you, because there is no %s ID in the URL. Please go to an %s list and open an %s from there.", 'onoffice-for-wp-websites'), $type, $type, $type, $type) . '</p>';

        if (!empty($pDataDetail)) {
            $description = sprintf(__('Since you are logged in, here is a link to a random %s so that you can preview the detail page: %s', 'onoffice-for-wp-websites'), $type, $linkDetail);
        }

        if (is_user_logged_in()) {
            $html .= '<p class="oo-detailview-helper-text oo-detailview-helper-text--admin">' . $description . '</p>';
        }
        $html .= '</div>';

        return $html;
    }
}