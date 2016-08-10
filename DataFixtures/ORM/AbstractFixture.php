<?php

namespace Ekyna\Bundle\CommerceBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture as BaseFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ekyna\Bundle\CommerceBundle\Entity\CustomerAddress;
use Ekyna\Bundle\CommerceBundle\Entity\OrderAddress;
use Ekyna\Bundle\CommerceBundle\Event\CustomerEvent;
use Ekyna\Bundle\CommerceBundle\Event\ProductEvent;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\UserBundle\Model\IdentityInterface;
use Ekyna\Component\Commerce\Customer\Event\CustomerEvents;
use Ekyna\Component\Commerce\Product\Event\ProductEvents;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
//use Ekyna\Component\Commerce\Product\Model\ProductTypes;
use Faker\Factory;
use libphonenumber\PhoneNumberUtil;
use Nelmio\Alice\Fixtures;
use Nelmio\Alice\ProcessorInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class AbstractFixtures
 * @package Ekyna\Bundle\CommerceBundle\DataFixtures\ORM
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractFixture
    extends BaseFixture
    implements FixtureInterface,
               OrderedFixtureInterface,
//               ProcessorInterface,
               ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \libphonenumber\PhoneNumberUtil
     */
    protected $phoneUtil;

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;


    /**
     * @inheritDoc
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->phoneUtil = PhoneNumberUtil::getInstance();
        $this->faker = Factory::create($this->container->getParameter('hautelook_alice.locale'));
    }

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $om)
    {
        Fixtures::load($this->getFixtures(), $om, [
            'locale'    => $this->container->getParameter('hautelook_alice.locale'),
            'seed'      => uniqid(),
            'providers' => [$this],
        ]/*, [
            $this,
        ]*/);
    }

    /**
     * @inheritdoc
     * @todo remove
     */
    public function preProcess($object)
    {
        if ($object instanceof CustomerInterface) {
            $this->dispatch(CustomerEvents::PRE_CREATE, new CustomerEvent($object));
        } elseif ($object instanceof ProductInterface) {
            /*if ($object->getType() === ProductTypes::TYPE_VARIABLE) {
                $this->generateProductVariants($object);
            }*/
            $this->dispatch(ProductEvents::PRE_CREATE, new ProductEvent($object));
        }
    }

    /**
     * @inheritdoc
     * @todo remove
     */
    public function postProcess($object)
    {

    }

    /**
     * Returns the country by code.
     *
     * @param string $code
     *
     * @return null|\Ekyna\Component\Commerce\Common\Model\CountryInterface
     */
    public function countryByCode($code)
    {
        return $this->container
            ->get('ekyna_commerce.country.repository')
            ->findOneBy(['code' => $code]);
    }

    /**
     * Returns the currency by code.
     *
     * @param string $code
     *
     * @return null|\Ekyna\Component\Commerce\Common\Model\CurrencyInterface
     */
    public function currencyByCode($code)
    {
        return $this->container
            ->get('ekyna_commerce.currency.repository')
            ->findOneBy(['code' => $code]);
    }

    /**
     * Generates an address.
     *
     * @param IdentityInterface $owner
     * @param null|bool         $ownerIdentity
     *
     * @return OrderAddress|CustomerAddress
     */
    public function generateAddress(IdentityInterface $owner, $ownerIdentity = null)
    {
        if ($owner instanceof OrderInterface) {
            $address = new OrderAddress();
        } elseif ($owner instanceof CustomerInterface) {
            $address = new CustomerAddress();
            $address->setCustomer($owner);
        } else {
            throw new \InvalidArgumentException('Unexpected owner.');
        }

        if ((null !== $ownerIdentity && $ownerIdentity) || 50 < rand(0, 100)) {
            $address
                ->setGender($owner->getGender())
                ->setFirstName($owner->getFirstName())
                ->setLastName($owner->getLastName());
        } else {
            $address
                ->setGender('mr')
                ->setFirstName($this->faker->firstName)
                ->setLastName($this->faker->lastName);
        }

        $address
            ->setStreet($this->faker->streetAddress)
            ->setPostalCode(str_replace(' ', '', $this->faker->postcode))
            ->setCity($this->faker->city)
            ->setCountry($this->countryByCode('FR'));

        if (50 < rand(0, 100)) {
            $address->setPhone($this->phoneUtil->parse($this->faker->phoneNumber, 'FR'));
        }
        if (50 < rand(0, 100)) {
            $address->setMobile($this->phoneUtil->parse($this->faker->phoneNumber, 'FR'));
        }

        return $address;
    }

    /**
     * Generates the product variant.
     *
     * @param ProductInterface $product
     */
    /*protected function generateProductVariants(ProductInterface $product)
    {
        $variants = $this->container
            ->get('ekyna_commerce.product.variant_builder')
            ->buildVariations($product);

        $minPrice = $product->getNetPrice() * 8000;
        $maxPrice = $product->getNetPrice() * 12000;

        /** @var ProductInterface $variant */
        /*$count = 0;
        foreach ($variants as $variant) {
            $count++;
            $variant
                ->setReference($product->getReference() . '-' . $count)
                ->setNetPrice(rand($minPrice, $maxPrice) / 10000);

            $product->addVariant($variant);

            $this->dispatch(ProductEvents::PRE_CREATE, new ProductEvent($variant));
        }
    }*/

    /**
     * Dispatches the entity event.
     *
     * @param string     $eventName
     * @param Event|null $event
     */
    protected function dispatch($eventName, Event $event = null)
    {
        if (null === $this->dispatcher) {
            $this->dispatcher = $this->container->get('event_dispatcher');
        }

        $this->dispatcher->dispatch($eventName, $event);
    }

    /**
     * Returns the fixtures files.
     *
     * @return array|string
     */
    protected function getFixtures()
    {
        return [];
    }
}
