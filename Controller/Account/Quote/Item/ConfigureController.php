<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Item;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemConfigureType;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteViewHelper;
use Ekyna\Bundle\CommerceBundle\Service\SaleHelper;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Bundle\UiBundle\Service\Modal\ModalRenderer;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function array_replace;

/**
 * Class ConfigureController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Item
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConfigureController extends AbstractItemController
{
    public function __construct(
        protected readonly QuoteResourceHelper    $resourceHelper,
        protected readonly SaleHelper             $saleHelper,
        private readonly UrlGeneratorInterface    $urlGenerator,
        private readonly FormFactoryInterface     $formFactory,
        private readonly ResourceManagerInterface $quoteManager,
        private readonly QuoteViewHelper          $viewHelper,
        private readonly ModalRenderer            $modalRenderer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $quote = $this->findQuote($request);
        $item = $this->findItem($request, $quote);

        if (!$item->isConfigurable()) {
            throw new NotFoundHttpException('Item is not configurable.');
        }
        if ($item->isImmutable()) {
            throw new NotFoundHttpException('Item is immutable.');
        }

        $action = $this->urlGenerator->generate('ekyna_commerce_account_quote_item_configure', [
            'number' => $quote->getNumber(),
            'id'     => $item->getId(),
        ]);

        $form = $this->formFactory->create(SaleItemConfigureType::class, $item, [
            'method' => 'post',
            'action' => $action,
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->quoteManager->update($quote);

            return $this->viewHelper->buildXhrSaleViewResponse($quote);
        }

        $modal = new Modal();
        $modal
            ->setTitle('sale.header.item.configure')
            ->setDomain('EkynaCommerce')
            ->setCondensed(true)
            ->setForm($form->createView())
            ->addButton(array_replace(Modal::BTN_SUBMIT, [
                'label' => 'button.save',
            ]))
            ->addButton(Modal::BTN_CANCEL);

        return $this->modalRenderer->render($modal);
    }
}
