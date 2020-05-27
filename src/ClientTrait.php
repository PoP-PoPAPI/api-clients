<?php

declare(strict_types=1);

namespace PoP\APIClients;

use PoP\ComponentModel\ComponentConfiguration as ComponentModelComponentConfiguration;
use PoP\API\Configuration\Request;

trait ClientTrait
{
    /**
     * Vendor Dir Path
     *
     * @return string
     */
    abstract protected function getVendorDirPath(): string;
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
    protected function getAssetsDirname(): string
    {
        return 'assets';
    }
    /**
     * Base dir
     *
     * @return string
     */
    abstract protected function getBaseDir(): string;
    /**
     * Base URL
     *
     * @return string
     */
    abstract protected function getBaseURL(): string;
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
        $dirPath = $this->getVendorDirPath();
        $file = $this->getBaseDir() . $dirPath . '/' . $this->getIndexFilename();
        $fileContents = \file_get_contents($file, true);
        $jsFileName = $this->getJSFilename();
        /**
         * Relative asset paths do not work, since the location of the JS/CSS file is
         * different than the URL under which the client is accessed.
         * Then add the URL to the plugin to all assets (they are all located under "assets/...")
         */
        $fileContents = \str_replace(
            '"' . $this->getAssetsDirname() . '/',
            '"' . \trim($this->getBaseURL(), '/') . $dirPath . '/' . $this->getAssetsDirname() . '/',
            $fileContents
        );

        // Current domain
        $endpointURL = $this->getEndpointURL();
        if (ComponentModelComponentConfiguration::namespaceTypesAndInterfaces()) {
            $endpointURL = \add_query_arg(Request::URLPARAM_USE_NAMESPACE, true, $endpointURL);
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
