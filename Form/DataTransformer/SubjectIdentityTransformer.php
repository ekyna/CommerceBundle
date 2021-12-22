<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\DataTransformer;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class SubjectIdentityTransformer
 * @package Ekyna\Bundle\CommerceBundle\Form\DataTransformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectIdentityTransformer implements DataTransformerInterface
{
    private SubjectProviderRegistryInterface $registry;


    /**
     * Constructor.
     *
     * @param SubjectProviderRegistryInterface $registry
     */
    public function __construct(SubjectProviderRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @inheritDoc
     *
     * @param SubjectIdentity $value
     */
    public function transform($value)
    {
        if (!empty($name = $value->getProvider())) {
            $provider = $this->registry->getProviderByName($name);

            try {
                $provider->reverseTransform($value);
            } catch (InvalidArgumentException $e) {
                throw new TransformationFailedException();
            }
        }

        return $value;
    }

    /**
     * @inheritDoc
     *
     * @param SubjectIdentity $value
     */
    public function reverseTransform($value)
    {
        $subject = $value->getSubject();

        $value->clear();

        if (null !== $subject) {
            $value->setSubject();

            try {
                $provider = $this->registry->getProviderBySubject($subject);

                $provider->transform($subject, $value);
            } catch (InvalidArgumentException $e) {
                throw new TransformationFailedException();
            }
        }

        return $value;
    }

}
