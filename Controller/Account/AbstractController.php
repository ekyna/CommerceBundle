<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;

/**
 * Class AbstractController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AbstractController extends Controller
{
    /**
     * @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface
     */
    private $customer;


    /**
     * Returns the current (logged in) customer.
     *
     * @return \Ekyna\Component\Commerce\Customer\Model\CustomerInterface|null
     */
    protected function getCustomer()
    {
        if (null !== $this->customer) {
            return $this->customer;
        }

        $provider = $this->get('ekyna_commerce.customer.security_provider');

        if (!$provider->hasCustomer()) {
            throw $this->createAccessDeniedException('Customer not found.');
        }

        return $this->customer = $provider->getCustomer();
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
}
