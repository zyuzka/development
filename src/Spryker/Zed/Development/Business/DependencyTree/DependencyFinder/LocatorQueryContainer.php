<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Development\Business\DependencyTree\DependencyFinder;

use Spryker\Zed\Development\Business\DependencyTree\DependencyReport\DependencyReport;
use Symfony\Component\Finder\SplFileInfo;

class LocatorQueryContainer extends AbstractDependencyFinder
{

    /**
     * @param SplFileInfo $fileInfo
     *
     * @throws \Exception
     *
     * @return void
     */
    public function findDependencies(SplFileInfo $fileInfo)
    {
        $content = $fileInfo->getContents();

        if (preg_match_all('/->(.*?)\(\)->queryContainer\(\)/', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $toBundle = $match[1];

                if (preg_match('/->/', $toBundle)) {
                    $foundParts = explode('->', $toBundle);
                    $toBundle = array_pop($foundParts);
                }

                $toBundle = ucfirst($toBundle);
                $this->addDependency($fileInfo, $toBundle, [DependencyReport::META_DEPENDS_LAYER => self::LAYER_PERSISTENCE]);
            }
        }
    }

}
