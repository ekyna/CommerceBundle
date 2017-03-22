<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Subject;

use Ekyna\Bundle\AdminBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelper as BaseHelper;

/**
 * Class SubjectHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectHelper extends BaseHelper
{
    /**
     * @var ResourceHelper
     */
    protected $resourceHelper;


    /**
     * Constructor.
     *
     * @param SubjectProviderRegistryInterface $providerRegistry
     * @param ResourceHelper                   $resourceHelper
     */
    public function __construct(
        SubjectProviderRegistryInterface $providerRegistry,
        ResourceHelper $resourceHelper
    ) {
        parent::__construct($providerRegistry);

        $this->resourceHelper = $resourceHelper;
    }

    /**
     * Generates the admin url for the given subject relative's subject.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @return null|string
     *
     * @deprecated Use UrlGenerator
     */
    public function generateSubjectAdminUrl(SubjectRelativeInterface $relative)
    {
        if (null !== $subject = $this->resolve($relative, false)) {
            return $this->resourceHelper->generateResourcePath($subject);
        }

        return null;
    }
}
