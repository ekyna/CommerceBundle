<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Quote;

use Ekyna\Bundle\CommerceBundle\Form\Type\Account\QuoteVoucherType;
use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes as BDocumentTypes;
use Ekyna\Bundle\CommerceBundle\Model\QuoteVoucher;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes as CDocumentTypes;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

use function Symfony\Component\Translation\t;

/**
 * Class VoucherController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Quote
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class VoucherController
{
    public function __construct(

        private readonly QuoteResourceHelper      $resourceHelper,
        private readonly UrlGeneratorInterface    $urlGenerator,
        private readonly FormFactoryInterface     $formFactory,
        private readonly FlashHelper              $flashHelper,
        private readonly FactoryHelperInterface   $factoryHelper,
        private readonly TranslatorInterface      $translator,
        private readonly ResourceManagerInterface $quoteManager,
        private readonly Environment              $twig,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $customer = $this->resourceHelper->getCustomer();

        $quote = $this->resourceHelper->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_quote_read', [
            'number' => $quote->getNumber(),
        ]);

        if ($customer->hasParent()) {
            $this->flashHelper->addFlash(t('account.quote.message.voucher_denied', [], 'EkynaCommerce'), 'warning');

            return new RedirectResponse($redirect);
        }

        // Create voucher attachment if not exists
        if (null === $attachment = $quote->getVoucherAttachment()) {
            $attachment = $this
                ->factoryHelper
                ->createAttachmentForSale($quote);

            $type = CDocumentTypes::TYPE_VOUCHER;

            $attachment
                ->setType(CDocumentTypes::TYPE_VOUCHER)
                ->setTitle(BDocumentTypes::getLabel($type)->trans($this->translator));

            $quote->addAttachment($attachment);
        }

        $voucher = new QuoteVoucher();
        $voucher
            ->setNumber($quote->getVoucherNumber())
            ->setAttachment($attachment);

        $form = $this->formFactory->create(QuoteVoucherType::class, $voucher, [
            'action' => $this->urlGenerator->generate('ekyna_commerce_account_quote_voucher', [
                'number' => $quote->getNumber(),
            ]),
        ]);

        FormUtil::addFooter($form, ['cancel_path' => $redirect]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quote->setVoucherNumber($voucher->getNumber());
            $event = $this->quoteManager->update($quote);

            $this->flashHelper->fromEvent($event);

            if (!$event->hasErrors()) {
                return new RedirectResponse($redirect);
            }
        }

        $quotes = $this->resourceHelper->findQuotesByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Quote/voucher.html.twig', [
            'customer'     => $customer,
            'route_prefix' => 'ekyna_commerce_account_quote',
            'quote'        => $quote,
            'form'         => $form->createView(),
            'quotes'       => $quotes,
        ]);

        return (new Response($content))->setPrivate();
    }
}
