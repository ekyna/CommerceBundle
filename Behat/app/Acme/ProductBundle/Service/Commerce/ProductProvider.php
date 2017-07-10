<?php

namespace Acme\ProductBundle\Service\Commerce;

use Acme\ProductBundle\Entity\Product;
use Acme\ProductBundle\Repository\ProductRepository;
use Ekyna\Component\Commerce\Exception\SubjectException;
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
        return $this->transform($subject, $relative->getSubjectIdentity());
    }

    /**
     * @inheritDoc
     */
    public function resolve(SubjectRelativeInterface $relative)
    {
        return $this->reverseTransform($relative->getSubjectIdentity());
    }

    /**
     * @inheritdoc
     */
    public function transform($subject, SubjectIdentity $identity)
    {
        $this->assertSupportsSubject($subject);

        if ($subject === $identity->getSubject()) {
            return $this;
        }

        /** @var Product $subject */
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

        $productId = intval($identity->getIdentifier());

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
        return $relative->getSubjectIdentity()->getProvider() === self::NAME;
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
     * @inheritDoc
     */
    public function getSearchRouteAndParameters($context)
    {
        return [
            'route'      => 'acme_product_product_admin_search',
            'parameters' => [],
        ];
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
        if ($identity->getProvider() != static::NAME) {
            throw new SubjectException('Unsupported subject identity.');
        }
    }
}
