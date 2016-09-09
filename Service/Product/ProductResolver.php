<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Product;

use Ekyna\Bundle\CommerceBundle\Service\Subject\AbstractSubjectResolver;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectResolverInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Product\Repository\ProductRepositoryInterface;

/**
 * Class ProductResolver
 * @package Ekyna\Bundle\CommerceBundle\Service\Product
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductResolver extends AbstractSubjectResolver implements SubjectResolverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(SaleItemInterface $item)
    {
        $this->assertSupports($item);

        // TODO $item->getSubject() ?

        $data = $item->getSubjectData();

        return $this->productRepository->findById($data['id']);

        // TODO $item->setSubject($product) ?
    }

    /**
     * @inheritdoc
     */
    public function generateFrontOfficePath(SaleItemInterface $item)
    {
        $this->assertSupports($item);

        // TODO: Implement generateFrontOfficePath() method.
    }

    /**
     * @inheritdoc
     */
    public function generateBackOfficePath(SaleItemInterface $item)
    {
        $this->assertSupports($item);

        // TODO: Implement generateBackOfficePath() method.
    }

    /**
     * @inheritdoc
     */
    public function supports(SaleItemInterface $item)
    {
        $data = $item->getSubjectData();

        if (empty(array_diff(['provider', 'id'], array_keys($data)))) {
            return $data['provider'] === ProductProvider::NAME && is_int($data['id']) && 0 < $data['id'];
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        // TODO: Implement getName() method.
    }

    /**
     * Asserts that the sale item is supported.
     *
     * @param SaleItemInterface $item
     * @throws InvalidArgumentException
     */
    protected function assertSupports(SaleItemInterface $item)
    {
        if (!$this->supports($item)) {
            throw new InvalidArgumentException('Unsupported sale item.');
        }
    }
}
