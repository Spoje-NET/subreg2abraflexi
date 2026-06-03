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
    private string $srcDir;

    protected function setUp(): void
    {
        $this->scriptPath = \dirname(__DIR__).'/src/subreg2abraflexi.php';
        $this->srcDir = \dirname($this->scriptPath);
    }

    public function testScriptFileExists(): void
    {
        $this->assertFileExists($this->scriptPath);
    }

    /**
     * Subreg SOAP unreachable → SoapFault caught → exit 1.
     * Uses the real AbraFlexi demo server so the AbraFlexi side succeeds
     * and only the Subreg connection is the failure point.
     */
    public function testScriptExitsWithOneOnSoapFault(): void
    {
        $env = [
            'ABRAFLEXI_URL' => getenv('ABRAFLEXI_URL') ?: 'https://demo.flexibee.eu:5434',
            'ABRAFLEXI_LOGIN' => getenv('ABRAFLEXI_LOGIN') ?: 'winstrom',
            'ABRAFLEXI_PASSWORD' => getenv('ABRAFLEXI_PASSWORD') ?: 'winstrom',
            'ABRAFLEXI_COMPANY' => getenv('ABRAFLEXI_COMPANY') ?: 'demo_de',
            'SUBREG_LOCATION' => 'https://invalid.soap.example.invalid/cmd.php',
            'SUBREG_URI' => 'https://invalid.soap.example.invalid/soap',
            'SUBREG_LOGIN' => 'test',
            'SUBREG_PASSWORD' => 'test',
            'EASE_LOGGER' => 'void',
        ];

        $exitCode = $this->runScript($env);

        $this->assertNotSame(255, $exitCode, 'Exit code 255 means an uncaught exception — SoapFault must be caught');
        $this->assertContains($exitCode, [1, 2], 'Expected exit 1 (SoapFault) or 2 (Exception) on Subreg connection failure');
    }

    private function runScript(array $env): int
    {
        $envStr = implode(' ', array_map(
            static fn($k, $v) => $k.'='.escapeshellarg((string) $v),
            array_keys($env),
            $env,
        ));

        exec(
            'cd '.escapeshellarg($this->srcDir).' && '.$envStr.' php -f '.escapeshellarg(\basename($this->scriptPath)).' -- -e /dev/null 2>/dev/null',
            $output,
            $exitCode,
        );

        return $exitCode;
    }
}
