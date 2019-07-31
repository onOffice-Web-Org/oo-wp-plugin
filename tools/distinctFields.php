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

namespace onOffice\WPlugin;

use DI\ContainerBuilder;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\DistinctFieldsChecker;
use onOffice\WPlugin\Field\DistinctFieldsHandler;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\WP\WPScriptStyleDefault;
use const ONOFFICE_DI_CONFIG_PATH;

require '../../../../wp-load.php';

header('Content-Type: application/json');


$pContainerBuilder = new ContainerBuilder;
$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
$pContainer = $pContainerBuilder->build();
$pFieldsCollectionBuilderShort = $pContainer->get(FieldsCollectionBuilderShort::class);

$pDistinctFieldsChecker = new DistinctFieldsChecker(new RequestVariablesSanitizer(), new WPScriptStyleDefault());
$pHandlerEnvironment = $pDistinctFieldsChecker->createHandlerEnvironment();

$pDistinctFieldsHandler = new DistinctFieldsHandler(new SDKWrapper(), $pFieldsCollectionBuilderShort, $pHandlerEnvironment);
$pDistinctFieldsHandler->check();
$value = $pDistinctFieldsHandler->getValues();

echo json_encode($value);
die;