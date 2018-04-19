<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Shipment;

use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentLabelInterface;
use Knp\Snappy\GeneratorInterface;
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
     * @var GeneratorInterface
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
     * @param GeneratorInterface       $generator
     * @param SettingsManagerInterface $settings
     */
    public function __construct(
        EngineInterface $templating,
        GeneratorInterface $generator,
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
     *
     * @return Response
     */
    public function render(array $labels)
    {
        $layout = false;
        $options = [
            'page-size'     => 'A4',
            'margin-top'    => '0',
            'margin-right'  => '0',
            'margin-bottom' => '0',
            'margin-left'   => '0',
        ];

        $config = $this->settings->getParameter('commerce.shipment_label');

        if (null !== $size = $config['size']) {
            $options['page-size'] = $size;
            $layout = $size === LabelRenderer::SIZE_A4;
        } elseif (!empty($config['width']) && !empty($config['height'])) {
            $options['page-width'] = $config['width'] . 'mm';
            $options['page-height'] = $config['width'] . 'mm';

            if (!empty($config['margin'])) {
                $options['margin-top'] = $config['margin'] . 'mm';
                $options['margin-right'] = $config['margin'] . 'mm';
                $options['margin-bottom'] = $config['margin'] . 'mm';
                $options['margin-left'] = $config['margin'] . 'mm';
            }
        }

        $content = $this->templating->render('EkynaCommerceBundle:Admin/Common/Shipment:labels.html.twig', [
            'layout' => $layout,
            'labels' => $labels,
        ]);

        $content = $this->generator->getOutputFromHtml($content, $options);

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
