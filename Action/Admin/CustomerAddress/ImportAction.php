<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\CustomerAddress;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\AddressImportType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Customer\Import\AddressImport;
use Ekyna\Component\Commerce\Customer\Import\AddressImporter;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

use function implode;
use function pathinfo;
use function Symfony\Component\Translation\t;
use function sys_get_temp_dir;
use function transliterator_transliterate;
use function uniqid;

use const PATHINFO_FILENAME;

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

    private AddressImporter $addressImporter;

    public function __construct(AddressImporter $addressImporter)
    {
        $this->addressImporter = $addressImporter;
    }

    public function __invoke(): Response
    {
        $customer = $this->context->getParentResource();

        if (!$customer instanceof CustomerInterface) {
            throw new UnexpectedTypeException($customer, CustomerInterface::class);
        }

        $config = new AddressImport($customer);

        $form = $this
            ->createForm(AddressImportType::class, $config, [
                'action' => $this->generateResourcePath('ekyna_commerce.customer_address', self::class),
                'attr'   => [
                    'class' => 'form-horizontal',
                ],
                'method' => 'POST',
            ]);

        FormUtil::addFooter($form, [
            'submit_label' => t('button.import', [], 'EkynaUi'),
            'submit_icon'  => 'import',
            'cancel_path'  => $this->generateResourcePath($customer),
        ]);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->prepareImport($config, $form->get('file')->getData());

                $count = $this->addressImporter->import($config);

                if (0 < $count) {
                    $this->addFlash("Importing $count addresses&hellip;", 'success');

                    return $this->redirect($this->generateResourcePath($customer));
                }

                if (!empty($errors = $this->addressImporter->getErrors())) {
                    $this->addFlash(implode('<br>', $errors), 'danger');
                } else {
                    $this->addFlash('No address found in file.', 'warning');
                }
            } catch (CommerceExceptionInterface $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $this->breadcrumbFromContext($this->context);

        return $this->render($this->options['template'], [
            'context' => $this->context,
            'form'    => $form->createView(),
        ]);
    }

    /**
     * Moves the uploaded address file.
     *
     * @param AddressImport $config
     * @param UploadedFile  $file
     */
    private function prepareImport(AddressImport $config, UploadedFile $file): void
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $safeFilename = transliterator_transliterate(
            'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',
            $originalFilename
        );
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        // Move the file to the directory where brochures are stored
        try {
            $file = $file->move(sys_get_temp_dir(), $newFilename);
        } catch (FileException $e) {
            throw new RuntimeException($e->getMessage());
        }

        $config->setPath($file->getRealPath());
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
