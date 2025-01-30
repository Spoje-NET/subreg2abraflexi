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

\define('APP_NAME', 'Subreg User Report');

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
            if ($username) {
                if ($username === $userListData['username']) {
                    $report[] = $subregUserData;
                }
            } else {
                $report[] = $subregUserData;
            }
        }
    }
} catch (\Exception $exc) {
    $subreg = new \Subreg\Client(['autologin' => false, 'uri' => \Ease\Shared::cfg('SUBREG_URI'), 'location' => \Ease\Shared::cfg('SUBREG_LOCATION')]);
    $exitcode = $exc->getCode();
    $report['message'] = $exc->getMessage();
}

$written = file_put_contents($destination, json_encode($report, \Ease\Shared::cfg('DEBUG') ? \JSON_PRETTY_PRINT : 0));
$subreg->addStatusMessage(sprintf(_('Saving result to %s'), $destination), $written ? 'success' : 'error');

exit($exitcode);
