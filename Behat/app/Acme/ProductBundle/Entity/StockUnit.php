<?php

namespace Acme\ProductBundle\Entity;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;

/**
 * Class StockUnit
 * @package Acme\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnit extends AbstractStockUnit
{
    /**
     * @var Product
     */
    protected $product;


    /**
     * @inheritdoc
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @inheritdoc
     */
    public function setSubject(StockSubjectInterface $subject)
    {
        if (!$subject instanceof Product) {
            throw new InvalidArgumentException("Expected instance of Product.");
        }

        return $this->setProduct($subject);
    }

    /**
     * @inheritdoc
     */
    public function getSubject()
    {
        return $this->getProduct();
    }
}
