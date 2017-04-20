<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierOrder;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\CommerceBundle\Form\Type\Notify\NotifyType;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Ekyna\Component\Commerce\Common\Notify\NotifyBuilder;
use Ekyna\Component\Commerce\Common\Notify\NotifyQueue;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

/**
 * Class NotifyAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierOrder
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NotifyAction extends AbstractAction implements AdminActionInterface
{
    use FlashTrait;
    use HelperTrait;
    use FormTrait;
    use BreadcrumbTrait;
    use TemplatingTrait;

    private NotifyBuilder $notifyBuilder;
    private NotifyQueue   $notifyQueue;

    public function __construct(NotifyBuilder $notifyBuilder, NotifyQueue $notifyQueue)
    {
        $this->notifyBuilder = $notifyBuilder;
        $this->notifyQueue = $notifyQueue;
    }

    public function __invoke(): Response
    {
        $resource = $this->context->getResource();

        if (!$resource instanceof SupplierOrderInterface) {
            throw new UnexpectedTypeException($resource, SupplierOrderInterface::class);
        }

        if ($resource->getState() === SupplierOrderStates::STATE_NEW) {
            $this->addFlash('Can\'t notify non submitted order.', 'warning'); // TODO Translation

            return $this->redirect($this->generateResourcePath($resource));
        }

        $notify = $this->notifyBuilder->create(NotificationTypes::MANUAL, $resource);

        $this->notifyBuilder->build($notify);

        $form = $this->createNotifyForm($resource, $notify);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->notifyQueue->enqueue($notify);

            $this->addFlash(t('notify.message.sent', [], 'EkynaCommerce'), 'success');

            return $this->redirect($this->generateResourcePath($resource));
        }

        $this->breadcrumbFromContext($this->context);

        return $this->render($this->options['template'], [
            'context' => $this->context,
            'form'    => $form->createView(),
        ]);
    }

    protected function createNotifyForm(
        SupplierOrderInterface $order,
        Notify                 $notification,
        bool                   $footer = true
    ): FormInterface {
        $action = $this->generateResourcePath($order, static::class);

        $form = $this->createForm(NotifyType::class, $notification, [
            'source'            => $order,
            'action'            => $action,
            'attr'              => ['class' => 'form-horizontal'],
            'method'            => 'POST',
            '_redirect_enabled' => true,
        ]);

        if ($footer) {
            FormUtil::addFooter($form, [
                'submit_label' => t('button.send', [], 'EkynaUi'),
                'submit_icon'  => 'envelope',
                'cancel_path'  => $this->generateResourcePath($order),
            ]);
        }

        return $form;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_supplier_order_notify',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_notify',
                'path'     => '/notify',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'button.notify',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'envelope',
            ],
            'options'    => [
                'template' => '@EkynaCommerce/Admin/SupplierOrder/notify.html.twig',
            ],
        ];
    }
}
