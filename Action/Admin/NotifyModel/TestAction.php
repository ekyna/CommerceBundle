<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\NotifyModel;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\CommerceBundle\Model\NotifyModelInterface;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\RepositoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\SettingBundle\Manager\SettingManagerInterface;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Bundle\UiBundle\Form\Type\FormActionsType;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Ekyna\Component\Commerce\Common\Model\Recipient;
use Ekyna\Component\Commerce\Common\Notify\NotifyBuilder;
use Ekyna\Component\Commerce\Common\Notify\NotifyQueue;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Resource\Action\AbstractAction;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

use function current;
use function Symfony\Component\Translation\t;

/**
 * Class TestAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\NotifyModel
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class TestAction extends AbstractAction implements AdminActionInterface
{
    use RepositoryTrait;
    use FormTrait;
    use HelperTrait;
    use BreadcrumbTrait;
    use TemplatingTrait;
    use FlashTrait;

    private SettingManagerInterface $settingManager;
    private NotifyBuilder           $notifyBuilder;
    private NotifyQueue             $notifyQueue;

    public function __construct(
        SettingManagerInterface $settingManager,
        NotifyBuilder           $notifyBuilder,
        NotifyQueue             $notifyQueue
    ) {
        $this->settingManager = $settingManager;
        $this->notifyBuilder = $notifyBuilder;
        $this->notifyQueue = $notifyQueue;
    }

    public function __invoke(): Response
    {
        if ($this->request->isXmlHttpRequest()) {
            return new Response('XHR not yet supported');
        }

        if (null === $resource = $this->context->getResource()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if (!$resource instanceof NotifyModelInterface) {
            throw new UnexpectedTypeException($resource, NotifyModelInterface::class);
        }

        $redirect = $this->generateResourcePath($resource);

        $form = $this->getForm($redirect);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notify = new Notify();
            $notify
                ->setTest(true)
                ->setType($resource->getType());

            if ($this->setSource($notify)) {
                if ($this->notifyBuilder->build($notify)) {
                    $notify
                        ->clearRecipients()
                        ->addRecipient(new Recipient($form->getData()['email']));

                    $this
                        ->notifyQueue
                        ->enqueue($notify);

                    $this->addFlash(t('notify_model.message.test_success', [], 'EkynaCommerce'), 'success');

                    return new RedirectResponse($redirect);
                } else {
                    $this->addFlash(t('notify_model.message.test_failure', [], 'EkynaCommerce'), 'danger');
                }
            }
        }

        $this->breadcrumbFromContext($this->context);

        return $this->render($this->options['template'], [
            'context' => $this->context,
            'form'    => $form->createView(),
        ]);
    }

    private function getForm(string $cancelPath): FormInterface
    {
        $data = [
            'email' => null,
            'order' => null,
        ];

        $emails = $this
            ->settingManager
            ->getParameter('notification.to_emails');

        if (!empty($emails)) {
            $data['email'] = current($emails);
        }

        return $this
            ->createForm(Type\FormType::class, $data, [
                'attr'              => ['class' => 'form-horizontal'],
                'method'            => 'POST',
                '_redirect_enabled' => true,
            ])
            ->add('email', Type\EmailType::class, [
                'label'       => t('field.email', [], 'EkynaUi'),
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
                            'label'        => t('button.send', [], 'EkynaUi'),
                            'attr'         => ['icon' => 'envelope'],
                        ],
                    ],
                    'cancel' => [
                        'type'    => Type\ButtonType::class,
                        'options' => [
                            'label'        => t('button.cancel', [], 'EkynaUi'),
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
    }

    /**
     * Sets the source for the test.
     */
    protected function setSource(Notify $notify): bool
    {
        $source = null;

        switch ($notify->getType()) {
            case NotificationTypes::CART_REMIND:
                $source = $this
                    ->repositoryFactory
                    ->getRepository('ekyna_commerce.cart')
                    ->findOneBy([], ['expiresAt' => 'ASC']);
                break;

            case NotificationTypes::QUOTE_REMIND:
                $source = $this
                    ->repositoryFactory
                    ->getRepository('ekyna_commerce.quote')
                    ->findOneBy([], ['expiresAt' => 'ASC']);
                break;

            case NotificationTypes::PAYMENT_EXPIRED:
                $source = $this
                    ->repositoryFactory
                    ->getRepository('ekyna_commerce.order_payment')
                    ->findOneBy(['state' => PaymentStates::STATE_EXPIRED], ['id' => 'DESC']);
                break;

            case NotificationTypes::PAYMENT_AUTHORIZED:
                $method = $this
                    ->repositoryFactory
                    ->getRepository('ekyna_commerce.payment_method')
                    ->findOneBy(['factoryName' => 'offline'], []);

                if (null !== $method) {
                    $source = $this
                        ->repositoryFactory
                        ->getRepository('ekyna_commerce.order_payment')
                        ->findOneBy([
                            'state'  => PaymentStates::STATE_AUTHORIZED,
                            'method' => $method,
                        ], ['id' => 'DESC']);
                }

                break;

            case NotificationTypes::PAYMENT_CAPTURED:
                $method = $this
                    ->repositoryFactory
                    ->getRepository('ekyna_commerce.payment_method')
                    ->findOneBy(['factoryName' => 'offline'], []);

                if (null !== $method) {
                    $source = $this
                        ->repositoryFactory
                        ->getRepository('ekyna_commerce.order_payment')
                        ->findOneBy([
                            'state'  => PaymentStates::STATE_CAPTURED,
                            'method' => $method,
                        ], ['id' => 'DESC']);
                }

                break;

            case NotificationTypes::PAYMENT_PAYEDOUT:
                $method = $this
                    ->repositoryFactory
                    ->getRepository('ekyna_commerce.payment_method')
                    ->findOneBy(['factoryName' => 'offline'], []);

                if (null !== $method) {
                    $source = $this
                        ->repositoryFactory
                        ->getRepository('ekyna_commerce.order_payment')
                        ->findOneBy([
                            'state'  => PaymentStates::STATE_PAYEDOUT,
                            'method' => $method,
                        ], ['id' => 'DESC']);
                }

                break;

            case NotificationTypes::SHIPMENT_PARTIAL:
                // TODO Partial ...
                $source = $this
                    ->repositoryFactory
                    ->getRepository('ekyna_commerce.order_shipment')
                    ->findOneBy([
                        'return' => false,
                        'state'  => ShipmentStates::STATE_SHIPPED,
                    ], ['id' => 'DESC']);

                break;

            case NotificationTypes::SHIPMENT_COMPLETE:
                $source = $this
                    ->repositoryFactory
                    ->getRepository('ekyna_commerce.order_shipment')
                    ->findOneBy([
                        'return' => false,
                        'state'  => ShipmentStates::STATE_SHIPPED,
                    ], ['id' => 'DESC']);

                break;

            case NotificationTypes::RETURN_PENDING:
                $source = $this
                    ->repositoryFactory
                    ->getRepository('ekyna_commerce.order_shipment')
                    ->findOneBy([
                        'return' => true,
                        'state'  => ShipmentStates::STATE_PENDING,
                    ], ['id' => 'DESC']);

                break;

            case NotificationTypes::RETURN_RECEIVED:
                $source = $this
                    ->repositoryFactory
                    ->getRepository('ekyna_commerce.order_shipment')
                    ->findOneBy([
                        'return' => true,
                        'state'  => ShipmentStates::STATE_SHIPPED,
                    ], ['id' => 'DESC']);
                break;

            default:
                $source = $this
                    ->repositoryFactory
                    ->getRepository('ekyna_commerce.order')
                    ->findOneBy(['state' => OrderStates::STATE_ACCEPTED], ['id' => 'DESC']);
        }

        if (null !== $source) {
            $notify->setSource($source);

            return true;
        }

        $this->addFlash(t('notify_model.message.source_failure', [], 'EkynaCommerce'), 'warning');

        return false;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'notify_model_test',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_test',
                'path'     => '/test',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'notify_model.button.test',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'primary',
                'icon'         => 'envelope',
            ],
            'options'    => [
                'template' => '@EkynaCommerce/Admin/NotifyModel/test.html.twig',
            ],
        ];
    }
}
