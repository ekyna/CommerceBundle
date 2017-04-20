<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Subject;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierProduct\CreateAction;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\RepositoryTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function Symfony\Component\Translation\t;

/**
 * Class CreateSupplierProductAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Subject
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateSupplierProductAction extends AbstractAction implements AdminActionInterface
{
    use RepositoryTrait;
    use HelperTrait;
    use FlashTrait;

    private SubjectHelperInterface $subjectHelper;

    public function __construct(SubjectHelperInterface $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;
    }

    public function __invoke(): Response
    {
        if ($this->request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not yet implemented.');
        }

        $product = $this->context->getResource();
        if (!$product instanceof SubjectInterface) {
            throw new UnexpectedTypeException($product, SubjectInterface::class);
        }

        $form = $this->subjectHelper->getCreateSupplierProductForm($product);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SupplierInterface $supplier */
            $supplier = $form->get('supplier')->getData();

            $supplierProduct = $this
                ->getRepository(SupplierProductInterface::class)
                ->findOneBySubjectAndSupplier($product, $supplier);

            if (null === $supplierProduct) {
                $path = $this->generateResourcePath(SupplierProductInterface::class, CreateAction::class, [
                    'supplierId' => $supplier->getId(),
                    'provider'   => $product::getProviderName(),
                    'identifier' => $product->getIdentifier(),
                ]);

                return $this->redirect($path);
            }

            $this->addFlash(t('product.alert.supplier_product_exists', [
                '%name%' => $supplier->getName(),
            ], 'EkynaProduct'), 'warning');
        } else {
            $errors = '';
            foreach ($form->getErrors(true) as $error) {
                $errors .= $error->getMessage() . '<br>';
            }
            $this->addFlash($errors, 'danger');
        }

        return $this->redirect($this->generateResourcePath($product));
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_subject_create_supplier_product',
            'permission' => Permission::CREATE,
            'route'      => [
                'name'    => 'admin_%s_create_supplier_product',
                'path'    => '/create-supplier-product',
                'methods' => ['GET', 'POST'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'supplier_product.label.singular',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'success',
                'icon'         => 'plus',
            ],
        ];
    }
}
