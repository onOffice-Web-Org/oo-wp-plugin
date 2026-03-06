<?php

declare(strict_types=1);

namespace onOffice\tests;

use PHPUnit\Framework\TestCase;

class TestClassSuperchatWidgetInjector extends TestCase
{
	public function testSuperchatWidgetInjectorExists(): void
	{
		$this->assertTrue(class_exists('onOffice\WPlugin\Superchat\SuperchatWidgetInjector'));
	}
}
