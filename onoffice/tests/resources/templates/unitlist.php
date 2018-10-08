<?php

use onOffice\tests\EstateListMocker;
$i = 0;
/* @var $pEstates EstateListMocker */
while ($currentEstate = $pEstates->estateIterator()) {
	foreach ($currentEstate as $field => $value) {
		echo $field.': '.(is_array($value) ? implode(', ', $value) : $value)."\n";
	}
	echo "--\n";
}
