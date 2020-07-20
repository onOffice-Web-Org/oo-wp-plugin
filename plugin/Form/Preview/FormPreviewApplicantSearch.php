<?php

declare(strict_types=1);

namespace onOffice\WPlugin\Form\Preview;

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;
use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Form\FormPostFactory;

class FormPreviewApplicantSearch
{
	/** @var DataFormConfigurationFactory */
	private $_pDataFormConfigurationFactory;

	/** @var FormPostFactory */
	private $_pFormPostFactory;

	/**
	 * @param DataFormConfigurationFactory $pDataFormConfigurationFactory
	 * @param FormPostFactory $pFormPostFactory
	 */
	public function __construct(
		DataFormConfigurationFactory $pDataFormConfigurationFactory,
		FormPostFactory $pFormPostFactory)
	{
		$this->_pDataFormConfigurationFactory = $pDataFormConfigurationFactory;
		$this->_pFormPostFactory = $pFormPostFactory;
	}

	/**
	 * @param string $formName
	 * @return int
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 */
	public function preview(string $formName): int
	{
		$formNo = filter_var($_POST['oo_formno'] ?? '', FILTER_SANITIZE_NUMBER_INT);
		try
		{
			$pDataFormConfiguration = $this->_pDataFormConfigurationFactory->loadByFormName($formName);
			if ($pDataFormConfiguration->getFormType() !== Form::TYPE_APPLICANT_SEARCH) {
				return 0;
			}
			$pDataFormConfiguration->setLimitResults(0);
			$pFormPost = $this->_pFormPostFactory->createOnceApplicantSearchForm();
			$pFormPost->initialCheck($pDataFormConfiguration, (int)$formNo);
			return $pFormPost->getAbsolutCountResults();
		} catch (UnknownFormException $pException) {
			return 0;
		}
	}
}