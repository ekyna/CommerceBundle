<?php

namespace Ekyna\Bundle\CommerceBundle\Install;

use Ekyna\Bundle\InstallBundle\Install\AbstractInstaller;
use Ekyna\Bundle\InstallBundle\Install\OrderedInstallerInterface;
use Ekyna\Component\Commerce\Install\Installer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CommerceInstaller
 * @package Ekyna\Bundle\CommerceBundle\Install
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CommerceInstaller extends AbstractInstaller implements OrderedInstallerInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;


    /**
     * Sets the container.
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function install(Command $command, InputInterface $input, OutputInterface $output)
    {
        $installer = new Installer($this->container->get('doctrine.orm.default_entity_manager'), $output);

        $output->writeln('<info>[Commerce] Installing countries:</info>');
        $countries = $this->container->getParameter('ekyna_commerce.default.countries');
        $installer->installCountries($countries);
        $output->writeln('');

        $output->writeln('<info>[Commerce] Installing currencies:</info>');
        $currencies = $this->container->getParameter('ekyna_commerce.default.currencies');
        $installer->installCurrencies($currencies);
        $output->writeln('');
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 512;
    }
}
