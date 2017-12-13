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

    public function getBarcodeDatamatrix($data)
    {
        $barcode = new Barcode();

        $bobj = $barcode->getBarcodeObj(
            'DATAMATRIX',               // barcode type and additional comma-separated parameters
            $data,                      // data string to encode
            256,                        // bar height (use absolute or negative value as multiplication factor)
            256,                        // bar width (use absolute or negative value as multiplication factor)
            'black',                    // foreground color
            array(0, 0, 0, 0)           // padding (use absolute or negative values as multiplication factors)
        )->setBackgroundColor('white'); // background color

        return base64_encode($bobj->getPngData());
    }

    public function getBarcode128($data)
    {
        $barcode = new Barcode();

        $bobj = $barcode->getBarcodeObj(
            'C128',
            $data,
            380,
            135,
            'black',
            array(0, 0, 0, 0)
        )->setBackgroundColor('white');

        return base64_encode($bobj->getPngData());
    }
}
