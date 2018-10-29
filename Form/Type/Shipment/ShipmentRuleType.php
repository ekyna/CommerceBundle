<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\MoneyType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\VatDisplayModeType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ShipmentRuleType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentRuleType extends ResourceFormType
{
    /**
     * @var string
     */
    protected $defaultCurrency;


    /**
     * Constructor.
     *
     * @param string $dataClass
     * @param string $defaultCurrency
     */
    public function __construct(string $dataClass, string $defaultCurrency)
    {
        parent::__construct($dataClass);

        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label' => 'ekyna_core.field.name',
            ])
            ->add('vatMode', VatDisplayModeType::class, [
                'label'    => 'ekyna_commerce.shipment_rule.field.vat_mode',
                'required' => true,
                'attr'     => [
                    'inline'            => true,
                    'align_with_widget' => true,
                    'help_text'         => 'ekyna_commerce.shipment_rule.help.vat_mode',
                ],
            ])
            ->add('methods', ShipmentMethodChoiceType::class, [
                'label'    => 'ekyna_commerce.shipment_method.label.plural',
                'multiple' => true,
                'required' => false,
                'attr'     => [
                    'help_text' => 'ekyna_commerce.shipment_rule.help.methods',
                ],
            ])
            ->add('countries', CountryChoiceType::class, [
                'label'    => 'ekyna_commerce.country.label.plural',
                'multiple' => true,
                'required' => false,
                'attr'     => [
                    'help_text' => 'ekyna_commerce.shipment_rule.help.countries',
                ],
            ])
            ->add('customerGroups', CustomerGroupChoiceType::class, [
                'label'    => 'ekyna_commerce.customer_group.label.plural',
                'multiple' => true,
                'required' => false,
                'attr'     => [
                    'help_text' => 'ekyna_commerce.shipment_rule.help.customer_groups',
                ],
            ])
            ->add('startAt', Type\DateTimeType::class, [
                'label'    => 'ekyna_commerce.shipment_rule.field.start_at',
                'format'   => 'dd/MM/yyyy',
                'required' => false,
            ])
            ->add('endAt', Type\DateTimeType::class, [
                'label'    => 'ekyna_commerce.shipment_rule.field.end_at',
                'format'   => 'dd/MM/yyyy',
                'required' => false,
            ])
            ->add('baseTotal', MoneyType::class, [
                'label'    => 'ekyna_commerce.shipment_rule.field.base_total',
                'currency' => $this->defaultCurrency,
                'attr'     => [
                    'help_text' => 'ekyna_commerce.shipment_rule.help.base_total',
                ],
            ])
            ->add('netPrice', MoneyType::class, [
                'label'    => 'ekyna_commerce.field.net_price',
                'currency' => $this->defaultCurrency,
                'attr'     => [
                    'help_text' => 'ekyna_commerce.field.net_price',
                ],
            ]);
    }
}
