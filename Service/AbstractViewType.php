<?php

namespace Ekyna\Bundle\CommerceBundle\Service;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\View\AbstractViewType as BaseType;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AbstractViewType
 * @package Ekyna\Bundle\CommerceBundle\Service
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractViewType extends BaseType
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var SubjectHelperInterface
     */
    private $subjectHelper;


    /**
     * Sets the url generator.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Sets the subject provider registry.
     *
     * @param SubjectHelperInterface $subjectHelper
     */
    public function setSubjectHelper(SubjectHelperInterface $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;
    }

    /**
     * Returns the subject public url.
     *
     * @param SubjectRelativeInterface|SubjectInterface $subject
     *
     * @return null|string
     */
    public function getPublicUrl($subject)
    {
        return $this->subjectHelper->generatePublicUrl($subject, false);
    }

    /**
     * Returns the subject public url.
     *
     * @param SubjectRelativeInterface|SubjectInterface $subject
     *
     * @return null|string
     */
    public function getPrivateUrl($subject)
    {
        return $this->subjectHelper->generatePrivateUrl($subject, false);
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
     * Translates the given message.
     *
     * @param string $id
     * @param array  $parameters
     * @param string $domain
     *
     * @return string
     */
    protected function trans($id, array $parameters = [], $domain = null)
    {
        return $this->translator->trans($id, $parameters, $domain);
    }

    /**
     * Resolves the item's subject.
     *
     * @param Model\SaleItemInterface $item
     * @param bool                    $throw
     *
     * @return mixed
     *
     * @see SubjectProviderRegistryInterface
     */
    protected function resolveItemSubject(Model\SaleItemInterface $item, $throw = true)
    {
        return $this->subjectHelper->resolve($item, $throw);
    }
}
