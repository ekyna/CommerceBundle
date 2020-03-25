<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Shipment;

use Ekyna\Bundle\CommerceBundle\Service\Document\PdfGenerator;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentLabelInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class LabelRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LabelRenderer
{
    const SIZE_A4 = 'A4';
    const SIZE_A5 = 'A5';
    const SIZE_A6 = 'A6';


    /**
     * @var  EngineInterface
     */
    private $templating;

    /**
     * @var PdfGenerator
     */
    private $generator;

    /**
     * @var SettingsManagerInterface
     */
    private $settings;


    /**
     * Constructor.
     *
     * @param EngineInterface          $templating
     * @param PdfGenerator             $generator
     * @param SettingsManagerInterface $settings
     */
    public function __construct(
        EngineInterface $templating,
        PdfGenerator $generator,
        SettingsManagerInterface $settings
    ) {
        $this->templating = $templating;
        $this->generator = $generator;
        $this->settings = $settings;
    }

    /**
     * Renders the labels.
     *
     * @param ShipmentLabelInterface[] $labels
     * @param bool                     $raw
     *
     * @return Response|string
     */
    public function render(array $labels, $raw = false)
    {
        $layout = false;

        $options = [
            'format'  => 'A4',
            'paper'   => [
                'width'  => null,
                'height' => null,
                'unit'   => 'mm',
            ],
            'margins' => [
                'top'    => 6,
                'right'  => 6,
                'bottom' => 6,
                'left'   => 6,
                'unit'   => 'mm',
            ],
        ];

        $config = $this->settings->getParameter('commerce.shipment_label');

        if (null !== $size = $config['size']) {
            $options['format'] = $size;
            $layout = $size === LabelRenderer::SIZE_A4;
        } elseif (!empty($config['width']) && !empty($config['height'])) {
            $options['margins'] = [
                'top'    => 6,
                'right'  => 6,
                'bottom' => 6,
                'left'   => 6,
                'unit'   => 'mm',
            ];
            $options['paper']['width'] = $config['width'];
            $options['paper']['height'] = $config['height'];

            if (!is_null($config['margin'])) {
                $options['margins'] = array_replace($options['margins'], [
                    'top'    => $config['margin'],
                    'right'  => $config['margin'],
                    'bottom' => $config['margin'],
                    'left'   => $config['margin'],
                ]);
            }
        }

        $content = $this->templating->render('@EkynaCommerce/Admin/Common/Shipment/labels.html.twig', [
            'layout' => $layout,
            'labels' => $labels,
        ]);

        $content = $this->generator->generateFromHtml($content, $options);

        if ($raw) {
            return $content;
        }

        return new Response($content, 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Returns the available sizes.
     *
     * @return array
     */
    public static function getSizes()
    {
        return [
            static::SIZE_A4 => static::SIZE_A4,
            static::SIZE_A5 => static::SIZE_A5,
            static::SIZE_A6 => static::SIZE_A6,
        ];
    }
}
