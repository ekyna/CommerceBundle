<?php

namespace Ekyna\Bundle\CommerceBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture as BaseFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Faker\Factory;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AbstractFixtures
 * @package Ekyna\Bundle\CommerceBundle\DataFixtures\ORM
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractFixture extends BaseFixture implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
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
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->phoneUtil = PhoneNumberUtil::getInstance();
        $this->faker = Factory::create($this->container->getParameter('hautelook_alice.locale'));
    }
}
