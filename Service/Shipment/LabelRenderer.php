<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Shipment;

use Ekyna\Bundle\SettingBundle\Manager\SettingManagerInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentLabelInterface;
use Ekyna\Component\Resource\Exception\PdfException;
use Ekyna\Component\Resource\Helper\PdfGenerator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Twig\Environment;

/**
 * Class LabelRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LabelRenderer
{
    public const SIZE_A4 = 'A4';
    public const SIZE_A5 = 'A5';
    public const SIZE_A6 = 'A6';

    private Environment             $twig;
    private PdfGenerator            $generator;
    private SettingManagerInterface $settings;

    public function __construct(
        Environment             $twig,
        PdfGenerator            $generator,
        SettingManagerInterface $settings
    ) {
        $this->twig = $twig;
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
     *
     * @throws PdfException
     */
    public function render(array $labels, bool $raw = false)
    {
        foreach ($labels as $label) {
            if (!$label instanceof ShipmentLabelInterface) {
                throw new UnexpectedTypeException($label, ShipmentLabelInterface::class);
            }
        }

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

        $content = $this->twig->render('@EkynaCommerce/Admin/Common/Shipment/labels.html.twig', [
            'layout' => $layout,
            'labels' => $labels,
        ]);

        $content = $this->generator->generateFromHtml($content, $options);

        if ($raw) {
            return $content;
        }

        if ($config['download']) {
            $file = sys_get_temp_dir() . '/print-label.pdf';
            if (file_exists($file)) {
                unlink($file);
            }
            file_put_contents($file, $content);

            $response = new BinaryFileResponse($file);
            $response->headers->set('Content-Type', 'application/pdf');
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'print-label.pdf');

            return $response;
        }

        return new Response($content, 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Returns the available sizes.
     *
     * @return array<string, string>
     */
    public static function getSizes(): array
    {
        return [
            static::SIZE_A4 => static::SIZE_A4,
            static::SIZE_A5 => static::SIZE_A5,
            static::SIZE_A6 => static::SIZE_A6,
        ];
    }
}
