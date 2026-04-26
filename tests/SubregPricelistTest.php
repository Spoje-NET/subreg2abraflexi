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
use SpojeNet\SubregAbraFlexi\SubregPricelist;

class SubregPricelistTest extends TestCase
{
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(SubregPricelist::class));
    }

    public function testExtendsAbraFlexiCenik(): void
    {
        $this->assertTrue(is_subclass_of(SubregPricelist::class, \AbraFlexi\Cenik::class));
    }

    public function testSaveDomainCommonPriceUsesRegisterPrice(): void
    {
        $pricelist = $this->getMockBuilder(SubregPricelist::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sync', 'saveDomainPrice', 'dataReset', 'setDataValue', 'getRecordCode'])
            ->getMock();

        $pricelist->method('sync')->willReturn(true);
        $pricelist->method('saveDomainPrice')->willReturn(true);
        $pricelist->method('getRecordCode')->willReturn('code:DOMENAEXAMPLE');

        $captured = [];
        $pricelist->method('setDataValue')
            ->willReturnCallback(static function (string $key, $value) use (&$captured): bool {
                $captured[$key] = $value;

                return true;
            });

        $pricelist->saveDomain('common', [
            'name' => 'example',
            'country' => 'US',
            'continent' => 'NA',
            'prices' => ['register' => '10.00', 'renew' => '12.00'],
        ]);

        $this->assertSame(10.0, $captured['nakupCena']);
        $this->assertSame('DOMENAexample', $captured['kod']);
    }

    public function testSaveDomainRenewAddsItemTypeSuffix(): void
    {
        $pricelist = $this->getMockBuilder(SubregPricelist::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sync', 'saveDomainPrice', 'dataReset', 'setDataValue', 'getRecordCode'])
            ->getMock();

        $pricelist->method('sync')->willReturn(true);
        $pricelist->method('saveDomainPrice')->willReturn(true);
        $pricelist->method('getRecordCode')->willReturn('code:DOMENAEXAMPLE_RENEW');

        $captured = [];
        $pricelist->method('setDataValue')
            ->willReturnCallback(static function (string $key, $value) use (&$captured): bool {
                $captured[$key] = $value;

                return true;
            });

        $pricelist->saveDomain('renew', [
            'name' => 'example',
            'country' => 'US',
            'continent' => 'NA',
            'prices' => ['register' => '10.00', 'renew' => '12.00'],
        ]);

        $this->assertSame(12.0, $captured['nakupCena']);
        $this->assertStringContainsString('_renew', $captured['kod']);
        $this->assertStringContainsString('_renew', $captured['id']);
    }

    public function testSaveDomainRegisterHasNoItemTypeSuffix(): void
    {
        $pricelist = $this->getMockBuilder(SubregPricelist::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sync', 'saveDomainPrice', 'dataReset', 'setDataValue', 'getRecordCode'])
            ->getMock();

        $pricelist->method('sync')->willReturn(true);
        $pricelist->method('saveDomainPrice')->willReturn(true);
        $pricelist->method('getRecordCode')->willReturn('code:DOMENAEXAMPLE');

        $captured = [];
        $pricelist->method('setDataValue')
            ->willReturnCallback(static function (string $key, $value) use (&$captured): bool {
                $captured[$key] = $value;

                return true;
            });

        $pricelist->saveDomain('register', [
            'name' => 'example',
            'country' => 'US',
            'continent' => 'NA',
            'prices' => ['register' => '10.00', 'renew' => '12.00'],
        ]);

        $this->assertSame(10.0, $captured['nakupCena']);
        $this->assertStringNotContainsString('_register', $captured['kod']);
    }

    public function testImportReturnsArrayWithDomainCount(): void
    {
        $pricelist = $this->getMockBuilder(SubregPricelist::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['saveDomain', 'addStatusMessage'])
            ->getMock();

        $subregMock = $this->createMock(\Subreg\Client::class);
        $subregMock->method('pricelist')->willReturn([
            'com' => [
                'country' => 'US',
                'continent' => 'NA',
                'prices' => ['register' => '10.00', 'renew' => '10.00'],
            ],
            'net' => [
                'country' => 'US',
                'continent' => 'NA',
                'prices' => ['register' => '8.00', 'renew' => '9.00'],
            ],
        ]);

        $reflection = new \ReflectionClass(SubregPricelist::class);
        $prop = $reflection->getProperty('subreg');
        $prop->setAccessible(true);
        $prop->setValue($pricelist, $subregMock);

        $pricelist->method('saveDomain')->willReturn(true);
        $pricelist->method('addStatusMessage')->willReturn(true);

        $report = $pricelist->import();

        $this->assertIsArray($report);
        $this->assertSame(2, $report['domains']);
    }

    public function testImportHandlesDomainsWithNoPrices(): void
    {
        $pricelist = $this->getMockBuilder(SubregPricelist::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['saveDomain', 'addStatusMessage'])
            ->getMock();

        $subregMock = $this->createMock(\Subreg\Client::class);
        $subregMock->method('pricelist')->willReturn([
            'broken' => ['country' => 'US', 'continent' => 'NA'],
        ]);

        $reflection = new \ReflectionClass(SubregPricelist::class);
        $prop = $reflection->getProperty('subreg');
        $prop->setAccessible(true);
        $prop->setValue($pricelist, $subregMock);

        $pricelist->expects($this->never())->method('saveDomain');
        $pricelist->method('addStatusMessage')->willReturn(true);

        $report = $pricelist->import();

        $this->assertSame(1, $report['domains']);
    }
}
