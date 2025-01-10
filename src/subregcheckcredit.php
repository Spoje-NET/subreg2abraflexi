<?php

declare(strict_types=1);

/**
 * This file is part of the Subreg2AbraFlexi package
 *
 * https://github.com/Spoje-NET/subreg2abraflexi/
 *
 * (c) Vítězslav Dvořák <http://spojenet.cz/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SpojeNet;

\define('APP_NAME', 'Subreg Credit Check');

require_once '../vendor/autoload.php';

$options = getopt('o::e::', ['output::environment::']);

\Ease\Shared::init(
    [
        'ABRAFLEXI_URL',
        'ABRAFLEXI_LOGIN',
        'ABRAFLEXI_PASSWORD',
        'ABRAFLEXI_COMPANY',
        'SUBREG_LOCATION',
        'SUBREG_URI',
        'SUBREG_LOGIN',
        'SUBREG_PASSWORD',
    ],
    \array_key_exists('environment', $options) ? $options['environment'] : '../.env',
);

$exitcode = 0;
$customers = new SubregAbraFlexi\Customers();

if (\Ease\Shared::cfg('APP_DEBUG')) {
    $syncer->logBanner();
}

$destination = \array_key_exists('output', $options) ? $options['output'] : \Ease\Shared::cfg('RESULT_FILE', 'php://stdout');

$subreg = new \Subreg\Client();

$report = [];

foreach ($customers->getCustomers() as $flexiCode => $subregId) {
    $customerInfo = $subreg->infoUser($subregId);
    $report[$flexiCode] = [
        'subreg_id' => $subregId,
        'subreg_credit' => $customerInfo['credit'],
    ];
}

$written = file_put_contents($destination, json_encode($report, \Ease\Shared::cfg('DEBUG') ? \JSON_PRETTY_PRINT : 0));
$customers->addStatusMessage(sprintf(_('Saving result to %s'), $destination), $written ? 'success' : 'error');

exit($exitcode);
