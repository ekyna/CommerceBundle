<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice;

use Ekyna\Bundle\AdminBundle\Action\DeleteAction as BaseAction;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use function array_replace_recursive;

/**
 * Class DeleteAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DeleteAction extends BaseAction
{
    use ArchiverTrait;

    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    protected function onInit(): ?Response
    {
        $invoice = $this->context->getResource();

        if (!$invoice instanceof InvoiceInterface) {
            throw new UnexpectedTypeException($invoice, InvoiceInterface::class);
        }

        if (!$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            return new Response('You are not allowed to delete this resource.', Response::HTTP_FORBIDDEN);
        }

        return null;
    }

    protected function onPrePersist(): ?Response
    {
        /** @var InvoiceInterface $invoice */
        $invoice = $this->context->getResource();

        if (!$this->archive($invoice)) {
            return $this->redirectToReferer(
                $this->generateResourcePath($invoice->getSale())
            );
        }

        return null;
    }

    public static function configureAction(): array
    {
        return array_replace_recursive(parent::configureAction(), [
            'name'    => 'commerce_invoice_delete',
            'button'  => [
                'label'        => 'invoice.button.remove',
                'trans_domain' => 'EkynaCommerce',
            ],
            'options' => [
                'template'      => '@EkynaCommerce/Admin/Common/Invoice/delete.html.twig',
                'form_template' => '@EkynaAdmin/Entity/Crud/_form_confirm.html.twig',
            ],
        ]);
    }
}
