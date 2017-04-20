<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Bundle\UiBundle\Service\Modal\ModalRenderer;
use Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;

/**
 * Class AbstractTicketController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractTicketController implements ControllerInterface
{
    use CustomerTrait;

    protected FactoryFactoryInterface       $factoryFactory;
    protected RepositoryFactoryInterface    $repositoryFactory;
    protected ManagerFactoryInterface       $managerFactory;
    protected SerializerInterface           $serializer;
    protected AuthorizationCheckerInterface $authorizationChecker;
    protected UrlGeneratorInterface         $urlGenerator;
    protected FormFactoryInterface          $formFactory;
    protected Environment                   $twig;
    protected ModalRenderer                 $modalRenderer;

    public function __construct(
        FactoryFactoryInterface       $factoryFactory,
        RepositoryFactoryInterface    $repositoryFactory,
        ManagerFactoryInterface       $managerFactory,
        SerializerInterface           $serializer,
        AuthorizationCheckerInterface $authorizationChecker,
        UrlGeneratorInterface         $urlGenerator,
        FormFactoryInterface          $formFactory,
        Environment                   $twig,
        ModalRenderer                 $modalRenderer
    ) {
        $this->factoryFactory = $factoryFactory;
        $this->repositoryFactory = $repositoryFactory;
        $this->managerFactory = $managerFactory;
        $this->serializer = $serializer;
        $this->authorizationChecker = $authorizationChecker;
        $this->urlGenerator = $urlGenerator;
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->modalRenderer = $modalRenderer;
    }

    protected function findTicket(Request $request): TicketInterface
    {
        $ticket = $this
            ->repositoryFactory
            ->getRepository(TicketInterface::class)
            ->find($request->attributes->getInt('ticketId'));

        if (!$ticket instanceof TicketInterface) {
            throw new NotFoundHttpException('Ticket not found.');
        }

        $this->checkTicketOwner($ticket);

        return $ticket;
    }

    protected function findMessage(Request $request): TicketMessageInterface
    {
        $message = $this
            ->repositoryFactory
            ->getRepository(TicketMessageInterface::class)
            ->find($request->attributes->getInt('ticketMessageId'));

        if (!$message instanceof TicketMessageInterface) {
            throw new NotFoundHttpException('Ticket message not found.');
        }

        /** @var TicketInterface $ticket */
        $ticket = $message->getTicket();
        if ($request->attributes->getInt('ticketId') !== $ticket->getId()) {
            throw new NotFoundHttpException('Ticket message not found.');
        }

        $this->checkTicketOwner($ticket);

        return $message;
    }

    protected function findAttachment(Request $request): TicketAttachmentInterface
    {
        /** @var TicketAttachmentInterface $attachment */
        $attachment = $this
            ->repositoryFactory
            ->getRepository(TicketAttachmentInterface::class)
            ->find($request->attributes->getInt('ticketAttachmentId'));

        if (!$attachment instanceof TicketAttachmentInterface) {
            throw new NotFoundHttpException('Ticket attachment not found.');
        }

        $message = $attachment->getMessage();
        if ($request->attributes->getInt('ticketMessageId') !== $message->getId()) {
            throw new NotFoundHttpException('Ticket attachment not found.');
        }

        /** @var TicketInterface $ticket */
        $ticket = $message->getTicket();
        if ($request->attributes->getInt('ticketId') !== $ticket->getId()) {
            throw new NotFoundHttpException('Ticket attachment not found.');
        }

        $this->checkTicketOwner($ticket);

        return $attachment;
    }

    /**
     * Checks that the given ticket belongs to the logged customer.
     */
    protected function checkTicketOwner(TicketInterface $ticket): void
    {
        if ($ticket->getCustomer() === $this->getCustomer()) {
            return;
        }

        throw new NotFoundHttpException('Ticket not found');
    }

    protected function serialize(array $data, array $groups = ['Default']): string
    {
        return $this->serializer->serialize($data, 'json', ['groups' => $groups]);
    }

    /**
     * Throws an exception unless the attribute is granted against the current authentication token and optionally
     * supplied subject.
     *
     * @throws AccessDeniedException
     */
    protected function denyAccessUnlessGranted($attribute, $subject = null, string $message = 'Access Denied.'): void
    {
        if ($this->authorizationChecker->isGranted($attribute, $subject)) {
            return;
        }

        $exception = new AccessDeniedException($message);
        $exception->setAttributes($attribute);
        $exception->setSubject($subject);

        throw $exception;
    }

    protected function createModal(string $title, string $button = null): Modal
    {
        $modal = new Modal($title);

        if ($button === 'confirm') {
            $modal->addButton(Modal::BTN_CONFIRM);
        } else {
            $modal->addButton(Modal::BTN_SUBMIT);
        }

        $modal
            ->addButton(Modal::BTN_CLOSE)
            ->setDomain('EkynaCommerce');

        return $modal;
    }
}
