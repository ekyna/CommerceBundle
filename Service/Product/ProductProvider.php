<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Product;

use Ekyna\Bundle\SocialButtonsBundle\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Product\Model\ProductTypes;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Class ProductProvider
 * @package Ekyna\Bundle\CommerceBundle\Service\Product
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductProvider implements SubjectProviderInterface
{
    const NAME = 'product';

    /**
     * @var ItemBuilder
     */
    private $itemBuilder;

    /**
     * @var FormBuilder
     */
    private $formBuilder;


    /**
     * Constructor.
     *
     * @param ItemBuilder $itemBuilder
     * @param FormBuilder $formBuilder
     */
    public function __construct(ItemBuilder $itemBuilder, FormBuilder $formBuilder)
    {
        $this->itemBuilder = $itemBuilder;
        $this->formBuilder = $formBuilder;
    }

    /**
     * @inheritdoc
     */
    public function needChoice(SaleItemInterface $item)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function buildChoiceForm(FormInterface $form)
    {
        $this->formBuilder->buildChoiceForm($form);
    }

    /**
     * @inheritdoc
     */
    public function handleChoiceSubmit(SaleItemInterface $item)
    {
        $subject = $this->getItemSubject($item);

        $data = [
            'provider' => $this->getName(),
            'id'       => $subject->getId(),
        ];

        $item->setSubjectData(array_replace((array)$item->getSubjectData(), $data));

        if (!$this->needConfiguration($item)) {
            $this->itemBuilder->buildItem($item, $subject);
        }
    }

    /**
     * @inheritdoc
     */
    public function needConfiguration(SaleItemInterface $item)
    {
        $subject = $this->getItemSubject($item);

        /** @var \Ekyna\Component\Commerce\Product\Model\ProductInterface $subject */
        return $subject->getType() === ProductTypes::TYPE_CONFIGURABLE;
    }

    /**
     * @inheritdoc
     */
    public function buildConfigurationForm(FormInterface $form, SaleItemInterface $item)
    {
        $subject = $this->getItemSubject($item);

        $this->formBuilder->buildConfigurableForm($form, $subject);
    }

    /**
     * @inheritdoc
     */
    public function handleConfigurationSubmit(SaleItemInterface $item)
    {
        $subject = $this->getItemSubject($item);

        $this->itemBuilder->buildItem($item, $subject);
    }

    /**
     * @inheritdoc
     */
    public function supports($subject)
    {
        return $subject instanceof ProductInterface;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return 'ekyna_commerce.product.label.singular';
    }

    /**
     * Asserts that the item subject is set.
     *
     * @param SaleItemInterface $item
     *
     * @return ProductInterface
     * @throws RuntimeException
     */
    private function getItemSubject(SaleItemInterface $item)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        if (null === $subject = $item->getSubject()) {
            throw new RuntimeException('Item subject must be set.');
        }

        if (!$subject instanceof ProductInterface) {
            throw new InvalidArgumentException("Expected instance of " . ProductInterface::class);
        }

        return $subject;
    }
}
