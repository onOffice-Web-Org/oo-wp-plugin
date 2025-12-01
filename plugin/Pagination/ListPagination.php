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
namespace onOffice\WPlugin\Pagination;

class ListPagination
{
    protected $class;
    protected $type;
    protected $anchor;
    protected $list_id;
    protected $parameter;
    protected $numpages;
    protected $page;

    public function __construct($args = [])
    {
        $args = wp_parse_args(
            $args,
            [
                'class' => null,
                'type' => null,
                'anchor' => null,
                'numpages' => null,
                'list_id' => null,
            ]
        );

        $this->class = $args['class'];
        $this->type = $args['type'];
        $this->anchor = $this->normalizeAnchor($args['anchor']);
        $this->list_id = $args['list_id'];
        $this->parameter = $this->list_id ? 'page_of_id_' . $this->list_id : null;
        $this->page = $this->determineCurrentPage();
        $this->numpages = $this->determineNumPages($args['numpages']);
    }

    protected function normalizeAnchor($anchor)
    {
        if ($anchor && substr($anchor, 0, 1) != '#') {
            return '#' . $anchor;
        }
        return $anchor;
    }

    protected function determineCurrentPage()
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Public pagination parameter, no side effects
        if ($this->parameter && isset($_GET[$this->parameter]) && is_numeric($_GET[$this->parameter])) {
            return (int) $_GET[$this->parameter];
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended
    
        // Fallback to standard pagination query vars
        if (is_home() || is_front_page()) {
            return get_query_var('page') ?: 1;
        }
    
        return get_query_var('paged') ?: 1;
    }

    protected function determineNumPages($numpagesArg)
    {
        if ($numpagesArg) {
            return $numpagesArg;
        } elseif (in_array($this->type, ['property', 'address'])) {
            global $numpages;
            return $numpages;
        } else {
            global $wp_query;
            return $wp_query->max_num_pages;
        }
    }

    public function render()
{
    if ($this->numpages <= 1) {
        return;
    }

    echo '<nav class="' . esc_attr($this->class) . ' ' .
        ($this->type ? esc_attr($this->type) : '') .
        '" aria-label="' . esc_attr(__('Post navigation', 'onoffice-for-wp-websites')) . '">';
    echo '<ul>';
 
    for ($i = 1; $i <= $this->numpages; $i++) {
        if (1 != $this->numpages) {
            echo '<li>';
            if ($this->page == $i) {
                echo '<span class="current" aria-current="page">' . esc_html($i) . '</span>';
            } else {
                /* translators: %d: page number */
                echo '<a href="' . esc_url($this->getPagenumLink($i)) . '" aria-label="' . 
                /* translators: %d: page number */
                esc_attr(sprintf(esc_html_x('Page %d', 'template', 'onoffice-for-wp-websites'), $i)) . 
                '"><span aria-hidden="true">' . esc_html($i) . '</span></a>';
            }
            echo '</li>';
        }
    }

    echo '</ul></nav>';
}
    protected function renderLink($pageNum, $icon, $label)
    {
        echo '<li>';
        echo '<a href="' .
            esc_url($this->getPagenumLink($pageNum)) . '">';
        echo '<span class="u-screen-reader-only">' . esc_html($label) . '</span>';
        echo '</a></li>';
    }
    protected function getPagenumLink($paged)
    {

        $base_url = get_pagenum_link(1);
        $base_url = htmlspecialchars_decode($base_url);

        if (!empty($this->parameter)) {
            $base_url = remove_query_arg($this->parameter, $base_url);
            $pagenum_link = add_query_arg($this->parameter, $paged, $base_url);
        } else {
            $pagenum_link = get_pagenum_link($paged);
        }

        if (!empty($this->anchor)) {
            $pagenum_link .= $this->anchor;
        }

        return esc_url($pagenum_link);
    }
}