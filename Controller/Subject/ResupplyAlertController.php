<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Subject;

use Ekyna\Bundle\CommerceBundle\Service\Stock\ResupplyAlertHelper;
use Ekyna\Bundle\CoreBundle\Modal;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Ekyna\Component\Commerce\Features;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class ResupplyAlertController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Subject
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResupplyAlertController
{
    /**
     * @var Modal\Renderer
     */
    private $modalRenderer;

    /**
     * @var CustomerProviderInterface
     */
    private $customerProvider;

    /**
     * @var ResupplyAlertHelper
     */
    private $helper;

    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * @var Features
     */
    private $features;


    /**
     * Constructor.
     *
     * @param Modal\Renderer            $modalRenderer
     * @param CustomerProviderInterface $customerProvider
     * @param ResupplyAlertHelper       $helper
     * @param EngineInterface           $engine
     * @param Features                  $features
     */
    public function __construct(
        Modal\Renderer $modalRenderer,
        CustomerProviderInterface $customerProvider,
        ResupplyAlertHelper $helper,
        EngineInterface $engine,
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
     *
     * @param Request $request
     *
     * @return Response
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

        $modal = new Modal\Modal();
        $modal
            ->setTitle('Resupply alert')
            ->setSize(Modal\Modal::SIZE_NORMAL);

        $subscribe = $this->helper->createSubscribeForm(['email' => $email]);

        if (null === $result = $this->helper->handleSubscriptionForm($request)) {
            // Not posted
            $modal
                ->setType(Modal\Modal::TYPE_PRIMARY)
                ->setContent($subscribe->createView())
                ->addButton(Modal\Modal::BTN_SUBMIT)
                ->addButton(Modal\Modal::BTN_CANCEL);
        } elseif ($result) {
            // Success
            $modal
                ->setType(Modal\Modal::TYPE_SUCCESS)
                ->setContent($this->engine->render(
                    '@EkynaCommerce/ResupplyAlert/subscribe_success.html.twig'
                ))
                ->addButton(Modal\Modal::BTN_CLOSE);
        } else {
            $this->helper->initialize($request);

            $unsubscribe = $this->helper->createUnsubscribeForm([
                'email' => $subscribe->get('email')->getData()
            ]);

            // Already subscribed
            $modal
                ->setType(Modal\Modal::TYPE_WARNING)
                ->setContent($unsubscribe->createView())
                ->addButton(Modal\Modal::BTN_CONFIRM)
                ->addButton(Modal\Modal::BTN_CANCEL);
        }

        return $this->modalRenderer->render($modal);
    }

    /**
     * Subject resupply alert unsubscribe action.
     *
     * @param Request $request
     *
     * @return Response
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

        $modal = new Modal\Modal();
        $modal
            ->setTitle('Resupply alert')
            ->setSize(Modal\Modal::SIZE_NORMAL);

        $unsubscribe = $this->helper->createUnsubscribeForm(['email' => $email]);

        if (null === $result = $this->helper->handleUnsubscriptionForm($request)) {
            // Not posted
            $modal
                ->setType(Modal\Modal::TYPE_PRIMARY)
                ->setContent($unsubscribe->createView())
                ->addButton(Modal\Modal::BTN_CONFIRM)
                ->addButton(Modal\Modal::BTN_CANCEL);
        } elseif($result) {
            // Success
            $modal
                ->setType(Modal\Modal::TYPE_WARNING)
                ->setContent($this->engine->render(
                    '@EkynaCommerce/ResupplyAlert/unsubscribe_success.html.twig'
                ))
                ->addButton(Modal\Modal::BTN_CLOSE);
        } else {
            $this->helper->initialize($request);

            $subscribe = $this->helper->createSubscribeForm([
                'email' => $unsubscribe->get('email')->getData()
            ]);

            // Already subscribed
            $modal
                ->setType(Modal\Modal::TYPE_WARNING)
                ->setContent($subscribe->createView())
                ->addButton(Modal\Modal::BTN_SUBMIT)
                ->addButton(Modal\Modal::BTN_CANCEL);
        }

        return $this->modalRenderer->render($modal);
    }
}
