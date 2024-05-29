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

namespace onOffice\WPlugin\WP;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 */

class WPPluginChecker {

    /**
     * @return bool
     */
    public function isSEOPluginActive():bool {
        return count($this->getActiveSEOPlugins()) > 0;
    }

    /**
     * @return array
     */
    public function getActiveSEOPlugins():array
    {
        $activeSEOPlugins = [];

        $ListSEOPlugins = [
            "wpseo/wp-seo.php" => "wpSEO",
            "seo-by-rank-math/rank-math.php" => "Rank Math SEO",
            "wordpress-seo/wp-seo.php" => "Yoast SEO",
            "all-in-one-seo-pack/all_in_one_seo_pack.php" => "All in One SEO",
            "autodescription/autodescription.php" => "SEO Framework",
            "wp-seopress/seopress.php" => "SEOPress",
            "squirrly-seo/squirrly.php" => "Squirrly SEO"
        ];
        foreach ($ListSEOPlugins as $keySEOPlugin => $nameSEOPlugin)
        {
            if( in_array( $keySEOPlugin, get_option("active_plugins") ) )
            {
                array_push($activeSEOPlugins, $nameSEOPlugin);
            }
        }
        return $activeSEOPlugins;
    }
}