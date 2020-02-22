<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Front;

use Ekyna\Bundle\CommerceBundle\Service\Newsletter\SubscriptionHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class NewsletterController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Front
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NewsletterController
{
    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var EngineInterface
     */
    private $engine;


    /**
     * Constructor.
     *
     * @param SubscriptionHelper $subscriptionHelper
     * @param UrlGeneratorInterface $urlGenerator
     * @param EngineInterface $engine
     */
    public function __construct(
        SubscriptionHelper $subscriptionHelper,
        UrlGeneratorInterface $urlGenerator,
        EngineInterface $engine
    ) {
        $this->subscriptionHelper = $subscriptionHelper;
        $this->urlGenerator       = $urlGenerator;
        $this->engine             = $engine;
    }

    /**
     * Newsletter subscription action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function subscription(Request $request): Response
    {
        if ($this->subscriptionHelper->handleSubscription($request)) {
            return new RedirectResponse($this->urlGenerator->generate('ekyna_commerce_newsletter_subscribed'));
        }

        return new Response($this->engine->render('@EkynaCommerce/Newsletter/subscription.html.twig', [
            'form' => $this->subscriptionHelper->getSubscriptionForm()->createView(),
        ]));
    }

    /**
     * Newsletter subscribed action.
     *
     * @return Response
     */
    public function subscribed(): Response
    {
        return new Response($this->engine->render('@EkynaCommerce/Newsletter/subscribed.html.twig'));
    }
}
