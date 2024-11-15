<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use RuntimeException;

use function gc_collect_cycles;
use function json_decode;
use function json_encode;
use function json_last_error;

use const JSON_ERROR_NONE;

/**
 * Class SaleItemDescriptionMigrator
 * @package Ekyna\Bundle\CommerceBundle\Service\Migration
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemDescriptionMigrator
{
    /** @var array<int, DescriptionConverterInterface>  */
    private array $converters = [];

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function addConverter(DescriptionConverterInterface $converter): void
    {
        $this->converters[] = $converter;
    }

    public function migrate(): void
    {
        $logger = $this->connection->getConfiguration()->getSQLLogger();
        $this->connection->getConfiguration()->setSQLLogger(null);

        $this->addConverter(
            new class implements DescriptionConverterInterface {
                public function convert(array &$result, string &$description): void
                {
                    if (empty($description)) {
                        return;
                    }

                    $result['default'] = $description;
                    $description = '';
                }
            }
        );

        foreach (['cart', 'order', 'quote'] as $source) {
            $this->updateItems($source);
        }

        $this->connection->getConfiguration()->setSQLLogger($logger);
    }

    private function updateItems(string $source): void
    {
        $fetch = $this->connection->prepare(
            <<<SQL
            SELECT id, description 
            FROM commerce_{$source}_item 
            WHERE description IS NOT NULL
            LIMIT :limit OFFSET :offset 
            SQL
        );

        $update = $this->connection->prepare(
            <<<SQL
            UPDATE commerce_{$source}_item
            SET description = :description
            WHERE id = :id
            LIMIT 1 
            SQL
        );

        $size = 20;
        $page = 0;

        do {
            $fetch->bindValue('limit', $size, ParameterType::INTEGER);
            $fetch->bindValue('offset', $size * $page, ParameterType::INTEGER);
            $page++;

            $result = $fetch
                ->executeQuery()
                ->fetchAllAssociative();

            foreach ($result as $row) {
                if (empty($old = $row['description'])) {
                    continue;
                }

                json_decode($old, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Valid JSON
                    continue;
                }

                $new = [];
                foreach ($this->converters as $converter) {
                    $converter->convert($new, $old);
                }

                $new = json_encode($new);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new RuntimeException('Failed to convert value');
                }

                $update->executeQuery([
                    'id'          => $row['id'],
                    'description' => $new,
                ]);
            }

            gc_collect_cycles();

        } while (!empty($result));
    }
}
