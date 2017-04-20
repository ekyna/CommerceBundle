<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\CustomerAddress;

use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class ChoiceListController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin\CustomerAddress
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ChoiceListController
{
    private AuthorizationCheckerInterface $authorizationChecker;
    private RepositoryFactoryInterface    $repositoryFactory;
    private SerializerInterface           $serializer;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        RepositoryFactoryInterface $repositoryFactory,
        SerializerInterface $serializer
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->repositoryFactory = $repositoryFactory;
        $this->serializer = $serializer;
    }

    public function __invoke(Request $request): Response
    {
        $this->authorizationChecker->isGranted(CustomerAddressInterface::class, 'VIEW');

        /** @var CustomerInterface $customer */
        $customer = $this
            ->repositoryFactory
            ->getRepository(CustomerInterface::class)
            ->find($request->attributes->getInt('customerId'));

        if (!$customer) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        $addresses = $this
            ->repositoryFactory
            ->getRepository(CustomerAddressInterface::class)
            ->findByCustomerAndParents($customer);

        $data = $this
            ->serializer
            ->serialize(['choices' => $addresses], 'json', ['groups' => ['Default']]);

        return new Response($data, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }
}
