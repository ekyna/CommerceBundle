<?php

namespace Ekyna\Bundle\CommerceBundle\Install;

use Ekyna\Bundle\InstallBundle\Install\AbstractInstaller;
use Ekyna\Bundle\InstallBundle\Install\OrderedInstallerInterface;
use Ekyna\Bundle\MediaBundle\Model\FolderInterface;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Constants as Credit;
use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Constants as Outstanding;
use Ekyna\Component\Commerce\Install\Installer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;

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
        $installer = new Installer(
            $this->container->get('doctrine.orm.entity_manager'),
            $this->container->getParameter('ekyna_commerce.customer_group.class'),
            $output
        );

        $output->writeln('<info>[Commerce] Installing countries:</info>');
        $countries = $this->container->getParameter('ekyna_commerce.default.countries');
        $installer->installCountries($countries);
        $output->writeln('');

        $output->writeln('<info>[Commerce] Installing currencies:</info>');
        $currencies = $this->container->getParameter('ekyna_commerce.default.currencies');
        $installer->installCurrencies($currencies);
        $output->writeln('');

        $output->writeln('<info>[Commerce] Installing taxes:</info>');
        $installer->installTaxes($countries);
        $output->writeln('');

        $output->writeln('<info>[Commerce] Installing tax group:</info>');
        $installer->installTaxGroups($countries);
        $output->writeln('');

        $output->writeln('<info>[Commerce] Installing tax rules:</info>');
        $installer->installTaxRules($countries);
        $output->writeln('');

        $output->writeln('<info>[Commerce] Installing default customer group:</info>');
        $installer->installCustomerGroups();
        $output->writeln('');

        $output->writeln('<info>[Commerce] Installing payment methods:</info>');
        $this->installPaymentMethods($output);
        $output->writeln('');

        $output->writeln('<info>[Commerce] Installing shipment methods:</info>');
        $this->installShipmentMethods($output);
        $output->writeln('');
    }

    /**
     * Creates the payment images folder.
     *
     * @return FolderInterface
     */
    private function createImageFolder()
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $folderRepository = $this->container->get('ekyna_media.folder.repository');

        if (null === $rootFolder = $folderRepository->findRoot()) {
            throw new \RuntimeException('Can\'t find root folder. Please run MediaBundle installer first.');
        }

        $name = 'Payment method';

        $paymentFolder = $folderRepository->findOneBy([
            'name'   => $name,
            'parent' => $rootFolder,
        ]);
        if (null !== $paymentFolder) {
            return $paymentFolder;
        }

        $paymentFolder = $folderRepository->createNew();
        $paymentFolder
            ->setName($name)
            ->setParent($rootFolder);

        $em->persist($paymentFolder);
        $em->flush();

        return $paymentFolder;
    }

    /**
     * Installs the default payment methods.
     *
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    private function installPaymentMethods(OutputInterface $output)
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        //$registry = $this->container->get('payum');
        $methodRepository = $this->container->get('ekyna_commerce.payment_method.repository');
        $mediaRepository = $this->container->get('ekyna_media.media.repository');

        $folder = $this->createImageFolder();
        $imageDir = realpath(__DIR__ . '/../Resources/install/payment-method');

        $methods = [
            'Solde de compte client' => [
                'factory'     => Credit::FACTORY_NAME,
                'image'       => 'virement.png',
                'description' => '<p>Utilisez votre solde de compte client ...</p>',
                'enabled'     => true,
            ],
            'Encours client'         => [
                'factory'     => Outstanding::FACTORY_NAME,
                'image'       => 'virement.png',
                'description' => '<p>Utilisez votre encours client ...</p>',
                'enabled'     => true,
            ],
            /*'Paypal'   => [
                'factory'     => 'paypal_express_checkout_nvp',
                'image'       => 'paypal.png',
                'description' => '<p>Réglez avec votre compte paypal, ou votre carte bancaire.</p>',
                'enabled'     => false,
            ],*/
        ];

        $ccGateways = [
            'payzen'    => 'Ekyna\Bundle\PayumPayzenBundle\EkynaPayumPayzenBundle',
            'atos_sips' => 'Ekyna\Bundle\PayumSipsBundle\EkynaPayumSipsBundle',
        ];
        foreach ($ccGateways as $factory => $class) {
            if (class_exists($class)) {
                $methods['Carte bancaire'] = [
                    'factory'     => $factory,
                    'image'       => 'credit-card.png',
                    'description' => '<p>Réglez avec votre carte bancaire.</p>',
                    'enabled'     => true,
                ];
                break;
            }
        }

        $methods = array_merge($methods, [
            'Virement' => [
                'factory'     => 'offline',
                'image'       => 'virement.png',
                'description' => '<p>Veuillez adresser votre virement à l\'ordre de ...</p>',
                'enabled'     => true,
            ],
            'Chèque'   => [
                'factory'     => 'offline',
                'image'       => 'cheque.png',
                'description' => '<p>Veuillez adresser votre chèque à l\'ordre de ...</p>',
                'enabled'     => true,
            ],
        ]);

        $position = 0;
        foreach ($methods as $name => $options) {
            $output->write(sprintf(
                '- <comment>%s</comment> %s ',
                $name,
                str_pad('.', 44 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));

            // TODO check that factory method exists

            if (null !== $method = $methodRepository->findOneBy(['name' => $name])) {
                $output->writeln('already exists.');
                continue;
            }

            $source = $imageDir . '/' . $options['image'];
            if (!file_exists($source)) {
                throw new \Exception(sprintf('File "%s" does not exists.', $source));
            }
            $target = sys_get_temp_dir() . '/' . uniqid() . '.' . pathinfo($options['image'], PATHINFO_EXTENSION);
            if (!copy($source, $target)) {
                throw new \Exception(sprintf('Failed to copy "%s" into "%s".', $source, $target));
            }

            /** @var \Ekyna\Bundle\MediaBundle\Model\MediaInterface $image */
            $image = $mediaRepository->createNew();
            $image
                ->setFile(new File($target))
                ->setFolder($folder)
                ->setTitle($name)
                ->setType(MediaTypes::IMAGE);

            /** @var \Ekyna\Bundle\CommerceBundle\Entity\PaymentMethod $method */
            $method = $methodRepository->createNew();
            $method
                ->setMedia($image)
                ->setName($name)
                ->setFactoryName($options['factory'])
                ->setTitle($name)
                ->setDescription($options['description'])
                ->setEnabled($options['enabled'])
                ->setAvailable(true)
                ->setPosition($position);

            $em->persist($method);

            $output->writeln('created.');

            $position++;
        }
        $em->flush();
    }

    /**
     * Installs the default shipment methods.
     *
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    private function installShipmentMethods(OutputInterface $output)
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $methodRepository = $this->container->get('ekyna_commerce.shipment_method.repository');
        $mediaRepository = $this->container->get('ekyna_media.media.repository');

        $defaultTaxGroup = $this->container
            ->get('ekyna_commerce.tax_group.repository')
            ->findDefault();

        $folder = $this->createImageFolder();
        $imageDir = realpath(__DIR__ . '/../Resources/install/shipment-method');

        $methods = [
            'Retrait en magasin' => [
                'factory'     => 'noop',
                'image'       => 'in-store.png',
                'description' => '<p>Vous pourrez retirer votre colis à notre magasin ...</p>',
                'enabled'     => true,
            ],
        ];

        $position = 0;
        foreach ($methods as $name => $options) {
            $output->write(sprintf(
                '- <comment>%s</comment> %s ',
                $name,
                str_pad('.', 44 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));

            if (null !== $method = $methodRepository->findOneBy(['name' => $name])) {
                $output->writeln('already exists.');
                continue;
            }

            $source = $imageDir . '/' . $options['image'];
            if (!file_exists($source)) {
                throw new \Exception(sprintf('File "%s" does not exists.', $source));
            }
            $target = sys_get_temp_dir() . '/' . $options['image'];
            if (!copy($source, $target)) {
                throw new \Exception(sprintf('Failed to copy "%s" into "%s".', $source, $target));
            }

            /** @var \Ekyna\Bundle\MediaBundle\Model\MediaInterface $image */
            $image = $mediaRepository->createNew();
            $image
                ->setFile(new File($target))
                ->setFolder($folder)
                ->setTitle($name)
                ->setType(MediaTypes::IMAGE);

            /** @var \Ekyna\Bundle\CommerceBundle\Entity\ShipmentMethod $method */
            $method = $methodRepository->createNew();
            $method
                ->setTaxGroup($defaultTaxGroup)
                ->setMedia($image)
                ->setName($name)
                ->setEnabled($options['enabled'])
                ->setAvailable(true)
                ->setPosition($position)
                ->setTitle($name)
                ->setDescription($options['description']);

            $em->persist($method);

            $output->writeln('created.');

            $position++;
        }
        $em->flush();
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'ekyna_commerce';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 512;
    }
}
