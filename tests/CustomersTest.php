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
use SpojeNet\SubregAbraFlexi\Customers;

class CustomersTest extends TestCase
{
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(Customers::class));
    }

    public function testExtendsAbraFlexiAdresar(): void
    {
        $this->assertTrue(is_subclass_of(Customers::class, \AbraFlexi\Adresar::class));
    }

    public function testGetCustomersReturnsOnlyAddressesWithSubregId(): void
    {
        $customers = $this->getMockBuilder(Customers::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getColumnsFromAbraFlexi', 'setData', 'getExternalID', 'getRecordCode'])
            ->getMock();

        $customers->method('getColumnsFromAbraFlexi')->willReturn([
            ['kod' => 'CUSTOMER1', 'id' => 1],
            ['kod' => 'CUSTOMER2', 'id' => 2],
            ['kod' => 'CUSTOMER3', 'id' => 3],
        ]);

        $customers->method('getExternalID')
            ->willReturnOnConsecutiveCalls('12345', null, '67890');

        $customers->method('getRecordCode')
            ->willReturnOnConsecutiveCalls('code:CUSTOMER1', 'code:CUSTOMER2', 'code:CUSTOMER3');

        $result = $customers->getCustomers();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        foreach ($result as $value) {
            $this->assertIsInt($value);
        }
    }

    public function testGetCustomersReturnsEmptyArrayWhenNoSubregCustomers(): void
    {
        $customers = $this->getMockBuilder(Customers::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getColumnsFromAbraFlexi', 'setData', 'getExternalID', 'getRecordCode'])
            ->getMock();

        $customers->method('getColumnsFromAbraFlexi')->willReturn([
            ['kod' => 'CUSTOMER1', 'id' => 1],
        ]);

        $customers->method('getExternalID')->willReturn(null);

        $result = $customers->getCustomers();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetCustomersCastsSubregIdToInt(): void
    {
        $customers = $this->getMockBuilder(Customers::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getColumnsFromAbraFlexi', 'setData', 'getExternalID', 'getRecordCode'])
            ->getMock();

        $customers->method('getColumnsFromAbraFlexi')->willReturn([
            ['kod' => 'CUSTOMER1', 'id' => 1],
        ]);

        $customers->method('getExternalID')->willReturn('42');
        $customers->method('getRecordCode')->willReturn('code:CUSTOMER1');

        $result = $customers->getCustomers();

        $this->assertSame(42, $result['code:CUSTOMER1']);
    }

    public function testGetCustomersUsesCorrectAbraFlexiDetail(): void
    {
        $customers = $this->getMockBuilder(Customers::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getColumnsFromAbraFlexi', 'setData', 'getExternalID', 'getRecordCode'])
            ->getMock();

        $customers->expects($this->once())
            ->method('getColumnsFromAbraFlexi')
            ->with('summary', ['limit' => 0])
            ->willReturn([]);

        $result = $customers->getCustomers();

        $this->assertSame([], $result);
    }
}
