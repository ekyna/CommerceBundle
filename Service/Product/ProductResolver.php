<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Product;

use Ekyna\Bundle\CommerceBundle\Service\Subject\AbstractSubjectResolver;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectResolverInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Product\Repository\ProductRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;

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

        return $this->productRepository->findOneById($data['id']);

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
    public function supportsItem(SaleItemInterface $item)
    {
        return $item->getSubjectData(SubjectProviderInterface::DATA_KEY) === ProductProvider::NAME
            && 0 < intval($item->getSubjectData('id'));
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
     *
     * @throws InvalidArgumentException
     */
    protected function assertSupports(SaleItemInterface $item)
    {
        if (!$this->supportsItem($item)) {
            throw new InvalidArgumentException('Unsupported sale item.');
        }
    }
}
