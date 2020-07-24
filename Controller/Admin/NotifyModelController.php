<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\AdminBundle\Controller\Resource\ToggleableTrait;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Ekyna\Component\Commerce\Common\Model\Recipient;
use Ekyna\Component\Commerce\Common\Notify\NotifyBuilder;
use Ekyna\Component\Commerce\Common\Notify\NotifyQueue;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class NotifyModelController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyModelController extends ResourceController
{
    use ToggleableTrait;


    /**
     * Test action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Bundle\CommerceBundle\Entity\NotifyModel $resource */
        $resource = $context->getResource($resourceName);

        $this->isGranted('EDIT', $resource);

        if ($request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('XHR not yet supported');
        }

        $data = [
            'email' => null,
            'order' => null,
        ];
        $emails = $this->get('ekyna_setting.manager')->getParameter('notification.to_emails');
        if (!empty($emails)) {
            $data['email'] = current($emails);
        }

        $cancelPath = $this->generateResourcePath($resource);

        $form = $this
            ->createForm(Type\FormType::class, $data, [
                //'action'            => $action,
                'attr'              => ['class' => 'form-horizontal'],
                'method'            => 'POST',
                '_redirect_enabled' => true,
            ])
            ->add('email', Type\EmailType::class, [
                'label'       => 'ekyna_core.field.email',
                'constraints' => [
                    new Assert\Email(),
                ],
            ])
            ->add('actions', FormActionsType::class, [
                'buttons' => [
                    'send'   => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'primary',
                            'label'        => 'ekyna_core.button.send',
                            'attr'         => ['icon' => 'envelope'],
                        ],
                    ],
                    'cancel' => [
                        'type'    => Type\ButtonType::class,
                        'options' => [
                            'label'        => 'ekyna_core.button.cancel',
                            'button_class' => 'default',
                            'as_link'      => true,
                            'attr'         => [
                                'class' => 'form-cancel-btn',
                                'icon'  => 'remove',
                                'href'  => $cancelPath,
                            ],
                        ],
                    ],
                ],
            ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $notify = new Notify();
            $notify
                ->setTest(true)
                ->setType($resource->getType());

            if ($this->setSource($notify)) {
                if ($this->get(NotifyBuilder::class)->build($notify)) {
                    $notify
                        ->clearRecipients()
                        ->addRecipient(new Recipient($form->getData()['email']));

                    $this
                        ->get(NotifyQueue::class)
                        ->enqueue($notify);

                    $this->addFlash('ekyna_commerce.notify_model.message.test_success', 'success');

                    return $this->redirect($cancelPath);
                } else {
                    $this->addFlash('ekyna_commerce.notify_model.message.test_failure', 'danger');
                }
            }
        }

        $this->appendBreadcrumb(
            sprintf('%s_test', $resourceName),
            'ekyna_commerce.notify_model.button.test'
        );

        return $this->render(
            $this->config->getTemplate('test.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * Sets the source for the test.
     *
     * @param Notify $notify
     *
     * @return bool
     */
    protected function setSource(Notify $notify)
    {
        $source = null;

        switch ($notify->getType()) {
            case NotificationTypes::CART_REMIND:
                $source = $this
                    ->get('ekyna_commerce.cart.repository')
                    ->findOneBy([], ['expiresAt' => 'ASC']);
                break;

            case NotificationTypes::QUOTE_REMIND:
                $source = $this
                    ->get('ekyna_commerce.quote.repository')
                    ->findOneBy([], ['expiresAt' => 'ASC']);
                break;

            case NotificationTypes::PAYMENT_EXPIRED:
                $source = $this
                    ->get('ekyna_commerce.order_payment.repository')
                    ->findOneBy(['state' => PaymentStates::STATE_EXPIRED], ['id' => 'DESC']);
                break;

            case NotificationTypes::PAYMENT_AUTHORIZED:
                $method = $this
                    ->get('ekyna_commerce.payment_method.repository')
                    ->findOneBy(['factoryName' => 'offline'], []);
                if (null !== $method) {
                    $source = $this
                        ->get('ekyna_commerce.order_payment.repository')
                        ->findOneBy(['state' => PaymentStates::STATE_AUTHORIZED, 'method' => $method], ['id' => 'DESC']);
                }
                break;

            case NotificationTypes::PAYMENT_CAPTURED:
                $method = $this
                    ->get('ekyna_commerce.payment_method.repository')
                    ->findOneBy(['factoryName' => 'offline'], []);
                if (null !== $method) {
                    $source = $this
                        ->get('ekyna_commerce.order_payment.repository')
                        ->findOneBy(['state' => PaymentStates::STATE_CAPTURED, 'method' => $method], ['id' => 'DESC']);
                }
                break;

            case NotificationTypes::PAYMENT_PAYEDOUT:
                $method = $this
                    ->get('ekyna_commerce.payment_method.repository')
                    ->findOneBy(['factoryName' => 'offline'], []);
                if (null !== $method) {
                    $source = $this
                        ->get('ekyna_commerce.order_payment.repository')
                        ->findOneBy(['state' => PaymentStates::STATE_PAYEDOUT, 'method' => $method], ['id' => 'DESC']);
                }
                break;

            case NotificationTypes::SHIPMENT_PARTIAL:
                // TODO Partial ...
                $source = $this
                    ->get('ekyna_commerce.order_shipment.repository')
                    ->findOneBy(['return' => false, 'state' => ShipmentStates::STATE_SHIPPED], ['id' => 'DESC']);
                break;

            case NotificationTypes::SHIPMENT_COMPLETE:
                $source = $this
                    ->get('ekyna_commerce.order_shipment.repository')
                    ->findOneBy(['return' => false, 'state' => ShipmentStates::STATE_SHIPPED], ['id' => 'DESC']);
                break;

            case NotificationTypes::RETURN_PENDING:
                $source = $this
                    ->get('ekyna_commerce.order_shipment.repository')
                    ->findOneBy(['return' => true, 'state' => ShipmentStates::STATE_PENDING], ['id' => 'DESC']);
                break;

            case NotificationTypes::RETURN_RECEIVED:
                $source = $this
                    ->get('ekyna_commerce.order_shipment.repository')
                    ->findOneBy(['return' => true, 'state' => ShipmentStates::STATE_SHIPPED], ['id' => 'DESC']);
                break;

            default:
                $source = $this
                    ->get('ekyna_commerce.order.repository')
                    ->findOneBy(['state' => OrderStates::STATE_ACCEPTED], ['id' => 'DESC']);
        }

        if (null !== $source) {
            $notify->setSource($source);

            return true;
        }

        $this->addFlash('ekyna_commerce.notify_model.message.source_failure', 'warning');

        return false;
    }
}
