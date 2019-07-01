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

use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeAddressEnvironment;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeAddressEnvironmentDefault;
use onOffice\WPlugin\Form\FormPostApplicantSearchConfiguration;
use onOffice\WPlugin\Form\FormPostApplicantSearchConfigurationDefault;
use onOffice\WPlugin\Form\FormPostConfiguration;
use onOffice\WPlugin\Form\FormPostConfigurationDefault;
use onOffice\WPlugin\Form\FormPostContactConfiguration;
use onOffice\WPlugin\Form\FormPostContactConfigurationDefault;
use onOffice\WPlugin\Form\FormPostInterestConfiguration;
use onOffice\WPlugin\Form\FormPostInterestConfigurationDefault;
use onOffice\WPlugin\Form\FormPostOwnerConfiguration;
use onOffice\WPlugin\Form\FormPostOwnerConfigurationDefault;
use onOffice\WPlugin\Impressum;
use onOffice\WPlugin\Template;
use onOffice\WPlugin\WP\WPOptionWrapperBase;
use onOffice\WPlugin\WP\WPOptionWrapperDefault;
use onOffice\WPlugin\WP\WPScriptStyleBase;
use onOffice\WPlugin\WP\WPScriptStyleDefault;
use function DI\autowire;
use function DI\get;

return [
	Template::class => autowire()
		->constructorParameter('templateName', '')
		->method('setImpressum', get(Impressum::class)),
	ContentFilterShortCodeAddressEnvironment::class => autowire(ContentFilterShortCodeAddressEnvironmentDefault::class),
	FormPostConfiguration::class => autowire(FormPostConfigurationDefault::class),
	FormPostOwnerConfiguration::class => autowire(FormPostOwnerConfigurationDefault::class),
	FormPostContactConfiguration::class => autowire(FormPostContactConfigurationDefault::class),
	FormPostInterestConfiguration::class => autowire(FormPostInterestConfigurationDefault::class),
	FormPostApplicantSearchConfiguration::class => autowire(FormPostApplicantSearchConfigurationDefault::class),
	WPScriptStyleBase::class => autowire(WPScriptStyleDefault::class),
	WPOptionWrapperBase::class => autowire(WPOptionWrapperDefault::class),
];