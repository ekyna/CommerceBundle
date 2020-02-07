<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Stat;

use Ekyna\Component\Commerce\Common\Export\AbstractExporter;
use Ekyna\Component\Commerce\Common\Export\RegionProvider;
use Ekyna\Component\Commerce\Stat\Calculator\StatCalculatorInterface;
use Ekyna\Component\Commerce\Stat\Calculator\StatFilter;

/**
 * Class StatExporter
 * @package Ekyna\Bundle\CommerceBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatExporter extends AbstractExporter
{
    /**
     * @var RegionProvider
     */
    private $regionProvider;

    /**
     * @var StatCalculatorInterface
     */
    private $calculator;


    /**
     * Constructor.
     *
     * @param RegionProvider          $regionProvider
     * @param StatCalculatorInterface $calculator
     */
    public function __construct(RegionProvider $regionProvider, StatCalculatorInterface $calculator)
    {
        parent::__construct();

        $this->regionProvider = $regionProvider;
        $this->calculator     = $calculator;
    }

    /**
     * Exports the order stats.
     *
     * @param \DateTime       $from
     * @param \DateTime       $to
     * @param StatFilter|null $filter
     *
     * @return string
     */
    public function exportByMonths(\DateTime $from, \DateTime $to, StatFilter $filter = null)
    {
        $period = new \DatePeriod(
            (clone $from)->setTime(0, 0, 0, 0),
            new \DateInterval('P1M'),
            (clone $to)->setTime(23, 59, 59, 999999)
        );

        $rows = [
            ['Date', 'Region', 'Revenue', 'Shipping'],
        ];

        $regions = $this->regionProvider->getRegions();

        $filter = $filter ?? new StatFilter();

        /** @var \DateTime $date */
        foreach ($period as $date) {
            foreach ($regions as $region => $countries) {
                $filter->setCountries($countries);

                if (null === $result = $this->calculator->calculateMonthOrderStats($date, $filter)) {
                    $result = $this->calculator->createEmptyResult();
                }

                $rows[] = [
                    $date->format('Y-m'),
                    $region,
                    $result['revenue'] - $result['shipping'],
                    $result['shipping']
                ];
            }
        }

        return $this->createFile($rows, sprintf('orders-stats_%s_%s.csv', $from->format('Y-m'), $to->format('Y-m')));
    }
}
