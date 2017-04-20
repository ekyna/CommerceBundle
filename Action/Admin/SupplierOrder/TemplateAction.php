<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierOrder;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\RepositoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierTemplateInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

/**
 * Class TemplateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierOrder
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class TemplateAction extends AbstractAction implements AdminActionInterface, RoutingActionInterface
{
    use RepositoryTrait;

    private FormatterFactory $formatterFactory;
    private string           $defaultLocale;

    public function __construct(FormatterFactory $formatterFactory, string $defaultLocale)
    {
        $this->formatterFactory = $formatterFactory;
        $this->defaultLocale = $defaultLocale;
    }

    public function __invoke(): Response
    {
        $resource = $this->context->getResource();

        if (!$resource instanceof SupplierOrderInterface) {
            throw new UnexpectedTypeException($resource, SupplierOrderInterface::class);
        }

        $template = $this->getRepository(SupplierTemplateInterface::class)->find(
            $this->request->attributes->getInt('id')
        );

        if (!$template instanceof SupplierTemplateInterface) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $formatter = $this->formatterFactory->create(null, $resource->getCurrency()->getCode());

        $replacements = [
            '%number%' => $resource->getNumber(),
            '%date%'   => $resource->getOrderedAt() ? $formatter->date($resource->getOrderedAt()) : null,
        ];

        $locale = $this->request->query->get('_locale', $this->defaultLocale);

        $translation = $template->translate($locale);

        return new JsonResponse([
            'subject' => strtr($translation->getSubject(), $replacements),
            'message' => strtr($translation->getMessage(), $replacements),
        ]);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_supplier_order_template',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_template',
                'path'     => '/template/{id}',
                'resource' => true,
                'methods'  => ['GET'],
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'id' => '\d+',
        ]);
    }
}
