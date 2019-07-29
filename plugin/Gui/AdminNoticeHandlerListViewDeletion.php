<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

namespace onOffice\WPlugin\Gui;

/**
 *
 */

class AdminNoticeHandlerListViewDeletion
{
	/**
	 *
	 * @param int $deletedCount
	 * @return string
	 *
	 */

	public function handleListView(int $deletedCount): string
	{
 		if ($deletedCount > 0) {
			/* translators: %s will be replaced with a number. */
			$message = sprintf(_n('%s list view has been deleted.', '%s list views have been deleted.',
				$deletedCount, 'onoffice'), number_format_i18n($deletedCount));
			return $this->displayListViewDeleteSuccess($message);
		} else {
			return $this->displayListViewDeleteError(__('No list view was deleted.', 'onoffice'));
		}
	}


	/**
	 *
	 * @param int $deletedCount
	 * @return string
	 *
	 */

	public function handleFormView(int $deletedCount): string
	{
		if ($deletedCount > 0) {
			/* translators: %s will be replaced with a number. */
			$message = sprintf(_n('%s form has been deleted.', '%s forms have been deleted.',
				$deletedCount, 'onoffice'), number_format_i18n($deletedCount));
			return $this->displayListViewDeleteSuccess($message);
		} else {
			return $this->displayListViewDeleteError(__('No form was deleted.', 'onoffice'));
		}
	}



	/**
	 *
	 * @return string
	 *
	 */

	private function displayListViewDeleteSuccess(string $message): string
	{
		$class = 'notice notice-success is-dismissible';
		return sprintf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function displayListViewDeleteError(string $message): string
	{
		$class = 'notice notice-error is-dismissible';
		return sprintf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
	}
}
