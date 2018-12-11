<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace onOffice\WPlugin;

use onOffice\WPlugin\Field\DistinctFieldsChecker;

require '../../../../wp-load.php';
//header('Content-Type: application/json');
/**
 *
 */


$pDistinctFieldsChecker = new DistinctFieldsChecker();
$pDistinctFieldsChecker->check();
die;