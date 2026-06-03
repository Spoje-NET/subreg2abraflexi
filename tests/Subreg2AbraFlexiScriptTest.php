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

namespace SpojeNet\SubregAbraFlexi\Tests;

use PHPUnit\Framework\TestCase;

class Subreg2AbraFlexiScriptTest extends TestCase
{
    private string $scriptPath;

    protected function setUp(): void
    {
        $this->scriptPath = \dirname(__DIR__).'/src/subreg2abraflexi.php';
    }

    public function testScriptFileExists(): void
    {
        $this->assertFileExists($this->scriptPath);
    }

    public function testScriptExitsWithOneOnSoapFault(): void
    {
        $env = [
            'ABRAFLEXI_URL' => 'https://invalid.example.invalid:5434',
            'ABRAFLEXI_LOGIN' => 'test',
            'ABRAFLEXI_PASSWORD' => 'test',
            'ABRAFLEXI_COMPANY' => 'test',
            'SUBREG_LOCATION' => 'https://invalid.soap.example.invalid/cmd.php',
            'SUBREG_URI' => 'https://invalid.soap.example.invalid/soap',
            'SUBREG_LOGIN' => 'test',
            'SUBREG_PASSWORD' => 'test',
            'EASE_LOGGER' => 'void',
        ];

        $envStr = implode(' ', array_map(
            static fn($k, $v) => $k.'='.escapeshellarg((string) $v),
            array_keys($env),
            $env,
        ));

        $srcDir = \dirname($this->scriptPath);
        exec('cd '.escapeshellarg($srcDir).' && '.$envStr.' php -f '.escapeshellarg(\basename($this->scriptPath)).' -- -e /dev/null 2>/dev/null', $output, $exitCode);

        $this->assertContains($exitCode, [1, 2], 'Script should exit with 1 (SoapFault) or 2 (Exception) on connection failure, not 255');
        $this->assertNotSame(255, $exitCode, 'Exit code 255 means an uncaught exception — SoapFault must be caught');
    }
}
