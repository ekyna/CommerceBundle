<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierProduct;

use Craue\FormFlowBundle\Form\FormFlowInterface;
use Ekyna\Bundle\AdminBundle\Action\AbstractCreateFlowAction;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Supplier\Factory\SupplierProductFactoryInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function array_replace_recursive;

/**
 * Class CreateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierProduct
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateAction extends AbstractCreateFlowAction
{
    public function __construct(
        FormFlowInterface                            $flow,
        private readonly ResourceRepositoryInterface $supplierRepository,
        private readonly SubjectHelperInterface      $subjectHelper
    ) {
        parent::__construct($flow);
    }

    protected function createResource(): ResourceInterface
    {
        $factory = $this->getFactory();
        if (!$factory instanceof SupplierProductFactoryInterface) {
            throw new UnexpectedTypeException($factory, SupplierProductFactoryInterface::class);
        }

        return $factory->createWithSubjectAndSupplier(
            $this->getQuerySupplier(),
            $this->getQuerySubject()
        );
    }

    private function getQuerySubject(): ?SubjectInterface
    {
        $provider = $this->request->query->get('provider');
        $identifier = $this->request->query->getInt('identifier');

        if (empty($provider) && empty($identifier)) {
            return null;
        }

        if (!$subject = $this->subjectHelper->find($provider, $identifier)) {
            throw new NotFoundHttpException('Subject not found');
        }

        return $subject;
    }

    private function getQuerySupplier(): ?SupplierInterface
    {
        if (0 >= $id = $this->request->query->getInt('supplierId')) {
            return null;
        }

        $supplier = $this->supplierRepository->find($id);
        if (!$supplier instanceof SupplierInterface) {
            throw new NotFoundHttpException('Supplier not found');
        }

        return $supplier;
    }

    public static function configureAction(): array
    {
        return array_replace_recursive(parent::configureAction(), [
            'name'    => 'commerce_supplier_product_create',
            'options' => [
                'template'      => '@EkynaCommerce/Admin/SupplierProduct/create.html.twig',
                'form_template' => '@EkynaCommerce/Admin/SupplierProduct/_flow.html.twig',
            ],
        ]);
    }
}
