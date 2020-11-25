<?php

namespace Ekyna\Bundle\CommerceBundle\Dashboard;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\AbstractWidgetType;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use Ekyna\Component\Commerce\Stat\Entity\StockStat;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class StockWidget
 * @package Ekyna\Bundle\CommerceBundle\Dashboard
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockWidget extends AbstractWidgetType
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @var \Ekyna\Component\Commerce\Stat\Repository\StockStatRepositoryInterface
     */
    protected $stockStatRepository;


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
        $current = $this->getStockStatRepository()->findOneByDay();

        return $twig->render('@EkynaCommerce/Admin/Dashboard/widget_stock.html.twig', [
            'current'     => $current,
            'stock_chart' => $this->buildStockChart(),
        ]);
    }

    /**
     * Builds the yearly revenues chart config.
     *
     * @return array
     */
    private function buildStockChart()
    {
        $repository = $this->getStockStatRepository();

        $stats = $repository->findLatest();

        $stats = array_reverse($stats);

        $labels = $inValues = $soldValues = [];
        /** @var StockStat $stat */
        foreach ($stats as $stat) {
            $labels[] = (new \DateTime($stat->getDate()))->format('j M');
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
                'title' => ['display' => false],
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

    /**
     * Returns the order repository.
     *
     * @return \Ekyna\Component\Commerce\Stat\Repository\StockStatRepositoryInterface
     */
    protected function getStockStatRepository()
    {
        if (null !== $this->stockStatRepository) {
            return $this->stockStatRepository;
        }

        return $this->stockStatRepository = $this->registry->getRepository(StockStat::class);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'commerce_stock';
    }
}
