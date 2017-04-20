<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Exception\UnexpectedTypeException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class CustomerContactType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerContactType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $filters = false;
        /** @var CustomerInterface $customer */
        if (null !== $customer = $options['customer']) {
            $source = $builder->getSource();
            if (!$source instanceof EntitySource) {
                throw new UnexpectedTypeException($source, EntitySource::class);
            }

            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, string $alias) use ($customer): void {
                $qb
                    ->andWhere($qb->expr()->eq($alias . '.customer', ':customer'))
                    ->setParameter('customer', $customer);
            });

            $builder->setFilterable(false);
        } else {
            $filters = true;
            $builder
                ->setExportable(true)
                ->setConfigurable(true)
                ->setProfileable(true);
        }

        $builder
            ->addColumn('email', Ctype\Column\TextType::class, [
                'position' => 10,
            ])
            ->addColumn('identity', Ctype\Column\TextType::class, [
                'position'      => 20,
            ])
            ->addColumn('title', CType\Column\TextType::class, [
                'label'    => t('field.title', [], 'EkynaUi'),
                'position' => 40,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    UpdateAction::class,
                    DeleteAction::class,
                ],
            ]);

        if (!$filters) {
            return;
        }

        $builder
            ->addFilter('email', CType\Filter\TextType::class, [
                'label'    => t('field.email', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addFilter('firstName', CType\Filter\TextType::class, [
                'label'    => t('field.first_name', [], 'EkynaUi'),
                'position' => 20,
            ])
            ->addFilter('lastName', CType\Filter\TextType::class, [
                'label'    => t('field.last_name', [], 'EkynaUi'),
                'position' => 30,
            ])
            ->addFilter('title', CType\Filter\TextType::class, [
                'label'    => t('field.title', [], 'EkynaUi'),
                'position' => 40,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('customer', null)
            ->setAllowedTypes('customer', ['null', CustomerInterface::class]);
    }
}
