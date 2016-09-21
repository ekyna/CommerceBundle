<?php

namespace Ekyna\Bundle\CommerceBundle\Service;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleQuantitiesType;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\View\ViewBuilder;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class SaleHelper
 * @package Ekyna\Bundle\CommerceBundle\Service
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleHelper
{
    /**
     * @var SubjectProviderRegistryInterface
     */
    private $subjectProviderRegistry;

    /**
     * @var ViewBuilder
     */
    private $viewBuilder;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param SubjectProviderRegistryInterface $subjectProviderRegistry
     * @param ViewBuilder                      $viewBuilder
     * @param FormFactoryInterface             $formFactory
     * @param UrlGeneratorInterface            $urlGenerator
     * @param TranslatorInterface              $translator
     */
    public function __construct(
        SubjectProviderRegistryInterface $subjectProviderRegistry,
        ViewBuilder $viewBuilder,
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator
    ) {
        $this->subjectProviderRegistry = $subjectProviderRegistry;
        $this->viewBuilder = $viewBuilder;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }

    /**
     * Returns the view builder.
     *
     * @return ViewBuilder
     */
    public function getViewBuilder()
    {
        return $this->viewBuilder;
    }

    /**
     * Returns the form factory.
     *
     * @return FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * Returns the subject provider registry.
     *
     * @return SubjectProviderRegistryInterface
     */
    public function getSubjectProviderRegistry()
    {
        return $this->subjectProviderRegistry;
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string $route         The name of the route
     * @param mixed  $parameters    An array of parameters
     * @param int    $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     *
     * @see UrlGeneratorInterface
     */
    public function generateUrl($route, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_URL)
    {
        return $this->urlGenerator->generate($route, $parameters, $referenceType);
    }

    /**
     * Translate the given message.
     *
     * @param string $id
     * @param array  $parameters
     * @param null   $domain
     * @param null   $locale
     *
     * @return string
     *
     * @see TranslatorInterface
     */
    public function translate($id, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Builds the sale view.
     *
     * @param Model\SaleInterface $sale
     * @param array               $options
     *
     * @return \Ekyna\Component\Commerce\Common\View\SaleView
     */
    public function buildView(Model\SaleInterface $sale, array $options = [])
    {
        return $this->viewBuilder->buildSaleView($sale, $options);
    }

    /**
     * Resolves the item's subject.
     *
     * @param Model\SaleItemInterface $item
     *
     * @return mixed|null
     *
     * @see SubjectProviderRegistryInterface
     */
    public function resolveItemSubject(Model\SaleItemInterface $item)
    {
        return $this->subjectProviderRegistry->resolveItemSubject($item);
    }

    /**
     * Creates the items quantities form.
     *
     * @param Model\SaleInterface $sale
     * @param array               $options
     *
     * @return FormInterface
     */
    public function createQuantitiesForm(Model\SaleInterface $sale, array $options = [])
    {
        return $this
            ->formFactory
            ->create(SaleQuantitiesType::class, $sale, $options)
            ->add('submit', Type\SubmitType::class, [
                'label' => 'ekyna_commerce.sale.button.recalculate',
            ]);
    }

    /**
     * Finds the item by its id.
     *
     * @param Model\SaleInterface|Model\SaleItemInterface $saleOrItem
     * @param int                                         $itemId
     *
     * @return Model\SaleItemInterface|null
     */
    public function findItemById($saleOrItem, $itemId)
    {
        if ($saleOrItem instanceof Model\SaleInterface) {
            foreach ($saleOrItem->getItems() as $item) {
                if ($itemId == $item->getId()) {
                    return $item;
                }
                if (null !== $result = $this->findItemById($item, $itemId)) {
                    return $result;
                }
            }
        } elseif ($saleOrItem instanceof Model\SaleItemInterface) {
            foreach ($saleOrItem->getChildren() as $item) {
                if ($itemId == $item->getId()) {
                    return $item;
                }
                if (null !== $result = $this->findItemById($item, $itemId)) {
                    return $result;
                }
            }
        } else {
            throw new InvalidArgumentException('Expected sale or sale item.');
        }

        return null;
    }

    /**
     * Finds the sale adjustment by its id.
     *
     * @param Model\SaleInterface $sale
     * @param int                 $adjustmentId
     *
     * @return Model\AdjustmentInterface|null
     */
    public function findSaleAdjustmentById(Model\SaleInterface $sale, $adjustmentId)
    {
        foreach ($sale->getAdjustments() as $adjustment) {
            if ($adjustmentId == $adjustment->getId()) {
                return $adjustment;
            }
        }

        return null;
    }

    /**
     * Finds the sale item adjustment by its id.
     *
     * @param Model\SaleInterface|Model\SaleItemInterface $saleOrItem
     * @param int                                         $adjustmentId
     *
     * @return Model\AdjustmentInterface|null
     */
    public function findSaleItemAdjustmentById($saleOrItem, $adjustmentId)
    {
        if ($saleOrItem instanceof Model\SaleInterface) {
            foreach ($saleOrItem->getItems() as $item) {
                if (null !== $result = $this->findSaleItemAdjustmentById($item, $adjustmentId)) {
                    return $result;
                }
            }
        } elseif ($saleOrItem instanceof Model\SaleItemInterface) {
            foreach ($saleOrItem->getAdjustments() as $adjustment) {
                if ($adjustmentId == $adjustment->getId()) {
                    return $adjustment;
                }
            }
            foreach ($saleOrItem->getChildren() as $item) {
                if (null !== $result = $this->findSaleItemAdjustmentById($item, $adjustmentId)) {
                    return $result;
                }
            }
        } else {
            throw new InvalidArgumentException('Expected sale or sale item.');
        }

        return null;
    }

    /**
     * Removes the item by its id.
     *
     * @param Model\SaleInterface|Model\SaleItemInterface $saleOrItem
     * @param int                                         $itemId
     *
     * @return bool
     */
    public function removeItemById($saleOrItem, $itemId)
    {
        if ($saleOrItem instanceof Model\SaleInterface) {
            foreach ($saleOrItem->getItems() as $item) {
                if ($itemId == $item->getId()) {
                    $saleOrItem->removeItem($item);

                    return true;
                }
                if ((!$item->isImmutable()) && $this->removeItemById($item, $itemId)) {
                    return true;
                }
            }
        } elseif ($saleOrItem instanceof Model\SaleItemInterface) {
            foreach ($saleOrItem->getChildren() as $item) {
                if ($itemId == $item->getId()) {
                    $saleOrItem->removeChild($item);

                    return true;
                }
                if ((!$item->isImmutable()) && $this->removeItemById($item, $itemId)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Removes the sale adjustment by its id.
     *
     * @param Model\SaleInterface $sale
     * @param int                 $adjustmentId
     *
     * @return bool
     */
    public function removeSaleAdjustmentById(Model\SaleInterface $sale, $adjustmentId)
    {
        if (null !== $adjustment = $this->findSaleAdjustmentById($sale, $adjustmentId)) {
            if (!$adjustment->isImmutable()) {
                $sale->removeAdjustment($adjustment);

                return true;
            }
        }

        return false;
    }
}
