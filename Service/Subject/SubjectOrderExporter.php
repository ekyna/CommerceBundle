<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Subject;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class SubjectOrderExporter
 * @package Ekyna\Bundle\CommerceBundle\Service\Subject
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubjectOrderExporter
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var string
     */
    private $assignmentClass;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface  $urlGenerator
     * @param string                 $assignmentClass
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        string $assignmentClass
    ) {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->assignmentClass = $assignmentClass;
    }

    /**
     * @param StockSubjectInterface $subject
     *
     * @return string
     */
    public function export(StockSubjectInterface $subject): string
    {
        $connection = $this->entityManager->getConnection();

        if ($connection->getDatabasePlatform()->getName() !== 'mysql') {
            throw new RuntimeException("Unsupported database platform");
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

        /** @noinspection SqlResolve */
        $select = $connection->executeQuery(
            <<<SQL
            SELECT o.id, 
                   o.number, 
                   o.company, 
                   DATE(o.created_at) as created_at, 
                   ROUND(SUM(a.sold_quantity)-SUM(a.shipped_quantity)) as quantity
            FROM $assignmentTable AS a
            JOIN $itemTable AS i ON i.id=a.order_item_id
            JOIN $orderTable AS o ON o.id=i.order_id
            WHERE i.subject_provider=:subject_provider
              AND i.subject_identifier=:subject_identifier
            GROUP BY a.id
            HAVING SUM(a.sold_quantity)>SUM(a.shipped_quantity)
            ORDER BY o.created_at DESC
            SQL,
            [
                'subject_provider'   => $subject::getProviderName(),
                'subject_identifier' => $subject->getIdentifier(),
            ]
        );

        $rows = [
            ['Number', 'Date', 'Company', 'Remaining quantity', 'Admin URL'],
        ];

        while (false !== $data = $select->fetch(\PDO::FETCH_ASSOC)) {
            $rows[] = [
                $data['number'],
                $data['created_at'],
                $data['company'],
                $data['quantity'],
                $this->urlGenerator->generate('ekyna_commerce_order_admin_show', [
                    'orderId' => $data['id'],
                ], UrlGeneratorInterface::ABSOLUTE_URL),
            ];
        }

        return $this->createFile($rows, 'subject_pending_orders');
    }

    /**
     * Creates the CSV file.
     *
     * @param array  $rows
     * @param string $name
     *
     * @return string
     */
    protected function createFile(array $rows, string $name): string
    {
        if (false === $path = tempnam(sys_get_temp_dir(), $name)) {
            throw new RuntimeException("Failed to create temporary file.");
        }

        if (false === $handle = fopen($path, "w")) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }

        foreach ($rows as $row) {
            fputcsv($handle, $row, ';', '"');
        }

        fclose($handle);

        return $path;
    }
}
