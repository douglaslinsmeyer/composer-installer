<?php
/**
 * File HtaccessInstaller.php
 *
 * @author Douglas Linsmeyer <douglinsmeyer@gmail.com>
 */

namespace Dlinsmeyer\Composer\Installer;

/**
 * Class HtaccessInstaller
 *
 * @package Nerdery\WordPress\Installer
 * @author Douglas Linsmeyer <douglinsmeyer@gmail.com>
 */
class HtaccessInstaller
{
    /*
     * Constants
     */
    const CONFIG_KEY_HTACCESS = 'htaccess';
    const DISTRIBUTION_FILE_CONFIG_KEY = 'dist-file';
    const DESTINATION_FILE_CONFIG_KEY = 'file';
    const PLACEHOLDER_REWRITE_BASE = '{REWRITE_BASE}';
    const PLACEHOLDER_ENVIRONMENT = '{ENVIRONMENT}';
    const PLACEHOLDER_SITE_URL = '{SITE_HOST}';
    const ERROR_DIST_FILE_NOT_FOUND = 'Htaccess distribution file does not exist.';
    const CONFIG_KEY_REWRITE_BASE = 'rewrite_base';
    const CONFIG_KEY_SITE_HOST = 'site_host';
    const CONFIG_KEY_ENVIRONMENT = 'environment';
    const ERROR_CONFIGURATION_KEY_NOT_SET = '%s configuration key is not set.';

    /**
     * @var array
     */
    private $installerConfig;

    /**
     * @var array
     */
    private $environmentConfig;

    /**
     * Constructor
     *
     * @param array $installerConfig
     * @param array $environmentConfig
     *
     * @return self
     */
    public function __construct(Array $installerConfig, Array $environmentConfig)
    {
        $this->installerConfig = $installerConfig;
        $this->environmentConfig = $environmentConfig;

        $this->validateConfig($this->environmentConfig);
    }

    /**
     * Generate
     *
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function generate()
    {
        $htaccessConfig = $this->installerConfig[self::CONFIG_KEY_HTACCESS];

        $distributionFileName = $htaccessConfig[self::DISTRIBUTION_FILE_CONFIG_KEY];
        $destinationFileName = $htaccessConfig[self::DESTINATION_FILE_CONFIG_KEY];

        if (file_exists($destinationFileName)) {
            return true;
        }

        if (false === file_exists($distributionFileName)) {
            throw new \InvalidArgumentException(self::ERROR_DIST_FILE_NOT_FOUND);
        }

        $replacementMap = array(
            self::PLACEHOLDER_REWRITE_BASE => $this->environmentConfig[self::CONFIG_KEY_REWRITE_BASE],
            self::PLACEHOLDER_SITE_URL => $this->environmentConfig[self::CONFIG_KEY_SITE_HOST],
            self::PLACEHOLDER_ENVIRONMENT => $this->environmentConfig[self::CONFIG_KEY_ENVIRONMENT]
        );

        $distributionFileContents = file_get_contents($distributionFileName);
        $actualFileContents = str_replace(
            array_keys($replacementMap),
            array_values($replacementMap),
            $distributionFileContents
        );

        $result = file_put_contents($destinationFileName, $actualFileContents);

        return (false === $result) ? false : true;
    }

    /**
     * Validate the configuration
     *
     * @param array $configs
     *
     * @throws \InvalidArgumentException
     * @return bool
     */
    public static function validateConfig(Array $configs)
    {
        if (false === array_key_exists(self::CONFIG_KEY_SITE_HOST, $configs)) {
            throw new \InvalidArgumentException(
                sprintf(
                    self::ERROR_CONFIGURATION_KEY_NOT_SET,
                    self::CONFIG_KEY_SITE_HOST
                )
            );
        }

        if (false === array_key_exists(self::CONFIG_KEY_REWRITE_BASE, $configs)) {
            throw new \InvalidArgumentException(
                sprintf(
                    self::ERROR_CONFIGURATION_KEY_NOT_SET,
                    self::CONFIG_KEY_REWRITE_BASE
                )
            );
        }

        if (false === array_key_exists(self::CONFIG_KEY_ENVIRONMENT, $configs)) {
            throw new \InvalidArgumentException(
                sprintf(
                    self::ERROR_CONFIGURATION_KEY_NOT_SET,
                    self::CONFIG_KEY_ENVIRONMENT
                )
            );
        }

        return true;
    }
}
