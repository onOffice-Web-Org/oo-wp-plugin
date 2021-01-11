<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

declare (strict_types=1);

namespace onOffice;

use DateTimeImmutable;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Controller\AddressListEnvironment;
use onOffice\WPlugin\Controller\AddressListEnvironmentDefault;
use onOffice\WPlugin\Controller\InputVariableReaderConfig;
use onOffice\WPlugin\Controller\InputVariableReaderConfigFieldnames;
use onOffice\WPlugin\Filesystem\Filesystem;
use onOffice\WPlugin\Filesystem\FilesystemDirect;
use onOffice\WPlugin\Form\FormPostConfiguration;
use onOffice\WPlugin\Form\FormPostConfigurationDefault;
use onOffice\WPlugin\Form\FormPostContactConfiguration;
use onOffice\WPlugin\Form\FormPostContactConfigurationDefault;
use onOffice\WPlugin\Form\FormPostInterestConfiguration;
use onOffice\WPlugin\Form\FormPostInterestConfigurationDefault;
use onOffice\WPlugin\Form\FormPostOwnerConfiguration;
use onOffice\WPlugin\Form\FormPostOwnerConfigurationDefault;
use onOffice\WPlugin\Installer\DatabaseChanges;
use onOffice\WPlugin\Installer\DatabaseChangesInterface;
use onOffice\WPlugin\Region\RegionController;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderBuilderConfig;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderBuilderConfigDefault;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderGenericConfiguration;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderGenericConfigurationDefault;
use onOffice\WPlugin\Utility\HTTPHeaders;
use onOffice\WPlugin\Utility\HTTPHeadersGeneric;
use onOffice\WPlugin\WP\WPNonceWrapper;
use onOffice\WPlugin\WP\WPNonceWrapperDefault;
use onOffice\WPlugin\WP\WPOptionWrapperBase;
use onOffice\WPlugin\WP\WPOptionWrapperDefault;
use onOffice\WPlugin\WP\WPScreenWrapper;
use onOffice\WPlugin\WP\WPScreenWrapperDefault;
use onOffice\WPlugin\WP\WPScriptStyleBase;
use onOffice\WPlugin\WP\WPScriptStyleDefault;
use onOffice\WPlugin\Factory\DateTimeImmutableFactory;
use wpdb;
use function DI\autowire;



return [
	APIClientActionGeneric::class => autowire()
		->constructorParameter('actionId', '')
		->constructorParameter('resourceType', ''),
	RegionController::class => autowire()
		->constructorParameter('init', false),
	FormPostConfiguration::class => autowire(FormPostConfigurationDefault::class),
	FormPostOwnerConfiguration::class => autowire(FormPostOwnerConfigurationDefault::class),
	FormPostContactConfiguration::class => autowire(FormPostContactConfigurationDefault::class),
	FormPostInterestConfiguration::class => autowire(FormPostInterestConfigurationDefault::class),
	WPScriptStyleBase::class => autowire(WPScriptStyleDefault::class),
	WPOptionWrapperBase::class => autowire(WPOptionWrapperDefault::class),
	ScriptLoaderBuilderConfig::class => autowire(ScriptLoaderBuilderConfigDefault::class),
	ScriptLoaderGenericConfiguration::class => autowire(ScriptLoaderGenericConfigurationDefault::class),
	Filesystem::class => autowire(FilesystemDirect::class),
	wpdb::class => static function() {
		global $wpdb;
		return $wpdb;
	},
	WPNonceWrapper::class => autowire(WPNonceWrapperDefault::class),
	WPScreenWrapper::class => autowire(WPScreenWrapperDefault::class),
	InputVariableReaderConfig::class => autowire(InputVariableReaderConfigFieldnames::class),
	DatabaseChangesInterface::class => autowire(DatabaseChanges::class),
	AddressListEnvironment::class => autowire(AddressListEnvironmentDefault::class),
	DateTimeImmutableFactory::class => autowire(DateTimeImmutableFactory::class),
	HTTPHeaders::class => autowire(HTTPHeadersGeneric::class),
];