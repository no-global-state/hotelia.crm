<?php

/**
 * This file is part of the Krystal Framework
 * 
 * Copyright (c) No Global State Lab
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

// Make paths relative to the root folder
chdir(dirname(__DIR__));

function d($v) { echo '<pre>', print_r($v, 1), '</pre>'; /*exit;*/ }

require('vendor/autoload.php');
require('environment.php');

// Return prepared application's instance
return \Krystal\Application\KernelFactory::build(require('app.php'));