<?php

/**
 * Subreg2AbraFlexi - Credit Checker.
 *
 * @author Vítězslav Dvořák <vitezslav.dvorak@spojenet.cz>
 * @copyright  2020-2024 SpojeNet s.r.o.
 */

namespace SpojeNet;

define('APP_NAME', 'Subreg Credit Check');
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

$customers = new SubregAbraFlexi\Customers();

$config = [
    "location" => \Ease\Shared::cfg('SUBREG_LOCATION'),
    "uri" => \Ease\Shared::cfg('SUBREG_URI'),
    "login" => \Ease\Shared::cfg('SUBREG_LOGIN'),
    "password" => \Ease\Shared::cfg('SUBREG_PASSWORD')
];

$subreg = new \Subreg\Client($config);
$subreg->login();

$report = [];
foreach ($customers->getCustomers() as $flexiCode => $subregId) {
    $customerInfo = $subreg->infoUser($subregId);
    $report[$flexiCode] = [
        'subreg_id' => $subregId,
        'subreg_credit' => $customerInfo['credit'],
    ];
}

echo json_encode($report, \Ease\Shared::cfg('DEBUG') ? JSON_PRETTY_PRINT : 0);
