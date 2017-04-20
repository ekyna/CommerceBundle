<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Subject;

use Ekyna\Bundle\CommerceBundle\Service\Stock\ResupplyAlertHelper;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Bundle\UiBundle\Service\Modal\ModalRenderer;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Ekyna\Component\Commerce\Features;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

/**
 * Class ResupplyAlertController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Subject
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResupplyAlertController
{
    private ModalRenderer $modalRenderer;
    private CustomerProviderInterface $customerProvider;
    private ResupplyAlertHelper $helper;
    private Environment $engine;
    private Features $features;


    public function __construct(
        ModalRenderer $modalRenderer,
        CustomerProviderInterface $customerProvider,
        ResupplyAlertHelper $helper,
        Environment $engine,
        Features $features
    ) {
        $this->modalRenderer    = $modalRenderer;
        $this->customerProvider = $customerProvider;
        $this->helper           = $helper;
        $this->engine           = $engine;
        $this->features         = $features;
    }

    /**
     * Subject resupply alert subscribe action.
     */
    public function subscribe(Request $request): Response
    {
        if (!$this->features->isEnabled(Features::RESUPPLY_ALERT)) {
            throw new NotFoundHttpException();
        }

        $email = null;
        if ($customer = $this->customerProvider->getCustomer()) {
            $email = $customer->getEmail();
        }

        $this->helper->initialize($request);

        $modal = new Modal();
        $modal
            ->setTitle('Resupply alert')
            ->setSize(Modal::SIZE_NORMAL);

        $subscribe = $this->helper->createSubscribeForm(['email' => $email]);

        if (null === $result = $this->helper->handleSubscriptionForm($request)) {
            // Not posted
            $modal
                ->setType(Modal::TYPE_PRIMARY)
                ->setForm($subscribe->createView())
                ->addButton(Modal::BTN_SUBMIT)
                ->addButton(Modal::BTN_CANCEL);
        } elseif ($result) {
            // Success
            $modal
                ->setType(Modal::TYPE_SUCCESS)
                ->setContent($this->engine->render(
                    '@EkynaCommerce/ResupplyAlert/subscribe_success.html.twig'
                ))
                ->addButton(Modal::BTN_CLOSE);
        } else {
            $this->helper->initialize($request);

            $unsubscribe = $this->helper->createUnsubscribeForm([
                'email' => $subscribe->get('email')->getData()
            ]);

            // Already subscribed
            $modal
                ->setType(Modal::TYPE_WARNING)
                ->setForm($unsubscribe->createView())
                ->addButton(Modal::BTN_CONFIRM)
                ->addButton(Modal::BTN_CANCEL);
        }

        return $this->modalRenderer->render($modal);
    }

    /**
     * Subject resupply alert unsubscribe action.
     */
    public function unsubscribe(Request $request): Response
    {
        if (!$this->features->isEnabled(Features::RESUPPLY_ALERT)) {
            throw new NotFoundHttpException();
        }

        $email = null;
        if ($customer = $this->customerProvider->getCustomer()) {
            $email = $customer->getEmail();
        }

        $this->helper->initialize($request);

        $modal = new Modal();
        $modal
            ->setTitle('Resupply alert')
            ->setSize(Modal::SIZE_NORMAL);

        $unsubscribe = $this->helper->createUnsubscribeForm(['email' => $email]);

        if (null === $result = $this->helper->handleUnsubscriptionForm($request)) {
            // Not posted
            $modal
                ->setType(Modal::TYPE_PRIMARY)
                ->setForm($unsubscribe->createView())
                ->addButton(Modal::BTN_CONFIRM)
                ->addButton(Modal::BTN_CANCEL);
        } elseif($result) {
            // Success
            $modal
                ->setType(Modal::TYPE_WARNING)
                ->setContent($this->engine->render(
                    '@EkynaCommerce/ResupplyAlert/unsubscribe_success.html.twig'
                ))
                ->addButton(Modal::BTN_CLOSE);
        } else {
            $this->helper->initialize($request);

            $subscribe = $this->helper->createSubscribeForm([
                'email' => $unsubscribe->get('email')->getData()
            ]);

            // Already subscribed
            $modal
                ->setType(Modal::TYPE_WARNING)
                ->setForm($subscribe->createView())
                ->addButton(Modal::BTN_SUBMIT)
                ->addButton(Modal::BTN_CANCEL);
        }

        return $this->modalRenderer->render($modal);
    }
}
