<?php


namespace Ekyna\Bundle\CommerceBundle\Resolver;

use Ekyna\Component\Commerce\Subject\Resolver\SubjectResolverInterface as BaseInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Interface SubjectResolverInterface
 * @package Ekyna\Bundle\CommerceBundle\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SubjectResolverInterface extends BaseInterface
{
    /**
     * Sets the urlGenerator.
     *
     * @param UrlGeneratorInterface $urlGenerator
     *
     * @return $this|AbstractSubjectResolver
     */
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator);

    /**
     * Returns the order item form options.
     *
     * @param OrderItemInterface $item
     * @param string             $property
     * @return array
     */
    public function getFormOptions(OrderItemInterface $item, $property);
}
