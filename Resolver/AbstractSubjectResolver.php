<?php

namespace Ekyna\Bundle\CommerceBundle\Resolver;

use Ekyna\Component\Commerce\Subject\Resolver\AbstractSubjectResolver as BaseResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class AbstractSubjectResolver
 * @package Ekyna\Bundle\CommerceBundle\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSubjectResolver extends BaseResolver implements SubjectResolverInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;


    /**
     * Sets the urlGenerator.
     *
     * @param UrlGeneratorInterface $urlGenerator
     *
     * @return $this|AbstractSubjectResolver
     */
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;

        return $this;
    }
}
