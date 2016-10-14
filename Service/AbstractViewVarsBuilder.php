<?php

namespace Ekyna\Bundle\CommerceBundle\Service;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\View\ViewVarsBuilderInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class AbstractViewVarsBuilder
 * @package Ekyna\Bundle\CommerceBundle\Service
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractViewVarsBuilder implements ViewVarsBuilderInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var SubjectProviderRegistryInterface
     */
    private $subjectProviderRegistry;


    /**
     * Sets the url generator.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function setUrlGenerator($urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Sets the subject provider registry.
     *
     * @param SubjectProviderRegistryInterface $subjectProviderRegistry
     */
    public function setSubjectProviderRegistry($subjectProviderRegistry)
    {
        $this->subjectProviderRegistry = $subjectProviderRegistry;
    }

    /**
     * @inheritDoc
     */
    public function buildSaleViewVars(Model\SaleInterface $sale, array $options = [])
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function buildItemViewVars(Model\SaleItemInterface $item, array $options = [])
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function buildAdjustmentViewVars(Model\AdjustmentInterface $adjustment, array $options = [])
    {
        return [];
    }

    /**
     * Generates the url.
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return string
     */
    protected function generateUrl($name, array $parameters = [])
    {
        return $this->urlGenerator->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Resolves the item's subject.
     *
     * @param Model\SaleItemInterface $item
     *
     * @return mixed|null
     *
     * @see SubjectProviderRegistryInterface
     */
    protected function resolveItemSubject(Model\SaleItemInterface $item)
    {
        return $this->subjectProviderRegistry->resolveRelativeSubject($item);
    }
}
