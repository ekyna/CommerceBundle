<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Attachment;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ControllerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentGenerator;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Document\Util\DocumentUtil;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Exception\PdfException;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function Symfony\Component\Translation\t;

/**
 * Class RefreshController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Document
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RefreshController implements ControllerInterface
{
    public function __construct(
        private readonly QuoteResourceHelper      $resourceHelper,
        private readonly UrlGeneratorInterface    $urlGenerator,
        private readonly ResourceManagerInterface $attachmentManager,
        private readonly FlashHelper              $flashHelper,
        private readonly DocumentGenerator        $documentGenerator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $customer = $this->resourceHelper->getCustomer();

        $quote = $this->resourceHelper->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        if (!$quote->isEditable()) {
            throw new AccessDeniedHttpException('Quote is not editable.');
        }

        $attachment = $this->resourceHelper->findAttachmentByQuoteAndId($quote, $request->attributes->getInt('id'));

        $redirect = new RedirectResponse(
            $this->urlGenerator->generate('ekyna_commerce_account_quote_read', [
                'number' => $quote->getNumber(),
            ])
        );

        $type = $attachment->getType();

        if (!DocumentUtil::isSaleSupportsDocumentType($quote, $type)) {
            return $redirect;
        }

        // Archive the current attachment
        $attachment
            ->setType(null)
            ->setInternal(true)
            ->setTitle('[archived] ' . $attachment->getTitle());

        $event = $this
            ->attachmentManager
            ->save($attachment);

        if ($event->hasErrors()) {
            $this->flashHelper->fromEvent($event);

            return $redirect;
        }

        try {
            $attachment = $this
                ->documentGenerator
                ->generate($quote, $type);
        } catch (InvalidArgumentException) {
            $this->flashHelper->addFlash(t('sale.message.already_exists', [], 'EkynaCommerce'), 'warning');

            return $redirect;
        } catch (PdfException) {
            $this->flashHelper->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');

            return $redirect;
        }

        $event = $this
            ->attachmentManager
            ->save($attachment);

        $this->flashHelper->fromEvent($event);

        return $redirect;
    }
}
