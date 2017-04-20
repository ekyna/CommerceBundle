<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Com\Tecnick\Barcode\Barcode;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class BarcodeExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BarcodeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'barcode_datamatrix',
                [$this, 'getBarcodeDatamatrix']
            ),
            new TwigFilter(
                'barcode_128',
                [$this, 'getBarcode128']
            ),
        ];
    }

    /**
     * Returns the base64 PNG barcode datamatrix.
     */
    public function getBarcodeDatamatrix(string $data, int $width = 256, int $height = 256): string
    {
        $barcode = new Barcode();

        $bc = $barcode
            ->getBarcodeObj('DATAMATRIX', $data, $width, $height, 'black', [0, 0, 0, 0])
            ->setBackgroundColor('white');

        return base64_encode($bc->getPngData());
    }

    /**
     * Returns the base64 PNG barcode 128.
     */
    public function getBarcode128(string $data, int $width = 380, int $height = 135): string
    {
        $barcode = new Barcode();

        $bc = $barcode
            ->getBarcodeObj('C128', $data, $width, $height, 'black', [0, 0, 0, 0])
            ->setBackgroundColor('white');

        return base64_encode($bc->getPngData());
    }
}
