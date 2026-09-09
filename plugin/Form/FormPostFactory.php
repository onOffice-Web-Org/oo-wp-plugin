<?php

declare(strict_types=1);

namespace onOffice\WPlugin\Form;

use onOffice\WPlugin\Vendor\DI\Container;
use onOffice\WPlugin\Vendor\DI\DependencyException;
use onOffice\WPlugin\Vendor\DI\NotFoundException;
use onOffice\WPlugin\FormPostApplicantSearch;

class FormPostFactory
{
	/** @var Container */
	private $_pContainer;

	/**
	 * @param Container $pContainer
	 */
	public function __construct(Container $pContainer)
	{
		$this->_pContainer = $pContainer;
	}

	/**
	 * Creates a new instance of `FormPostApplicantSearch` on every call
	 *
	 * @return FormPostApplicantSearch
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function createOnceApplicantSearchForm(): FormPostApplicantSearch
	{
		return $this->_pContainer->make(FormPostApplicantSearch::class);
	}
}