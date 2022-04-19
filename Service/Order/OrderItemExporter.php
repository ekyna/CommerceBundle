<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Order;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Ekyna\Component\Resource\Helper\File\Csv;
use Ekyna\Component\Resource\Helper\File\File;

use function sprintf;

/**
 * Class OrderItemExporter
 * @package Ekyna\Bundle\CommerceBundle\Service\Order
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderItemExporter
{
    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Exports the sample order's item lines.
     */
    public function exportSamples(DateTimeInterface $from = null, DateTimeInterface $to = null): File
    {
        if (!$from) {
            $from = new DateTime();
            $from
                ->modify('first day of january')
                ->setTime(0, 0);
        }

        if (!$to) {
            $to = new DateTime();
            $to
                ->modify('last day of december')
                ->setTime(23, 59, 59, 999999);
        }

        $sql = <<<SQL
SELECT
    i1.subject_identifier,
    i1.reference,
    i1.designation,
    sa.sold_quantity as quantity,
    i1.net_price as sell_price,
    su.net_price as buy_price,
    su.shipping_price as supply_price,
    o.number,
    g.name as customer_group,
    b.name as brand,
    c.name as category
FROM commerce_stock_assignment sa
JOIN commerce_stock_unit su ON su.id = sa.stock_unit_id
JOIN commerce_order_item i1 ON i1.id = sa.order_item_id
LEFT JOIN commerce_order_item i2 ON i2.id = i1.parent_id
LEFT JOIN commerce_order_item i3 ON i3.id = i2.parent_id
LEFT JOIN commerce_order_item i4 ON i4.id = i3.parent_id
LEFT JOIN commerce_order_item i5 ON i5.id = i4.parent_id
LEFT JOIN commerce_order_item i6 ON i6.id = i5.parent_id
JOIN commerce_order o ON o.id=IFNULL(i1.order_id, IFNULL(i2.order_id, IFNULL(i3.order_id, IFNULL(i4.order_id, IFNULL(i5.order_id, i6.order_id)))))
LEFT JOIN commerce_customer_group g ON g.id=o.customer_group_id
JOIN product_product p ON p.id=i1.subject_identifier
JOIN product_brand b ON b.id=p.brand_id
LEFT JOIN product_products_categories pc ON pc.product_id=p.id
LEFT JOIN product_category c ON c.id=pc.category_id
WHERE o.created_at BETWEEN :from AND :to
  AND o.is_sample=1 AND o.state IN ('accepted', 'completed')
SQL;

        $result = $this->connection->executeQuery($sql, [
            'from' => $from->format('Y-m-d H:i:s'),
            'to' => $to->format('Y-m-d H:i:s'),
        ]);

        $csv = Csv::create(sprintf(
            'sample-orders_%s_%s.csv',
            $from->format('Y-m-d'),
            $to->format('Y-m-d'))
        );

        $csv->addRow([
            'Product',
            'Reference',
            'Designation',
            'Quantity',
            'Sell price',
            'Buy price',
            'Supply price',
            'Order',
            'Customer group',
            'Brand',
            'Category',
        ]);

        while ($data = $result->fetchAssociative()) {
            $csv->addRow($data);
        }

        return $csv;
    }
}
