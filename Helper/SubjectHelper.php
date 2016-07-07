<?php

namespace Ekyna\Bundle\CommerceBundle\Helper;

use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Subject\Helper;

/**
 * Class SubjectHelper
 * @package Ekyna\Bundle\CommerceBundle\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectHelper extends Helper implements SubjectHelperInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFormOptions(OrderItemInterface $item, $property)
    {
        if ($item->hasSubjectIdentity() && null !== $resolver = $this->getResolver($item)) {
            /** @var \Ekyna\Bundle\CommerceBundle\Resolver\SubjectResolverInterface $resolver */
            return $resolver->getFormOptions($item, $property);
        }

        return [];
    }
}
