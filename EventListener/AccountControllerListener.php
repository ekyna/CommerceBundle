<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ControllerInterface;
use Ekyna\Bundle\ResourceBundle\Exception\RedirectException;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Ekyna\Component\User\Service\UserProviderInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function is_array;

/**
 * Class AccountControllerListener
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AccountControllerListener
{
    private UserProviderInterface     $userProvider;
    private CustomerProviderInterface $customerProvider;
    private UrlGeneratorInterface     $urlGenerator;

    public function __construct(
        UserProviderInterface     $userProvider,
        CustomerProviderInterface $customerProvider,
        UrlGeneratorInterface     $urlGenerator
    ) {
        $this->userProvider = $userProvider;
        $this->customerProvider = $customerProvider;
        $this->urlGenerator = $urlGenerator;
    }

    public function onController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if (!$controller instanceof ControllerInterface) {
            return;
        }

        if (!$this->userProvider->hasUser()) {
            $redirect = $this->urlGenerator->generate('ekyna_user_security_login', [
                'target_path' => 'ekyna_user_account_index',
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            throw new RedirectException($redirect);
        }

        if (!$this->customerProvider->hasCustomer()) {
            $redirect = $this->urlGenerator->generate('ekyna_user_account_registration', [
                'target_path' => 'ekyna_user_account_index',
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            throw new RedirectException($redirect);
        }
    }
}
