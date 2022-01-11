<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Service\Menu\MenuBuilder;
use Ekyna\Bundle\CommerceBundle\Service\Map\MapBuilder;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

/**
 * Class MapController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MapController
{
    private MapBuilder $mapBuilder;
    private Environment $twig;
    private CustomerRepositoryInterface $customerRepository;
    private MenuBuilder $menuBuilder;

    public function __construct(
        MapBuilder $mapBuilder,
        Environment $twig,
        CustomerRepositoryInterface $customerRepository,
        MenuBuilder $menuBuilder
    ) {
        $this->mapBuilder = $mapBuilder;
        $this->twig = $twig;
        $this->customerRepository = $customerRepository;
        $this->menuBuilder = $menuBuilder;
    }

    public function index(): Response
    {
        $this
            ->menuBuilder
            ->breadcrumbAppend([
                'name'         => 'customer_map',
                'label'        => 'button.map',
                'route'        => 'admin_ekyna_commerce_map',
                'trans_domain' => 'EkynaUi',
            ]);

        $output = $this->twig->render('@EkynaCommerce/Admin/Map/index.html.twig', [
            'map'  => $this->mapBuilder->buildMap(),
            'form' => $this->mapBuilder->buildForm()->createView(),
        ]);

        return (new Response($output))->setPrivate();
    }

    /**
     * Map markers action.
     */
    public function data(Request $request): Response
    {
        return (new JsonResponse([
            'locations' => $this->mapBuilder->buildLocations($request),
        ]))->setPrivate();
    }

    /**
     * Map marker info action.
     */
    public function info(Request $request): Response
    {
        /** @var CustomerInterface $customer */
        $customer = null;
        if ($customerId = $request->query->getInt('customerId')) {
            $customer = $this->customerRepository->find($customerId);
        }

        if (!$customer) {
            throw new NotFoundHttpException('Customer not found');
        }

        $content = $this->twig->render('@EkynaCommerce/Admin/Map/info.html.twig', [
            'customer' => $customer,
        ]);

        return (new Response($content))->setPrivate();
    }
}
