<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Map;

use Ekyna\Bundle\AdminBundle\Model\SiteAddress;
use Ekyna\Bundle\CommerceBundle\Form\Type\MapType;
use Ekyna\Bundle\CommerceBundle\Model\MapConfig;
use Ekyna\Bundle\CommerceBundle\Repository\CustomerAddressRepository;
use Ekyna\Bundle\CommerceBundle\Repository\CustomerRepository;
use Ekyna\Bundle\CommerceBundle\Repository\OrderRepository;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Layer\HeatmapLayer;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Overlay\Marker;
use Ivory\GoogleMap\Overlay\MarkerClusterType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MapBuilder
 * @package Ekyna\Bundle\CommerceBundle\Service\Map
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MapBuilder
{
    public const MODE_INVOICE  = 'invoice';
    public const MODE_DELIVERY = 'delivery';
    public const MODE_ORDER    = 'order';

    /**
     * @var CustomerAddressRepository
     */
    private $customerAddressRepository;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var SettingsManagerInterface
     */
    private $settingsManager;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var Map
     */
    private $map;

    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @var MapConfig
     */
    private $config;


    /**
     * Constructor.
     *
     * @param CustomerAddressRepository $customerAddressRepository
     * @param OrderRepository           $orderRepository
     * @param SettingsManagerInterface  $settingsManager
     * @param FormFactoryInterface      $formFactory
     */
    public function __construct(
        CustomerAddressRepository $customerAddressRepository,
        OrderRepository $orderRepository,
        SettingsManagerInterface $settingsManager,
        FormFactoryInterface $formFactory
    ) {
        $this->customerAddressRepository = $customerAddressRepository;
        $this->orderRepository = $orderRepository;
        $this->settingsManager = $settingsManager;
        $this->formFactory = $formFactory;
        $this->config = new MapConfig();
    }

    /**
     * Builds the map.
     *
     * @return Map
     */
    public function buildMap(): Map
    {
        if ($this->map) {
            return $this->map;
        }

        $map = new Map();
        $map->setHtmlId('commerceMap');
        $map->setVariable('commerceMap');
        //$map->setLanguage($this->localeProvider->getCurrentLocale());
        //$map->setAsync(true);
        $map->setLibraries(['geometry', 'places', 'visualization']);
        //$map->setAutoZoom(true);
        $map->setMapOptions([
            'zoom'                   => 6,
            'minZoom'                => 3,
            'maxZoom'                => 18,
            'disableDefaultUI'       => true,
            'disableDoubleClickZoom' => true,
            //'styles'                 => json_decode($this->getStyle()),
        ]);
        $map->setStylesheetOptions([
            'width'  => '100%',
            'height' => '760px',
        ]);

        $cluster = $map->getOverlayManager()->getMarkerCluster();
        $cluster->setVariable('commerceMarkerCluster');
        $cluster->setType(MarkerClusterType::MARKER_CLUSTERER);
        /*$cluster->setOptions([
            'styles'                 => json_decode($this->getStyle()),
        ]);*/

        $heat = new HeatmapLayer();
        $heat->setOption('radius', 20);
        $heat->setVariable('commerceHeatmap');
        $map->getLayerManager()->addHeatmapLayer($heat);

        $mainMarker = $this->getMainMarker();
        $cluster->addMarker($mainMarker);
        $map->setCenter($mainMarker->getPosition());

        return $this->map = $map;
    }

    /**
     * Returns the map form.
     *
     * @return FormInterface
     */
    public function buildForm(): FormInterface
    {
        if ($this->form) {
            return $this->form;
        }

        return $this->form = $this->formFactory->create(MapType::class, $this->config, [
            'method' => 'GET',
        ]);
    }

    /**
     * @param Request|null $request
     *
     * @return array
     */
    public function buildLocations(Request $request = null): array
    {
        if ($request) {
            $this->buildForm()->handleRequest($request);

            if ($this->form->isSubmitted() && !$this->form->isValid()) {
                return []; // TODO exception ?
            }
        }

        $groups = $this->config->getGroups()->toArray();

        switch ($this->config->getMode()) {
            case self::MODE_INVOICE:
                return $this->customerAddressRepository->findLocations($groups, true);

            case self::MODE_DELIVERY:
                return $this->customerAddressRepository->findLocations($groups, false);

            case self::MODE_ORDER:
                return $this->orderRepository->findLocations($groups);
        }

        throw new InvalidArgumentException("Unexpected mode '{$this->config->getMode()}'.");
    }

    /**
     * Returns the website address marker.
     *
     * @return Marker
     */
    private function getMainMarker(): Marker
    {
        /** @var SiteAddress $address */
        $address = $this->settingsManager->getParameter('general.site_address');

        $marker = new Marker(new Coordinate($address->getLatitude(), $address->getLongitude()));

        /*$icon = new Icon(
            '/bundles/web/img/icon-marker-main.png',
            new Point(17, 43),
            new Point(0, 0),
            new Size(34, 66),
            new Size(34, 66)
        );
        $icon->setVariable('dm_icon');
        $marker->setIcon($icon);*/

        return $marker;
    }

    /**
     * Returns the mode choices.
     *
     * @return array
     */
    public static function getModeChoices(): array
    {
        return [
            'ekyna_commerce.sale.field.invoice_address'  => self::MODE_INVOICE,
            'ekyna_commerce.sale.field.delivery_address' => self::MODE_DELIVERY,
            'ekyna_commerce.field.revenue'               => self::MODE_ORDER,
        ];
    }
}
