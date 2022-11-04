<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Address;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ControllerInterface;
use Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteAddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleAddressType;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteViewHelper;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Bundle\UiBundle\Service\Modal\ModalRenderer;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function array_replace;

/**
 * Class UpdateController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Address
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UpdateController implements ControllerInterface
{
    public function __construct(
        private readonly QuoteResourceHelper      $resourceHelper,
        private readonly UrlGeneratorInterface    $urlGenerator,
        private readonly FormFactoryInterface     $formFactory,
        private readonly ResourceManagerInterface $quoteManager,
        private readonly QuoteViewHelper          $viewHelper,
        private readonly ModalRenderer            $modalRenderer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $type = $request->attributes->get('type', 'invoice');

        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not yet implemented.');
        }

        $customer = $this->resourceHelper->getCustomer();

        $quote = $this->resourceHelper->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        if (!$quote->isEditable()) {
            throw new AccessDeniedHttpException('Quote is not editable.');
        }

        $action = $this->urlGenerator->generate('ekyna_commerce_account_quote_address_' . $type, [
            'number' => $quote->getNumber(),
        ]);

        $form = $this->formFactory->create(SaleAddressType::class, $quote, [
            'method'            => 'post',
            'action'            => $action,
            'attr'              => [
                'class' => 'form-horizontal',
            ],
            'address_type'      => QuoteAddressType::class,
            'validation_groups' => ['Address'],
            'delivery'          => $type === 'delivery',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->quoteManager->update($quote);

            if (!$event->hasErrors()) {
                return $this->viewHelper->buildXhrSaleViewResponse($quote);
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $modal = new Modal();
        $modal
            ->setTitle('checkout.button.edit_' . $type)
            ->setDomain('EkynaCommerce')
            ->setForm($form->createView())
            ->addButton(array_replace(Modal::BTN_SUBMIT, [
                'label' => 'button.save',
            ]))
            ->addButton(Modal::BTN_CANCEL);

        return $this->modalRenderer->render($modal);
    }
}
