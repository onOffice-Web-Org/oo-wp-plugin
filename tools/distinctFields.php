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

namespace onOffice\WPlugin;

use DI\ContainerBuilder;
use onOffice\WPlugin\Field\DistinctFieldsHandler;
use const ONOFFICE_DI_CONFIG_PATH;
use function json_encode;

require '../../../../wp-load.php';

header('Content-Type: application/json');

//var_export($_POST); die;
$pContainerBuilder = new ContainerBuilder;
$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
$pContainer = $pContainerBuilder->build();
$value = $pContainer->get(DistinctFieldsHandler::class)->check();

echo json_encode($value);
die;