<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale;

use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\AdminBundle\Action\Util\ModalTrait;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleShipmentType;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UpdateShipmentAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UpdateShipmentAction extends AbstractSaleAction
{
    use XhrTrait;
    use FlashTrait;
    use ModalTrait;
    use BreadcrumbTrait;

    public function __invoke(): Response
    {
        if (!$sale = $this->getSale()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $isXhr = $this->request->isXmlHttpRequest();
        $form = $this->createShipmentEditForm($sale);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->getManager()->update($sale);
            if (!$isXhr) {
                $this->addFlashFromEvent($event);
            }

            if (!$event->hasErrors()) {
                if ($isXhr) {
                    return $this->buildXhrSaleViewResponse($sale);
                }

                return $this->redirect($this->generateResourcePath($sale));
            } elseif ($isXhr) {
                // TODO all event messages should be bound to XHR response
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage())); // TODO Translations
                }
            }
        }

        $templateVars = [
            'context'       => $this->context,
            'form_template' => $this->options['form_template'],
        ];

        if ($isXhr) {
            $modal = new Modal('sale.header.shipment.edit');
            $modal
                ->setDomain('EkynaCommerce')
                ->setForm($form->createView())
                ->addButton(array_replace(Modal::BTN_SUBMIT, [
                    'cssClass' => 'btn-warning',
                ]))
                ->addButton(Modal::BTN_CLOSE)
                ->setVars($templateVars);

            return $this->renderModal($modal);
        }

        $this->breadcrumbFromContext($this->context);

        return $this->render($this->options['template'], $templateVars);
    }

    protected function createShipmentEditForm(SaleInterface $sale): FormInterface
    {
        $action = $this->generateResourcePath($sale, self::class);

        $form = $this->createForm(SaleShipmentType::class, $sale, [
            'action'            => $action,
            'attr'              => ['class' => 'form-horizontal'],
            'method'            => 'POST',
            '_redirect_enabled' => true,
        ]);

        if (!$this->request->isXmlHttpRequest()) {
            FormUtil::addFooter($form, [
                'cancel_path' => $this->generateResourcePath($sale),
            ]);
        }

        return $form;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_update_shipment',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_update_shipment',
                'path'     => '/update-shipment',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            /* TODO 'button'     => [
                'label'        => 'button.refresh',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'refresh',
            ],*/
            'options'    => [
                'template'      => '@EkynaCommerce/Admin/Common/Sale/update_shipment.html.twig',
                'form_template' => '@EkynaCommerce/Admin/Common/Sale/_form_update_shipment.html.twig',
            ],
        ];
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined('template')
            ->setAllowedTypes('template', 'string');
    }
}
