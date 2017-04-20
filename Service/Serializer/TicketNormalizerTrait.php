<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Trait TicketNormalizerTrait
 * @package Ekyna\Bundle\CommerceBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait TicketNormalizerTrait
{
    protected AuthorizationCheckerInterface $authorization;


    /**
     * Sets the authorization.
     *
     * @param AuthorizationCheckerInterface $authorization
     */
    public function setAuthorization(AuthorizationCheckerInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * Returns whether permission is granted.
     *
     * @param $attributes
     * @param $subject
     *
     * @return bool
     */
    protected function isGranted($attributes, $subject): bool
    {
        return $this->authorization->isGranted($attributes, $subject);
    }
}
