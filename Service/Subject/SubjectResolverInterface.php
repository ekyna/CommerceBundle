<?php


namespace Ekyna\Bundle\CommerceBundle\Service\Subject;

use Ekyna\Component\Commerce\Subject\Resolver\SubjectResolverInterface as BaseInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Interface SubjectResolverInterface
 * @package Ekyna\Bundle\CommerceBundle\Service\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SubjectResolverInterface extends BaseInterface
{
    /**
     * Sets the urlGenerator.
     *
     * @param UrlGeneratorInterface $urlGenerator
     *
     * @return $this|AbstractSubjectResolver
     */
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator);
}
