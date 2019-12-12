<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Subject;

use Ekyna\Bundle\AdminBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelper as BaseHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
    private $resourceHelper;


    /**
     * @inheritDoc
     */
    public function __construct(
        SubjectProviderRegistryInterface $registry,
        EventDispatcherInterface $eventDispatcher,
        ResourceHelper $resourceHelper
    ) {
        parent::__construct($registry, $eventDispatcher);

        $this->resourceHelper = $resourceHelper;
    }

    /**
     * @inheritDoc
     */
    public function generateAddToCartUrl($subject, $path = true): ?string
    {
        $subject = $this->resolveSubject($subject);

        $type = $path ? UrlGeneratorInterface::ABSOLUTE_PATH : UrlGeneratorInterface::ABSOLUTE_URL;

        return $this->resourceHelper->getUrlGenerator()->generate(
            'ekyna_commerce_subject_add_to_cart',
            [
                'provider'   => $subject::getProviderName(),
                'identifier' => $subject->getIdentifier(),
            ],
            $type
        );
    }

    /**
     * Resolves the subject.
     *
     * @param SubjectRelativeInterface|SubjectInterface $subject
     *
     * @return SubjectInterface
     */
    private function resolveSubject($subject)
    {
        if ($subject instanceof SubjectRelativeInterface) {
            if (null === $subject = $this->resolve($subject, false)) {
                return null;
            }
        }

        if (!$subject instanceof SubjectInterface) {
            throw new InvalidArgumentException("Expected instance of ".SubjectInterface::class);
        }

        return $subject;
    }

    /**
     * @inheritDoc
     */
    public function generatePublicUrl($subject, bool $path = true): ?string
    {
        if (null === $subject = $this->resolveSubject($subject)) {
            return null;
        }

        return $this->resourceHelper->generatePublicUrl($subject, !$path);
    }

    /**
     * @inheritDoc
     */
    public function generateImageUrl($subject, bool $path = true): ?string
    {
        if (null === $subject = $this->resolveSubject($subject)) {
            return null;
        }

        return $this->resourceHelper->generateImageUrl($subject, !$path);
    }

    /**
     * @inheritDoc
     */
    public function generatePrivateUrl($subject, bool $path = true): ?string
    {
        $subject = $this->resolveSubject($subject);

        return $this->resourceHelper->generateResourcePath($subject, 'show', [], !$path);
    }
}
