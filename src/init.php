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

\define('APP_NAME', 'Subreg2AbraFlexi Init');

require_once '../vendor/autoload.php';
$options = getopt('o::e::', ['output::environment::']);

\Ease\Shared::init(
    [
        'ABRAFLEXI_URL',
        'ABRAFLEXI_LOGIN',
        'ABRAFLEXI_PASSWORD',
        'ABRAFLEXI_COMPANY',
    ],
    \array_key_exists('environment', $options) ? $options['environment'] : (\array_key_exists('e', $options) ? $options['e'] : '../.env'),
);

$engine = new \AbraFlexi\RW(null, ['evidence' => 'merna-jednotka']);

if (\Ease\Shared::cfg('APP_DEBUG')) {
    $engine->logBanner();
}

if (!$engine->recordExists('code:ROK')) {
    $engine->insertToAbraFlexi(['kod' => 'ROK', 'typMjK' => 'typMj.cas', 'kodTisk' => 'rok', 'kodTiskA' => 'yr', 'nazev' => 'Rok']);
}

$engine->setEvidence('adresar');

if (!$engine->recordExists('code:'.\Ease\Shared::cfg('ABRAFLEXI_GRANSY_CODE', 'GRANSY'))) {
    $engine->insertToAbraFlexi(['kod' => \Ease\Shared::cfg('ABRAFLEXI_GRANSY_CODE', 'GRANSY'), 'nazev' => 'Gransy s.r.o.']);
}
