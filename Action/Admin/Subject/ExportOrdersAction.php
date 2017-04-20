<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Subject;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Model\SubjectOrderExport;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectOrderExporter;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\AuthorizationTrait;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Helper\File\Csv;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class RefreshStockAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Product
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ExportOrdersAction extends AbstractAction implements AdminActionInterface
{
    use AuthorizationTrait;

    private SubjectOrderExporter $subjectOrderExporter;

    public function __construct(SubjectOrderExporter $subjectOrderExporter)
    {
        $this->subjectOrderExporter = $subjectOrderExporter;
    }

    public function __invoke(): Response
    {
        $this->isGranted(OrderInterface::class, Permission::READ);

        $subject = $this->context->getResource();

        if (!$subject instanceof StockSubjectInterface) {
            throw new NotFoundHttpException();
        }

        $data = new SubjectOrderExport();
        $data->addSubject($subject);

        $path = $this->subjectOrderExporter->export($data);

        return Csv::buildResponse($path, [
            'file_name' => $subject->getReference() . '_pending-orders.csv',
        ]);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_subject_export_orders',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_export_orders',
                'path'     => '/export-orders',
                'methods'  => ['GET'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'order.label.plural',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'default',
                'icon'         => 'download',
            ],
        ];
    }
}
