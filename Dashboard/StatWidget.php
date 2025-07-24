<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Dashboard;

use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\AbstractWidgetType;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use Ekyna\Bundle\CommerceBundle\Dashboard\Builder\OrderChartBuilder;
use Ekyna\Bundle\CommerceBundle\Model\Permission;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Stat\Entity\InvoiceStat;
use Ekyna\Component\Commerce\Stat\Entity\OrderStat;
use Ekyna\Component\Commerce\Stat\Model\StatInterface;
use Ekyna\Component\Commerce\Stat\Repository\StatRepositoryInterface;
use Ekyna\Component\Commerce\Stat\StatHelper;
use Ekyna\Component\Resource\Model\DateRange;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

use function current;

/**
 * Class StatisticsWidget
 * @package Ekyna\Bundle\CommerceBundle\Dashboard
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatWidget extends AbstractWidgetType
{
    public const NAME = 'commerce_stat';

    public function __construct(
        protected readonly ManagerRegistry               $registry,
        protected readonly StatHelper                    $helper,
        protected readonly AuthorizationCheckerInterface $authorization,
    ) {
    }

    public function render(WidgetInterface $widget, Environment $twig): string
    {
        $content = '';

        if ($this->authorization->isGranted(Permission::STAT_CHART, OrderInterface::class)) {
            $content .= $this->renderChars($twig, OrderStat::class);
        }

        if ($this->authorization->isGranted(Permission::STAT_CHART, OrderInvoiceInterface::class)) {
            $content .= $this->renderChars($twig, InvoiceStat::class);
        }

        return $content;
    }

    public function renderChars(Environment $twig, string $class): string
    {
        /** @var StatRepositoryInterface $repository */
        $repository = $this->registry->getRepository($class);

        $builder = new OrderChartBuilder($repository, $this->helper);

        // TODO Cache

        $currentDate = new DateTime();
        $compareDate = (clone $currentDate)->modify('-1 year');

        // Day chart data
        $currentDay = $repository->findOneByDay($currentDate);
        $compareDay = $repository->findOneByDay($compareDate);
        $dailyChart = $builder->buildDailyChart($currentDate);

        // Month chart data
        $currentMonth = $repository->findOneByMonth($currentDate);
        $compareMonth = $repository->findOneByMonth($compareDate);
        $monthlyChart = $builder->buildMonthlyChart($currentDate);

        // Year chart data
        $currentYear = $this->helper->getYearForDate($currentDate);
        $currentYear = $repository->findOneByYear($currentYear);

        $compareYear = $this->helper->getYearForDate($compareDate);
        $compareYear = $repository->findOneByYear($compareYear);

        $aggregateYear = $this->buildAggregateYear($class, $currentDate);
        $yearlyChart = $builder->buildYearlyChart();

        $type = match ($class) {
            OrderStat::class => 'order',
            InvoiceStat::class => 'invoice',
        };

        /** @noinspection PhpUnhandledExceptionInspection */
        return $twig->render('@EkynaCommerce/Admin/Dashboard/widget_stat.html.twig', [
            'type'           => $type,
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

    private function buildAggregateYear(string $class, DateTime $currentDate): StatInterface
    {
        $currentRange = $this->helper->getYearRangeForDate($currentDate);
        $currentRange->setEnd($currentDate);

        $compareDate = (clone $currentDate)->modify('-1 year');
        $compareRange = new DateRange(
            $currentRange->getStart()->modify('-1 year'),
            $compareDate
        );

        /** @var StatRepositoryInterface $repository */
        $repository = $this->registry->getRepository($class);

        $data = $repository->findSumByDateRange($compareRange);

        $year = $this->helper->getYearForDate($compareDate);

        /** @var StatInterface $result */
        $result = new $class();
        $result
            ->setDate($year)
            ->setType(StatInterface::TYPE_YEAR)
            ->loadResult(current($data));

        return $result;
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

    public static function getName(): string
    {
        return self::NAME;
    }
}
