<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Stat;

use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Stat\Calculator\StatCalculatorInterface;
use Ekyna\Component\Commerce\Stat\Model\StatInterface;
use Ekyna\Component\Commerce\Stat\Repository\StatRepositoryInterface;
use Ekyna\Component\Commerce\Stat\StatHelperInterface;
use Ekyna\Component\Commerce\Stat\Updater\AbstractStatUpdater;

use function get_class;

/**
 * Class StatUpdater
 * @package Ekyna\Bundle\CommerceBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatUpdater extends AbstractStatUpdater
{
    private ?StatRepositoryInterface $repository = null;

    public function __construct(
        StatCalculatorInterface          $calculator,
        StatHelperInterface              $helper,
        private readonly ManagerRegistry $registry,
        private readonly string          $statClass,
    ) {
        parent::__construct($calculator, $helper);
    }

    protected function persist(object $object): void
    {
        $this->registry->getManagerForClass(get_class($object))->persist($object);
    }

    protected function getRepository(): StatRepositoryInterface
    {
        if (null !== $this->repository) {
            return $this->repository;
        }

        $repository = $this->registry->getRepository($this->statClass);
        if (!$repository instanceof StatRepositoryInterface) {
            throw new UnexpectedTypeException($repository, StatRepositoryInterface::class);
        }

        return $this->repository = $repository;
    }

    protected function createNewStat(): StatInterface
    {
        return new $this->statClass();
    }
}
