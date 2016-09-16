<?php

namespace Ekyna\Bundle\CommerceBundle\Helper;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleQuantitiesType;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\View\ViewBuilder;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class SaleHelper
 * @package Ekyna\Bundle\CommerceBundle\Helper
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
     * @param SaleInterface $sale
     * @param array         $options
     *
     * @return \Ekyna\Component\Commerce\Common\View\SaleView
     */
    public function buildView(SaleInterface $sale, array $options = [])
    {
        return $this->viewBuilder->buildSaleView($sale, $options);
    }

    /**
     * Resolves the item's subject.
     *
     * @param SaleItemInterface $item
     *
     * @return mixed|null
     *
     * @see SubjectProviderRegistryInterface
     */
    public function resolveItemSubject(SaleItemInterface $item)
    {
        return $this->subjectProviderRegistry->resolveItemSubject($item);
    }

    /**
     * Creates the items quantities form.
     *
     * @param SaleInterface $sale
     * @param array         $options
     *
     * @return FormInterface
     */
    public function createQuantitiesForm(SaleInterface $sale, array $options = [])
    {
        return $this
            ->formFactory
            ->create(SaleQuantitiesType::class, $sale, $options)
            ->add('submit', Type\SubmitType::class, [
                'label' => 'Recalculer', // TODO translation
            ]);
    }

    /**
     * Finds the item by its item.
     *
     * @param SaleInterface|SaleItemInterface $saleOrItem
     * @param int                             $itemId
     *
     * @return SaleItemInterface|null
     */
    public function findItemById($saleOrItem, $itemId)
    {
        if ($saleOrItem instanceof SaleInterface) {
            foreach ($saleOrItem->getItems() as $item) {
                if ($itemId == $item->getId()) {
                    return $item;
                }
                if (null !== $result = $this->findItemById($item, $itemId)) {
                    return $result;
                }
            }
        } elseif ($saleOrItem instanceof SaleItemInterface) {
            foreach ($saleOrItem->getChildren() as $item) {
                if ($itemId == $item->getId()) {
                    return $item;
                }
                if (null !== $result = $this->findItemById($item, $itemId)) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Removes the item by its id.
     *
     * @param SaleInterface|SaleItemInterface $saleOrItem
     * @param int                             $itemId
     *
     * @return bool
     */
    public function removeItemById($saleOrItem, $itemId)
    {
        if ($saleOrItem instanceof SaleInterface) {
            foreach ($saleOrItem->getItems() as $item) {
                if ($itemId == $item->getId()) {
                    $saleOrItem->removeItem($item);

                    return true;
                }
                if ($this->removeItemById($item, $itemId)) {
                    return true;
                }
            }
        } elseif ($saleOrItem instanceof SaleItemInterface) {
            foreach ($saleOrItem->getChildren() as $item) {
                if ($itemId == $item->getId()) {
                    $saleOrItem->removeChild($item);

                    return true;
                }
                if ($this->removeItemById($item, $itemId)) {
                    return true;
                }
            }
        }

        return false;
    }
}
