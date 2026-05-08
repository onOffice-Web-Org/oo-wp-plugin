<?php

namespace onOffice\WPlugin\Form;

use onOffice\WPlugin\SDKWrapper;

interface NewsletterFormPostConfiguration
{
	public function getSDKWrapper(): SDKWrapper;

	public function getNewsletterAccepted(): bool;
}