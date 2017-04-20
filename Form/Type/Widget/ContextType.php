<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Widget;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\LocaleChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function count;
use function json_decode;
use function json_encode;
use function Symfony\Component\Translation\t;

/**
 * Class ContextType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Widget
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ContextType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $param = $builder
            ->create('param', HiddenType::class)
            ->addModelTransformer(new CallbackTransformer(
                function ($value) {
                    if (empty($value)) {
                        return null;
                    }

                    return json_encode($value);
                },
                function ($value) {
                    if (empty($value)) {
                        return null;
                    }

                    return json_decode($value, true);
                }
            ));

        $builder
            ->add('country', CountryChoiceType::class, [
                'label'   => t('context.field.delivery_country', [], 'EkynaCommerce'),
                'select2' => true,
            ])
            ->add('currency', CurrencyChoiceType::class, [
                'select2' => false,
            ])
            ->add('route', HiddenType::class)
            ->add($param);

        if (2 > count($options['locales'])) {
            return;
        }

        $builder->add('locale', LocaleChoiceType::class, [
            'locales' => $options['locales'],
            'select2' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('locales')
            ->setAllowedTypes('locales', 'array');
    }
}
