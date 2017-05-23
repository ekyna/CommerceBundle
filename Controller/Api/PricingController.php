<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Api;

use Ekyna\Component\Commerce\Pricing\Api\PricingApiInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class PricingController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Api
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingController
{
    /**
     * @var PricingApiInterface
     */
    private $api;

    /**
     * @var EngineInterface
     */
    private $templating;


    /**
     * Constructor.
     *
     * @param PricingApiInterface $api
     * @param EngineInterface     $templating
     */
    public function __construct(PricingApiInterface $api, EngineInterface $templating)
    {
        $this->api = $api;
        $this->templating = $templating;
    }

    /**
     * Validate VAT number action.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function validateVatNumberAction(Request $request)
    {
        // TODO authorisation / throttle

        if (!$request->isXmlHttpRequest()) {
            throw $this->$this->createNotFoundException();
        }

        $data = [
            'valid'   => false,
            'content' => null,
        ];

        if (!empty($number = $request->query->get('number'))) {
            if (null !== $result = $this->api->validateVatNumber($number)) {
                $data['valid'] = $result->isValid();
                $data['content'] = $this->templating->render('EkynaCommerceBundle:Admin/Common:vat_details.html.twig', [
                    'details' => $result->getDetails(),
                ]);
            }
        }

        return JsonResponse::create($data);
    }
}
