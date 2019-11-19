<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Menu\MenuBuilder;
use Ekyna\Bundle\CommerceBundle\Repository\CustomerRepository;
use Ekyna\Bundle\CommerceBundle\Service\Map\MapBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class MapController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MapController
{
    /**
     * @var MapBuilder
     */
    private $mapBuilder;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var MenuBuilder
     */
    private $menuBuilder;


    /**
     * Constructor.
     *
     * @param MapBuilder         $mapBuilder
     * @param EngineInterface    $templating
     * @param CustomerRepository $customerRepository
     * @param MenuBuilder        $menuBuilder
     */
    public function __construct(
        MapBuilder $mapBuilder,
        EngineInterface $templating,
        CustomerRepository $customerRepository,
        MenuBuilder $menuBuilder
    ) {
        $this->mapBuilder = $mapBuilder;
        $this->templating = $templating;
        $this->customerRepository = $customerRepository;
        $this->menuBuilder = $menuBuilder;
    }

    /**
     * Map index action.
     *
     * @return Response
     */
    public function index(): Response
    {
        $this
            ->menuBuilder
            ->breadcrumbAppend(
                'customer_map',
                'ekyna_core.button.map',
                'ekyna_commerce_admin_map'
            );

        $output = $this->templating->render('@EkynaCommerce/Admin/Map/index.html.twig', [
            'map'  => $this->mapBuilder->buildMap(),
            'form' => $this->mapBuilder->buildForm()->createView(),
        ]);

        return new Response($output);
    }

    /**
     * Map markers action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function data(Request $request): Response
    {
        return new JsonResponse([
            'locations' => $this->mapBuilder->buildLocations($request),
        ]);
    }

    /**
     * Map marker info action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function info(Request $request): Response
    {
        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $customer */
        $customer = null;
        if ($customerId = $request->query->get('customerId')) {
            $customer = $this->customerRepository->find($customerId);
        }

        if (!$customer) {
            throw new NotFoundHttpException("Customer not found");
        }

        return new Response($this->templating->render('@EkynaCommerce/Admin/Map/info.html.twig', [
            'customer' => $customer,
        ]));
    }
}
