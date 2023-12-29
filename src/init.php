<?php

namespace SpojeNet;

define('APP_NAME', 'Subreg2AbraFlexi Init');
require_once '../vendor/autoload.php';
\Ease\Shared::init(
    [
            'ABRAFLEXI_URL',
            'ABRAFLEXI_LOGIN',
            'ABRAFLEXI_PASSWORD',
            'ABRAFLEXI_COMPANY',
        ],
    '../.env'
);

$engine = new \AbraFlexi\RW(null, ['evidence' => 'merna-jednotka']);
$engine->logBanner();
if (!$engine->recordExists('code:ROK')) {
    $engine->insertToAbraFlexi(['kod' => 'ROK', 'typMjK' => 'typMj.cas', 'kodTisk' => 'rok', 'kodTiskA' => 'yr', 'nazev' => 'Rok']);
}
