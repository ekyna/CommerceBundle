<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Dashboard;

use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\AbstractWidgetType;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use Ekyna\Component\Commerce\Common\Model\SaleSources;
use Ekyna\Component\Commerce\Stat\Entity\OrderStat;
use Ekyna\Component\Commerce\Stat\Repository\OrderStatRepositoryInterface;
use OzdemirBurak\Iris\Color\Hex;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Class StatisticsWidget
 * @package Ekyna\Bundle\CommerceBundle\Dashboard
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatWidget extends AbstractWidgetType
{
    public const NAME = 'commerce_stat';

    protected ManagerRegistry               $registry;
    protected ?OrderStatRepositoryInterface $orderStatRepository = null;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function render(WidgetInterface $widget, Environment $twig): string
    {
        $repository = $this->getOrderStatRepository();

        // TODO Cache

        $currentDate = new DateTime();
        $compareDate = (clone $currentDate)->modify('-1 year');

        $currentDay = $repository->findOneByDay($currentDate);
        $compareDay = $repository->findOneByDay($compareDate);
        $dailyChart = $this->buildDailyChart($currentDate);

        $currentMonth = $repository->findOneByMonth($currentDate);
        $compareMonth = $repository->findOneByMonth($compareDate);
        $monthlyChart = $this->buildMonthlyChart($currentDate);

        $currentYear = $repository->findOneByYear($currentDate);
        $compareYear = $repository->findOneByYear($compareDate);
        $aggregateYear = $repository->findSumByYear($compareDate);
        $yearlyChart = $this->buildYearlyChart();

        /** @noinspection PhpUnhandledExceptionInspection */
        return $twig->render('@EkynaCommerce/Admin/Dashboard/widget_stat.html.twig', [
            'current_day'    => $currentDay,
            'compare_day'    => $compareDay,
            'daily_chart'    => $dailyChart,
            'current_month'  => $currentMonth,
            'compare_month'  => $compareMonth,
            'monthly_chart'  => $monthlyChart,
            'current_year'   => $currentYear,
            'compare_year'   => $compareYear,
            'aggregate_year' => $aggregateYear,
            'yearly_chart'   => $yearlyChart,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'frame'    => false,
            'position' => 9999,
            'css_path' => 'bundles/ekynacommerce/css/admin-dashboard.css',
        ]);
    }

    protected function getOrderStatRepository(): OrderStatRepositoryInterface
    {
        if (null !== $this->orderStatRepository) {
            return $this->orderStatRepository;
        }

        return $this->orderStatRepository = $this->registry->getRepository(OrderStat::class);
    }

    /**
     * Builds the daily revenues chart config.
     */
    private function buildDailyChart(DateTime $currentDate): array
    {
        $repository = $this->getOrderStatRepository();

        $currentRevenues = $repository->findDayRevenuesByMonth($currentDate);

        $compareDate = (clone $currentDate)->modify('-1 year');
        $compareRevenues = $repository->findDayRevenuesByMonth($compareDate);

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
    private function buildMonthlyChart(DateTime $currentDate): array
    {
        $repository = $this->getOrderStatRepository();

        $currentRevenues = $repository->findMonthRevenuesByYear($currentDate, true);

        $compareDate = (clone $currentDate)->modify('-1 year');
        $compareRevenues = $repository->findMonthRevenuesByYear($compareDate, true);

        $labels = array_map(function ($d) {
            return (new DateTime($d))->format('M');
        }, array_keys($currentRevenues));

        $datasets = [];
        $stacks = [
            [
                'color'  => '#0277bd',
                'date'   => $currentDate->format('Y'),
                'stack'  => $currentDate->format('Y-m'),
                'values' => $currentRevenues,
            ],
            [
                'color'  => '#aaa',
                'date'   => $compareDate->format('Y'),
                'stack'  => $compareDate->format('Y-m'),
                'values' => $compareRevenues,
            ],
        ];

        foreach ($stacks as $stack) {
            $hex = new Hex($stack['color']);

            foreach (SaleSources::getSources() as $source) {
                $datasets[] = [
                    'label'           => ucfirst($source) . ' ' . $stack['date'],
                    'stack'           => $stack['stack'],
                    'backgroundColor' => (string)$hex,
                    'data'            => array_values(array_map(function ($data) use ($source) {
                        return $data[$source];
                    }, $stack['values'])),
                ];

                $hex = new Hex($hex->lighten(5));
            }
        }

        return [
            'type'    => 'bar',
            'data'    => [
                'labels'   => $labels,
                'datasets' => $datasets,
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
    private function buildYearlyChart(): array
    {
        $repository = $this->getOrderStatRepository();

        $data = $repository->findYearRevenues();

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

    public static function getName(): string
    {
        return self::NAME;
    }
}
