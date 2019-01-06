<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Com\Tecnick\Barcode\Barcode;

/**
 * Class BarcodeExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BarcodeExtension extends \Twig_Extension
{
    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'barcode_datamatrix',
                [$this, 'getBarcodeDatamatrix']
            ),
            new \Twig_SimpleFilter(
                'barcode_128',
                [$this, 'getBarcode128']
            ),
        ];
    }

    /**
     * Returns the base64 PNG barcode datamatrix.
     *
     * @param string $data
     * @param int    $width
     * @param int    $height
     *
     * @return string
     */
    public function getBarcodeDatamatrix(string $data, $width = 256, $height = 256)
    {
        $barcode = new Barcode();

        $bc = $barcode
            ->getBarcodeObj('DATAMATRIX', $data, $width, $height, 'black', [0, 0, 0, 0])
            ->setBackgroundColor('white');

        return base64_encode($bc->getPngData());
    }

    /**
     * Returns the base64 PNG barcode 128.
     *
     * @param string $data
     * @param int    $width
     * @param int    $height
     *
     * @return string
     */
    public function getBarcode128(string $data, $width = 380, $height = 135)
    {
        $barcode = new Barcode();

        $bc = $barcode
            ->getBarcodeObj('C128', $data, $width, $height, 'black', [0, 0, 0, 0])
            ->setBackgroundColor('white');

        return base64_encode($bc->getPngData());
    }
}
