<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service;

use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\View\AbstractViewType as BaseType;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AbstractViewType
 * @package Ekyna\Bundle\CommerceBundle\Service
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO PHP8 Union types
 */
abstract class AbstractViewType extends BaseType
{
    private UrlGeneratorInterface $urlGenerator;
    private TranslatorInterface $translator;
    private ResourceHelper $resourceHelper;
    private SubjectHelperInterface $subjectHelper;

    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator): void
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function setResourceHelper(ResourceHelper $resourceHelper): void
    {
        $this->resourceHelper = $resourceHelper;
    }

    public function setSubjectHelper(SubjectHelperInterface $subjectHelper): void
    {
        $this->subjectHelper = $subjectHelper;
    }

    /**
     * Returns the subject public url.
     *
     * @param SubjectRelativeInterface|SubjectInterface $subject
     */
    public function getPublicUrl($subject): ?string
    {
        return $this->subjectHelper->generatePublicUrl($subject, false);
    }

    /**
     * Returns the subject public url.
     *
     * @param SubjectRelativeInterface|SubjectInterface $subject
     */
    public function getPrivateUrl($subject): ?string
    {
        return $this->subjectHelper->generatePrivateUrl($subject, false);
    }

    /**
     * Generates the resource url.
     *
     * @param ResourceInterface|string $resource
     */
    protected function resourceUrl($resource, string $action, array $parameters = []): string
    {
        return $this->resourceHelper->generateResourcePath($resource, $action, $parameters, true);
    }

    /**
     * Generates the url.
     */
    protected function generateUrl(string $name, array $parameters = []): string
    {
        return $this->urlGenerator->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Translates the given message.
     */
    protected function trans(string $id, array $parameters = [], string $domain = null): string
    {
        return $this->translator->trans($id, $parameters, $domain);
    }

    /**
     * Resolves the item's subject.
     *
     * @see SubjectProviderRegistryInterface
     */
    protected function resolveItemSubject(Model\SaleItemInterface $item, bool $throw = true): ?SubjectInterface
    {
        return $this->subjectHelper->resolve($item, $throw);
    }
}
