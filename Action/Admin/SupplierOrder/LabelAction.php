<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierOrder;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectLabelRenderer;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

use function array_filter;
use function array_map;
use function in_array;
use function intval;

/**
 * Class LabelAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierOrder
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LabelAction extends AbstractAction implements AdminActionInterface
{
    public function __construct(
        private readonly SubjectHelperInterface $subjectHelper,
        private readonly SubjectLabelRenderer $labelRenderer
    ) {
    }

    public function __invoke(): Response
    {
        /** @var SupplierOrderInterface $resource */
        $resource = $this->context->getResource();

        if (!$resource instanceof SupplierOrderInterface) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        // Read request id parameter as array of positive integers
        $ids = array_filter(
            array_map(
                fn($id) => intval($id),
                (array)$this->request->query->get('id', [])
            ),
            fn($id) => 0 < $id
        );

        // Load subjects from ids
        $subjects = [];
        foreach ($resource->getItems() as $item) {
            if (in_array(intval($item->getId()), $ids, true)) {
                $subjects[] = $this->subjectHelper->resolve($item);
            }
        }

        if (empty($subjects)) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $pdf = $this->labelRenderer->render(SubjectLabelRenderer::FORMAT_LARGE, $subjects, [
            'supplierOrder' => $resource,
            'geocode'       => $this->request->query->get('geocode'),
        ]);

        return new Response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_supplier_order_label',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_label',
                'path'     => '/label.pdf',
                'resource' => true,
                'methods'  => ['GET'],
            ],
        ];
    }
}
