<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentRowsBuilder;

/**
 * Class DocumentExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentExtension extends \Twig_Extension
{
    /**
     * @var DocumentRowsBuilder
     */
    private $documentRowsBuilder;


    /**
     * Constructor.
     *
     * @param DocumentRowsBuilder $documentRowsBuilder
     */
    public function __construct(DocumentRowsBuilder $documentRowsBuilder)
    {
        $this->documentRowsBuilder = $documentRowsBuilder;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'document_goods_rows',
                [$this->documentRowsBuilder, 'buildGoodRows'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
