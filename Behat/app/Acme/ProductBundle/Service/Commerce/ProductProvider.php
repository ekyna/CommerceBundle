<?php

namespace Acme\ProductBundle\Service\Commerce;

use Acme\ProductBundle\Entity\Product;
use Acme\ProductBundle\Repository\ProductRepository;
use Ekyna\Component\Commerce\Exception\SubjectException;
use Ekyna\Component\Commerce\Subject\Builder\FormBuilderInterface;
use Ekyna\Component\Commerce\Subject\Builder\ItemBuilderInterface;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;

/**
 * Class ProductProvider
 * @package Acme\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductProvider implements SubjectProviderInterface
{
    const NAME = 'acme_product';

    /**
     * @var ProductRepository
     */
    private $productRepository;

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
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritDoc
     */
    public function assign(SubjectRelativeInterface $relative, $subject)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        return $this->transform($subject, $relative->getSubjectIdentity());
    }

    /**
     * @inheritDoc
     */
    public function resolve(SubjectRelativeInterface $relative)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        return $this->reverseTransform($relative->getSubjectIdentity());
    }

    /**
     * @inheritdoc
     */
    public function transform($subject, SubjectIdentity $identity)
    {
        $this->assertSupportsSubject($subject);

        /** @noinspection PhpInternalEntityUsedInspection */
        if ($subject === $identity->getSubject()) {
            return $this;
        }

        /** @var Product $subject */

        /** @noinspection PhpInternalEntityUsedInspection */
        $identity
            ->setProvider(static::NAME)
            ->setIdentifier($subject->getId())
            ->setSubject($subject);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function reverseTransform(SubjectIdentity $identity)
    {
        $this->assertSupportsIdentity($identity);

        /** @noinspection PhpInternalEntityUsedInspection */
        $productId = intval($identity->getIdentifier());

        /** @noinspection PhpInternalEntityUsedInspection */
        if (null !== $product = $identity->getSubject()) {
            if ((!$product instanceof Product) || ($product->getId() != $productId)) {
                // TODO Clear identity data ?
                throw new SubjectException("Failed to resolve item subject.");
            }

            return $product;
        }

        if (null === $product = $this->productRepository->find($productId)) {
            // TODO Clear identity data ?
            throw new SubjectException("Failed to resolve item subject.");
        }

        /** @noinspection PhpInternalEntityUsedInspection */
        $identity->setSubject($product);

        return $product;
    }

    /**
     * @inheritdoc
     */
    public function supportsSubject($subject)
    {
        return $subject instanceof Product;
    }

    /**
     * @inheritdoc
     */
    public function supportsRelative(SubjectRelativeInterface $relative)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        return $relative->getSubjectIdentity()->getProvider() === self::NAME;
    }

    /**
     * Returns the item builder.
     *
     * @return ItemBuilderInterface
     */
    public function getItemBuilder()
    {
        if (null !== $this->itemBuilder) {
            return $this->itemBuilder;
        }

        return $this->itemBuilder = new ItemBuilder($this);
    }

    /**
     * Returns the form builder.
     *
     * @return FormBuilderInterface
     */
    public function getFormBuilder()
    {
        if (null !== $this->formBuilder) {
            return $this->formBuilder;
        }

        return $this->formBuilder = new FormBuilder($this);
    }

    /**
     * @inheritdoc
     */
    public function getProductRepository()
    {
        return $this->productRepository;
    }

    /**
     * @inheritdoc
     */
    public function getSubjectClass()
    {
        return Product::class;
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
        return 'Acme Product';
    }

    /**
     * Asserts that the subject relative is supported.
     *
     * @param mixed $subject
     *
     * @throws SubjectException
     */
    protected function assertSupportsSubject($subject)
    {
        if (!$this->supportsSubject($subject)) {
            throw new SubjectException('Unsupported subject.');
        }
    }

    /**
     * Asserts that the subject relative is supported.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @throws SubjectException
     */
    protected function assertSupportsRelative(SubjectRelativeInterface $relative)
    {
        if (!$this->supportsRelative($relative)) {
            throw new SubjectException('Unsupported subject relative.');
        }
    }

    /**
     * Asserts that the subject identity is supported.
     *
     * @param SubjectIdentity $identity
     *
     * @throws SubjectException
     */
    protected function assertSupportsIdentity(SubjectIdentity $identity)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        if ($identity->getProvider() != static::NAME) {
            throw new SubjectException('Unsupported subject identity.');
        }
    }
}
