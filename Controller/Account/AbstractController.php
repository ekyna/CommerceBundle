<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Ekyna\Bundle\CoreBundle\Exception\RedirectException;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class AbstractController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractController extends Controller
{
    /**
     * Returns the current (logged in) customer or redirect.
     *
     * @return CustomerInterface|null
     *
     * @throws RedirectException
     */
    protected function getCustomerOrRedirect()
    {
        if (null === $this->getUser()) {
            throw new RedirectException($this->generateUrl('fos_user_security_login', [
                'target_path' => 'ekyna_user_account_index',
            ], UrlGeneratorInterface::ABSOLUTE_URL));
        }

        $provider = $this->get('ekyna_commerce.customer.security_provider');

        if (!$provider->hasCustomer()) {
            throw new RedirectException($this->generateUrl('fos_user_registration_register', [
                'target_path' => 'ekyna_user_account_index',
            ], UrlGeneratorInterface::ABSOLUTE_URL));
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $provider->getCustomer();
    }

    /**
     * Returns the current user.
     *
     * @return UserInterface|null
     */
    protected function getUser()
    {
        return $this->get('ekyna_user.user.provider')->getUser();
    }

    /**
     * Adds the footer to the form.
     *
     * @param FormInterface $form
     * @param array         $options
     */
    protected function createFormFooter(FormInterface $form, array $options = [])
    {
        $submit_label = isset($options['submit_label']) ? $options['submit_label'] : 'ekyna_core.button.save';
        $submit_class = isset($options['submit_class']) ? $options['submit_class'] : 'primary';
        $submit_icon = isset($options['submit_icon']) ? $options['submit_icon'] : 'ok';

        $buttons = [
            'submit' => [
                'type'    => Type\SubmitType::class,
                'options' => [
                    'button_class' => $submit_class,
                    'label'        => $submit_label,
                    'attr'         => ['icon' => $submit_icon],
                ],
            ],
        ];

        if (isset($options['cancel_path'])) {
            $buttons['cancel'] = [
                'type'    => Type\ButtonType::class,
                'options' => [
                    'label'        => 'ekyna_core.button.cancel',
                    'button_class' => 'default',
                    'as_link'      => true,
                    'attr'         => [
                        'class' => 'form-cancel-btn',
                        'icon'  => 'remove',
                        'href'  => $options['cancel_path'],
                    ],
                ],
            ];
        }

        $form->add('actions', FormActionsType::class, [
            'buttons' => $buttons,
        ]);
    }

    /**
     * Validates the sale step.
     *
     * @param SaleInterface $sale
     * @param string        $step
     *
     * @return bool
     */
    protected function validateSaleStep(SaleInterface $sale, $step)
    {
        $validator = $this->get('ekyna_commerce.sale_step_validator');

        if ($validator->validate($sale, $step)) {
            return true;
        }

        $messages = [];
        foreach ($validator->getViolationList() as $violation) {
            $messages[] = $violation->getMessage();
        }

        if (!empty($messages)) {
            $this->addFlash(implode('<br>', $messages), 'danger');
        }

        return false;
    }
}
