<?php

declare(strict_types=1);

namespace onOffice\tests;

use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationApplicantSearch;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationContact;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;
use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Form\FormPostFactory;
use onOffice\WPlugin\Form\Preview\FormPreviewApplicantSearch;
use onOffice\WPlugin\FormPostApplicantSearch;
use PHPUnit\Framework\MockObject\MockObject;

class TestClassFormPreviewApplicantSearch
	extends \WP_UnitTestCase
{
	/**
	 * @return Container
	 * @throws Exception
	 */
	private function buildContainer(): Container
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$pDataFormConfigurationFactory = $this->getMockBuilder(DataFormConfigurationFactory::class)
			->getMock();
		$pFormPostFactory = $this->getMockBuilder(FormPostFactory::class)
			->disableOriginalConstructor()
			->getMock();
		$pContainer->set(DataFormConfigurationFactory::class, $pDataFormConfigurationFactory);
		$pContainer->set(FormPostFactory::class, $pFormPostFactory);
		return $pContainer;
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 * @throws Exception
	 */
	public function testPreviewWithUnknownFormIsZero()
	{
		$pContainer = $this->buildContainer();
		/** @var MockObject $pDataFormConfigurationFactory */
		$pDataFormConfigurationFactory = $pContainer->get(DataFormConfigurationFactory::class);
		$pDataFormConfigurationFactory->expects($this->once())->method('loadByFormName')
			->willThrowException(new UnknownFormException);

		$pSubject = $pContainer->get(FormPreviewApplicantSearch::class);
		$this->assertSame(0, $pSubject->preview('testFormUnknown'));
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 */
	public function testPreviewWithOtherFormTypeIsZero()
	{
		$pContainer = $this->buildContainer();
		/** @var MockObject $pDataFormConfigurationFactory */
		$pDataFormConfigurationFactory = $pContainer->get(DataFormConfigurationFactory::class);
		$pDataFormConfiguration = new DataFormConfigurationContact;
		$pDataFormConfiguration->setFormType(Form::TYPE_CONTACT);
		$pDataFormConfigurationFactory->expects($this->once())->method('loadByFormName')
			->with('testForm')
			->willReturn($pDataFormConfiguration);

		$pSubject = $pContainer->get(FormPreviewApplicantSearch::class);
		$this->assertSame(0, $pSubject->preview('testForm'));
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 */
	public function testPreview()
	{
		$_POST['oo_formno'] = '1';
		$pContainer = $this->buildContainer();
		/** @var MockObject $pDataFormConfigurationFactory */
		$pDataFormConfigurationFactory = $pContainer->get(DataFormConfigurationFactory::class);
		$pDataFormConfiguration = new DataFormConfigurationApplicantSearch;
		$pDataFormConfiguration->setLimitResults(10);
		$pDataFormConfiguration->setFormType(Form::TYPE_APPLICANT_SEARCH);
		$pDataFormConfigurationFactory->expects($this->once())->method('loadByFormName')
			->with('testForm')
			->willReturn($pDataFormConfiguration);

		$pFormPostApplicantSeach = $this->getMockBuilder(FormPostApplicantSearch::class)
			->disableOriginalConstructor()
			->getMock();

		$pFormPostApplicantSeach
			->expects($this->once())
			->method('initialCheck')
			->id("1")
			->with($pDataFormConfiguration, 1);
		$pFormPostApplicantSeach
			->expects($this->once())
			->method('getAbsolutCountResults')
			->after("1")
			->willReturn("6");

		/** @var MockObject $pFormPostFactory */
		$pFormPostFactory = $pContainer->get(FormPostFactory::class);
		$pFormPostFactory
			->expects($this->once())
			->method('createOnceApplicantSearchForm')
			->willReturn($pFormPostApplicantSeach);

		$pSubject = $pContainer->get(FormPreviewApplicantSearch::class);
		$this->assertSame(6, $pSubject->preview('testForm'));
		$this->assertSame(0, $pDataFormConfiguration->getLimitResults());
	}
}