<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Class NewsletterController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NewsletterController implements ControllerInterface
{
    use CustomerTrait;

    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function index(): Response
    {
        $customer = $this->getCustomer();

        $content = $this->twig->render('@EkynaCommerce/Account/Newsletter/index.html.twig', [
            'customer' => $customer,
        ]);

        return (new Response($content))->setPrivate();
    }
}
