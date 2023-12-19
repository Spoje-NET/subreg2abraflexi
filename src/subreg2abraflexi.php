<?php

namespace SpojeNet;

define('APP_NAME', 'Subreg2AbraFlexi');
require_once '../vendor/autoload.php';
\Ease\Shared::init(
    [
            'ABRAFLEXI_URL',
            'ABRAFLEXI_LOGIN',
            'ABRAFLEXI_PASSWORD',
            'ABRAFLEXI_COMPANY',
            'SUBREG_LOCATION',
            'SUBREG_URI',
            'SUBREG_LOGIN',
            'SUBREG_PASSWORD'
        ],
    '../.env'
);

$syncer = new SubregAbraFlexi\SubregPricelist();
$syncer->import();
