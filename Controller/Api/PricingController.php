<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Api;

use Ekyna\Component\Commerce\Pricing\Api\PricingApiInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

/**
 * Class PricingController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Api
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingController
{
    private PricingApiInterface $api;
    private Environment $twig;


    public function __construct(PricingApiInterface $api, Environment $twig)
    {
        $this->api = $api;
        $this->twig = $twig;
    }

    /**
     * Validate VAT number action.
     */
    public function validateVatNumber(Request $request): JsonResponse
    {
        // TODO authorisation / throttle

        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        $data = [
            'valid'   => false,
            'content' => null,
        ];

        if (!empty($number = $request->query->get('number'))) {
            if (null !== $result = $this->api->validateVatNumber($number)) {
                $data['valid'] = $result->isValid();
                $data['content'] = $this->twig->render('@EkynaCommerce/Admin/Common/vat_details.html.twig', [
                    'details' => $result->getDetails(),
                ]);
            }
        }

        return JsonResponse::create($data);
    }
}
