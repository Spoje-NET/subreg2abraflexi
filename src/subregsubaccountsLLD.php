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

\define('APP_NAME', 'Subreg User LLD');

require_once '../vendor/autoload.php';

$options = getopt('o::e::u::', ['output::environment::username::']);

\Ease\Shared::init(
    [
        'SUBREG_LOCATION',
        'SUBREG_URI',
        'SUBREG_LOGIN',
        'SUBREG_PASSWORD',
    ],
    \array_key_exists('environment', $options) ? $options['environment'] : (\array_key_exists('e', $options) ? $options['e'] : '../.env'),
);
$destination = \array_key_exists('o', $options) ? $options['o'] : (\array_key_exists('output', $options) ? $options['output'] : \Ease\Shared::cfg('RESULT_FILE', 'php://stdout'));
$username = \array_key_exists('u', $options) ? $options['u'] : (\array_key_exists('username', $options) ? $options['username'] : \Ease\Shared::cfg('USERNAME', ''));
$exitcode = 0;

try {
    $subreg = new \Subreg\Commands();

    if (\Ease\Shared::cfg('APP_DEBUG')) {
        $subreg->logBanner();
    }

    $report = [];

    $userListData = $subreg->Users_List();

    if (\array_key_exists('users', $userListData)) {
        foreach ($userListData['users'] as $subregUserData) {
            $lldLine = [
                '{#SUBREGID}' => $subregUserData['id'],
                '{#USERNAME}' => $subregUserData['username'],
                '{#NAME}' => $subregUserData['name'],
                '{#CREDIT}' => $subregUserData['credit'],
                '{#CURRENCY}' => $subregUserData['currency'],
                '{#BILLING_NAME}' => $subregUserData['billing_name'],
                '{#BILLING_NAME_SIMPLE}' => preg_replace('/[^a-zA-Z0-9\.\s]/', '', \Ease\Functions::rip($subregUserData['billing_name'])),
                '{#BILLING_STREET}' => $subregUserData['billing_street'],
                '{#BILLING_CITY}' => $subregUserData['billing_city'],
                '{#BILLING_PC}' => $subregUserData['billing_pc'],
                '{#BILLING_COUNTRY}' => $subregUserData['billing_country'],
                '{#COMPANY_ID}' => $subregUserData['company_id'],
                '{#COMPANY_VAT}' => $subregUserData['company_vat'],
                '{#EMAIL}' => $subregUserData['email'],
                '{#PHONE}' => $subregUserData['phone'],
                '{#LASTLOGIN}' => $subregUserData['last_login'],
                '{#DOMAINS_COUNT}' => $subregUserData['domains_count'],
            ];

            if ($username) {
                if ($username === $userListData['username']) {
                    $report[] = $lldLine;
                }
            } else {
                $report[] = $lldLine;
            }
        }
    }
} catch (\Exception $exc) {
    $subreg = new \Subreg\Client(['autologin' => false, 'uri' => \Ease\Shared::cfg('SUBREG_URI'), 'location' => \Ease\Shared::cfg('SUBREG_LOCATION')]);
    $exitcode = $exc->getCode();
    $report['message'] = $exc->getMessage();
}

$written = file_put_contents($destination, json_encode($report, \Ease\Shared::cfg('DEBUG') ? \JSON_PRETTY_PRINT | \JSON_INVALID_UTF8_IGNORE : 0));
$subreg->addStatusMessage(sprintf(_('Saving result to %s'), $destination), $written ? 'success' : 'error');

exit($exitcode);
