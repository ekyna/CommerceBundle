<?php

namespace Ekyna\Bundle\CommerceBundle\Service;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Subject\Helper;

/**
 * Class SubjectHelper
 * @package Ekyna\Bundle\CommerceBundle\Service
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectHelper extends Helper implements SubjectHelperInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFormOptions(SaleItemInterface $item, $property)
    {
        /*if ($item->hasSubjectIdentity() && null !== $resolver = $this->getResolver($item)) {
            /** @var \Ekyna\Bundle\CommerceBundle\Resolver\SubjectResolverInterface $resolver */
            /*return $resolver->getFormOptions($item, $property);
        }*/

        return [];
    }
}
