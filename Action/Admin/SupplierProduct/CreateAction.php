<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierProduct;

use Ekyna\Bundle\AdminBundle\Action\CreateAction as BaseAction;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function array_replace_recursive;

/**
 * Class CreateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierProduct
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateAction extends BaseAction
{
    private SubjectHelperInterface $subjectHelper;

    public function __construct(SubjectHelperInterface $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;
    }

    protected function onInit(): ?Response
    {
        $resource = $this->context->getResource();
        if (!$resource instanceof SupplierProductInterface) {
            throw new UnexpectedTypeException($resource, SupplierProductInterface::class);
        }

        $name = $this->request->query->get('provider');
        $identifier = $this->request->query->getInt('identifier');

        if (empty($name) && empty($identifier)) {
            return parent::onInit();
        }

        if (!$subject = $this->subjectHelper->find($name, $identifier)) {
            throw new NotFoundHttpException('Subject not found');
        }

        $this->subjectHelper->assign($resource, $subject);

        return parent::onInit();
    }

    public static function configureAction(): array
    {
        return array_replace_recursive(parent::configureAction(), [
            'name'    => 'commerce_supplier_product_create',
            'options' => [
                'template'           => '@EkynaCommerce/Admin/SupplierProduct/create.html.twig',
                'form_template'      => '@EkynaCommerce/Admin/SupplierProduct/_form.html.twig',
                'redirect_to_parent' => false,
            ],
        ]);
    }
}
