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
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('barcode_datamatrix', [$this, 'getBarcodeDatamatrix'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('barcode_128', [$this, 'getBarcode128'], ['is_safe' => ['html']]),
        ];
    }

    public function getBarcodeDatamatrix($data, $width = 256, $height = 256)
    {
        $barcode = new Barcode();

        $bc = $barcode
            ->getBarcodeObj('DATAMATRIX', $data, $width, $height, 'black', array(0, 0, 0, 0))
            ->setBackgroundColor('white');

        return base64_encode($bc->getPngData());
    }

    public function getBarcode128($data, $width = 380, $height = 135)
    {
        $barcode = new Barcode();

        $bc = $barcode
            ->getBarcodeObj('C128', $data, $width, $height, 'black', array(0, 0, 0, 0))
            ->setBackgroundColor('white');

        return base64_encode($bc->getPngData());
    }
}
