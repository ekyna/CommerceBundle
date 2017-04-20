<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Ticket;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\ModalTrait;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction as BaseAction;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\SerializerTrait;
use Ekyna\Bundle\UiBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Commerce\Support\Model\TicketStates;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class AbstractAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Ticket
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAction extends BaseAction implements AdminActionInterface
{
    protected static string $action;
    protected static string $state;

    use FormTrait;
    use HelperTrait;
    use ManagerTrait;
    use ModalTrait;
    use SerializerTrait;

    public function __invoke(): Response
    {
        if (!$this->request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Only XHR is supported');
        }

        if (null === $resource = $this->context->getResource()) {
            throw new NotFoundHttpException('');
        }

        if (!$resource instanceof TicketInterface) {
            throw new UnexpectedTypeException($resource, TicketInterface::class);
        }

        $form = $this->createForm(ConfirmType::class, null, [
            'action'  => $this->generateResourcePath($resource, static::class),
            'method'  => 'POST',
            'attr'    => [
                'class' => 'form-horizontal',
            ],
            'message' => t(sprintf('ticket.message.%s_confirm', static::$action), [], 'EkynaCommerce'),
            'buttons' => false,
        ]);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resource->setState(static::$state);

            $event = $this->getManager()->save($resource);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket' => $resource,
                ];

                $data = $this
                    ->getSerializer()
                    ->normalize($data, 'json', $this->options['serialization']);

                return new JsonResponse($data);
            }

            FormUtil::addErrorsFromResourceEvent($form, $event);
        }

        $modal = new Modal('ticket.header.' . static::$action);
        $modal
            ->setDomain('EkynaCommerce')
            ->setForm($form->createView())
            ->setSize(Modal::SIZE_NORMAL)
            ->addButton(Modal::BTN_CONFIRM)
            ->addButton(Modal::BTN_CLOSE);

        return $this->renderModal($modal);
    }
}
