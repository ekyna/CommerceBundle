<?php

namespace Ekyna\Bundle\CommerceBundle\Dashboard;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\AbstractWidgetType;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use Ekyna\Component\Commerce\Stat\Entity\OrderStat;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class StatisticsWidget
 * @package Ekyna\Bundle\CommerceBundle\Dashboard
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatWidget extends AbstractWidgetType
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @var string
     */
    protected $orderClass;

    /**
     * @var \Ekyna\Component\Commerce\Stat\Repository\OrderStatRepositoryInterface
     */
    protected $orderStatRepository;


    /**
     * Constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @inheritDoc
     */
    public function render(WidgetInterface $widget, \Twig_Environment $twig)
    {
        $repository = $this->getOrderStatRepository();

        // TODO Cache

        $currentDate = new \DateTime();
        $compareDate = (clone $currentDate)->modify('-1 year');

        $currentDay = $repository->findOneByDay($currentDate);
        $compareDay = $repository->findOneByDay($compareDate);
        $dailyChart = $this->buildDailyChart($currentDate);

        $currentMonth = $repository->findOneByMonth($currentDate);
        $compareMonth = $repository->findOneByMonth($compareDate);
        $monthlyChart = $this->buildMonthlyChart($currentDate);

        $currentYear = $repository->findOneByYear($currentDate);
        $compareYear = $repository->findOneByYear($compareDate);
        $yearlyChart = $this->buildYearlyChart();

        return $twig->render('EkynaCommerceBundle:Admin\Dashboard:widget_stat.html.twig', [
            'current_day'   => $currentDay,
            'compare_day'   => $compareDay,
            'daily_chart'   => $dailyChart,
            'current_month' => $currentMonth,
            'compare_month' => $compareMonth,
            'monthly_chart' => $monthlyChart,
            'current_year'  => $currentYear,
            'compare_year'  => $compareYear,
            'yearly_chart'  => $yearlyChart,
        ]);
    }

    /**
     * Builds the daily revenues chart config.
     *
     * @param \DateTime $date
     *
     * @return array
     */
    private function buildDailyChart(\DateTime $date)
    {
        $repository = $this->getOrderStatRepository();

        $current = $repository->findDayRevenuesByMonth($date);

        $compareDate = (clone $date)->modify('-1 year');
        $compare = $repository->findDayRevenuesByMonth($compareDate);

        $labels = array_map(function ($d) {
            return (new \DateTime($d))->format('j');
        }, array_keys($current));

        return [
            'type' => 'line',
            'data' => [
                'labels'   => $labels,
                'datasets' => [
                    [
                        'label'                => $date->format('M Y'),
                        'borderColor'          => '#00838f',
                        'backgroundColor'      => 'transparent',
                        'pointBackgroundColor' => '#00838f',
                        'pointBorderColor'     => 'transparent',
                        'pointBorderWidth'     => 0,
                        'data'                 => array_values($current),
                    ],
                    [
                        'label'           => $compareDate->format('M Y'),
                        'backgroundColor' => '#ddd',
                        'borderColor'     => 'transparent',
                        'borderWidth'     => 0,
                        'pointRadius'     => 0,
                        'data'            => array_values($compare),
                    ],
                ],
                'options' => [
                    'scales' => [
                        'yAxes' => [
                            [
                                'ticks' => [
                                    'suggestedMin' => 50,
                                    'suggestedMax' => 100,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Builds the monthly revenues chart config.
     *
     * @param \DateTime $date
     *
     * @return array
     */
    private function buildMonthlyChart(\DateTime $date)
    {
        $repository = $this->getOrderStatRepository();

        $current = $repository->findMonthRevenuesByYear($date);

        $compareDate = (clone $date)->modify('-1 year');
        $compare = $repository->findMonthRevenuesByYear($compareDate);

        $labels = array_map(function ($d) {
            return (new \DateTime($d))->format('M');
        }, array_keys($current));

        return [
            'type' => 'bar',
            'data' => [
                'labels'   => $labels,
                'datasets' => [
                    [
                        'label'           => $compareDate->format('Y'),
                        'backgroundColor' => '#ccc',
                        'data'            => array_values($compare),
                    ],
                    [
                        'label'           => $date->format('Y'),
                        'backgroundColor' => '#0277bd',
                        'data'            => array_values($current),
                    ],
                ],
                'options' => [
                    'scales' => [
                        'yAxes' => [
                            [
                                'ticks' => [
                                    'suggestedMin' => 50,
                                    'suggestedMax' => 100,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Builds the yearly revenues chart config.
     *
     * @return array
     */
    private function buildYearlyChart()
    {
        $repository = $this->getOrderStatRepository();

        $data = $repository->findYearRevenues();

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
                'layout' => ['padding' => ['top' => 16]],
                'scales' => [
                    'yAxes' => [
                        [
                            'ticks' => [
                                'suggestedMin' => 50,
                                'suggestedMax' => 100,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver); // TODO: Change the autogenerated stub

        $resolver->setDefaults([
            'frame'    => false,
            'position' => 9999,
            'css_path' => '/bundles/ekynacommerce/css/admin-dashboard.css',
        ]);
    }

    /**
     * Returns the order repository.
     *
     * @return \Ekyna\Component\Commerce\Stat\Repository\OrderStatRepositoryInterface
     */
    protected function getOrderStatRepository()
    {
        if (null !== $this->orderStatRepository) {
            return $this->orderStatRepository;
        }

        return $this->orderStatRepository = $this->registry->getRepository(OrderStat::class);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'commerce_stat';
    }
}