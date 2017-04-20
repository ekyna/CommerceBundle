<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\ResourceBundle\Form\DataTransformer\ResourceToIdentifierTransformer;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Shipment\Repository\RelayPointRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;

use function json_encode;
use function Symfony\Component\Translation\t;

/**
 * Class RelayPointType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RelayPointType extends AbstractType
{
    private RelayPointRepositoryInterface $repository;
    private SerializerInterface $serializer;
    private string $mapApiKey;


    public function __construct(
        RelayPointRepositoryInterface $repository,
        SerializerInterface $serializer,
        string $mapApiKey
    ) {
        $this->repository = $repository;
        $this->serializer = $serializer;
        $this->mapApiKey = $mapApiKey;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer(new ResourceToIdentifierTransformer($this->repository, 'number'));
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['map_api_key'] = $this->mapApiKey;
        $view->vars['relay_point'] = $relayPoint = $form->getData();

        // Default search (street / postalCode / city)
        /** @var AddressInterface $init */
        if (null !== $init = $options['search']) {
            $view->vars['attr']['data-search'] = json_encode([
                'street'      => $init->getStreet(),
                'postal_code' => $init->getPostalCode(),
                'city'        => $init->getCity(),
            ]);
        }

        // Initial relay point
        if (null !== $relayPoint) {
            $encoded = $this->serializer->serialize($relayPoint, 'json', ['groups' => ['Default']]);
            $view->vars['attr']['data-initial'] = $encoded;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label'          => t('relay_point.label.singular', [], 'EkynaCommerce'),
                'search'         => null,
                'required'       => false,
                'error_bubbling' => false,
            ])
            ->setAllowedTypes('search', [AddressInterface::class, 'null']);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_relay_point';
    }

    public function getParent(): ?string
    {
        return HiddenType::class;
    }
}
