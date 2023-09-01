<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\AdminBundle\Action\SummaryAction;
use Ekyna\Bundle\AdminBundle\Model\Ui;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\TableBundle\Model\Anchor;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntityAdapter;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Context\ActiveSort;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\AdapterInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;
use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class OrderCustomerType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleCustomerType extends AbstractColumnType
{
    private ConstantsHelper $constantsHelper;
    private ResourceHelper  $resourceHelper;

    public function __construct(ConstantsHelper $helper, ResourceHelper $resourceHelper)
    {
        $this->constantsHelper = $helper;
        $this->resourceHelper = $resourceHelper;
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $prefix = '';
        if (!empty($path = $column->getConfig()->getPropertyPath())) {
            $prefix = $path . '.';
        }

        $value = $this->constantsHelper->renderIdentity($row->getData($path));

        if (!empty($company = $row->getData($prefix . 'company'))) {
            $value = sprintf('<strong>%s</strong> %s', $company, $value);
        }

        /** @var CustomerInterface $customer */
        if (null !== $customer = $row->getData($prefix . 'customer')) {
            $href = $this->resourceHelper->generateResourcePath($customer, ReadAction::class);
            $summary = $this->resourceHelper->generateResourcePath($customer, SummaryAction::class);
            $view->vars = array_replace($view->vars, [
                'block_prefix' => 'anchor',
                'value'        => $value,
                'anchor'       => new Anchor($value, $value, ['href' => $href, Ui::SUMMARY_ATTR => $summary]),
            ]);

            return;
        }

        $view->vars = array_replace($view->vars, [
            'block_prefix' => 'text',
            'value'        => $value,
        ]);
    }

    public function applySort(
        AdapterInterface $adapter,
        ColumnInterface  $column,
        ActiveSort       $activeSort,
        array            $options
    ): bool {
        if (!$adapter instanceof EntityAdapter) {
            return false;
        }

        $qb = $adapter->getQueryBuilder();

        $prefix = '';
        if (!empty($path = $column->getConfig()->getPropertyPath())) {
            $prefix = $path . '.';
        }

        foreach (['company', 'lastName', 'firstName'] as $property) {
            $property = $adapter->getQueryBuilderPath($prefix . $property);

            $qb->addOrderBy($property, $activeSort->getDirection());
        }

        return true;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'         => t('customer.label.singular', [], 'EkynaCommerce'),
            'property_path' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'text';
    }

    public function getParent(): ?string
    {
        return PropertyType::class;
    }
}
