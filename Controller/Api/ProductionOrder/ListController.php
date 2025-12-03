<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Api\ProductionOrder;

use Ekyna\Component\Commerce\Manufacture\Repository\ProductionOrderRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class ListController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Api\ProductionOrder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ListController
{
    public function __construct(
        private readonly ProductionOrderRepositoryInterface $repository,
        private readonly NormalizerInterface                $normalizer,
    ) {
    }

    public function __invoke(): Response
    {
        $orders = $this->repository->findScheduled();

        $tasks = $this->normalizer->normalize($orders, 'json', ['groups' => ['Gantt']]);

        return new JsonResponse(['tasks' => $tasks]);
    }
}
