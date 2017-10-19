<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class InventoryController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InventoryController extends Controller
{
    /**
     * Subject stock action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stockAction(Request $request)
    {
        $content = '';

        try {
            $provider = $this
                ->get('ekyna_commerce.subject.provider_registry')
                ->getProvider($request->attributes->get('provider'));

            $subject = $provider->getRepository()->find($request->attributes->get('identifier'));

            if ($subject instanceof StockSubjectInterface) {
                $content = $this->renderView('EkynaCommerceBundle:Admin/Inventory:subject.html.twig', [
                    'subject' => $subject,
                ]);
            }
        } catch (InvalidArgumentException $e) {
        }

        return new Response($content);
    }
}
