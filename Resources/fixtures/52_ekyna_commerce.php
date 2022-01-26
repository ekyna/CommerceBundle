<?php

declare(strict_types=1);

use Ekyna\Component\Commerce\Common\Model\CouponInterface;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes;
use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerContactInterface;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteItemInterface;

return [
    CouponInterface::class          => [
        'coupon_dummy_01' => [
            '__factory' => [
                '@ekyna_commerce.factory.coupon::create' => [],
            ],
            'code'      => 'COUPON-CODE',
            'amount'    => "<decimal('10.')>",
        ],
    ],
    CustomerInterface::class        => [
        'customer_dummy_01' => [
            '__factory'     => [
                '@ekyna_commerce.factory.customer::create' => [],
            ],
            'email'         => 'customer1@example.org',
            'gender'        => 'mr',
            'firstName'     => 'John',
            'lastName'      => 'Doe',
            'customerGroup' => "<resource('ekyna_commerce.customer_group', '{id: 1}')>",
            'user'          => "<resource('ekyna_user.user', '{id: 1}')>",
        ],
        'customer_dummy_02' => [
            '__factory'     => [
                '@ekyna_commerce.factory.customer::create' => [],
            ],
            'email'         => 'customer2@example.org',
            'gender'        => 'mrs',
            'firstName'     => 'Jane',
            'lastName'      => 'Doe',
            'customerGroup' => "<resource('ekyna_commerce.customer_group', '{id: 2}')>",
            'user'          => "<resource('ekyna_user.user', '{id: 2}')>",
        ],
        'customer_dummy_03' => [
            '__factory'     => [
                '@ekyna_commerce.factory.customer::create' => [],
            ],
            'email'         => 'customer3@example.org',
            'gender'        => 'mr',
            'firstName'     => 'Bob',
            'lastName'      => 'Doe',
            'customerGroup' => "<resource('ekyna_commerce.customer_group', '{id: 2}')>",
        ],
    ],
    CustomerAddressInterface::class => [
        'customer_dummy_01_address_01' => [
            '__factory'       => [
                '@ekyna_commerce.factory.customer_address::create' => [],
            ],
            'customer'        => '@customer_dummy_01',
            'invoiceDefault'  => true,
            'deliveryDefault' => true,
            'street'          => 'Rue Saint-Michel',
            'city'            => 'Rennes',
            'postalCode'      => '35000',
            'country'         => "<resource('ekyna_commerce.country', '{code: fr}')>",
        ],
    ],
    CustomerContactInterface::class => [
        'customer_dummy_01_contact_01' => [
            '__factory'     => [
                '@ekyna_commerce.factory.customer_contact::create' => [],
            ],
            'customer'      => '@customer_dummy_01',
            'gender'        => 'mr',
            'firstName'     => 'Saul',
            'lastName'      => 'Goodman',
            'email'         => 'saul.goodman@example.org',
            'notifications' => [
                NotificationTypes::INVOICE_COMPLETE,
                NotificationTypes::INVOICE_PARTIAL,
            ],
        ],
    ],
    OrderInterface::class           => [
        'order_dummy_01' => [
            '__factory'      => [
                '@ekyna_commerce.factory.order::createWithCustomer' => [
                    '@customer_dummy_01',
                ],
            ],
            'shipmentMethod' => "<resource('ekyna_commerce.shipment_method', '{id: 1}')>",
        ],
    ],
    OrderItemInterface::class       => [
        'order_dummy_01_item_01' => [
            '__factory'   => [
                '@ekyna_commerce.factory.order_item::create' => [],
            ],
            'order'       => '@order_dummy_01',
            'designation' => 'Dummy item 01',
            'reference'   => 'O1-I1',
            'netPrice'    => "<decimal('10.0')>",
            'weight'      => "<decimal('0.2')>",
            'quantity'    => "<decimal('1.0')>",
            'taxGroup'    => "<resource('ekyna_commerce.tax_group', '{code: normal}')>",
        ],
    ],
    QuoteInterface::class           => [
        'quote_dummy_01' => [
            '__factory'      => [
                '@ekyna_commerce.factory.quote::createWithCustomer' => [
                    '@customer_dummy_01',
                ],
            ],
            'shipmentMethod' => "<resource('ekyna_commerce.shipment_method', '{id: 1}')>",
            'editable'       => true,
        ],
    ],
    QuoteItemInterface::class       => [
        'quote_dummy_01_item_01' => [
            '__factory'   => [
                '@ekyna_commerce.factory.quote_item::create' => [],
            ],
            'quote'       => '@quote_dummy_01',
            'designation' => 'Dummy item 01',
            'reference'   => 'O1-I1',
            'netPrice'    => "<decimal('10.0')>",
            'weight'      => "<decimal('0.2')>",
            'quantity'    => "<decimal('1.0')>",
            'taxGroup'    => "<resource('ekyna_commerce.tax_group', '{code: normal}')>",
        ],
    ],
];
