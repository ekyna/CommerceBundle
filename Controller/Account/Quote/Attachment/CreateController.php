<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Attachment;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ControllerInterface;
use Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteAttachmentType;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteAttachmentInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Class CreateController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Attachment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateController implements ControllerInterface
{
    public function __construct(
        private readonly QuoteResourceHelper      $resourceHelper,
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

        $quote = $this->resourceHelper->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        /** @var QuoteAttachmentInterface $attachment */
        $attachment = $this->factoryHelper->createAttachmentForSale($quote);
        $attachment->setQuote($quote);

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_quote_read', [
            'number' => $quote->getNumber(),
        ]);

        $form = $this->formFactory->create(QuoteAttachmentType::class, $attachment, [
            'action' => $this->urlGenerator->generate('ekyna_commerce_account_quote_attachment_create', [
                'number' => $quote->getNumber(),
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

        $quotes = $this->resourceHelper->findQuotesByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Quote/attachment_create.html.twig', [
            'customer'     => $customer,
            'route_prefix' => 'ekyna_commerce_account_quote',
            'quote'        => $quote,
            'form'         => $form->createView(),
            'quotes'       => $quotes,
        ]);

        return (new Response($content))->setPrivate();
    }
}
