<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomerGroupChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupChoiceType extends AbstractType
{
    /**
     * @var string
     */
    private $customerGroupClass;


    /**
     * Constructor.
     *
     * @param string $customerGroupClass
     */
    public function __construct(string $customerGroupClass)
    {
        $this->customerGroupClass = $customerGroupClass;
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
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

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'             => function (Options $options, $value) {
                if (false === $value || !empty($value)) {
                    return $value;
                }

                return 'ekyna_commerce.customer_group.label.' . ($options['multiple'] ? 'plural' : 'singular');
            },
            'class'             => $this->customerGroupClass,
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

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_customer_group';
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return ResourceType::class;
    }
}
