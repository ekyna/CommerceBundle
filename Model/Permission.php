<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

/**
 * Class Permission
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Permission
{
    public const DASHBOARD_EXPORT = 'dashboard_export';
    public const STAT_CHART       = 'stat_chart';
    public const ARCHIVE          = 'archive';
    public const GENERATE         = 'generate';

    private function __construct()
    {
    }
}
