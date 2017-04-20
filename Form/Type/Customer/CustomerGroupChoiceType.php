<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;

/**
 * Class CustomerGroupChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupChoiceType extends AbstractType
{
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if (!$view->vars['expanded']) {
            return;
        }

        foreach ($view->children as $child) {
            $child->vars['label_attr'] = array_replace($child->vars['label_attr'], [
                'class' => 'btn btn-default',
                'for'   => $child->vars['id'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'resource'          => 'ekyna_commerce.customer_group',
            'preferred_choices' => function (CustomerGroupInterface $customerGroup) {
                return $customerGroup->isDefault();
            },
            'choice_attr'       => function ($value) {
                if ($value instanceof CustomerGroupInterface) {
                    return [
                        'data-business' => $value->isBusiness() ? '1' : '0',
                    ];
                }

                return [];
            },
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_customer_group';
    }

    public function getParent(): ?string
    {
        return ResourceChoiceType::class;
    }
}
