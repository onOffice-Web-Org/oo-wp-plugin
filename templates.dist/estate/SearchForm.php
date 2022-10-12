<?php
/**
 *
 *    Copyright (C) 2018  onOffice Software AG
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'fields.php';
$visible = $pEstates->getVisibleFilterableFields();
if (count($visible) === 0) {
	return;
}
?>
<div class="oo-searchform">
	<form method="get" data-estate-search-name="<?php echo esc_attr($getListName()); ?>">
		<div class="oo-searchformfieldwrap">
			<?php
			foreach ($visible as $inputName => $properties) :
				echo '<div class="oo-searchformfield">';
				echo '<label>'.esc_html($properties['label']).':</label>';
				renderFieldEstateSearch($inputName, $properties);
				echo '</div>';
			endforeach;
			?>
            <div class="oo-searchformfield">
				<input type="submit" value="<?php echo esc_attr__('Search', 'onoffice-for-wp-websites'); ?>"
                       data-estate-count-list="<?php echo $pEstates->getEstateOverallCount(); ?>">
				<svg viewBox="0 0 30 30" id="spinner"></svg>
			</div>
		</div>
	</form>
</div>