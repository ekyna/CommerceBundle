<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Class InventoryController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InventoryController
{
    private SubjectProviderRegistryInterface $subjectProviderRegistry;
    private Environment                      $twig;

    public function __construct(SubjectProviderRegistryInterface $subjectProviderRegistry, Environment $twig)
    {
        $this->subjectProviderRegistry = $subjectProviderRegistry;
        $this->twig = $twig;
    }

    public function subjectStock(Request $request): Response
    {
        $content = '';

        try {
            $provider = $this
                ->subjectProviderRegistry
                ->getProvider($request->attributes->get('provider'));

            $subject = $provider->getRepository()->find($request->attributes->getInt('identifier'));

            if ($subject instanceof StockSubjectInterface) {
                $content = $this->twig->render('@EkynaCommerce/Admin/Inventory/subject.html.twig', [
                    'subject' => $subject,
                ]);
            }
        } catch (InvalidArgumentException $e) {
        }

        return (new Response($content))->setPrivate();
    }
}
