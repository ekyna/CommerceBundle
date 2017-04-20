<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Dashboard;

use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\AbstractWidgetType;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use Ekyna\Component\Commerce\Stat\Entity\StockStat;
use Ekyna\Component\Commerce\Stat\Repository\StockStatRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Class StockWidget
 * @package Ekyna\Bundle\CommerceBundle\Dashboard
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockWidget extends AbstractWidgetType
{
    public const NAME = 'commerce_stock';

    protected ManagerRegistry               $registry;
    protected ?StockStatRepositoryInterface $stockStatRepository = null;


    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function render(WidgetInterface $widget, Environment $twig): string
    {
        $current = $this->getStockStatRepository()->findOneByDay();

        return $twig->render('@EkynaCommerce/Admin/Dashboard/widget_stock.html.twig', [
            'current'     => $current,
            'stock_chart' => $this->buildStockChart(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'frame'      => false,
            'position'   => 9998,
            'class'      => 'commerce-stock',
            'col_md_min' => 4,
            'col_lg_min' => 4,
            'col_md'     => 4,
            'col_lg'     => 4,
            'css_path'   => 'bundles/ekynacommerce/css/admin-dashboard.css',
        ]);
    }

    protected function getStockStatRepository(): StockStatRepositoryInterface
    {
        if (null !== $this->stockStatRepository) {
            return $this->stockStatRepository;
        }

        return $this->stockStatRepository = $this->registry->getRepository(StockStat::class);
    }

    /**
     * Builds the yearly revenues chart config.
     */
    private function buildStockChart(): array
    {
        $repository = $this->getStockStatRepository();

        $stats = $repository->findLatest();

        $stats = array_reverse($stats);

        $labels = $inValues = $soldValues = [];
        /** @var StockStat $stat */
        foreach ($stats as $stat) {
            $labels[] = (new DateTime($stat->getDate()))->format('j M');
            $inValues[] = $stat->getInValue();
            $soldValues[] = $stat->getSoldValue();
        }

        return [
            'type'    => 'line',
            'data'    => [
                'labels'   => $labels,
                'datasets' => [
                    [
                        'label'       => 'Réel',
                        'borderColor' => '#e65100',
                        'borderWidth' => 1,
                        'data'        => $inValues,
                    ],
                    [
                        'label'       => 'Vendu',
                        'borderColor' => '#fb8c00',
                        'borderWidth' => 1,
                        'borderDash'  => [3, 3],
                        'data'        => $soldValues,
                    ],
                ],
            ],
            'options' => [
                'title'  => ['display' => false],
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

    public static function getName(): string
    {
        return self::NAME;
    }
}
