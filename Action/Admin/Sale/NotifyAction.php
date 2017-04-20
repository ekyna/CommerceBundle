<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale;

use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\CommerceBundle\Form\Type\Notify\NotifyType;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Notify\NotifyBuilder;
use Ekyna\Component\Commerce\Common\Notify\NotifyQueue;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

/**
 * Class NotifyAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NotifyAction extends AbstractSaleAction
{
    use HelperTrait;
    use FlashTrait;
    use BreadcrumbTrait;
    use FormTrait;
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
        if (!$sale = $this->getSale()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $notify = $this->notifyBuilder->create(NotificationTypes::MANUAL, $sale);

        $this->notifyBuilder->build($notify);

        $form = $this->getNotifyForm($sale, $notify);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->notifyQueue->enqueue($notify);

            $this->addFlash(t('notify.message.sent', [], 'EkynaCommerce'), 'success');

            return $this->redirect($this->generateResourcePath($sale));
        }

        $this->breadcrumbFromContext($this->context);

        return $this->render($this->options['template'], [
            'context'       => $this->context,
            'form_template' => $this->options['form_template'],
            'form'          => $form->createView(),
        ]);
    }

    protected function getNotifyForm(SaleInterface $sale, Notify $notification): FormInterface
    {
        $action = $this->generateResourcePath($sale, self::class);

        $form = $this->createForm(NotifyType::class, $notification, [
            'source'            => $sale,
            'action'            => $action,
            'attr'              => ['class' => 'form-horizontal'],
            'method'            => 'POST',
            '_redirect_enabled' => true,
        ]);

        if (!$this->request->isXmlHttpRequest()) {
            FormUtil::addFooter($form, [
                'submit_label' => t('button.send', [], 'EkynaUi'),
                'submit_icon'  => 'envelope',
                'cancel_path'  => $this->generateResourcePath($sale),
            ]);
        }

        return $form;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_notify',
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
                'template'      => '@EkynaCommerce/Admin/Common/Sale/notify.html.twig',
                'form_template' => '@EkynaCommerce/Admin/Common/Sale/_form_notify.html.twig',
            ],
        ];
    }
}
