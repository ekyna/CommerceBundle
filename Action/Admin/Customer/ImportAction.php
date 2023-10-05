<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Customer;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\ListAction;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\Import\AddressConfigType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\Import\CustomerConfigType;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\ResourceBundle\Form\Type\Import\ImportConfigType;
use Ekyna\Bundle\ResourceBundle\Service\Import\ImportConfig;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Customer\Import\AddressConsumer;
use Ekyna\Component\Commerce\Customer\Import\CustomerConsumer;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\ImportException;
use Ekyna\Component\Resource\Import\CsvImporter;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use function implode;
use function Symfony\Component\Translation\t;

/**
 * Class ImportAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Customer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ImportAction extends AbstractAction implements AdminActionInterface
{
    use FormTrait;
    use BreadcrumbTrait;
    use HelperTrait;
    use TemplatingTrait;
    use FlashTrait;

    private CsvImporter     $importer;
    private PhoneNumberUtil $phoneNumberUtil;

    public function __construct(
        CsvImporter     $importer,
        PhoneNumberUtil $phoneNumberUtil
    ) {
        $this->importer = $importer;
        $this->phoneNumberUtil = $phoneNumberUtil;
    }

    public function __invoke(): Response
    {
        $importConfig = $this->createImportConfig();

        $redirect = $this->generateResourcePath(CustomerInterface::class, ListAction::class);

        $form = $this->createImportForm($importConfig, $redirect);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $count = $this->importer->import($importConfig);

                if (0 < $count) {
                    $this->addFlash("Importing $count resource(s)&hellip;", 'success');

                    return $this->redirect($redirect);
                }

                if (!empty($errors = $this->importer->getErrors())) {
                    $this->addFlash(implode('<br>', $errors), 'danger');
                } else {
                    $this->addFlash('No resource found in file.', 'warning');
                }
            } catch (ImportException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $this->breadcrumbFromContext($this->context);

        return $this->render($this->options['template'], [
            'context' => $this->context,
            'form'    => $form->createView(),
        ]);
    }

    private function createImportConfig(): ImportConfig
    {
        $customerConsumer = new CustomerConsumer($this->phoneNumberUtil);
        $customerConsumer->getConfig()->setNumbers([
            'company'       => 1,
            'email'         => 2,
            'firstName'     => 3,
            'lastName'      => 4,
            'phone'         => 5,
            'mobile'        => 6,
            'companyNumber' => 7, // TODO Remove
        ]);

        $invoiceConsumer = new AddressConsumer($this->phoneNumberUtil);
        $invoiceConsumer->setCustomerConsumer($customerConsumer);
        $invoiceConsumer
            ->getConfig()
            ->setInvoiceDefault(true)
            ->setNumbers([
                'company'    => 8,
                'street'     => 9,
                'complement' => 10,
                'postalCode' => 11,
                'city'       => 12,
            ]);

        // TODO Make optional
        $deliveryConsumer = new AddressConsumer($this->phoneNumberUtil);
        $deliveryConsumer->setCustomerConsumer($customerConsumer);
        $deliveryConsumer
            ->getConfig()
            ->setDeliveryDefault(true)
            ->setNumbers([
                'company'    => 13,
                'street'     => 14,
                'complement' => 15,
                'postalCode' => 16,
                'city'       => 17,
            ]);

        $importConfig = new ImportConfig();
        $importConfig->addConsumer('customer', $customerConsumer);
        $importConfig->addConsumer('invoice', $invoiceConsumer);
        $importConfig->addConsumer('delivery', $deliveryConsumer);

        return $importConfig;
    }

    private function createImportForm(ImportConfig $importConfig, string $cancelPath): FormInterface
    {
        $form = $this->createForm(ImportConfigType::class, $importConfig, [
            'action'         => $this->generateResourcePath(CustomerInterface::class, self::class),
            'attr'           => [
                'class' => 'form-horizontal',
            ],
            'method'         => 'POST',
            'consumer_types' => [
                'customer' => [
                    'type'    => CustomerConfigType::class, // TODO Translation
                    'options' => [],
                ],
                'invoice'  => [
                    'type'    => AddressConfigType::class,
                    'options' => [
                        'label' => 'Invoice address config', // TODO Translation
                    ],
                ],
                'delivery' => [
                    'type'    => AddressConfigType::class,
                    'options' => [
                        'label' => 'Delivery address config', // TODO Translation
                    ],
                ],
            ],
        ]);

        FormUtil::addFooter($form, [
            'submit_label' => t('button.import', [], 'EkynaUi'),
            'submit_icon'  => 'import',
            'cancel_path'  => $cancelPath,
        ]);

        return $form;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_customer_import',
            'permission' => Permission::CREATE,
            'route'      => [
                'name'    => 'admin_%s_import',
                'path'    => '/import',
                'methods' => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'customer.button.import',
                'trans_domain' => 'EkynaCommerce',
                'icon'         => 'import',
            ],
            'options'    => [
                'template' => '@EkynaCommerce/Admin/Customer/import.html.twig',
            ],
        ];
    }
}
