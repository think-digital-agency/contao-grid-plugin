<?php

declare(strict_types=1);

namespace ThinkDigital\ContaoGridPlugin\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use ThinkDigital\ContaoGridPlugin\ContaoGridPluginBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ContaoGridPluginBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
