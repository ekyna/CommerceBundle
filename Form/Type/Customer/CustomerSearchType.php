<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceSearchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class CustomerSearchType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerSearchType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'resource'    => 'ekyna_commerce.customer',
                'required' => false,
                'parent'   => false,
                'attr'     => [
                    'help_text' => t('customer.help.hierarchy', [], 'EkynaCommerce'),
                ],
            ])
            ->setAllowedTypes('parent', 'bool')
            ->setNormalizer('search_parameters', function (Options $options, $value) {
                if ($options['parent'] && !isset($value['parent'])) {
                    $value['parent'] = 1;
                }

                return $value;
            });
    }

    public function getParent(): ?string
    {
        return ResourceSearchType::class;
    }
}
