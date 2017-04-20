<?php

declare(strict_types=1);

use Ekyna\Component\Commerce\Supplier\Model\SupplierAddressInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierCarrierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryItemInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;

return [
    SupplierCarrierInterface::class                                              => [
        'supplier_carrier_dummy' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_carrier::create' => [],
            ],
            'name'      => 'Dummy supplier carrier',
            'tax'       => "<resource('ekyna_commerce.tax', '{code: fr_normal}')>",
        ],
    ],
    SupplierAddressInterface::class                                              => [
        'supplier_address_dummy' => [
            '__factory'  => [
                '@ekyna_commerce.factory.supplier_address::create' => [],
            ],
            'street'     => 'Rue Saint-Michel',
            'city'       => 'Rennes',
            'postalCode' => '35000',
            'country'    => "<resource('ekyna_commerce.country', '{code: fr}')>",
        ],
    ],
    SupplierInterface::class                                                     => [
        'supplier_dummy' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier::create' => [],
            ],
            'name'      => 'Dummy supplier',
            'email'     => 'supplier@example.org',
            'currency'  => "<resource('ekyna_commerce.currency', '{code: EUR}')>",
            'carrier'   => '@supplier_carrier_dummy',
            'locale'    => 'fr',
            'address'   => '@supplier_address_dummy',
        ],
    ],
    SupplierProductInterface::class                                              => [
        'supplier_product_nfcr' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('NFCR')>)>",
        ],
        'supplier_product_musb' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('MUSB')>)>",
        ],
        'supplier_product_hub'  => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('HUB')>)>",
        ],
        'supplier_product_bk9'  => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('BK9')>)>",
        ],
        'supplier_product_bk11' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('BK11')>)>",
        ],
        'supplier_product_taba' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('TABA')>)>",
        ],
        'supplier_product_tabbw' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('TABB-W')>)>",
        ],
        'supplier_product_tabbb' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('TABB-B')>)>",
        ],
        'supplier_product_tabcw' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('TABC-W')>)>",
        ],
        'supplier_product_tabcb' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('TABC-B')>)>",
        ],
        'supplier_product_kesw' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('KES-W')>)>",
        ],
        'supplier_product_kesb' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('KES-B')>)>",
        ],
        'supplier_product_swcw' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('SWC-W')>)>",
        ],
        'supplier_product_swcb' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('SWC-B')>)>",
        ],
        'supplier_product_rocw' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('ROC-W')>)>",
        ],
        'supplier_product_rocb' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('ROC-B')>)>",
        ],
        'supplier_product_kdbw' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('KDB-W')>)>",
        ],
        'supplier_product_kdbb' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('KDB-B')>)>",
        ],
        'supplier_product_ksbw' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('KSB-W')>)>",
        ],
        'supplier_product_ksbb' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('KSB-B')>)>",
        ],
        'supplier_product_shaw' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('SHA-W')>)>",
        ],
        'supplier_product_shab' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('SHA-B')>)>",
        ],
        'supplier_product_shbw' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('SHB-W')>)>",
        ],
        'supplier_product_shbb' => [
            '__factory'       => [
                '@ekyna_commerce.factory.supplier_product::create' => [],
            ],
            'supplier'        => '@supplier_dummy',
            'subjectIdentity' => "<subjectIdentity(<getProduct('SHB-B')>)>",
        ],
    ],
    SupplierOrderInterface::class        => [
        'supplier_order_1' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_order::create' => [],
            ],
            'supplier'  => '@supplier_dummy',
            'carrier'   => '@supplier_carrier_dummy',
            'currency'  => '<currencyByCode(\'EUR\')>',
            'state'     => 'validated',
            'orderedAt' => '<datetime(\'now\')>',
            'warehouse' => '<defaultWarehouse()>',
        ],
    ],
    SupplierOrderItemInterface::class    => [
        'supplier_order_item_1_1'  => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_order_item::create' => [],
            ],
            'order'     => '@supplier_order_1',
            'product'   => '@supplier_product_nfcr',
            'quantity'  => "<decimal('20')>",
        ],
        'supplier_order_item_1_2'  => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_order_item::create' => [],
            ],
            'order'     => '@supplier_order_1',
            'product'   => '@supplier_product_musb',
            'quantity'  => "<decimal('25')>",
        ],
        'supplier_order_item_1_4'  => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_order_item::create' => [],
            ],
            'order'     => '@supplier_order_1',
            'product'   => '@supplier_product_bk9',
            'quantity'  => "<decimal('20')>",
        ],
        'supplier_order_item_1_5'  => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_order_item::create' => [],
            ],
            'order'     => '@supplier_order_1',
            'product'   => '@supplier_product_bk11',
            'quantity'  => "<decimal('20')>",
        ],
        'supplier_order_item_1_6'  => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_order_item::create' => [],
            ],
            'order'     => '@supplier_order_1',
            'product'   => '@supplier_product_hub',
            'quantity'  => "<decimal('30')>",
        ],
        'supplier_order_item_1_7'  => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_order_item::create' => [],
            ],
            'order'     => '@supplier_order_1',
            'product'   => '@supplier_product_taba',
            'quantity'  => "<decimal('20')>",
        ],
        'supplier_order_item_1_8'  => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_order_item::create' => [],
            ],
            'order'     => '@supplier_order_1',
            'product'   => '@supplier_product_tabbb',
            'quantity'  => "<decimal('20')>",
        ],
        'supplier_order_item_1_9'  => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_order_item::create' => [],
            ],
            'order'     => '@supplier_order_1',
            'product'   => '@supplier_product_tabcw',
            'quantity'  => "<decimal('20')>",
        ],
        'supplier_order_item_1_10' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_order_item::create' => [],
            ],
            'order'     => '@supplier_order_1',
            'product'   => '@supplier_product_kesb',
            'quantity'  => "<decimal('30')>",
        ],
        'supplier_order_item_1_11' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_order_item::create' => [],
            ],
            'order'     => '@supplier_order_1',
            'product'   => '@supplier_product_swcw',
            'quantity'  => "<decimal('25')>",
        ],
        'supplier_order_item_1_12' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_order_item::create' => [],
            ],
            'order'     => '@supplier_order_1',
            'product'   => '@supplier_product_swcb',
            'quantity'  => "<decimal('35')>",
        ],
        'supplier_order_item_1_13' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_order_item::create' => [],
            ],
            'order'     => '@supplier_order_1',
            'product'   => '@supplier_product_rocb',
            'quantity'  => "<decimal('32')>",
        ],
        'supplier_order_item_1_14' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_order_item::create' => [],
            ],
            'order'     => '@supplier_order_1',
            'product'   => '@supplier_product_kdbw',
            'quantity'  => "<decimal('24')>",
        ],
        'supplier_order_item_1_15' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_order_item::create' => [],
            ],
            'order'     => '@supplier_order_1',
            'product'   => '@supplier_product_ksbb',
            'quantity'  => "<decimal('28')>",
        ],
        'supplier_order_item_1_16' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_order_item::create' => [],
            ],
            'order'     => '@supplier_order_1',
            'product'   => '@supplier_product_shaw',
            'quantity'  => "<decimal('36')>",
        ],
        'supplier_order_item_1_17' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_order_item::create' => [],
            ],
            'order'     => '@supplier_order_1',
            'product'   => '@supplier_product_shbb',
            'quantity'  => "<decimal('34')>",
        ],
    ],
    SupplierDeliveryInterface::class     => [
        'supplier_delivery_1' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_delivery::create' => [],
            ],
            'order'     => '@supplier_order_1',
        ],
    ],
    SupplierDeliveryItemInterface::class => [
        'supplier_delivery_item_1_1'  => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_delivery_item::create' => [],
            ],
            'delivery'  => '@supplier_delivery_1',
            'orderItem' => '@supplier_order_item_1_1',
            'quantity'  => "<decimal('20')>",
            'geocode'   => 'A1',
        ],
        'supplier_delivery_item_1_2'  => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_delivery_item::create' => [],
            ],
            'delivery'  => '@supplier_delivery_1',
            'orderItem' => '@supplier_order_item_1_2',
            'quantity'  => "<decimal('25')>",
            'geocode'   => 'A2',
        ],
        'supplier_delivery_item_1_4'  => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_delivery_item::create' => [],
            ],
            'delivery'  => '@supplier_delivery_1',
            'orderItem' => '@supplier_order_item_1_4',
            'quantity'  => "<decimal('20')>",
            'geocode'   => 'A4',
        ],
        'supplier_delivery_item_1_5'  => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_delivery_item::create' => [],
            ],
            'delivery'  => '@supplier_delivery_1',
            'orderItem' => '@supplier_order_item_1_5',
            'quantity'  => "<decimal('20')>",
            'geocode'   => 'A5',
        ],
        'supplier_delivery_item_1_6'  => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_delivery_item::create' => [],
            ],
            'delivery'  => '@supplier_delivery_1',
            'orderItem' => '@supplier_order_item_1_6',
            'quantity'  => "<decimal('30')>",
            'geocode'   => 'A6',
        ],
        'supplier_delivery_item_1_7'  => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_delivery_item::create' => [],
            ],
            'delivery'  => '@supplier_delivery_1',
            'orderItem' => '@supplier_order_item_1_7',
            'quantity'  => "<decimal('20')>",
            'geocode'   => 'B1',
        ],
        'supplier_delivery_item_1_8'  => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_delivery_item::create' => [],
            ],
            'delivery'  => '@supplier_delivery_1',
            'orderItem' => '@supplier_order_item_1_8',
            'quantity'  => "<decimal('20')>",
            'geocode'   => 'B2',
        ],
        'supplier_delivery_item_1_9'  => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_delivery_item::create' => [],
            ],
            'delivery'  => '@supplier_delivery_1',
            'orderItem' => '@supplier_order_item_1_9',
            'quantity'  => "<decimal('20')>",
            'geocode'   => 'B3',
        ],
        'supplier_delivery_item_1_10' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_delivery_item::create' => [],
            ],
            'delivery'  => '@supplier_delivery_1',
            'orderItem' => '@supplier_order_item_1_10',
            'quantity'  => "<decimal('30')>",
            'geocode'   => 'B4',
        ],
        'supplier_delivery_item_1_11' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_delivery_item::create' => [],
            ],
            'delivery'  => '@supplier_delivery_1',
            'orderItem' => '@supplier_order_item_1_11',
            'quantity'  => "<decimal('25')>",
            'geocode'   => 'B5',
        ],
        'supplier_delivery_item_1_12' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_delivery_item::create' => [],
            ],
            'delivery'  => '@supplier_delivery_1',
            'orderItem' => '@supplier_order_item_1_12',
            'quantity'  => "<decimal('35')>",
            'geocode'   => 'B6',
        ],
        'supplier_delivery_item_1_13' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_delivery_item::create' => [],
            ],
            'delivery'  => '@supplier_delivery_1',
            'orderItem' => '@supplier_order_item_1_13',
            'quantity'  => "<decimal('32')>",
            'geocode'   => 'C1',
        ],
        'supplier_delivery_item_1_14' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_delivery_item::create' => [],
            ],
            'delivery'  => '@supplier_delivery_1',
            'orderItem' => '@supplier_order_item_1_14',
            'quantity'  => "<decimal('24')>",
            'geocode'   => 'C2',
        ],
        'supplier_delivery_item_1_15' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_delivery_item::create' => [],
            ],
            'delivery'  => '@supplier_delivery_1',
            'orderItem' => '@supplier_order_item_1_15',
            'quantity'  => "<decimal('28')>",
            'geocode'   => 'C3',
        ],
        'supplier_delivery_item_1_16' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_delivery_item::create' => [],
            ],
            'delivery'  => '@supplier_delivery_1',
            'orderItem' => '@supplier_order_item_1_16',
            'quantity'  => "<decimal('36')>",
            'geocode'   => 'C4',
        ],
        'supplier_delivery_item_1_17' => [
            '__factory' => [
                '@ekyna_commerce.factory.supplier_delivery_item::create' => [],
            ],
            'delivery'  => '@supplier_delivery_1',
            'orderItem' => '@supplier_order_item_1_17',
            'quantity'  => "<decimal('34')>",
            'geocode'   => 'C5',
        ],
    ],
];
