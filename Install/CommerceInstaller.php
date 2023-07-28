<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Install;

use Ekyna\Bundle\CommerceBundle\Entity\PaymentMethod;
use Ekyna\Bundle\CommerceBundle\Entity\ShipmentMethod;
use Ekyna\Bundle\CommerceBundle\Model\NotificationTypes as BNotifyTypes;
use Ekyna\Bundle\CommerceBundle\Model\NotifyModelInterface;
use Ekyna\Bundle\InstallBundle\Install\AbstractInstaller;
use Ekyna\Bundle\MediaBundle\Model\FolderInterface;
use Ekyna\Bundle\MediaBundle\Model\MediaInterface;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\MediaBundle\Repository\FolderRepositoryInterface;
use Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Constants as Credit;
use Ekyna\Component\Commerce\Bridge\Payum\Offline\Constants as Offline;
use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Constants as Outstanding;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes as CNotifyTypes;
use Ekyna\Component\Commerce\Install\Installer;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Pricing\Repository\TaxGroupRepositoryInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\InStore\InStorePlatform;
use Ekyna\Component\Commerce\Shipment\Gateway\Virtual\VirtualPlatform;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class CommerceInstaller
 * @package Ekyna\Bundle\CommerceBundle\Install
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CommerceInstaller extends AbstractInstaller
{
    private RepositoryFactoryInterface $repositoryFactory;
    private FactoryFactoryInterface    $factoryFactory;
    private ManagerFactoryInterface    $managerFactory;
    private TranslatorInterface        $translator;
    private string                     $defaultCountry;
    private string                     $defaultCurrency;


    public function __construct(
        RepositoryFactoryInterface $repositoryFactory,
        FactoryFactoryInterface    $factoryFactory,
        ManagerFactoryInterface    $managerFactory,
        TranslatorInterface        $translator,
        string                     $defaultCountry,
        string                     $defaultCurrency
    ) {
        $this->repositoryFactory = $repositoryFactory;
        $this->factoryFactory = $factoryFactory;
        $this->managerFactory = $managerFactory;
        $this->translator = $translator;
        $this->defaultCountry = $defaultCountry;
        $this->defaultCurrency = $defaultCurrency;
    }

    public function install(Command $command, InputInterface $input, OutputInterface $output): void
    {
        $installer = new Installer(
            $this->repositoryFactory,
            $this->factoryFactory,
            $this->managerFactory,
            function ($name, $result) use ($output) {
                $output->writeln(sprintf(
                    '- <comment>%s</comment> %s %s.',
                    $name,
                    str_pad('.', 44 - mb_strlen($name), '.', STR_PAD_LEFT),
                    $result
                ));
            }
        );

        $output->writeln('<info>[Commerce] Installing countries:</info>');
        $installer->installCountries($this->defaultCountry);
        $output->writeln('');

        $output->writeln('<info>[Commerce] Installing currencies:</info>');
        $installer->installCurrencies($this->defaultCurrency);
        $output->writeln('');

        $output->writeln('<info>[Commerce] Installing taxes:</info>');
        $installer->installTaxes();
        $output->writeln('');

        $output->writeln('<info>[Commerce] Installing tax group:</info>');
        $installer->installTaxGroups();
        $output->writeln('');

        $output->writeln('<info>[Commerce] Installing tax rules:</info>');
        $installer->installTaxRules();
        $output->writeln('');

        $output->writeln('<info>[Commerce] Installing customer groups:</info>');
        $installer->installCustomerGroups();
        $output->writeln('');

        $output->writeln('<info>[Commerce] Installing default warehouse:</info>');
        $installer->installDefaultWarehouse();
        $output->writeln('');

        $output->writeln('<info>[Commerce] Installing supplier templates:</info>');
        $installer->installSupplierTemplates();
        $output->writeln('');

        $output->writeln('<info>[Commerce] Installing payment methods:</info>');
        $this->installPaymentMethods($output);
        $output->writeln('');

        $output->writeln('<info>[Commerce] Installing shipment methods:</info>');
        $this->installShipmentMethods($output);
        $output->writeln('');

        $output->writeln('<info>[Commerce] Installing notification models:</info>');
        $this->installNotificationModels($output);
        $output->writeln('');
    }

    /**
     * Creates the payment images folder.
     *
     * @return FolderInterface
     */
    private function createImageFolder(): FolderInterface
    {
        /** @var FolderRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(FolderInterface::class);

        if (null === $root = $repository->findRoot()) {
            throw new Exception('Can\'t find root folder. Please run MediaBundle installer first.');
        }

        $name = 'Payment method';

        $folder = $repository->findOneBy([
            'name'   => $name,
            'parent' => $root,
        ]);
        if (null !== $folder) {
            return $folder;
        }

        /** @var FolderInterface $folder */
        $folder = $this->factoryFactory->getFactory(FolderInterface::class)->create();
        $folder
            ->setName($name)
            ->setParent($root);

        $manager = $this->managerFactory->getManager(FolderInterface::class);
        $manager->persist($folder);
        $manager->flush();

        return $folder;
    }

    /**
     * Installs the default payment methods.
     *
     * @param OutputInterface $output
     *
     * @throws Exception
     */
    private function installPaymentMethods(OutputInterface $output): void
    {
        $manager = $this->managerFactory->getManager(PaymentMethodInterface::class);

        $methodRepository = $this->repositoryFactory->getRepository(PaymentMethodInterface::class);
        $methodFactory = $this->factoryFactory->getFactory(PaymentMethodInterface::class);
        $mediaFactory = $this->factoryFactory->getFactory(MediaInterface::class);

        $folder = $this->createImageFolder();
        $directory = realpath(__DIR__ . '/../Resources/install/payment-method');

        $methods = [
            'Virement'               => [
                'factory'     => Offline::FACTORY_NAME,
                'image'       => 'virement.png',
                'description' => '<p>Veuillez adresser votre virement à l\'ordre de ...</p>',
                'enabled'     => true,
            ],
            'Chèque'                 => [
                'factory'     => Offline::FACTORY_NAME,
                'image'       => 'cheque.png',
                'description' => '<p>Veuillez adresser votre chèque à l\'ordre de ...</p>',
                'enabled'     => true,
            ],
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
        ];

        $ccGateways = [
            'payzen'    => 'Ekyna\Bundle\PayumPayzenBundle\EkynaPayumPayzenBundle',
            'atos_sips' => 'Ekyna\Bundle\PayumSipsBundle\EkynaPayumSipsBundle',
            'monetico'  => 'Ekyna\Bundle\PayumMoneticoBundle\EkynaPayumMoneticoBundle',
        ];
        foreach ($ccGateways as $factory => $class) {
            if (!class_exists($class)) {
                continue;
            }

            $methods['Carte bancaire'] = [
                'factory'     => $factory,
                'image'       => 'credit-card.png',
                'description' => '<p>Réglez avec votre carte bancaire.</p>',
                'enabled'     => true,
            ];
            break;
        }

        $paypalFactory = null;
        if (class_exists('Payum\Paypal\ExpressCheckout\Nvp\PaypalExpressCheckoutGatewayFactory')) {
            $paypalFactory = 'paypal_express_checkout';
        } /*elseif (class_exists('Payum\Paypal\Rest\PaypalRestGatewayFactory')) {
            $paypalFactory = 'paypal_rest';
        }*/
        if (!empty($paypalFactory)) {
            $methods['PayPal'] = [
                'factory'     => $paypalFactory,
                'image'       => 'paypal.png',
                'description' => '<p>Réglez avec votre compte PayPal, ou votre carte bancaire.</p>',
                'enabled'     => false,
            ];
        }

        $position = 0;
        foreach ($methods as $name => $options) {
            $output->write(sprintf(
                '- <comment>%s</comment> %s ',
                $name,
                str_pad('.', 44 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));

            // TODO check that factory method exists

            if ($methodRepository->findOneBy(['name' => $name])) {
                $output->writeln('already exists.');
                continue;
            }

            $source = $directory . '/' . $options['image'];
            if (!file_exists($source)) {
                throw new Exception(sprintf('File "%s" does not exists.', $source));
            }

            $target = sys_get_temp_dir() . '/' . uniqid() . '.' . pathinfo($options['image'], PATHINFO_EXTENSION);
            if (!copy($source, $target)) {
                throw new Exception(sprintf('Failed to copy "%s" into "%s".', $source, $target));
            }

            /** @var MediaInterface $image */
            $image = $mediaFactory->create();
            $image
                ->setFile(new File($target))
                ->setFolder($folder)
                ->setTitle($name)
                ->setType(MediaTypes::IMAGE);

            /** @var PaymentMethod $method */
            $method = $methodFactory->create();
            $method
                ->setMedia($image)
                ->setName($name)
                ->setFactoryName($options['factory'])
                ->setTitle($name)
                ->setDescription($options['description'])
                ->setEnabled($options['enabled'])
                ->setAvailable(true)
                ->setPosition($position);

            $manager->persist($method);

            $output->writeln('created.');

            $position++;
        }

        $manager->flush();
    }

    /**
     * Installs the default shipment methods.
     *
     * @param OutputInterface $output
     *
     * @throws Exception
     */
    private function installShipmentMethods(OutputInterface $output): void
    {
        $manager = $this->managerFactory->getManager(ShipmentMethodInterface::class);

        $methodRepository = $this->repositoryFactory->getRepository(ShipmentMethodInterface::class);
        $methodFactory = $this->factoryFactory->getFactory(ShipmentMethodInterface::class);
        $mediaFactory = $this->factoryFactory->getFactory(MediaInterface::class);

        /** @var TaxGroupRepositoryInterface $taxGroupRepository */
        $taxGroupRepository = $this->repositoryFactory->getRepository(TaxGroupInterface::class);
        $defaultTaxGroup = $taxGroupRepository->findDefault();

        $folder = $this->createImageFolder();
        $directory = realpath(__DIR__ . '/../Resources/install/shipment-method');

        $methods = [
            'Dématérialisé'      => [
                'platform'    => VirtualPlatform::NAME,
                'image'       => 'virtual.png',
                'description' => '<p>Vous serez notifié une fois les services mis en place</p>',
                'available'   => true,
                'enabled'     => true,
            ],
            'Retrait en magasin' => [
                'platform'    => InStorePlatform::NAME,
                'image'       => 'in-store.png',
                'description' => '<p>Vous pourrez retirer votre colis à notre magasin ...</p>',
                'available'   => true,
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

            if ($methodRepository->findOneBy(['name' => $name])) {
                $output->writeln('already exists.');
                continue;
            }

            $source = $directory . '/' . $options['image'];
            if (!file_exists($source)) {
                throw new Exception(sprintf('File "%s" does not exists.', $source));
            }
            $target = sys_get_temp_dir() . '/' . $options['image'];
            if (!copy($source, $target)) {
                throw new Exception(sprintf('Failed to copy "%s" into "%s".', $source, $target));
            }

            /** @var MediaInterface $image */
            $image = $mediaFactory->create();
            $image
                ->setFile(new File($target))
                ->setFolder($folder)
                ->setTitle($name)
                ->setType(MediaTypes::IMAGE);

            /** @var ShipmentMethod $method */
            $method = $methodFactory->create();
            $method
                ->setMedia($image)
                ->setTaxGroup($defaultTaxGroup)
                ->setPlatformName($options['platform'])
                ->setName($name)
                ->setEnabled($options['enabled'])
                ->setAvailable($options['available'])
                ->setPosition($position)
                ->setTitle($name)
                ->setDescription($options['description']);

            $manager->persist($method);

            $output->writeln('created.');

            $position++;
        }

        $manager->flush();
    }

    /**
     * Installs the default notifications models.
     *
     * @param OutputInterface $output
     */
    private function installNotificationModels(OutputInterface $output): void
    {
        $manager = $this->managerFactory->getManager(NotifyModelInterface::class);
        $repository = $this->repositoryFactory->getRepository(NotifyModelInterface::class);
        $factory = $this->factoryFactory->getFactory(NotifyModelInterface::class);

        foreach (BNotifyTypes::getChoices([CNotifyTypes::MANUAL]) as $label => $type) {
            $name = $this->translator->trans($label, [], 'EkynaCommerce');

            $output->write(sprintf(
                '- <comment>%s</comment> %s ',
                $name,
                str_pad('.', 44 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));

            if ($repository->findOneBy(['type' => $type])) {
                $output->writeln('already exists.');

                continue;
            }

            /** @var NotifyModelInterface $model */
            $model = $factory->create();
            $model
                ->setType($type)
                ->setEnabled(false);

            $manager->persist($model);

            $output->writeln('created.');
        }

        $manager->flush();
    }

    public static function getName(): string
    {
        return 'ekyna_commerce';
    }
}
