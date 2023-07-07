<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Shipment;

use Ekyna\Bundle\SettingBundle\Manager\SettingManagerInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentLabelInterface;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\Tcpdf\Fpdi;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

use function is_resource;
use function stream_get_contents;
use function strpos;
use function substr;

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
        private readonly Environment             $twig,
        private readonly SettingManagerInterface $setting,
    ) {
    }

    /**
     * Renders the labels.
     *
     * @param array<ShipmentLabelInterface> $labels
     */
    public function render(array $labels): Response|string
    {
        foreach ($labels as $label) {
            if (!$label instanceof ShipmentLabelInterface) {
                throw new UnexpectedTypeException($label, ShipmentLabelInterface::class);
            }
        }

        if (1 === count($labels)) {
            return $this->renderSingleLabel($labels[0]);
        }

        return $this->renderMultipleLabels($labels);

        // TODO Drop unused settings
        // $config = $this->setting->getParameter('commerce.shipment_label');
    }

    /**
     * @param array<int, ShipmentLabelInterface> $labels
     * @return Response
     */
    private function renderMultipleLabels(array $labels): Response
    {
        /** @var array<int, ShipmentLabelInterface> $codes */
        $codes = [];
        /** @var array<int, ShipmentLabelInterface> $pdfs */
        $pdfs = [];

        $codeFormats = [
            ShipmentLabelInterface::FORMAT_ZPL,
            ShipmentLabelInterface::FORMAT_EPL,
        ];

        foreach ($labels as $label) {
            if (in_array($label->getFormat(), $codeFormats, true)) {
                $codes[] = $label;

                continue;
            }

            $pdfs[] = $label;
        }

        if (!empty($pdfs)) {
            $pdf = new Fpdi();

            foreach ($pdfs as $label) {
                $format = $label->getFormat();

                // PDF page
                if (ShipmentLabelInterface::FORMAT_PDF === $format) {
                    if (!is_resource($content = $label->getContent())) {
                        $content = StreamReader::createByString($content);
                    }

                    $count = $pdf->setSourceFile($content);

                    for ($i = 1; $i <= $count; $i++) {
                        $pdf->AddPage();
                        $template = $pdf->importPage($i);
                        $pdf->useTemplate($template);
                    }

                    continue;
                }

                if (is_resource($content = $label->getContent())) {
                    $content = stream_get_contents($content);
                }

                // Image page
                $pdf->AddPage();
                $pdf->Image(
                    '@' . $content,
                    w: 180,
                    type: substr($format, strpos($format, '/') + 1)
                );
            }

            foreach ($codes as $label) {
                // TODO Convert ZPL to image and add to PDF
            }

            $content = $pdf->Output(dest: 'S');

            $response = new Response($content, 200, [
                'Content-Type' => 'application/pdf',
            ]);

            $config = $this->setting->getParameter('commerce.shipment_label');

            if ($config['download']) {
                $disposition = HeaderUtils::makeDisposition(
                    HeaderUtils::DISPOSITION_ATTACHMENT,
                    'shipment-labels.pdf'
                );
                $response->headers->set('Content-Disposition', $disposition);
            }

            return $response;
        }

        $content = $this->twig->render('@EkynaCommerce/Admin/Common/Shipment/labels.html.twig', [
            'labels' => $codes,
        ]);

        return new Response($content);
    }

    private function renderSingleLabel(ShipmentLabelInterface $label): Response
    {
        $extension = match ($format = $label->getFormat()) {
            ShipmentLabelInterface::FORMAT_GIF  => 'gif',
            ShipmentLabelInterface::FORMAT_JPEG => 'jpeg',
            ShipmentLabelInterface::FORMAT_PNG  => 'png',
            ShipmentLabelInterface::FORMAT_ZPL  => 'zpl',
            ShipmentLabelInterface::FORMAT_EPL  => 'epl',
            default                             => 'pdf',
        };

        $filename = $label->getShipment()->getNumber() . '.' . $extension;

        if (is_resource($content = $label->getContent())) {
            $content = stream_get_contents($content);
        }

        $response = new Response($content, headers: [
            'Content-Type' => $format,
        ]);

        $config = $this->setting->getParameter('commerce.shipment_label');

        if ($config['download']) {
            $disposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_INLINE, $filename);
            $response->headers->set('Content-Disposition', $disposition);
        }

        return $response;
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
