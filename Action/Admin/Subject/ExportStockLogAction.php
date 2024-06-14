<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Subject;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Component\Commerce\Stock\Export\StockSubjectLogExporter;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ExportStockLogAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Product
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ExportStockLogAction extends AbstractAction implements AdminActionInterface
{
    public function __construct(
        private readonly StockSubjectLogExporter $exporter
    ) {
    }

    public function __invoke(): Response
    {
        $subject = $this->context->getResource();

        if (!$subject instanceof StockSubjectInterface) {
            throw new NotFoundHttpException();
        }

        $file = $this->exporter->export($subject, null);

        return $file->download();
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_subject_export_stock_log',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_export_stock_log',
                'path'     => '/export-stock-log',
                'methods'  => ['GET'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'stock_subject.button.export_stock_log',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'default',
                'icon'         => 'download',
            ],
        ];
    }
}
