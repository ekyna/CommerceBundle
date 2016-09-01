<?php

namespace Ekyna\Bundle\CommerceBundle\DataFixtures\ORM;

/**
 * Class LoadBaseData
 * @package Ekyna\Bundle\CommerceBundle\DataFixtures\ORM
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LoadBaseData extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function getFixtures()
    {
        return [
            realpath(__DIR__.'/data/attribute.yml'),
            realpath(__DIR__.'/data/customer.yml'),
            realpath(__DIR__.'/data/tax.yml'),
            realpath(__DIR__.'/data/product.yml'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 0;
    }
}
