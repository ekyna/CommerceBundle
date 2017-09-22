<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Security;

use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;

/**
 * Class AuthenticationSuccessHandler
 * @package Ekyna\Bundle\CommerceBundle\Service\Security
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OAuthAuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * @var CustomerProviderInterface
     */
    private $customerProvider;


    /**
     * Sets the customer provider.
     *
     * @param CustomerProviderInterface $customerProvider
     */
    public function setCustomerProvider($customerProvider)
    {
        $this->customerProvider = $customerProvider;
    }

    /**
     * @inheritdoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if (!$token instanceof OAuthToken) {
            return parent::onAuthenticationSuccess($request, $token);
        }

        if ($this->customerProvider->hasCustomer()) {
            return $this->httpUtils->createRedirectResponse($request, 'ekyna_user_account_index');
        }

        return $this->httpUtils->createRedirectResponse($request, 'fos_user_registration_register');
    }
}
