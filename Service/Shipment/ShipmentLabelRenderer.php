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

use function array_replace;
use function file_exists;
use function file_put_contents;
use function sys_get_temp_dir;
use function unlink;

/**
 * Class ShipmentLabelRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentLabelRenderer
{
    public const SIZE_A4 = 'A4';
    public const SIZE_A5 = 'A5';
    public const SIZE_A6 = 'A6';

    public function __construct(
        private readonly Environment $twig,
        private readonly PdfGenerator $generator,
        private readonly SettingManagerInterface $setting
    ) {
    }

    /**
     * Renders the labels.
     *
     * @param array<ShipmentLabelInterface> $labels
     *
     * @throws PdfException
     */
    public function render(array $labels, bool $raw = false): Response|string
    {
        foreach ($labels as $label) {
            if (!$label instanceof ShipmentLabelInterface) {
                throw new UnexpectedTypeException($label, ShipmentLabelInterface::class);
            }
        }

        $layout = false;

        $options = [
            'unit'         => 'mm',
            'marginTop'    => 6,
            'marginBottom' => 6,
            'marginLeft'   => 6,
            'marginRight'  => 6,
            'paperWidth'   => 210,
            'paperHeight'  => 297,
        ];

        $config = $this->setting->getParameter('commerce.shipment_label');

        if (null !== $size = $config['size']) {
            if (self::SIZE_A4 === $size) {
                $layout = true;
            } elseif (self::SIZE_A5 === $size) {
                $options['paperWidth'] = 148;
                $options['paperHeight'] = 210;
            } elseif (self::SIZE_A6 === $size) {
                $options['paperWidth'] = 105;
                $options['paperHeight'] = 148;
            }
        } elseif ((0 < $width = ($config['width'] ?? 0)) && (0 < $height = ($config['height'] ?? 0))) {
            $options['paperWidth'] = $width;
            $options['paperHeight'] = $height;

            if (0 < $margin = $config['margin'] ?? 0) {
                $options = array_replace($options, [
                    'marginTop'    => $margin,
                    'marginBottom' => $margin,
                    'marginLeft'   => $margin,
                    'marginRight'  => $margin,
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
