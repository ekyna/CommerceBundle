<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale;

use Ekyna\Component\Commerce\Common\Updater\SaleUpdaterInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RecalculateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RecalculateAction extends AbstractSaleAction
{
    use XhrTrait;

    public function __construct(
        private readonly SaleUpdaterInterface $saleUpdater
    ) {
    }

    public function __invoke(): Response
    {
        if (!$this->request->isXmlHttpRequest()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if (!$sale = $this->getSale()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $form = $this->buildQuantitiesForm($sale);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->saleUpdater->recalculate($sale);

            $event = $this->getManager()->update($sale);

            // TODO Some important information to display may have changed (state, etc)

            if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }
        }

        return $this->buildXhrSaleViewResponse($sale, $form);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_recalculate',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_recalculate',
                'path'     => '/recalculate',
                'resource' => true,
                'methods'  => ['POST'],
            ],
            'button'     => [
                'label'        => 'button.recalculate',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'calculator',
            ],
        ];
    }
}
