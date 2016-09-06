<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Product;

use Ekyna\Bundle\CommerceBundle\Service\Subject\AbstractSubjectResolver;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectResolverInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Class ProductResolver
 * @package Ekyna\Bundle\CommerceBundle\Service\Product
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductResolver extends AbstractSubjectResolver implements SubjectResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(SaleItemInterface $item)
    {
        // TODO: Implement resolve() method.
    }

    /**
     * @inheritdoc
     */
    public function generateFrontOfficePath(SaleItemInterface $item)
    {
        // TODO: Implement generateFrontOfficePath() method.
    }

    /**
     * @inheritdoc
     */
    public function generateBackOfficePath(SaleItemInterface $item)
    {
        // TODO: Implement generateBackOfficePath() method.
    }

    /**
     * @inheritdoc
     */
    public function supports(SaleItemInterface $item)
    {
        // TODO: Implement supports() method.
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        // TODO: Implement getName() method.
    }
}
