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
use SpojeNet\SubregAbraFlexi\Report;

class ReportTest extends TestCase
{
    public function testRequiredFieldsPresent(): void
    {
        $report = Report::build('app', Report::STATUS_SUCCESS, 'done', ['count' => 3]);

        $this->assertSame('app', $report['producer']);
        $this->assertSame('success', $report['status']);
        $this->assertNotFalse(\DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $report['timestamp']));
        $this->assertSame('done', $report['message']);
        $this->assertSame(['count' => 3], $report['metrics']);
    }

    public function testArtifactsEncodedAsObject(): void
    {
        $this->assertStringContainsString('"artifacts":{}', json_encode(Report::build('app', Report::STATUS_ERROR)));
    }

    public function testPayloadCannotOverrideSchemaFields(): void
    {
        $report = Report::build('app', Report::STATUS_WARNING, '', [], [], ['status' => 'bogus', 'domains' => 2]);

        $this->assertSame('warning', $report['status']);
        $this->assertSame(2, $report['domains']);
    }
}
