<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\CoreBundle\Form\DataTransformer\ObjectToIdentifierTransformer;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Shipment\Repository\RelayPointRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class RelayPointType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RelayPointType extends AbstractType
{
    /**
     * @var RelayPointRepositoryInterface
     */
    private $repository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $mapApiKey;

    /**
     * Constructor.
     *
     * @param RelayPointRepositoryInterface $repository
     * @param SerializerInterface           $serializer
     * @param string                        $mapApiKey
     */
    public function __construct(RelayPointRepositoryInterface $repository, SerializerInterface $serializer, $mapApiKey)
    {
        $this->repository = $repository;
        $this->serializer = $serializer;
        $this->mapApiKey = $mapApiKey;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new ObjectToIdentifierTransformer($this->repository, 'number'));
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
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

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'          => 'ekyna_commerce.relay_point.label.singular',
                'search'         => null,
                'required'       => false,
                'error_bubbling' => false,
            ])
            ->setAllowedTypes('search', [AddressInterface::class, 'null']);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_relay_point';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return HiddenType::class;
    }
}
