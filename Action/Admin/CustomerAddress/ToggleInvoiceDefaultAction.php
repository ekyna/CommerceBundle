<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\CustomerAddress;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ToggleInvoiceDefaultAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\CustomerAddress
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ToggleInvoiceDefaultAction extends AbstractAction implements AdminActionInterface
{
    use HelperTrait;
    use ManagerTrait;
    use FlashTrait;

    public function __invoke(): Response
    {
        $resource = $this->context->getResource();
        if (!$resource instanceof CustomerAddressInterface) {
            throw new UnexpectedTypeException($resource, CustomerAddressInterface::class);
        }

        $customer = $this->context->getParentResource();
        if (!$customer instanceof CustomerInterface) {
            throw new UnexpectedTypeException($customer, CustomerInterface::class);
        }

        $parentHasDefault = $customer->hasParent()
            && $customer->getParent()->getDefaultInvoiceAddress(true);

        if ($resource->isInvoiceDefault() && !$parentHasDefault) {
            return $this->redirect($this->generateResourcePath($customer));
        }

        $resource->setInvoiceDefault(!$resource->isInvoiceDefault());

        $event = $this->persist($resource);

        $this->addFlashFromEvent($event);

        return $this->redirect($this->generateResourcePath($customer));
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_customer_address_toggle_invoice_default',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_invoice_default',
                'path'     => '/invoice-default',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'customer_address.button.invoice_default',
                'trans_domain' => 'EkynaCommerce',
            ],
        ];
    }
}
