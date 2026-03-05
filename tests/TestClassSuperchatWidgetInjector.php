<?php

declare(strict_types=1);

namespace onOffice\tests;

use onOffice\WPlugin\Superchat\SuperchatWidgetInjector;
use PHPUnit\Framework\TestCase;

class TestClassSuperchatWidgetInjector extends TestCase
{
	public function testSanitizeApplicationKeyRemovesWhitespace(): void
	{
		$this->assertSame('ABCDEF', SuperchatWidgetInjector::sanitizeApplicationKey("  AB CD\nEF\t"));
	}

	public function testSanitizeApplicationKeyKeepsValidKey(): void
	{
		$this->assertSame('WCLXQ1yxm4V9K2NqrPlAKdMDbW', SuperchatWidgetInjector::sanitizeApplicationKey('WCLXQ1yxm4V9K2NqrPlAKdMDbW'));
	}
}
