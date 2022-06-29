<?php

namespace onOffice\tests;

use onOffice\WPlugin\ArrayContainerEscape;
use onOffice\WPlugin\EstateDetail;
use onOffice\WPlugin\Template;
use RuntimeException;
use SebastianBergmann\Environment\Runtime;

class TestClassTemplate extends \WP_UnitTestCase
{
	/**
	 * @var EstateDetail
	 */
	private $_pEstate;

	/**
	 * @before
	 */
	public function setUpEstateDetail()
	{
		$this->_pEstate = $this->getMockBuilder(EstateDetail::class)
			->setMethods([
				'getEstateUnits',
				'estateIterator',
				'getFieldLabel',
				'getEstateContacts',
				'getMovieEmbedPlayers',
				'getEstatePictures',
				'setEstateId',
				'getEstateMovieLinks',
				'getEstatePictureUrl',
				'getEstatePictureTitle',
				'getDocument',
				'getCurrentEstateId',
				'getSimilarEstates',
			])
			->disableOriginalConstructor()
			->getMock();

		$estateData = [
			'objekttitel' => 'flach begrüntes Grundstück',
			'objektart' => 'Grundstück',
			'objekttyp' => 'Wohnen',
			'vermarktungsart' => 'Kauf',
			'plz' => '52078',
			'ort' => 'Aachen',
			'objektnr_extern' => 'AP001',
			'grundstuecksflaeche' => 'ca. 5.400 m²',
			'kaufpreis' => '80.000,00 €',
			'objektbeschreibung' => 'große Freifläche',
			'lage' => 'Das Grundstück liegt am Waldrand und ist über einen geteerten Feldweg erreichbar.',
			'ausstatt_beschr' => 'teilweise mit einer alten Mauer aus Findlingen umgeben',
			'sonstige_angaben' => 'Vereinbaren sie noch heute einen Besichtigungstermin',
		];

		$pArrayContainerEstateDetail = new ArrayContainerEscape($estateData);

		$this->_pEstate->setEstateId(52);
		$this->_pEstate->method('estateIterator')
			->will($this->onConsecutiveCalls($pArrayContainerEstateDetail, false));
		$this->_pEstate->method('getFieldLabel')->with($this->anything())
			->willReturnCallback(function (string $field): string {
				return 'label-' . $field;
			});
		$this->_pEstate->method('getMovieEmbedPlayers')->willReturn([]);
		$this->_pEstate->method('getEstatePictures')->willReturn([]);

		$this->_pEstate->method('getEstateContacts')->willReturn([]);
	}

	public function testRender_templatesInThemeDir()
	{
		$this->assertNotEmpty(get_stylesheet_directory());
		$themeTemplateDir = get_stylesheet_directory().'/onoffice-theme/';

		if (!is_dir($themeTemplateDir) &&
			!mkdir($themeTemplateDir . '/templates/', 755, true) &&
			!is_dir($themeTemplateDir . '/templates/'))
		{
			throw new RuntimeException(sprintf('Directory "%s" was not created', $themeTemplateDir . '/templates/'));
		}
		copy(__DIR__.'/resources/templates/default_detail.php', $themeTemplateDir.'/templates/default_detail.php');

		$output = (new Template)
			->withEstateList($this->_pEstate)
			->withTemplateName('onoffice-theme/templates/default_detail.php')
			->render();
		$this->assertStringEqualsFile(__DIR__ . '/resources/templates/TestClassTemplate_expected.txt', $output);
	}

	public function testRender_templatesInPersonalizedDir()
	{
		$templatePath = ABSPATH.'/wp-content/plugins/onoffice-personalized/';
		if (!is_dir($templatePath) &&
			!mkdir($templatePath . '/templates/', 755, true) &&
			!is_dir($templatePath . '/templates/'))
		{
			throw new RuntimeException(sprintf('Directory "%s" was not created', $templatePath . '/templates/'));
		}
		copy(__DIR__.'/resources/templates/default_detail.php', $templatePath.'/templates/default_detail.php');
		$output = (new Template)
			->withEstateList($this->_pEstate)
			->withTemplateName('onoffice-personalized/templates/default_detail.php')
			->render();
		$this->assertStringEqualsFile(__DIR__ . '/resources/templates/TestClassTemplate_expected.txt', $output);
	}

	public function testRender_templatesInPluginDir()
	{
		$pluginDirName = basename(ONOFFICE_PLUGIN_DIR);
		$templatePath = ABSPATH.'/wp-content/plugins/'.$pluginDirName;
		if (!is_dir($templatePath) &&
			!mkdir($templatePath . '/templates.dist/estate/', 755, true) &&
			!is_dir($templatePath . '/templates.dist/estate/'))
		{
			throw new RuntimeException(sprintf('Directory "%s" was not created', $templatePath . '/templates.dist/estate/'));
		}
		copy(__DIR__.'/resources/templates/default_detail.php', $templatePath.'/templates.dist/estate/default_detail.php');
		$output = (new Template)
			->withEstateList($this->_pEstate)
			->withTemplateName($pluginDirName.'/templates.dist/estate/default_detail.php')
			->render();
		$this->assertStringEqualsFile(__DIR__ . '/resources/templates/TestClassTemplate_expected.txt', $output);
	}

	public function testRender_invalidDir()
	{
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Invalid template path');
		(new Template)
			->withEstateList($this->_pEstate)
			->withTemplateName('/etc/passwd')
			->render();
	}
}