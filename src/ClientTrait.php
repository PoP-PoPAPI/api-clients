<?php

declare(strict_types=1);

namespace PoP\APIClients;

use PoP\API\Configuration\Request;
use PoP\ComponentModel\Misc\GeneralUtils;
use PoP\ComponentModel\ComponentConfiguration as ComponentModelComponentConfiguration;

trait ClientTrait
{
    /**
     * Relative Path
     *
     * @return string
     */
    abstract protected function getClientRelativePath(): string;
    /**
     * JavaScript file name
     *
     * @return string
     */
    abstract protected function getJSFilename(): string;
    /**
     * HTML file name
     *
     * @return string
     */
    protected function getIndexFilename(): string
    {
        return 'index.html';
    }
    /**
     * Assets folder name
     *
     * @return string
     */
    protected function getAssetDirname(): string
    {
        return 'assets';
    }
    /**
     * Base dir
     *
     * @return string
     */
    abstract protected function getComponentBaseDir(): string;
    /**
     * Base URL
     *
     * @return string|null
     */
    protected function getComponentBaseURL(): ?string
    {
        return null;
    }
    /**
     * Endpoint URL
     *
     * @return string
     */
    abstract protected function getEndpointURL(): string;

    /**
     * HTML to print the client
     *
     * @return string
     */
    public function getClientHTML(): string
    {
        // Read from the static HTML files and replace their endpoints
        $assetRelativePath = $this->getClientRelativePath();
        $file = $this->getComponentBaseDir() . $assetRelativePath . '/' . $this->getIndexFilename();
        $fileContents = \file_get_contents($file, true);
        $jsFileName = $this->getJSFilename();
        /**
         * Relative asset paths do not work, since the location of the JS/CSS file is
         * different than the URL under which the client is accessed.
         * Then add the URL to the plugin to all assets (they are all located under "assets/...")
         */
        if ($componentBaseURL = $this->getComponentBaseURL()) {
            $fileContents = \str_replace(
                '"' . $this->getAssetDirname() . '/',
                '"' . \trim($componentBaseURL, '/') . $assetRelativePath . '/' . $this->getAssetDirname() . '/',
                $fileContents
            );
        }

        // Can pass either URL or path under current domain
        $endpointURL = $this->getEndpointURL();
        if (ComponentModelComponentConfiguration::namespaceTypesAndInterfaces()) {
            $endpointURL = GeneralUtils::addQueryArgs(
                [
                    Request::URLPARAM_USE_NAMESPACE => true,
                ],
                $endpointURL
            );
        }
        // Modify the endpoint, as a param to the script
        $fileContents = \str_replace(
            '/' . $jsFileName . '?',
            '/' . $jsFileName . '?endpoint=' . urlencode($endpointURL) . '&',
            $fileContents
        );

        return $fileContents;
    }

    /**
     * If the endpoint for the client is requested, print the client and exit
     *
     * @return void
     */
    protected function executeEndpoint(): void
    {
        echo $this->getClientHTML();
        die;
    }
}
