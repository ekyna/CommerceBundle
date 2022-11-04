<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Order\Attachment;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ControllerInterface;
use Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderAttachmentType;
use Ekyna\Bundle\CommerceBundle\Service\Account\OrderResourceHelper;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Order\Model\OrderAttachmentInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Class CreateController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Order\Attachment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateController implements ControllerInterface
{
    public function __construct(
        private readonly OrderResourceHelper      $resourceHelper,
        private readonly FactoryHelperInterface   $factoryHelper,
        private readonly UrlGeneratorInterface    $urlGenerator,
        private readonly FormFactoryInterface     $formFactory,
        private readonly ResourceManagerInterface $attachmentManager,
        private readonly FlashHelper              $flashHelper,
        private readonly Environment              $twig,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $customer = $this->resourceHelper->getCustomer();

        $order = $this->resourceHelper->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        /** @var OrderAttachmentInterface $attachment */
        $attachment = $this->factoryHelper->createAttachmentForSale($order);
        $attachment->setOrder($order);

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_order_read', [
            'number' => $order->getNumber(),
        ]);

        $form = $this->formFactory->create(OrderAttachmentType::class, $attachment, [
            'action' => $this->urlGenerator->generate('ekyna_commerce_account_order_attachment_create', [
                'number' => $order->getNumber(),
            ]),
        ]);

        FormUtil::addFooter($form, ['cancel_path' => $redirect]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->attachmentManager->create($attachment);

            $this->flashHelper->fromEvent($event);

            if (!$event->hasErrors()) {
                return new RedirectResponse($redirect);
            }
        }

        $orders = $this->resourceHelper->findOrdersByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Order/attachment_create.html.twig', [
            'customer'     => $customer,
            'route_prefix' => 'ekyna_commerce_account_order',
            'order'        => $order,
            'form'         => $form->createView(),
            'orders'       => $orders,
        ]);

        return (new Response($content))->setPrivate();
    }
}
