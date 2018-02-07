<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class StockIntegrityCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockIntegrityCommand extends ContainerAwareCommand
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var bool
     */
    private $fix = false;

    /**
     * @var array
     */
    private $unitIds;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;


    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:commerce:stock:integrity')
            ->setDescription('Checks the stock integrity.')
            ->addOption('fix', 'f', InputOption::VALUE_NONE);
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->fix = $input->getOption('fix');

        $this->connection = $this->getContainer()->get('doctrine.dbal.default_connection');

        $this->unitIds = [];

        $assignmentMap = [
            'id'         => 'ID',
            'product_id' => 'Product',
            'order_id'   => 'Order',
            'qty'        => 'Assignment qty',
            'sum'        => 'Items sum',
        ];

        // Assignment sold quantities
        /*$this->check('Assignment sold quantities', '
            SELECT a.id, i1.subject_identifier AS product_id, o.id AS order_id, a.sold_quantity AS qty, i1.quantity-SUM(line.quantity) AS sum
            FROM commerce_stock_assignment AS a
            JOIN commerce_order_item AS i1 ON i1.id=a.order_item_id
            LEFT JOIN commerce_order_item AS i2 ON i2.id=i1.parent_id
            LEFT JOIN commerce_order_item AS i3 ON i3.id=i2.parent_id
            LEFT JOIN commerce_order_item AS i4 ON i4.id=i3.parent_id
            LEFT JOIN commerce_order_item AS i5 ON i5.id=i4.parent_id
            JOIN commerce_order AS o ON (o.id=i1.order_id OR o.id=i2.order_id OR o.id=i3.order_id OR o.id=i4.order_id OR o.id=i5.order_id) 
            LEFT JOIN commerce_order_invoice AS invoice ON (invoice.order_id=o.id AND invoice.type=\'credit\')
            LEFT JOIN commerce_order_invoice_line AS line ON line.invoice_id=invoice.id
            GROUP BY a.id
            HAVING qty != sum;',
            $assignmentMap
        );*/
        $this->check('Assignment sold quantities',
            'SELECT a.id, i1.subject_identifier AS product_id, o.id AS order_id, SUM(a.sold_quantity) AS qty, 
                (
                    (i1.quantity * IFNULL(i2.quantity, 1) * IFNULL(i3.quantity, 1) * IFNULL(i4.quantity, 1) * IFNULL(i5.quantity, 1))-
                    IFNULL((
                        SELECT SUM(line.quantity)
                        FROM commerce_order_invoice_line AS line
                        JOIN commerce_order_invoice AS invoice ON invoice.id=line.invoice_id
                        WHERE line.order_item_id=i1.id
                          AND invoice.type=\'credit\'
                    ), 0)
                ) AS sum
            FROM commerce_stock_assignment AS a
            JOIN commerce_order_item AS i1 ON i1.id=a.order_item_id
            LEFT JOIN commerce_order_item AS i2 ON i2.id=i1.parent_id
            LEFT JOIN commerce_order_item AS i3 ON i3.id=i2.parent_id
            LEFT JOIN commerce_order_item AS i4 ON i4.id=i3.parent_id
            LEFT JOIN commerce_order_item AS i5 ON i5.id=i4.parent_id
            JOIN commerce_order AS o ON (o.id=i1.order_id OR o.id=i2.order_id OR o.id=i3.order_id OR o.id=i4.order_id OR o.id=i5.order_id) 
            GROUP BY a.order_item_id
            HAVING qty != sum;',
            $assignmentMap,
            function ($result) {
                if ($result['sum'] > $result['qty']) {
                    return new Action(sprintf(
                        "Product #%d Assignment #%d sold: %f < %f (increment is not yet supported)",
                        $result['product_id'],
                        $result['id'],
                        $result['qty'],
                        $result['sum']
                    ));
                }

                // TODO Prevent/fix unit overflow

                return new Fix(
                    sprintf("Product #%d Assignment #%d sold: %f => %f", $result['product_id'], $result['id'], $result['qty'], $result['sum']),
                    'UPDATE commerce_stock_assignment SET sold_quantity=? WHERE id=? LIMIT 1',
                    [$result['sum'], $result['id']]
                );
            }
        );

        // Assignment shipped quantities
        /*$this->check('Assignment shipped quantities', '
            SELECT a.id, i1.subject_identifier AS product_id, o.id AS order_id, a.sold_quantity AS qty, SUM(si.quantity)-SUM(ri.quantity) AS sum
            FROM commerce_stock_assignment AS a
            JOIN commerce_order_item AS i1 ON i1.id=a.order_item_id
            LEFT JOIN commerce_order_item AS i2 ON i2.id=i1.parent_id
            LEFT JOIN commerce_order_item AS i3 ON i3.id=i2.parent_id
            LEFT JOIN commerce_order_item AS i4 ON i4.id=i3.parent_id
            LEFT JOIN commerce_order_item AS i5 ON i5.id=i4.parent_id
            JOIN commerce_order AS o ON (o.id=i1.order_id OR o.id=i2.order_id OR o.id=i3.order_id OR o.id=i4.order_id OR o.id=i5.order_id)
            LEFT JOIN commerce_order_shipment AS s ON (s.order_id=o.id AND s.is_return=0)
            LEFT JOIN commerce_order_shipment_item AS si ON si.shipment_id=s.id
            LEFT JOIN commerce_order_shipment AS r ON (r.order_id=o.id AND r.is_return=1)
            LEFT JOIN commerce_order_shipment_item AS ri ON ri.shipment_id=r.id
            GROUP BY a.id
            HAVING qty != sum;',
            $assignmentMap
        );*/
        $this->check('Assignment shipped quantities', '
            SELECT a.id, i1.subject_identifier AS product_id, o.id AS order_id, SUM(a.shipped_quantity) AS qty, 
            IFNULL((
                SELECT SUM(si.quantity)
                FROM commerce_order_shipment_item AS si
                JOIN commerce_order_shipment AS s ON s.id=si.shipment_id
                WHERE si.order_item_id=i1.id
                  AND s.is_return=0
                  AND s.state IN (\'ready\', \'shipped\')
            ), 0) - IFNULL((
                SELECT SUM(si.quantity)
                FROM commerce_order_shipment_item AS si
                JOIN commerce_order_shipment AS s ON s.id=si.shipment_id
                WHERE si.order_item_id=i1.id
                  AND s.is_return=1
                  AND s.state IN (\'returned\')
            ), 0) AS sum
            FROM commerce_stock_assignment AS a
            JOIN commerce_order_item AS i1 ON i1.id=a.order_item_id
            LEFT JOIN commerce_order_item AS i2 ON i2.id=i1.parent_id
            LEFT JOIN commerce_order_item AS i3 ON i3.id=i2.parent_id
            LEFT JOIN commerce_order_item AS i4 ON i4.id=i3.parent_id
            LEFT JOIN commerce_order_item AS i5 ON i5.id=i4.parent_id
            JOIN commerce_order AS o ON (o.id=i1.order_id OR o.id=i2.order_id OR o.id=i3.order_id OR o.id=i4.order_id OR o.id=i5.order_id) 
            GROUP BY a.order_item_id
            HAVING qty != sum;',
            $assignmentMap,
            function($result) {
                if ($result['sum'] > $result['qty']) {
                    return new Action(sprintf(
                        "Product #%d Assignment #%d shipped: %f < %f (increment is not yet supported)",
                        $result['product_id'],
                        $result['id'],
                        $result['qty'],
                        $result['sum']
                    ));
                }

                // TODO Prevent/fix unit overflow

                return new Fix(
                    sprintf("Product #%d Assignment #%d shipped: %f => %f", $result['product_id'], $result['id'], $result['qty'], $result['sum']),
                    'UPDATE commerce_stock_assignment SET shipped_quantity=? WHERE id=? LIMIT 1',
                    [$result['sum'], $result['id']]
                );
            }
        );

        // Assignment sold < shipped
        $this->check('Assignment sold < shipped', '
            SELECT a.id, i1.subject_identifier AS product_id, o.id AS order_id, a.sold_quantity AS sold, a.shipped_quantity AS shipped
            FROM commerce_stock_assignment AS a
            JOIN commerce_order_item AS i1 ON i1.id=a.order_item_id
            LEFT JOIN commerce_order_item AS i2 ON i2.id=i1.parent_id
            LEFT JOIN commerce_order_item AS i3 ON i3.id=i2.parent_id
            LEFT JOIN commerce_order_item AS i4 ON i4.id=i3.parent_id
            JOIN commerce_order AS o ON (o.id=i1.order_id OR o.id=i2.order_id OR o.id=i3.order_id OR o.id=i4.order_id)
            WHERE a.sold_quantity<a.shipped_quantity
            GROUP BY a.id',
            [
                'id'         => 'ID',
                'product_id' => 'Product',
                'order_id'   => 'Order',
                'sold'       => 'Sold',
                'shipped'    => 'Shipped',
            ]
        );

        $unitMap = [
            'id'         => 'ID',
            'product_id' => 'Product',
            'qty'        => 'Unit qty',
            'sum'        => 'Assignments sum',
        ];

        // Unit sold quantities
        $this->check('Unit sold quantities', '
            SELECT u.id, u.product_id, u.sold_quantity AS qty, SUM(a.sold_quantity) AS sum,
                u.ordered_quantity AS ordered, u.adjusted_quantity AS adjusted
            FROM commerce_stock_unit AS u
            JOIN commerce_stock_assignment AS a ON a.stock_unit_id=u.id
            GROUP BY u.id
            HAVING qty != sum;',
            $unitMap,
            function($result) {
                if (0 < $result['ordered'] && $result['sum'] > $max = $result['ordered'] + $result['adjusted']) {
                    return new Action(sprintf(
                        "Product #%d Unit #%d sold: %f > %f (overflow)",
                        $result['product_id'],
                        $result['id'],
                        $result['sum'],
                        $max
                    ));
                }

                // TODO Prevent/fix unit overflow

                return new Fix(
                    sprintf("Product #%d Unit #%d sold: %f => %f", $result['product_id'], $result['id'], $result['qty'], $result['sum']),
                    'UPDATE commerce_stock_unit SET sold_quantity=? WHERE id=? LIMIT 1',
                    [$result['sum'], $result['id']],
                    $result['id']
                );
            }
        );

        // Unit shipped quantities
        $this->check('Unit shipped quantities', '
            SELECT u.id, u.product_id, u.shipped_quantity AS qty, SUM(a.shipped_quantity) AS sum,
                u.received_quantity AS received, u.adjusted_quantity AS adjusted
            FROM commerce_stock_unit AS u
            JOIN commerce_stock_assignment AS a ON a.stock_unit_id=u.id
            GROUP BY u.id
            HAVING qty != sum;',
            $unitMap,
            function($result) {
                if ($result['sum'] > $max = $result['received'] + $result['adjusted']) {
                    return new Action(sprintf(
                        "Product #%d Unit #%d shipped: %f > %f (overflow)",
                        $result['product_id'],
                        $result['id'],
                        $result['sum'],
                        $max
                    ));
                }

                // TODO Prevent/fix unit overflow

                return new Fix(
                    sprintf("Unit #%s shipped: %f => %f", $result['id'], $result['qty'], $result['sum']),
                    'UPDATE commerce_stock_unit SET shipped_quantity=? WHERE id=? LIMIT 1',
                    [$result['sum'], $result['id']],
                    $result['id']
                );
            }
        );

        // Unit ordered
        $this->check('Unit ordered quantities', '
            SELECT u.id, u.product_id, u.ordered_quantity AS qty, SUM(oi.quantity) AS sum
            FROM commerce_stock_unit AS u
            JOIN commerce_supplier_order_item AS oi ON oi.id=u.supplier_order_item_id
            JOIN commerce_supplier_order AS o ON o.id=oi.supplier_order_id
            WHERE o.state!=\'new\'
            GROUP BY u.id
            HAVING qty != sum;',
            $unitMap
        );

        // Unit received
        $this->check('Unit received quantities', '
            SELECT u.id, u.product_id, u.received_quantity AS qty, SUM(di.quantity) AS sum
            FROM commerce_stock_unit AS u
            JOIN commerce_supplier_order_item AS oi ON oi.id=u.supplier_order_item_id
            JOIN commerce_supplier_order AS o ON o.id=oi.supplier_order_id
            LEFT JOIN commerce_supplier_delivery_item AS di ON di.supplier_order_item_id=oi.id
            GROUP BY u.id
            HAVING qty != sum;',
            $unitMap
        );

        // Unit adjusted
        $this->check('Unit adjusted quantities', '
            SELECT u.id, u.product_id, u.adjusted_quantity AS qty, 
            IFNULL((
                SELECT SUM(a1.quantity) FROM commerce_stock_adjustment AS a1 
                WHERE a1.stock_unit_id=u.id AND a1.reason IN (\'credit\', \'found\')
            ), 0) - IFNULL((
                SELECT SUM(a2.quantity) FROM commerce_stock_adjustment AS a2 
                WHERE a2.stock_unit_id=u.id AND a2.reason IN (\'debit\', \'faulty\', \'improper\')
            ), 0) AS sum
            FROM commerce_stock_unit AS u
            HAVING qty != sum;',
            $unitMap
        );

        // Unit ordered<received or received<shipped or sold<shipped
        $this->check('Unit ordered<received or (ordered+adjusted)<sold or (received+adjusted)<shipped or sold<shipped', '
            SELECT u.id, u.product_id, 
                u.ordered_quantity AS ordered, 
                u.received_quantity AS received,
                u.adjusted_quantity AS adjusted,
                u.sold_quantity AS sold, 
                u.shipped_quantity AS shipped
            FROM commerce_stock_unit AS u
            WHERE u.ordered_quantity<u.received_quantity 
               OR (u.supplier_order_item_id IS NOT NULL AND (u.adjusted_quantity+u.ordered_quantity)<u.sold_quantity)
               OR (u.adjusted_quantity+u.received_quantity)<u.shipped_quantity
               OR u.sold_quantity<u.shipped_quantity;',
            [
                'id'         => 'ID',
                'product_id' => 'Product',
                'ordered'    => 'Ordered',
                'received'   => 'Received',
                'sold'       => 'Sold',
                'shipped'    => 'Shipped',
            ]
        );

        $this->updateSubjects();
    }

    /**
     * Display the query results.
     *
     * @param string   $title
     * @param string   $sql
     * @param array    $map
     * @param callable $fixer
     *
     * @return array
     */
    private function check($title, $sql, array $map, callable $fixer = null)
    {
        $fixes = [];

        $this->output->write($title . ': ');

        $results = $this->connection->executeQuery($sql);
        if (0 < $results->rowCount()) {
            $this->output->writeln('<error>error</error>');

            $table = new Table($this->output);
            $table->setHeaders(array_values($map));
            $ids = [];
            while (false !== $result = $results->fetch(\PDO::FETCH_ASSOC)) {
                $table->addRow(array_intersect_key($result, $map));
                $ids[] = $result['id'];

                if ($fixer) {
                    $fixes[] = $fixer($result);
                }
            }

            $table->render();

            $this->output->writeln('<comment>id IN (' . implode(',', $ids) . ')</comment>');
        } else {
            $this->output->writeln('<info>ok</info>');
        }

        $this->output->writeln('');

        $this->fix($fixes);

        $this->output->writeln('');

        return $fixes;
    }

    /**
     * Performs auto fix.
     *
     * @param array $actions
     */
    private function fix(array $actions)
    {
        if (empty($actions)) {
            return;
        }

        if (!$this->fix) {
            return;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('<question>Would you like to apply fixes ?</question>', false);
        if (!$helper->ask($this->input, $this->output, $question)) {
            return;
        }

        $this->connection->beginTransaction();
        try {
            foreach ($actions as $action) {
                if (!$action instanceof Fix) {
                    $this->output->writeln('<error>' . $action->getLabel() . '</error>');
                    continue;
                }

                $this->output->write($action->getLabel() . ': ');

                if (1 === $this->connection->executeUpdate($action->getQuery(), $action->getParameters())) {
                    $this->output->writeln('<info>ok</info>');

                    if ((0 < $id = $action->getUnitId()) && !in_array($id, $this->unitIds)) {
                        $this->unitIds[] = $id;
                    }

                } else {
                    $this->output->writeln('<info>error</info>');
                }
            }
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Updates the fixed unit's subjects.
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function updateSubjects()
    {
        if (empty($this->unitIds)) {
            return;
        }

        $updater = $this->getContainer()->get('ekyna_commerce.stock_subject_updater');
        $manager = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        /** @var AbstractStockUnit[] $units */
        $units = $manager
            ->getRepository(AbstractStockUnit::class)
            ->findBy(['id' => $this->unitIds]);

        foreach ($units as $unit) {
            $subject = $unit->getSubject();

            $this->output->write((string) $subject . ' ... ');

            if ($updater->update($subject)) {
                $manager->persist($subject);
                $this->output->writeln('<info>updated</info>');
            } else {
                $this->output->writeln('ok');
            }
        }

        $manager->flush();
    }
}

class Action
{
    private $label;

    /**
     * @param string $label
     */
    public function __construct($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
}

class Fix extends Action
{
    private $query;
    private $parameters;
    private $unitId;

    /**
     * @param string $label
     * @param string $query
     * @param array  $parameters
     * @param int    $unitId
     */
    public function __construct($label, $query, array $parameters, $unitId = null)
    {
        parent::__construct($label);

        $this->query = $query;
        $this->parameters = $parameters;
        $this->unitId = $unitId;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return int
     */
    public function getUnitId()
    {
        return $this->unitId;
    }
}