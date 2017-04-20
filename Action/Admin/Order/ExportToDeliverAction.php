<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Order;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\ListAction;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\CommerceBundle\Form\Type\Order\SubjectOrderExportType;
use Ekyna\Bundle\CommerceBundle\Model\SubjectOrderExport;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectOrderExporter;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Helper\File\File;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

/**
 * Class ExportToDeliverAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Order
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ExportToDeliverAction extends AbstractAction implements AdminActionInterface
{
    use FormTrait;
    use HelperTrait;
    use BreadcrumbTrait;
    use TemplatingTrait;

    private SubjectOrderExporter $subjectOrderExporter;

    public function __construct(SubjectOrderExporter $subjectOrderExporter)
    {
        $this->subjectOrderExporter = $subjectOrderExporter;
    }

    public function __invoke(): Response
    {
        $data = new SubjectOrderExport();

        $form = $this->createForm(SubjectOrderExportType::class, $data);

        FormUtil::addFooter($form, [
            'submit_label' => t('button.export', [], 'EkynaUi'),
            'cancel_path'  => $this->generateResourcePath('ekyna_commerce.order', ListAction::class),
        ]);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $path = $this->subjectOrderExporter->export($data);

            return File::buildResponse($path, [
                'file_name' => 'orders-to-deliver.csv',
            ]);
        }

        $this->breadcrumbFromContext($this->context);

        return $this->render($this->options['template'], [
            'form' => $form->createView(),
        ]);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_order_export_to_deliver',
            'permission' => Permission::READ,
            'route'      => [
                'name'    => 'admin_%s_export_to_deliver',
                'path'    => '/export-to-deliver',
                'methods' => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'order.header.export_to_deliver',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'default',
                'icon'         => 'download',
            ],
            'options'    => [
                'template' => '@EkynaCommerce/Admin/Order/export_to_deliver.html.twig',
            ],
        ];
    }
}
