<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\CustomerAddress;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\ListAction;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\Import\AddressConfigType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\ResourceBundle\Form\Type\Import\ImportConfigType;
use Ekyna\Bundle\ResourceBundle\Service\Import\ImportConfig;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Customer\Import\AddressConsumer;
use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
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
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\CustomerAddress
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
        $customer = $this->context->getParentResource();

        if (!$customer instanceof CustomerInterface) {
            throw new UnexpectedTypeException($customer, CustomerInterface::class);
        }

        $importConfig = $this->createImportConfig($customer);

        $redirect = $this->generateResourcePath($customer);

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

    private function createImportConfig(CustomerInterface $customer): ImportConfig
    {
        $addressConsumer = new AddressConsumer($this->phoneNumberUtil);
        $addressConsumer
            ->getConfig()
            ->setCustomer($customer)
            ->setNumbers([
                'company'    => 1,
                'firstName'  => 2,
                'lastName'   => 3,
                'street'     => 4,
                'complement' => 5,
                'supplement' => 6,
                'extra'      => 7,
                'postalCode' => 8,
                'city'       => 9,
                'phone'      => 10,
                'mobile'     => 11,
            ]);

        $importConfig = new ImportConfig();
        $importConfig->addConsumer('address', $addressConsumer);

        return $importConfig;
    }

    private function createImportForm(ImportConfig $importConfig, string $cancelPath): FormInterface
    {
        $form = $this->createForm(ImportConfigType::class, $importConfig, [
            'action'         => $this->generateResourcePath(CustomerAddressInterface::class, self::class),
            'attr'           => [
                'class' => 'form-horizontal',
            ],
            'method'         => 'POST',
            'consumer_types' => [
                'address' => [
                    'type' => AddressConfigType::class,
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
            'name'       => 'commerce_customer_address_import',
            'permission' => Permission::CREATE,
            'route'      => [
                'name'    => 'admin_%s_import',
                'path'    => '/import',
                'methods' => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'customer_address.button.import',
                'trans_domain' => 'EkynaCommerce',
                'icon'         => 'import',
            ],
            'options'    => [
                'template' => '@EkynaCommerce/Admin/CustomerAddress/import.html.twig',
            ],
        ];
    }
}
