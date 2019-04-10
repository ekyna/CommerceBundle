<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Widget;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\LocaleChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ContextType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Widget
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ContextType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $param = $builder
            ->create('param', HiddenType::class)
            ->addModelTransformer(new CallbackTransformer(
                function($value) {
                    if (empty($value)) {
                        return null;
                    }

                    return \json_encode($value);
                },
                function ($value) {
                    if (empty($value)) {
                        return null;
                    }

                    return \json_decode($value, true);
                }
            ));

        $builder
            ->add('currency', CurrencyChoiceType::class)
            ->add('country', CountryChoiceType::class, [
                'label' => 'ekyna_commerce.context.field.delivery_country',
            ])
            ->add('locale', LocaleChoiceType::class)
            ->add('route', HiddenType::class)
            ->add($param);
    }
}
