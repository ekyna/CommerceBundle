<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class NewsletterController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NewsletterController extends AbstractController
{
    public function indexAction(): Response
    {
        $customer = $this->getCustomerOrRedirect();

        return $this->render('@EkynaCommerce/Account/Newsletter/index.html.twig', [
            'customer' => $customer,
        ]);
    }
}
