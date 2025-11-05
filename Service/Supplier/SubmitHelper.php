<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Supplier;

use DateTime;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderSubmit;
use Ekyna\Bundle\CommerceBundle\Service\Mailer\Mailer;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Supplier\Event\SupplierOrderEvents;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
use Ekyna\Component\Commerce\Supplier\Model\SupplierTemplateInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierTemplateRepositoryInterface;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Exception\PdfException;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;

use function Symfony\Component\Translation\t;

/**
 * Class SubmitHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Supplier
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubmitHelper
{
    public function __construct(
        private readonly SupplierTemplateRepositoryInterface $templateRepository,
        private readonly ResourceEventDispatcherInterface    $dispatcher,
        private readonly ResourceManagerInterface            $manager,
        private readonly FlashHelper                         $flashHelper,
        private readonly Mailer                              $mailer,
    ) {
    }

    public function prepare(SupplierOrderInterface $order): SupplierOrderSubmit
    {
        $submit = new SupplierOrderSubmit($order);

        $submit->setEmails([$order->getSupplier()->getEmail()]);

        $template = $this->templateRepository->findDefault();
        if ($template instanceof SupplierTemplateInterface) {
            $submit->setSubject($template->getSubject());
            $submit->setMessage($template->getMessage());
        }

        return $submit;
    }

    public function submit(SupplierOrderSubmit $submit): bool
    {
        $order = $submit->getOrder();

        $event = $this->dispatcher->createResourceEvent($order);
        $this->dispatcher->dispatch($event, SupplierOrderEvents::PRE_SUBMIT);
        $this->flashHelper->fromEvent($event);

        if ($event->isPropagationStopped()) {
            return false;
        }

        $order->setOrderedAt(new DateTime());
        $order->setState(SupplierOrderStates::STATE_ORDERED);

        $event = $this->manager->update($order);
        $this->flashHelper->fromEvent($event);

        if ($event->hasErrors()) {
            return false;
        }

        if ($submit->isSendEmail()) {
            try {
                if ($this->mailer->sendSupplierOrderSubmit($submit)) {
                    $this
                        ->flashHelper
                        ->addFlash(t('supplier_order.message.submit.success', [], 'EkynaCommerce'), 'success');
                } else {
                    $this
                        ->flashHelper
                        ->addFlash(t('supplier_order.message.submit.failure', [], 'EkynaCommerce'), 'danger');
                }
            } catch (PdfException) {
                $this
                    ->flashHelper
                    ->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');
            }
        }

        // TODO Post submit event ?

        return true;
    }
}
