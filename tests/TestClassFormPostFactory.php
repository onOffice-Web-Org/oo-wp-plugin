<?php

declare(strict_types=1);

namespace onOffice\tests;

use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\Form\FormPostFactory;

class TestClassFormPostFactory
	extends \WP_UnitTestCase
{
	/**
	 * @return FormPostFactory
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function buildSubject(): FormPostFactory
	{
		$pDIBuilder = new ContainerBuilder;
		$pDIBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pDI = $pDIBuilder->build();
		return $pDI->make(FormPostFactory::class);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testCreateOnceApplicantSearchForm()
	{
		$pSubject = $this->buildSubject();
		$pApplicantSearchForm1 = $pSubject->createOnceApplicantSearchForm();
		$pApplicantSearchForm2 = $pSubject->createOnceApplicantSearchForm();
		$this->assertNotSame($pApplicantSearchForm1, $pApplicantSearchForm2);
	}
}