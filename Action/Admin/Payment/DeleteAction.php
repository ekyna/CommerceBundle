<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Payment;

use Ekyna\Bundle\AdminBundle\Action\DeleteAction as BaseAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\XhrTrait;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\Response;

use function array_replace_recursive;

/**
 * Class DeleteAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Payment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DeleteAction extends BaseAction
{
    use XhrTrait;

    protected function onInit(): ?Response
    {
        $payment = $this->context->getResource();

        if (!$payment instanceof PaymentInterface) {
            throw new UnexpectedTypeException($payment, PaymentInterface::class);
        }

        return null;
    }

    protected function onPostPersist(): ?Response
    {
        $sale = $this->context->getParentResource();

        if (!$sale instanceof SaleInterface) {
            throw new UnexpectedTypeException($sale, SaleInterface::class);
        }

        if ($this->request->isXmlHttpRequest()) {
            return $this->buildXhrSaleViewResponse($sale);
        }

        return $this->redirect($this->generateResourcePath($sale));
    }

    public static function configureAction(): array
    {
        return array_replace_recursive(parent::configureAction(), [
            'name'    => 'commerce_payment_delete',
            'button'  => [
                'label'        => 'payment.button.remove',
                'trans_domain' => 'EkynaCommerce',
            ],
            'options' => [
                'template' => '@EkynaCommerce/Admin/Common/Payment/delete.html.twig',
            ],
        ]);
    }
}
