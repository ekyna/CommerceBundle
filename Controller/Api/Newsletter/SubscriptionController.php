<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Api\Newsletter;

use Ekyna\Bundle\CommerceBundle\Service\Newsletter\SubscriptionHelper;
use Ekyna\Component\Commerce\Customer\Repository\CustomerRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SubscriptionController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Api\Newsletter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionController
{
    private CustomerRepositoryInterface $customerRepository;
    private SubscriptionHelper $subscriptionHelper;

    public function __construct(CustomerRepositoryInterface $customerRepository, SubscriptionHelper $subscriptionHelper)
    {
        $this->customerRepository = $customerRepository;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    public function subscribe(Request $request): Response
    {
        if (empty($key = $request->attributes->get('key'))) {
            throw new NotFoundHttpException();
        }

        $email = $this->getEmail($request);

        $response = $this->subscriptionHelper->subscribe($key, $email);

        return new JsonResponse($response);
    }

    public function unsubscribe(Request $request): Response
    {
        if (empty($key = $request->attributes->get('key'))) {
            throw new NotFoundHttpException();
        }

        $email = $this->getEmail($request);

        $response = $this->subscriptionHelper->unsubscribe($key, $email);

        return new JsonResponse($response);
    }

    protected function getEmail(Request $request): string
    {
        if ($key = $request->request->get('customer')) {
            if (null === $customer = $this->customerRepository->findOneByKey($key)) {
                throw new NotFoundHttpException();
            }

            return $customer->getEmail();
        }

        if ($email = $request->request->get('email')) {
            return $email;
        }

        throw new NotFoundHttpException();
    }
}
