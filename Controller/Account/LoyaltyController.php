<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class LoyaltyController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LoyaltyController extends AbstractController
{
    public function indexAction(): Response
    {
        $customer = $this->getCustomerOrRedirect();

        $type = $this->get('ekyna_commerce.coupon.configuration')->getTableType();

        return $this->render('@EkynaCommerce/Account/Loyalty/index.html.twig', [
            'customer' => $customer,
        ]);
    }
}
