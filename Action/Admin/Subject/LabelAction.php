<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Subject;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\ListAction;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectLabelRenderer;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\RepositoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

use function array_filter;
use function array_map;

/**
 * Class LabelAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Subject
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LabelAction extends AbstractAction implements AdminActionInterface, RoutingActionInterface
{
    use RepositoryTrait;
    use HelperTrait;

    public function __construct(
        private readonly SubjectLabelRenderer $labelRenderer
    ) {
    }

    public function __invoke(): Response
    {
        $format = $this->request->attributes->get('format');

        $ids = (array)$this->request->query->get('id', []);
        $ids = array_map(fn($v) => (int)$v, $ids);
        $ids = array_filter($ids, fn($id) => 0 < $id);

        if (empty($ids)) {
            return $this->redirectToReferer(
                $this->generateResourcePath($this->context->getConfig()->getId(), ListAction::class)
            );
        }

        $subjects = (array)$this->getRepository()->findBy(['id' => $ids]);

        $pdf = $this->labelRenderer->render($format, $subjects);

        return new Response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_subject_label',
            'permission' => Permission::READ,
            'route'      => [
                'name'    => 'admin_%s_label',
                'path'    => '/label/{format}.pdf',
                'methods' => ['GET'],
            ],
            'button'     => [
                'label'        => 'button.print_label',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'default',
                'icon'         => 'barcode',
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->setDefault('format', SubjectLabelRenderer::FORMAT_LARGE);
    }
}
