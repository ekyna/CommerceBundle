<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class QuoteController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteController extends AbstractController
{
    /**
     * Order index action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        return $this->render('EkynaCommerceBundle:Account/Quote:index.html.twig');
    }
}
