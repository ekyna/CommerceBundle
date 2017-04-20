<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Customer;

use DateTime;
use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\ListAction;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerExportType;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Customer\Export\CustomerExport;
use Ekyna\Component\Commerce\Customer\Export\CustomerExporter;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

/**
 * Class ExportAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Customer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ExportAction extends AbstractAction implements AdminActionInterface
{
    use FormTrait;
    use HelperTrait;
    use BreadcrumbTrait;
    use TemplatingTrait;

    private CustomerExporter $customerExporter;

    public function __construct(CustomerExporter $customerExporter)
    {
        $this->customerExporter = $customerExporter;
    }

    public function __invoke(): Response
    {
        $data = new CustomerExport();
        $data
            ->setFrom(new DateTime('first day of january'))
            ->setTo(new DateTime());

        $form = $this->createForm(CustomerExportType::class, $data, [
            'action' => $this->generateResourcePath('ekyna_commerce.customer', self::class),
            'method' => 'POST',
            'attr'   => ['class' => 'form-horizontal'],
        ]);

        FormUtil::addFooter($form, [
            'submit_label' => t('button.export', [], 'EkynaUi'),
            'submit_icon'  => 'download',
            'cancel_path'  => $this->generateResourcePath('ekyna_commerce.customer', ListAction::class),
        ]);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this
                ->customerExporter
                ->export($data)
                ->download([
                    'file_name' => 'customers.csv',
                ]);
        }

        $this->breadcrumbFromContext($this->context);

        return $this->render($this->options['template'], [
            'context' => $this->context,
            'form'    => $form->createView(),
        ]);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_customer_export',
            'permission' => Permission::READ,
            'route'      => [
                'name'    => 'admin_%s_export',
                'path'    => '/export',
                'methods' => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'button.export',
                'trans_domain' => 'EkynaUi',
                'icon'         => 'download',
            ],
            'options'    => [
                'template' => '@EkynaCommerce/Admin/Customer/export.html.twig',
            ],
        ];
    }
}
