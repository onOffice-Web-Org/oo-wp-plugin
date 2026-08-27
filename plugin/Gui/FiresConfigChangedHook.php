<?php

/**
 *
 *    Copyright (C) 2026 onOffice GmbH
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
 * Shared by AdminPageEstate, AdminPageAddress and AdminPageFormList: after a
 * bulk delete/duplicate action on a list view / form, notify external
 * listeners (e.g. the hosting infra's nginx/Redis cache purger) that plugin
 * config changed - but ONLY when the operation actually succeeded.
 *
 * Deliberately extracted out of the bulk-action closures (which are gated by
 * check_admin_referer()/current_user_can() and use real, DI-constructed
 * RecordManager instances) so this specific piece of logic - "fire the hook
 * if and only if $success is true" - can be unit tested in isolation,
 * without any WordPress nonce, capability, or database dependency.
 */
trait FiresConfigChangedHook
{
	/**
	 * @param bool $success Whether the create/update/delete/duplicate operation
	 *                       actually completed successfully.
	 * @param string $type RecordManagerFactory::TYPE_ESTATE/TYPE_ADDRESS/TYPE_FORM
	 * @param string $action e.g. RecordManagerFactory::ACTION_DELETE or 'duplicate'
	 * @param int|null $recordId The affected record ID, if known/singular.
	 */
	protected static function fireConfigChangedHook(bool $success, string $type, string $action, int $recordId = null): void
	{
		if ($success) {
			do_action('onoffice/config_changed', $type, $action, $recordId);
		}
	}
}
