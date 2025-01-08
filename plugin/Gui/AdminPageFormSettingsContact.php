<?php

/**
 *
 *    Copyright (C) 2017-2019 onOffice GmbH
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

namespace onOffice\WPlugin\Gui;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorFormContact;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilder;
use onOffice\WPlugin\Model\InputModel\InputModelConfigurationFormContact;
use onOffice\WPlugin\Model\InputModel\InputModelDBBuilderGeneric;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigForm;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelBuilder\InputModelBuilderGeoRange;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Types\FieldsCollection;
use function __;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Language;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\API\ApiClientException;

/**
 *
 */

class AdminPageFormSettingsContact
	extends AdminPageFormSettingsBase
{
	/** */
	const FORM_VIEW_GEOFIELDS = 'geofields';


	/** @var array */
	private $_additionalCategories = array();

	/** @var bool */
	private $_showCreateAddress = false;

	/** @var bool */
	private $_showCreateInterest = false;

	/** @var bool */
	private $_showCreateOwner = false;

	/** @var bool */
	private $_showCheckDuplicates = false;

	/** @var bool */
	private $_showCheckDuplicatesInterestOwner = false;

	/** @var bool */
	private $_showNewsletterCheckbox = false;

	/** @var bool */
	private $_showGeoPositionSettings = false;

	/** @var bool */
	private $_showEstateContextCheckbox = false;

    /** @var bool */
    private $_showContactTypeSelect = false;


	/**
	 *
	 * @throws \Exception
	 */

	protected function buildForms()
	{
		parent::buildForms();
		$pFormModelBuilder = $this->getFormModelBuilder();
		$pConfigForm = new InputModelDBFactoryConfigForm();
		$pInputModelDBFactory = new InputModelDBFactory($pConfigForm);
		$pInputModelConfiguration = new InputModelConfigurationFormContact();
		$pInputModelBuilder = new InputModelDBBuilderGeneric($pInputModelDBFactory, $pInputModelConfiguration);
		$pRecordReadManager = new RecordManagerReadForm();

		if ($this->getListViewId() != null) {
			$values = $pRecordReadManager->getRowById($this->getListViewId());
			$pInputModelBuilder->setValues($values);
		}
		if ($this->getType() === Form::TYPE_CONTACT) {
			$pInputModelRecipient = $pFormModelBuilder->createInputModelRecipientContactForm();
		} else {
			$pInputModelRecipient = $pFormModelBuilder->createInputModelRecipient();
		}
		$pInputModelDefaultRecipient = $pFormModelBuilder->createInputModelDefaultRecipient();
		$pInputModelSubject = $pFormModelBuilder->createInputModelSubject();
		$pInputModelCaptcha = $pFormModelBuilder->createInputModelCaptchaRequired();
		$pFormModelFormSpecific = new FormModel();
		$pFormModelFormSpecific->setPageSlug($this->getPageSlug());
		$pFormModelFormSpecific->setGroupSlug(self::FORM_VIEW_FORM_SPECIFIC);
		$pFormModelFormSpecific->setLabel(__('Form Specific Options', 'onoffice-for-wp-websites'));
		$pFormModelFormSpecific->addInputModel($pInputModelDefaultRecipient);
		$pFormModelFormSpecific->addInputModel($pInputModelRecipient);
		$pFormModelFormSpecific->addInputModel($pInputModelSubject);
		$pFormModelFormSpecific->addInputModel($pInputModelCaptcha);

		if ($this->_showCreateAddress) {
			$pInputModel = $pInputModelBuilder->build(InputModelDBFactoryConfigForm::INPUT_FORM_CREATEADDRESS);
			if (empty($pInputModelBuilder->getValues())) {
				$pInputModel->setValue(true);
			}
			$linkLabel = esc_html__('Request Manager', 'onoffice-for-wp-websites');
			$linkUrl = esc_html__('https://de.enterprisehilfe.onoffice.com/category/additional-modules/request-manager/?lang=en', 'onoffice-for-wp-websites');
			$link = sprintf("<a href='%s' target='_blank' rel='noopener'>%s</a>", $linkUrl, $linkLabel);
			$textWithoutLink = esc_html__("By default, no link is created to the estate when the plugin creates the address. If the contact form is on an detail page and you want to link the address and the requested estate, you can open the email in onOffice enterprise or use the %s.", 'onoffice-for-wp-websites');
			$txtHint = sprintf($textWithoutLink, $link);
			$pInputModel->setHintHtml($txtHint);
			$pFormModelFormSpecific->addInputModel($pInputModel);
		}

		if ($this->_showCreateInterest) {
			$pInputModel = $pInputModelBuilder->build(InputModelDBFactoryConfigForm::INPUT_FORM_CREATEINTEREST);
			if (empty($pInputModelBuilder->getValues())) {
				$pInputModel->setValue(true);
			}
			$pFormModelFormSpecific->addInputModel($pInputModel);
		}

		if ($this->_showCreateOwner) {
			$pInputModel = $pInputModelBuilder->build(InputModelDBFactoryConfigForm::INPUT_FORM_CREATEOWNER);
			if (empty($pInputModelBuilder->getValues())) {
				$pInputModel->setValue(true);
			}
			$pFormModelFormSpecific->addInputModel($pInputModel);
		}

		if ($this->_showCheckDuplicates) {
			$pInputModel = $pInputModelBuilder->build(InputModelDBFactoryConfigForm::INPUT_FORM_CHECKDUPLICATES);
			$pInputModel->setHintHtml(esc_html__('The duplicate check identifies existing duplicates of address data records and triggers an e-mail notification with information on manual duplicate management.', 'onoffice-for-wp-websites'));
			$pFormModelFormSpecific->addInputModel($pInputModel);
		}

		if ($this->_showContactTypeSelect) {
			$pInputModelContactType = $pFormModelBuilder->createInputModelContactType();
			$pFormModelFormSpecific->addInputModel($pInputModelContactType);
		}

		if ($this->_showCheckDuplicatesInterestOwner) {
			$pInputModel = $pInputModelBuilder->build(InputModelDBFactoryConfigForm::INPUT_FORM_CHECKDUPLICATES_INTEREST_OWNER);
			$pInputModel->setHintHtml(esc_html__('The duplicate check identifies existing duplicates of address data records and triggers an e-mail notification with information on manual duplicate management.', 'onoffice-for-wp-websites'));
			$pFormModelFormSpecific->addInputModel($pInputModel);
		}

		if ($this->_showNewsletterCheckbox) {
			$pInputModel = $pInputModelBuilder->build(InputModelDBFactoryConfigForm::INPUT_FORM_NEWSLETTER);
			$pFormModelFormSpecific->addInputModel($pInputModel);
		}

		if ($this->_showEstateContextCheckbox) {
			$pInputModel = $pInputModelBuilder->build(InputModelDBFactoryConfigForm::INPUT_FORM_ESTATE_CONTEXT_AS_HEADING);
			$pFormModelFormSpecific->addInputModel($pInputModel);
		}

		$pInputModelWriteActivity = $pFormModelBuilder->createInputModelWriteActivity();
		$pInputModelActionKind = $pFormModelBuilder->createInputModelActionKind();
		$pInputModelActionType= $pFormModelBuilder->createInputModelActionType();
		$pInputModelOriginContact= $pFormModelBuilder->createInputModelOriginContact();
		$pInputModelAdvisoryLevel= $pFormModelBuilder->createInputModelAdvisoryLevel();
		$pInputModelCharacteristic= $pFormModelBuilder->createInputModelCharacteristic();
		$pInputModelRemark= $pFormModelBuilder->createInputModelRemark();
		$pFormModelFormActivitiy  = new FormModel();
		$pFormModelFormActivitiy->setPageSlug($this->getPageSlug());
		$pFormModelFormActivitiy->setGroupSlug(self::FORM_VIEW_FORM_ACTIVITYCONFIG);
		$pFormModelFormActivitiy->setLabel(__('Activities', 'onoffice-for-wp-websites'));
		$pFormModelFormActivitiy->addInputModel($pInputModelWriteActivity);
		$pFormModelFormActivitiy->addInputModel($pInputModelActionKind);
		$pFormModelFormActivitiy->addInputModel($pInputModelActionType);
		$pFormModelFormActivitiy->addInputModel($pInputModelOriginContact);
		if ($this->getType() !== Form::TYPE_INTEREST) {
			$pFormModelFormActivitiy->addInputModel($pInputModelAdvisoryLevel);
		}
		$pFormModelFormActivitiy->addInputModel($pInputModelCharacteristic);
		$pFormModelFormActivitiy->addInputModel($pInputModelRemark);

		$pInputModelEnableCreateTask = $pFormModelBuilder->createInputModelEnableCreateTask();
		$pInputModelTaskResponsibility = $pFormModelBuilder->createInputModelTaskResponsibility();
		$pInputModelTaskProcessor = $pFormModelBuilder->createInputModelTaskProcessor();
		$pInputModelTaskType = $pFormModelBuilder->createInputModelTaskType();
		$pInputModelTaskPriority = $pFormModelBuilder->createInputModelTaskPriority();
		$pInputModelTaskSubject = $pFormModelBuilder->createInputModelTaskSubject();
		$pInputModelTaskDescription = $pFormModelBuilder->createInputModelTaskDescription();
		$pInputModelTaskStatus = $pFormModelBuilder->createInputModelTaskStatus();
		$pFormModelFormTask = new FormModel();
		$pFormModelFormTask->setPageSlug($this->getPageSlug());
		$pFormModelFormTask->setGroupSlug(self::FORM_VIEW_TASKCONFIG);
		$pFormModelFormTask->setLabel(__('Tasks', 'onoffice-for-wp-websites'));
		$pFormModelFormTask->addInputModel($pInputModelEnableCreateTask);
		$pFormModelFormTask->addInputModel($pInputModelTaskResponsibility);
		$pFormModelFormTask->addInputModel($pInputModelTaskProcessor);
		$pFormModelFormTask->addInputModel($pInputModelTaskType);
		$pFormModelFormTask->addInputModel($pInputModelTaskPriority);
		$pFormModelFormTask->addInputModel($pInputModelTaskSubject);
		$pFormModelFormTask->addInputModel($pInputModelTaskDescription);
		$pFormModelFormTask->addInputModel($pInputModelTaskStatus);

		$this->addFormModel($pFormModelFormSpecific);
		$this->addFormModel($pFormModelFormActivitiy);
		$this->buildGeoPositionSettings();
		$this->addFieldConfigurationForMainModules($pFormModelBuilder);
		$this->addFormModel($pFormModelFormTask);

		$this->addSortableFieldsList($this->getSortableFieldModules(), $pFormModelBuilder,
			InputModelBase::HTML_TYPE_COMPLEX_SORTABLE_DETAIL_LIST);
		$this->addSearchFieldForFieldLists($this->getSortableFieldModules(), $pFormModelBuilder);
	}


	/**
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws ApiClientException
	 */
	private function getTypesOfAction(): array
	{
		$pLanguage = $this->getContainer()->get(Language::class);
		$pSDKWrapper = $this->getContainer()->get(SDKWrapper::class);
		$pApiClientAction = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'actionkindtypes');
		$pApiClientAction->setParameters(['lang'=> $pLanguage->getDefault()]);
		$pApiClientAction->addRequestToQueue()->sendRequests();
		$result = $pApiClientAction->getResultRecords();

		if (empty($result)) {
			return [];
		}

		$data = [];

		foreach ($result as $record) {
			$data[$record['elements']['key']] = $record['elements']['types'];
		}

		return $data;
	}

	/**
	 *
	 */

	private function buildGeoPositionSettings()
	{
		if ($this->_showGeoPositionSettings) {
			$pDataFormConfiguration = new DataFormConfiguration;
			$pDataFormConfiguration->setId($this->getListViewId() ?? 0);

			$pFormModelGeoFields = new FormModel();
			$pFormModelGeoFields->setPageSlug($this->getPageSlug());
			$pFormModelGeoFields->setGroupSlug(self::FORM_VIEW_GEOFIELDS);
			$pFormModelGeoFields->setLabel(__('Geo Fields', 'onoffice-for-wp-websites'));
			$pInputModelBuilderGeoRange = new InputModelBuilderGeoRange(onOfficeSDK::MODULE_SEARCHCRITERIA);
			foreach ($pInputModelBuilderGeoRange->build($pDataFormConfiguration) as $pInputModel) {
				$pFormModelGeoFields->addInputModel($pInputModel);
			}

			$this->addFormModel($pFormModelGeoFields);
		}
	}

	/**
	 * @return array
	 */

	public function getEnqueueData(): array
	{
		$returnArray = parent::getEnqueueData();
		$returnArray['action_type'] = $this->getTypesOfAction();
		$returnArray['default_label_choose'] = __('Please choose', 'onoffice-for-wp-websites');

		return $returnArray;
	}

	/**
	 *
	 */

	public function doExtraEnqueues()
	{
		parent::doExtraEnqueues();
		$pluginPath = ONOFFICE_PLUGIN_DIR.'/index.php';
		wp_register_script('onoffice-handle-activity-config', plugins_url('/dist/onoffice-handle-activity-config.min.js', $pluginPath));
		wp_enqueue_script('onoffice-handle-activity-config');
	}

	/**
	 *
	 */

	protected function generateMetaBoxes()
	{
		$pFormFormSpecific = $this->getFormModelByGroupSlug(self::FORM_VIEW_FORM_SPECIFIC);
        if($pFormFormSpecific !== null) {
            $this->createMetaBoxByForm($pFormFormSpecific, 'side');
        }

		if ($this->_showGeoPositionSettings) {
			$pFormGeoPosition = $this->getFormModelByGroupSlug(self::FORM_VIEW_GEOFIELDS);
			if($pFormGeoPosition !== null) {
                $this->createMetaBoxByForm($pFormGeoPosition, 'side');
            }
		}

		$pFormActivities = $this->getFormModelByGroupSlug(self::FORM_VIEW_FORM_ACTIVITYCONFIG);
		if($pFormActivities !== null) {
            $this->createMetaBoxByForm($pFormActivities, 'side');
        }

		parent::generateMetaBoxes();

        $pFormTaskConfig = $this->getFormModelByGroupSlug(self::FORM_VIEW_TASKCONFIG);
        if($pFormTaskConfig !== null) {
            $this->createMetaBoxByForm($pFormTaskConfig, 'normal');
        }
	}


	/**
	 *
	 */

	protected function generateAccordionBoxes()
	{
		parent::generateAccordionBoxes();

		foreach ($this->_additionalCategories as $category) {
			$slug = $this->generateGroupSlugByModuleCategory(null, $category);
			$pFormFieldsConfigCategory = $this->getFormModelByGroupSlug($slug);
			$this->createMetaBoxByForm($pFormFieldsConfigCategory, 'side');
		}
	}



	/** @param bool $showCreateAddress */
	public function setShowCreateAddress(bool $showCreateAddress)
		{ $this->_showCreateAddress = $showCreateAddress; }

	/** @param bool $showCheckDuplicates */
	public function setShowCheckDuplicates(bool $showCheckDuplicates)
		{ $this->_showCheckDuplicates = $showCheckDuplicates; }

	/** @param bool $showCheckDuplicatesInterestOwner */
	public function setShowCheckDuplicatesInterestOwner(bool $showCheckDuplicatesInterestOwner)
		{ $this->_showCheckDuplicatesInterestOwner = $showCheckDuplicatesInterestOwner; }

	/** @param bool $showNewsletterCheckbox */
	public function setShowNewsletterCheckbox(bool $showNewsletterCheckbox)
		{ $this->_showNewsletterCheckbox = $showNewsletterCheckbox; }

	/** @param bool $showGeoPositionSettings */
	public function setShowGeoPositionSettings(bool $showGeoPositionSettings)
		{ $this->_showGeoPositionSettings = $showGeoPositionSettings; }

	/** @param bool $showEstateContextCheckbox */
	public function setShowEstateContextCheckbox(bool $showEstateContextCheckbox)
		{ $this->_showEstateContextCheckbox = $showEstateContextCheckbox; }

    /** @param bool $showContactTypeSelect */
    public function setShowContactTypeSelect(bool $showContactTypeSelect)
		{ $this->_showContactTypeSelect = $showContactTypeSelect; }

	/**
	 * @param bool $showCreateInterest
	 */
	public function setShowCreateInterest( bool $showCreateInterest ) {
		$this->_showCreateInterest = $showCreateInterest;
	}

	/**
	 * @param bool $showCreateOwner
	 */
	public function setShowCreateOwner( bool $showCreateOwner ) {
		$this->_showCreateOwner = $showCreateOwner;
	}
}
