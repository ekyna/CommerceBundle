<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Front;

use Ekyna\Bundle\CommerceBundle\Service\Newsletter\SubscriptionHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Class NewsletterController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Front
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NewsletterController
{
    private SubscriptionHelper    $subscriptionHelper;
    private UrlGeneratorInterface $urlGenerator;
    private Environment           $twig;


    public function __construct(
        SubscriptionHelper $subscriptionHelper,
        UrlGeneratorInterface $urlGenerator,
        Environment $twig
    ) {
        $this->subscriptionHelper = $subscriptionHelper;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
    }

    /**
     * Newsletter subscription action.
     */
    public function subscription(Request $request): Response
    {
        if ($this->subscriptionHelper->handleSubscription($request)) {
            return new RedirectResponse($this->urlGenerator->generate('ekyna_commerce_newsletter_subscribed'));
        }

        $form = $this->subscriptionHelper->getSubscriptionForm();

        return new Response($this->twig->render('@EkynaCommerce/Newsletter/subscription.html.twig', [
            'form' => $form ? $form->createView() : null,
        ]));
    }

    /**
     * Newsletter subscribed action.
     */
    public function subscribed(): Response
    {
        return new Response($this->twig->render('@EkynaCommerce/Newsletter/subscribed.html.twig'));
    }
}
