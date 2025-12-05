<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Manufacture;


use Ekyna\Component\Commerce\Manufacture\Repository\ProductionOrderRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Twig\Environment;

/**
 * Class ManufactureRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Manufacture
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ManufactureRenderer
{
    public function __construct(
        private readonly ProductionOrderRepositoryInterface $productionOrderRepository,
        private readonly Environment $twig,
    ) {

    }

    public function renderSubjectProductionOrders(SubjectInterface $subject): string
    {
        $orders = $this->productionOrderRepository->findNotDoneBySubject($subject);

        return $this->twig->render('@EkynaCommerce/Admin/ProductionOrder/_list.html.twig', [
            'orders' => $orders,
        ]);
    }
}
