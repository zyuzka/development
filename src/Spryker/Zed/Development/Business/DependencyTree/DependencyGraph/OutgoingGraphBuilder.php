<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Development\Business\DependencyTree\DependencyGraph;

use ArrayObject;
use Generated\Shared\Transfer\BundleDependencyCollectionTransfer;
use Spryker\Zed\Development\Business\Dependency\BundleParserInterface;
use Spryker\Zed\Graph\Communication\Plugin\GraphPlugin;

class OutgoingGraphBuilder
{

    /**
     * @var string
     */
    protected $bundleName;

    /**
     * @var \Spryker\Zed\Graph\Communication\Plugin\GraphPlugin
     */
    protected $graph;

    /**
     * @var \Spryker\Zed\Development\Business\Dependency\BundleParserInterface
     */
    protected $bundleParser;

    /**
     * @param string $bundleName
     * @param \Spryker\Zed\Graph\Communication\Plugin\GraphPlugin $graph
     * @param \Spryker\Zed\Development\Business\Dependency\BundleParserInterface $bundleParser
     */
    public function __construct($bundleName, GraphPlugin $graph, BundleParserInterface $bundleParser)
    {
        $this->bundleName = $bundleName;
        $this->graph = $graph;
        $this->bundleParser = $bundleParser;
    }

    /**
     * @return string
     */
    public function build()
    {
        $this->graph->init('Outgoing dependencies');

        $allDependencies = new ArrayObject();
        $this->buildGraph($this->bundleName, $allDependencies);

        foreach ($allDependencies as $bundleName => $dependentBundles) {
            $this->graph->addNode($bundleName);
            foreach ($dependentBundles as $dependentBundle) {
                $this->graph->addNode($dependentBundle);
                $this->graph->addEdge($bundleName, $dependentBundle);
            }
        }

        return $this->graph->render('svg');
    }

    /**
     * @param string $bundleName
     * @param \ArrayObject $allDependencies
     *
     * @return void
     */
    protected function buildGraph($bundleName, ArrayObject $allDependencies)
    {
        $dependencies = $this->bundleParser->parseOutgoingDependencies($bundleName);
        $dependencies = $this->getBundleNames($dependencies);
        $allDependencies[$bundleName] = $dependencies;
        foreach ($dependencies as $dependentBundle) {
            if (array_key_exists($dependentBundle, $allDependencies)) {
                continue;
            }
            $this->buildGraph($dependentBundle, $allDependencies);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\BundleDependencyCollectionTransfer $bundleDependencyCollectionTransfer
     *
     * @return array
     */
    protected function getBundleNames(BundleDependencyCollectionTransfer $bundleDependencyCollectionTransfer)
    {
        $bundleNames = [];
        foreach ($bundleDependencyCollectionTransfer->getDependencyBundles() as $dependencyBundleTransfer) {
            $hasDependencyInSource = false;

            foreach ($dependencyBundleTransfer->getDependencies() as $dependencyTransfer) {
                if (!$dependencyTransfer->getIsInTest()) {
                    $hasDependencyInSource = true;
                }
            }

            if ($hasDependencyInSource) {
                $bundleNames[] = $dependencyBundleTransfer->getBundle();
            }
        }

        return $bundleNames;
    }

}
