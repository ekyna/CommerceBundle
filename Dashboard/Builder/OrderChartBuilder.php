<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Dashboard\Builder;

use DateTime;
use Ekyna\Component\Commerce\Stat\Model\StatInterface;
use Ekyna\Component\Commerce\Stat\Repository\StatRepositoryInterface;
use Ekyna\Component\Commerce\Stat\StatHelperInterface;
use Ekyna\Component\Resource\Model\DateRange;

use function array_keys;
use function array_map;
use function array_slice;
use function array_values;

/**
 * Class OrderChartBuilder
 * @package Ekyna\Bundle\CommerceBundle\Dashboard\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderChartBuilder
{
    public function __construct(
        private readonly StatRepositoryInterface $repository,
        private readonly StatHelperInterface     $statHelper,
    ) {
    }

    /**
     * Builds the daily revenues chart config.
     */
    public function buildDailyChart(DateTime $currentDate): array
    {
        $currentRevenues = $this
            ->repository
            ->findRevenues(
                StatInterface::TYPE_DAY,
                new DateRange(
                    (clone $currentDate)->modify('first day of this month'),
                    (clone $currentDate)->modify('last day of this month')
                )
            );

        $compareDate = (clone $currentDate)->modify('-1 year');

        $compareRevenues = $this
            ->repository
            ->findRevenues(
                StatInterface::TYPE_DAY,
                new DateRange(
                    (clone $compareDate)->modify('first day of this month'),
                    (clone $compareDate)->modify('last day of this month')
                )
            );

        $labels = array_map(function ($d) {
            return (new DateTime($d))->format('j');
        }, array_keys($currentRevenues));

        return [
            'type'    => 'line',
            'data'    => [
                'labels'   => $labels,
                'datasets' => [
                    [
                        'label'                => $currentDate->format('M Y'),
                        'borderColor'          => '#00838f',
                        'backgroundColor'      => 'transparent',
                        'pointBackgroundColor' => '#00838f',
                        'pointBorderColor'     => 'transparent',
                        'pointBorderWidth'     => 0,
                        'data'                 => array_values($currentRevenues),
                    ],
                    [
                        'label'           => $compareDate->format('M Y'),
                        'backgroundColor' => '#ddd',
                        'borderColor'     => 'transparent',
                        'borderWidth'     => 0,
                        'pointRadius'     => 0,
                        'data'            => array_values($compareRevenues),
                    ],
                ],
            ],
            'options' => [
                'title'  => ['display' => false],
                'legend' => ['display' => false],
                'layout' => ['padding' => ['top' => 12]],
                'scales' => [
                    'yAxes' => [
                        [
                            'ticks' => [
                                'suggestedMin' => 0,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Builds the monthly revenues chart config.
     */
    public function buildMonthlyChart(DateTime $currentDate): array
    {
        $range = $this->statHelper->getYearRangeForDate($currentDate);

        $currentRevenues = $this
            ->repository
            ->findRevenues(StatInterface::TYPE_MONTH, $range);

        $year = $this->statHelper->getYearForDate(new DateTime());

        $compareRevenues = $this
            ->repository
            ->findRevenues(
                StatInterface::TYPE_MONTH,
                new DateRange(
                    $range->getStart()->modify('-1 year'),
                    $range->getEnd()->modify('-1 year'),
                )
            );

        $labels = array_map(function ($d) {
            return (new DateTime($d))->format('M');
        }, array_keys($currentRevenues));

        return [
            'type'    => 'bar',
            'data'    => [
                'labels'   => $labels,
                'datasets' => [
                    [
                        'label'           => $year,
                        'backgroundColor' => '#0277bd',
                        'data'            => array_values($currentRevenues),
                    ],
                    [
                        'label'           => (string)($year - 1),
                        'backgroundColor' => '#aaa',
                        'data'            => array_values($compareRevenues),
                    ],
                ],
            ],
            'options' => [
                'title'  => ['display' => false],
                'legend' => ['display' => false],
                'layout' => ['padding' => ['top' => 12]],
                'scales' => [
                    'yAxes' => [
                        [
                            'ticks' => [
                                'suggestedMin' => 0,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Builds the yearly revenues chart config.
     */
    public function buildYearlyChart(): array
    {
        $year = $this->statHelper->getYearForDate(new DateTime());

        $range = new DateRange(
            new DateTime(($year - 7) . '-01-01'),
            new DateTime($year . '-01-01')
        );

        $data = $this->repository->findRevenues(StatInterface::TYPE_YEAR, $range);

        // TODO use Hex()
        $colors = array_slice([
            '#bbdefb',
            '#90caf9',
            '#64b5f6',
            '#42a5f5',
            '#2196f3',
            '#1e88e5',
            '#1976d2',
            '#1565c0',
        ], -count($data));

        return [
            'type'    => 'bar',
            'data'    => [
                'labels'   => array_keys($data),
                'datasets' => [
                    [
                        'backgroundColor' => $colors,
                        'data'            => array_values($data),
                    ],
                ],
            ],
            'options' => [
                'title'  => ['display' => false],
                'legend' => ['display' => false],
                'layout' => ['padding' => ['top' => 12]],
                'scales' => [
                    'yAxes' => [
                        [
                            'ticks' => [
                                'suggestedMin' => 0,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
