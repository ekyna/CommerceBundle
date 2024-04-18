<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Search;

use Ekyna\Component\Resource\Bridge\Symfony\Elastica\SearchRepository;
use Ekyna\Component\Resource\Search\Request;
use Ekyna\Component\Resource\Search\Result;

/**
 * Class ProjectRepository
 * @package Ekyna\Bundle\CommerceBundle\Service\Search
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProjectRepository extends SearchRepository
{
    /**
     * @inheritDoc
     */
    protected function createResult($source, Request $request): ?Result
    {
        if (!$result = parent::createResult($source, $request)) {
            return null;
        }

        return $result
            ->setIcon('fa fa-folder')
            ->setRoute('admin_ekyna_commerce_project_read') // TODO Use resource/action
            ->setParameters(['projectId' => $source['id']]);
    }

    protected function getDefaultFields(): array
    {
        return [
            'name^2',
            'name.analyzed',
            'designation',
            'designation.analyzed',
        ];
    }
}
