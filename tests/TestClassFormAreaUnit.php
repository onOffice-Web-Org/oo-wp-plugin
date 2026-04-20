<?php

declare (strict_types=1);

namespace onOffice\tests;

use DI\ContainerBuilder;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationContact;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;
use onOffice\WPlugin\Record\RecordManagerFactory;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter\DefaultValueModelToOutputConverter;

class TestClassFormAreaUnit extends \WP_UnitTestCase
{
    private $_pContainer;

    /**
     * @before
     */
    public function prepare()
    {
        $pContainerBuilder = new ContainerBuilder();
        $pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
        $this->_pContainer = $pContainerBuilder->build();

        $pFieldsCollectionBuilder = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $pFieldsCollectionBuilder->method('addFieldsAddressEstate')
            ->will($this->returnCallback(function(FieldsCollection $pFieldsCollection) use ($pFieldsCollectionBuilder) {
                $pFieldWohnflaeche = new Field('wohnflaeche', 'estate', 'Living area');
                $pFieldWohnflaeche->setType(FieldTypes::FIELD_TYPE_FLOAT);
                $pFieldsCollection->addField($pFieldWohnflaeche);

                $pFieldNormal = new Field('street', 'address', 'Street');
                $pFieldNormal->setType(FieldTypes::FIELD_TYPE_TEXT);
                $pFieldsCollection->addField($pFieldNormal);

                $pFieldCalculatedArea = new Field('calculatedArea', 'estate', 'Total Area');
                $pFieldCalculatedArea->setType(FieldTypes::FIELD_TYPE_FLOAT);
                $pFieldsCollection->addField($pFieldCalculatedArea);

                $pFieldGesamtflaecheVerfuegbar = new Field('gesamtflaeche_verfuegbar_qm', 'estate', 'Total available area');
                $pFieldGesamtflaecheVerfuegbar->setType(FieldTypes::FIELD_TYPE_FLOAT);
                $pFieldsCollection->addField($pFieldGesamtflaecheVerfuegbar);

                return $pFieldsCollectionBuilder;
            }));
        
        $this->_pContainer->set(FieldsCollectionBuilderShort::class, $pFieldsCollectionBuilder);

        $pRecordManagerReadForm = $this->getMockBuilder(RecordManagerReadForm::class)->getMock();
        $pRecordManagerFactory = $this->getMockBuilder(RecordManagerFactory::class)->getMock();
        $pRecordManagerFactory->method('create')->willReturn($pRecordManagerReadForm);
        $this->_pContainer->set(RecordManagerFactory::class, $pRecordManagerFactory);

        $pDefaultValueModelToOutputConverter = $this->getMockBuilder(DefaultValueModelToOutputConverter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_pContainer->set(DefaultValueModelToOutputConverter::class, $pDefaultValueModelToOutputConverter);
    }

    public function testGetFieldLabelWithAreaUnitEnabled()
    {
        $pConfig = new DataFormConfigurationContact();
        $pConfig->setDisplayUnitArea(true);
        $pConfig->setInputs(['wohnflaeche' => 'estate', 'street' => 'address', 'calculatedArea' => 'estate', 'gesamtflaeche_verfuegbar_qm' => 'estate']);

        $pDataFormConfigurationFactory = $this->getMockBuilder(DataFormConfigurationFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadByFormName'])
            ->getMock();
        $pDataFormConfigurationFactory->method('loadByFormName')->willReturn($pConfig);
        $this->_pContainer->set(DataFormConfigurationFactory::class, $pDataFormConfigurationFactory);

        $pForm = new Form('testForm', Form::TYPE_CONTACT, $this->_pContainer);

        $this->assertEquals('Living area (m²)', $pForm->getFieldLabel('wohnflaeche'));
        $this->assertEquals('Total Area (m²)', $pForm->getFieldLabel('calculatedArea'));
        $this->assertEquals('Total available area (m²)', $pForm->getFieldLabel('gesamtflaeche_verfuegbar_qm'));
        $this->assertEquals('Street', $pForm->getFieldLabel('street'));
    }

    public function testGetFieldLabelWithAreaUnitDisabled()
    {
        $pConfig = new DataFormConfigurationContact();
        $pConfig->setDisplayUnitArea(false);
        $pConfig->setInputs(['wohnflaeche' => 'estate', 'street' => 'address', 'calculatedArea' => 'estate', 'gesamtflaeche_verfuegbar_qm' => 'estate']);

        $pDataFormConfigurationFactory = $this->getMockBuilder(DataFormConfigurationFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadByFormName'])
            ->getMock();
        $pDataFormConfigurationFactory->method('loadByFormName')->willReturn($pConfig);
        $this->_pContainer->set(DataFormConfigurationFactory::class, $pDataFormConfigurationFactory);

        $pForm = new Form('testForm', Form::TYPE_CONTACT, $this->_pContainer);

        $this->assertEquals('Living area', $pForm->getFieldLabel('wohnflaeche'));
        $this->assertEquals('Total Area', $pForm->getFieldLabel('calculatedArea'));
        $this->assertEquals('Total available area', $pForm->getFieldLabel('gesamtflaeche_verfuegbar_qm'));
        $this->assertEquals('Street', $pForm->getFieldLabel('street'));
    }
}
