<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\OrderList;

use Ekyna\Bundle\AdminBundle\Service\Menu\MenuBuilder;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Table\TableFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Class AbstractListController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin\OrderList
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractListController
{
    protected static string $resource;
    protected static string $template;

    private ResourceHelper        $resourceHelper;
    private TableFactoryInterface $tableFactory;
    private MenuBuilder           $menuBuilder;
    private Environment           $twig;

    public function __construct(
        ResourceHelper        $resourceHelper,
        TableFactoryInterface $tableFactory,
        MenuBuilder           $menuBuilder,
        Environment           $twig
    ) {
        $this->resourceHelper = $resourceHelper;
        $this->tableFactory = $tableFactory;
        $this->menuBuilder = $menuBuilder;
        $this->twig = $twig;
    }

    public function __invoke(Request $request): Response
    {
        $this->resourceHelper->isGranted(Permission::LIST, static::$resource);

        $configuration = $this->resourceHelper->getResourceConfig(static::$resource);

        if (!$type = $configuration->getData('table')) {
            throw new LogicException('Table is not configured.');
        }

        $name = $configuration->getName();

        $table = $this->tableFactory->createTable($name, $type);

        $response = $table->handleRequest($request);
        if ($response instanceof Response) {
            return $response;
        }

        $this
            ->menuBuilder
            ->breadcrumbAppend([
                'name'         => 'order_list' . $name,
                'route'        => 'admin_ekyna_commerce_list_' . $name,
                'label'        => $configuration->getResourceLabel(true),
                'trans_domain' => $configuration->getTransDomain(),
                'resource'     => static::$resource,
            ]);

        $content = $this->twig->render(static::$template, [
            'resources' => $table->createView(),
        ]);

        return (new Response($content))->setPrivate();
    }
}
