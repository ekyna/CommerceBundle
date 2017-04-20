<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierOrder;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierOrderSubmitType;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderSubmit;
use Ekyna\Bundle\CommerceBundle\Service\Mailer\Mailer;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\ResourceEventDispatcherTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Supplier\Event\SupplierOrderEvents;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\PdfException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

/**
 * Class SubmitAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierOrder
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubmitAction extends AbstractAction implements AdminActionInterface
{
    use FormTrait;
    use HelperTrait;
    use ResourceEventDispatcherTrait;
    use ManagerTrait;
    use FlashTrait;
    use BreadcrumbTrait;
    use TemplatingTrait;

    private Mailer $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(): Response
    {
        $resource = $this->context->getResource();

        if (!$resource instanceof SupplierOrderInterface) {
            throw new UnexpectedTypeException($resource, SupplierOrderInterface::class);
        }

        $submit = new SupplierOrderSubmit($resource);
        $submit->setEmails([$resource->getSupplier()->getEmail()]);

        $form = $this->createSubmitForm($submit);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->resourceEventDispatcher->createResourceEvent($resource);
            $this->resourceEventDispatcher->dispatch($event, SupplierOrderEvents::PRE_SUBMIT);

            if (!$event->isPropagationStopped()) {
                $resource->setState(SupplierOrderStates::STATE_ORDERED);

                $event = $this->getManager()->update($resource);

                $this->addFlashFromEvent($event);

                if (!$event->hasErrors()) {
                    if ($submit->isSendEmail()) {
                        try {
                            if ($this->mailer->sendSupplierOrderSubmit($submit)) {
                                $this->addFlash(t('supplier_order.message.submit.success', [], 'EkynaCommerce'), 'success');
                            } else {
                                $this->addFlash(t('supplier_order.message.submit.failure', [], 'EkynaCommerce'), 'danger');
                            }
                        } catch (PdfException $e) {
                            $this->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');
                        }
                    }

                    // TODO Post submit event ?

                    return $this->redirect($this->generateResourcePath($this->context->getResource()));
                }
            }
        }

        $this->breadcrumbFromContext($this->context);

        return $this->render($this->options['template'], [
            'context' => $this->context,
            'form'    => $form->createView(),
        ]);
    }

    private function createSubmitForm(SupplierOrderSubmit $data): FormInterface
    {
        $form = $this->createForm(SupplierOrderSubmitType::class, $data, [
            'attr' => [
                'class' => 'form-horizontal',
            ],
        ]);

        FormUtil::addFooter($form, [
            'submit_label' => t('button.send', [], 'EkynaUi'),
            'submit_class' => 'warning',
            'submit_icon'  => 'envelope',
            'cancel_path'  => $this->generateResourcePath($this->context->getResource()),
        ]);

        return $form;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_supplier_order_submit',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_submit',
                'path'     => '/submit',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'supplier_order.button.submit',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'primary',
                'icon'         => 'link',
            ],
            'options'    => [
                'template' => '@EkynaCommerce/Admin/SupplierOrder/submit.html.twig',
            ],
        ];
    }
}
