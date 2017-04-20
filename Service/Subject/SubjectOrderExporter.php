<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Subject;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\CommerceBundle\Model\SubjectOrderExport;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Ekyna\Component\Resource\Helper\File\Csv;
use PDO;

/**
 * Class SubjectOrderExporter
 * @package Ekyna\Bundle\CommerceBundle\Service\Subject
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubjectOrderExporter
{
    private EntityManagerInterface           $entityManager;
    private SubjectProviderRegistryInterface $subjectProviderRegistry;
    private ResourceHelper                   $resourceHelper;
    private string                           $assignmentClass;

    public function __construct(
        EntityManagerInterface           $entityManager,
        SubjectProviderRegistryInterface $subjectProviderRegistry,
        ResourceHelper                   $resourceHelper,
        string                           $assignmentClass
    ) {
        $this->entityManager = $entityManager;
        $this->subjectProviderRegistry = $subjectProviderRegistry;
        $this->resourceHelper = $resourceHelper;
        $this->assignmentClass = $assignmentClass;
    }

    /**
     * Exports the orders to be delivered for
     */
    public function export(SubjectOrderExport $data): string
    {
        /** @var SubjectInterface[] $subjects */
        $subjects = [];
        foreach ($data->getSubjects() as $subject) {
            if ($subject instanceof SubjectIdentity) {
                if (!$provider = $this->subjectProviderRegistry->getProviderByName($subject->getProvider())) {
                    throw new RuntimeException('Unsupported subject');
                }

                $subject = $provider->reverseTransform($subject);
            }

            if ($subject instanceof SubjectInterface) {
                $subjects[] = $subject;

                continue;
            }

            throw new UnexpectedTypeException($subject, [
                SubjectInterface::class,
                SubjectIdentity::class,
            ]);
        }

        $orders = $this->fetch($subjects);

        $headers = ['Number', 'Date', 'Company', 'Email', 'Sample'];
        foreach ($subjects as $subject) {
            $headers[] = $subject->getReference();
        }
        $headers[] = 'Admin URL';

        $file = Csv::create('subject_pending_orders.csv');
        $file->addRow($headers);

        foreach ($orders as $data) {
            $row = [
                $data['number'],
                $data['created_at'],
                $data['company'],
                $data['email'],
                $data['is_sample'],
            ];

            foreach ($subjects as $subject) {
                $row[] = $data[$subject->getReference()];
            }

            $row[] = $this->resourceHelper->generateResourcePath('ekyna_commerce.order', ReadAction::class, [
                'orderId' => $data['id'],
            ], true);

            $file->addRow($row);
        }

        return $file->close();
    }

    /**
     * Fetch the orders to deliver for the given subjects.
     *
     * @param SubjectInterface[] $subjects
     *
     * @return array
     */
    protected function fetch(array $subjects): array
    {
        $model = [
            'id'         => '',
            'number'     => '',
            'created_at' => '',
            'company'    => '',
            'email'      => '',
            'is_sample'  => '',
        ];
        foreach ($subjects as $subject) {
            $model[$subject->getReference()] = '';
        }
        $model['url'] = '';

        $orders = [];

        $select = $this->createQuery();

        foreach ($subjects as $subject) {
            $result = $select->executeQuery([
                'provider'   => $subject::getProviderName(),
                'identifier' => $subject->getIdentifier(),
            ]);

            while (false !== $data = $result->fetchAssociative()) {
                if (isset($orders[$data['id']])) {
                    $orders[$data['id']][$subject->getReference()] = $data['quantity'];
                    continue;
                }

                $url = $this->resourceHelper->generateResourcePath('ekyna_commerce.order', ReadAction::class, [
                    'orderId' => $data['id'],
                ], true);

                $order = array_replace($model, [
                    'id'                     => $data['id'],
                    'number'                 => $data['number'],
                    'created_at'             => $data['created_at'],
                    'company'                => $data['company'],
                    'email'                  => $data['email'],
                    'is_sample'              => $data['is_sample'] ? 'Yes' : '',
                    $subject->getReference() => $data['quantity'],
                    'url'                    => $url,
                ]);

                $orders[$data['id']] = $order;
            }
        }

        return $orders;
    }

    /**
     * Creates the select orders query.
     */
    protected function createQuery(): Statement
    {
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        if (!$platform instanceof MySQLPlatform) {
            throw new RuntimeException('Unsupported database platform');
        }

        $assignmentMetadata = $this->entityManager->getClassMetadata($this->assignmentClass);
        $assignmentTable = $assignmentMetadata->getTableName();

        $itemMetadata = $this
            ->entityManager
            ->getClassMetadata(
                $assignmentMetadata->getAssociationMapping('orderItem')['targetEntity']
            );

        $itemTable = $itemMetadata->getTableName();

        $orderTable = $this
            ->entityManager
            ->getClassMetadata(
                $itemMetadata->getAssociationMapping('order')['targetEntity']
            )->getTableName();

        /** @noinspection SqlDialectInspection */
        return $connection->prepare(
            <<<SQL
            SELECT o.id,
                   o.number, 
                   o.company, 
                   o.email, 
                   o.is_sample, 
                   DATE(o.created_at) as created_at,
                   SUM(d.quantity) as quantity
            FROM (
                SELECT 
                    IFNULL(i1.order_id, 
                        IFNULL(i2.order_id, 
                            IFNULL(i3.order_id, 
                                IFNULL(i4.order_id, 
                                    IFNULL(i5.order_id, i6.order_id)
                                )
                            )
                        )
                    ) as id, 
                    ROUND(SUM(a.sold_quantity)-SUM(a.shipped_quantity)) as quantity
                FROM $assignmentTable AS a
                JOIN $itemTable AS i1 ON i1.id=a.order_item_id
                LEFT JOIN $itemTable AS i2 ON i2.id=i1.parent_id
                LEFT JOIN $itemTable AS i3 ON i3.id=i2.parent_id
                LEFT JOIN $itemTable AS i4 ON i4.id=i3.parent_id
                LEFT JOIN $itemTable AS i5 ON i5.id=i4.parent_id
                LEFT JOIN $itemTable AS i6 ON i6.id=i5.parent_id
                WHERE i1.subject_provider=:provider AND i1.subject_identifier=:identifier
                GROUP BY a.id
                HAVING SUM(a.sold_quantity)>SUM(a.shipped_quantity)
            ) as d
            JOIN $orderTable AS o ON o.id=d.id
            GROUP BY o.id
            ORDER BY o.created_at DESC
            SQL
        );
    }
}
