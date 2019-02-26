<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Bundle\CmsBundle\Service\Renderer\TagRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Common\FlagRenderer;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\SaleNormalizer as BaseNormalizer;

/**
 * Class SaleNormalizer
 * @package Ekyna\Bundle\CommerceBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleNormalizer extends BaseNormalizer
{
    /**
     * @var FlagRenderer
     */
    private $commonRenderer;

    /**
     * @var TagRenderer
     */
    protected $tagRenderer;


    /**
     * Constructor.
     *
     * @param FlagRenderer    $commonRenderer
     */
    public function __construct(FlagRenderer $commonRenderer)
    {
        $this->commonRenderer = $commonRenderer;
    }

    /**
     * @inheritdoc
     */
    public function normalize($sale, $format = null, array $context = [])
    {
        $data = parent::normalize($sale, $format, $context);

        if ($this->contextHasGroup('Summary', $context)) {
            $data['flags'] = $this->commonRenderer->renderSaleFlags($sale, ['badge' => false]);
            /** @var \Ekyna\Bundle\CmsBundle\Model\TagsSubjectInterface $sale */
            $data['tags'] = $this->getTagRenderer()->renderTags($sale, ['text' => false, 'badge' => false]);
        }

        return $data;
    }

    /**
     * Returns the tag renderer.
     *
     * @return TagRenderer
     */
    private function getTagRenderer()
    {
        if ($this->tagRenderer) {
            return $this->tagRenderer;
        }

        return $this->tagRenderer = new TagRenderer();
    }
}
