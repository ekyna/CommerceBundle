<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\VatDisplayModeType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ShipmentRuleType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentRuleType extends AbstractResourceType
{
    protected string $defaultCurrency;

    public function __construct(string $defaultCurrency)
    {
        $this->defaultCurrency = $defaultCurrency;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label' => t('field.name', [], 'EkynaUi'),
            ])
            ->add('vatMode', VatDisplayModeType::class, [
                'label'    => t('shipment_rule.field.vat_mode', [], 'EkynaCommerce'),
                'required' => true,
                'help'     => t('shipment_rule.help.vat_mode', [], 'EkynaCommerce'),
                'attr'     => [
                    'inline'            => true,
                    'align_with_widget' => true,
                ],
            ])
            ->add('methods', ShipmentMethodChoiceType::class, [
                'label'    => t('shipment_method.label.plural', [], 'EkynaCommerce'),
                'multiple' => true,
                'required' => false,
                'help'     => t('shipment_rule.help.methods', [], 'EkynaCommerce'),
            ])
            ->add('countries', CountryChoiceType::class, [
                'label'    => t('country.label.plural', [], 'EkynaCommerce'),
                'enabled'  => false,
                'multiple' => true,
                'required' => false,
                'help'     => t('shipment_rule.help.countries', [], 'EkynaCommerce'),
            ])
            ->add('customerGroups', CustomerGroupChoiceType::class, [
                'label'    => t('customer_group.label.plural', [], 'EkynaCommerce'),
                'multiple' => true,
                'required' => false,
                'help'     => t('shipment_rule.help.customer_groups', [], 'EkynaCommerce'),
            ])
            ->add('startAt', Type\DateType::class, [
                'label'    => t('shipment_rule.field.start_at', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('endAt', Type\DateType::class, [
                'label'    => t('shipment_rule.field.end_at', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('baseTotal', Type\MoneyType::class, [
                'label'    => t('shipment_rule.field.base_total', [], 'EkynaCommerce'),
                'decimal'  => true,
                'currency' => $this->defaultCurrency,
                'help'     => t('shipment_rule.help.base_total', [], 'EkynaCommerce'),
            ])
            ->add('netPrice', Type\MoneyType::class, [
                'label'    => t('field.net_price', [], 'EkynaCommerce'),
                'decimal'  => true,
                'currency' => $this->defaultCurrency,
                'help'     => t('field.net_price', [], 'EkynaCommerce'),
            ]);
    }
}
