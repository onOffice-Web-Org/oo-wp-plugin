<?php

foreach ($pAddressList->getRows() as $escapedValues) {


	foreach ($escapedValues as $field => $value) {
		$fieldLabel = $pAddressList->getFieldLabel($field);
		echo $fieldLabel, ': ', (is_array($value) ? implode(', ', array_filter($value)) : $value), '<br>';
	}
}