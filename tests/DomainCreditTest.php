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
use SpojeNet\SubregAbraFlexi\DomainCredit;

class DomainCreditTest extends TestCase
{
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(DomainCredit::class));
    }

    public function testExtendsEaseBrick(): void
    {
        $this->assertTrue(is_subclass_of(DomainCredit::class, \Ease\Brick::class));
    }

    public function testDefaultProductCode(): void
    {
        $reflection = new \ReflectionClass(DomainCredit::class);
        $prop = $reflection->getProperty('productCode');
        $prop->setAccessible(true);

        $credit = $this->getMockBuilder(DomainCredit::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertSame('KREDIT_DOMENA', $prop->getValue($credit));
    }

    public function testDefaultReqExtPrefix(): void
    {
        $reflection = new \ReflectionClass(DomainCredit::class);
        $prop = $reflection->getProperty('reqExtPrefix');
        $prop->setAccessible(true);

        $credit = $this->getMockBuilder(DomainCredit::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertSame('subreg', $prop->getValue($credit));
    }

    public function testProcessInvoiceIsCallable(): void
    {
        $credit = $this->getMockBuilder(DomainCredit::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $invoiceMock = $this->createMock(\AbraFlexi\FakturaVydana::class);

        $credit->processInvoice($invoiceMock);
        $this->addToAssertionCount(1);
    }

    public function testNamePropertyIsNullable(): void
    {
        $reflection = new \ReflectionClass(DomainCredit::class);
        $prop = $reflection->getProperty('name');

        $this->assertTrue($prop->getType()->allowsNull());
    }
}
