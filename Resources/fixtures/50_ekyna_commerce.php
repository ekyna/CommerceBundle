<?php

declare(strict_types=1);

use Ekyna\Component\Commerce\Accounting\Model\AccountingInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentTermInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentTermTranslationInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentTermTriggers;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentRuleInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentZoneInterface;

return [
    AccountingInterface::class             => [
        'accounting_dummy' => [
            '__factory' => [
                '@ekyna_commerce.factory.accounting::create' => [],
            ],
            'name'      => 'Dummy account',
            'number'    => '123456789',
            'enabled'   => true,
            'type'      => 'good',
            'taxRule'   => "<resource('ekyna_commerce.tax_rule', '{code: fr_fr}')>",
            'tax'       => "<resource('ekyna_commerce.tax', '{code: fr_normal}')>",
        ],
    ],
    PaymentTermInterface::class            => [
        'payment_term_dummy' => [
            '__factory'  => [
                '@ekyna_commerce.factory.payment_term::create' => [],
            ],
            'name'       => 'Dummy payment term',
            'days'       => '30',
            'endOfMonth' => true,
            'trigger'    => PaymentTermTriggers::TRIGGER_SHIPPED,
        ],
    ],
    PaymentTermTranslationInterface::class => [
        'payment_term_dummy_translation_fr' => [
            '__factory' => [
                'Ekyna\Bundle\ResourceBundle\DataFixtures\TranslationFactory::create' => [
                    '@payment_term_dummy',
                ],
            ],
            'locale'    => 'fr',
            'title'     => 'Dummy payment term',
        ],
    ],
    ShipmentZoneInterface::class           => [
        'shipment_zone_dummy' => [
            '__factory' => [
                '@ekyna_commerce.factory.shipment_zone::create' => [],
            ],
            'name'      => 'Dummy shipment zone',
            'countries' => [
                "<resource('ekyna_commerce.country', '{code: FR}')>",
            ],
        ],
    ],
    ShipmentPriceInterface::class          => [
        'shipment_price_dummy' => [
            '__factory' => [
                '@ekyna_commerce.factory.shipment_price::create' => [],
            ],
            'method'    => "<resource('ekyna_commerce.shipment_method', '{id: 1}')>",
            'zone'      => '@shipment_zone_dummy',
            'weight'    => "<decimal('5')>",
            'netPrice'  => "<decimal('5')>",
        ],
    ],
    ShipmentRuleInterface::class           => [
        'shipment_rule_dummy' => [
            '__factory'      => [
                '@ekyna_commerce.factory.shipment_rule::create' => [],
            ],
            'name'           => 'Dummy shipment rule',
            'methods'        => ["<resource('ekyna_commerce.shipment_method', '{id: 1}')>"],
            'countries'      => ["<resource('ekyna_commerce.country', '{code: FR}')>"],
            'customerGroups' => ["<resource('ekyna_commerce.customer_group', '{default: true}')>"],
            'baseTotal'      => "<decimal('50')>",
            'netPrice'       => "<decimal('5')>",
            'startAt'        => new DateTime('-6 months'),
            'endAt'          => new DateTime('+6 months'),
        ],
    ],
];
