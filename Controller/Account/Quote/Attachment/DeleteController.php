<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Attachment;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ControllerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class DeleteController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Attachment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DeleteController implements ControllerInterface
{
    public function __construct(
        private readonly QuoteResourceHelper      $resourceHelper,
        private readonly UrlGeneratorInterface    $urlGenerator,
        private readonly ResourceManagerInterface $attachmentManager,
        private readonly FlashHelper              $flashHelper,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $customer = $this->resourceHelper->getCustomer();

        $quote = $this->resourceHelper->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        if (!$quote->isEditable()) {
            throw new AccessDeniedHttpException('Quote is not editable.');
        }

        $redirect = new RedirectResponse(
            $this->urlGenerator->generate('ekyna_commerce_account_quote_read', [
                'number' => $quote->getNumber(),
            ])
        );

        $attachment = $this->resourceHelper->findAttachmentByQuoteAndId($quote, $request->attributes->getInt('id'));

        $event = $this->attachmentManager->delete($attachment);

        $this->flashHelper->fromEvent($event);

        return $redirect;
    }
}
