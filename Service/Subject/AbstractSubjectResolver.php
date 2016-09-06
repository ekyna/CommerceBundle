<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Subject;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class AbstractSubjectResolver
 * @package Ekyna\Bundle\CommerceBundle\Service\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSubjectResolver implements SubjectResolverInterface
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
