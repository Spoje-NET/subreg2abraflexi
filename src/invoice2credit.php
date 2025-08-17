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

\define('APP_NAME', 'Subreg2AbraFlexi');

require_once '../vendor/autoload.php';
$options = getopt('o::e::i:', ['output::environment::invoice:']);

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
    \array_key_exists('environment', $options) ? $options['environment'] : (\array_key_exists('e', $options) ? $options['e'] : '../.env'),
);
$destination = \array_key_exists('o', $options) ? $options['o'] : (\array_key_exists('output', $options) ? $options['output'] : \Ease\Shared::cfg('RESULT_FILE', 'php://stdout'));
$invoiceCode = \array_key_exists('i', $options) ? $options['i'] : (\array_key_exists('invoice', $options) ? $options['invoice'] : \Ease\Shared::cfg('INVOICE_CODE', ''));
$exitcode = 0;
$creditor = new SubregAbraFlexi\DomainCredit();

if (\Ease\Shared::cfg('APP_DEBUG')) {
    $creditor->logBanner();
}

$report = $creditor->processInvoice( new \AbraFlexi\FakturaVydana( $invoiceCode));

$written = file_put_contents($destination, json_encode($report, \Ease\Shared::cfg('DEBUG') ? \JSON_PRETTY_PRINT : 0));
$creditor->addStatusMessage(sprintf(_('Saving result to %s'), $destination), $written ? 'success' : 'error');

exit($exitcode);
