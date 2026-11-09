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

namespace SpojeNet\SubregAbraFlexi;

/**
 * Builds reports compliant with https://multiflexi.eu/schema/report.json.
 *
 * Required properties: producer, status, timestamp. Any additional
 * application specific payload is kept as extra top-level properties.
 */
class Report
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_WARNING = 'warning';
    public const STATUS_ERROR = 'error';

    /**
     * @param array<string, array<int, string>>  $artifacts name => list of paths/URLs
     * @param array<string, float|int|string>    $metrics   name => scalar value
     * @param array<string, mixed>               $extra     application specific payload
     *
     * @return array<string, mixed>
     */
    public static function build(
        string $producer,
        string $status,
        string $message = '',
        array $metrics = [],
        array $artifacts = [],
        array $extra = [],
    ): array {
        $report = $extra + [
            'producer' => $producer,
            'status' => $status,
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ];

        // Schema fields always win over application payload with the same key
        $report['producer'] = $producer;
        $report['status'] = $status;
        $report['timestamp'] = (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM);

        if ($message !== '') {
            $report['message'] = $message;
        }

        // "artifacts" is an object; "metrics" must be an object even when empty
        $report['artifacts'] = (object) $artifacts;

        if ($metrics) {
            $report['metrics'] = $metrics;
        }

        return $report;
    }
}
