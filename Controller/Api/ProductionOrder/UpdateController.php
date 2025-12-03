<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Api\ProductionOrder;

use Ekyna\Component\Commerce\Manufacture\Entity\ProductionOrder;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Commerce\Manufacture\Repository\ProductionOrderRepositoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UpdateController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Api\ProductionOrder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UpdateController
{
    public function __construct(
        private readonly ProductionOrderRepositoryInterface $repository,
        private readonly SerializerInterface                $serializer,
        private readonly ValidatorInterface                 $validator,
        private readonly ResourceManagerInterface           $manager,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $order = $this->repository->find($request->attributes->getInt('id'));

        if (!$order instanceof ProductionOrderInterface) {
            return new JsonResponse(['action' => 'error'], Response::HTTP_NOT_FOUND);
        }

        $this
            ->serializer
            ->deserialize(
                $request->getContent(),
                ProductionOrder::class, // TODO service parameter
                'json',
                [
                    'groups'                               => ['Gantt'],
                    AbstractNormalizer::OBJECT_TO_POPULATE => $order,
                ],
            );

        $violations = $this->validator->validate($order);
        if ($violations->count() > 0) {
            return new JsonResponse(['action' => 'error'], Response::HTTP_BAD_REQUEST);
        }

        $event = $this->manager->save($order);
        if ($event->hasErrors()) {
            return new JsonResponse(['action' => 'error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['action' => 'success'], Response::HTTP_OK);
    }
}
