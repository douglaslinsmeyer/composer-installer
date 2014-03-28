<?php
/**
 * File Installer.php
 *
 * @author Douglas Linsmeyer <douglinsmeyer@gmail.com>
 */

namespace Dlinsmeyer\Composer;

use Composer\Script\Event;
use Nerdery\WordPress\Installer\HtaccessInstaller;
use Symfony\Component\Yaml\Inline;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Installer
 *
 * @package Nerdery\WordPress
 * @author Douglas Linsmeyer <douglinsmeyer@gmail.com>
 */
class Installer
{
    /*
     * Constants
     */
    const EXTRAS_KEY_INSTALLER = 'nerdery-installer';
    const EXTRAS_KEY_INCENTEEV = 'incenteev-parameters';
    const CONFIG_KEY_CONFIG_FILE = 'config-file';
    const INCENTEEV_PARAMETER_KEY = 'parameter-key';
    const ERROR_INVALID_ENVIRONMENT_CONFIG = 'Invalid environment configuration, required key %s does not exist.';
    const ERROR_INCENTEEV_EXTRA_MISSING = 'Unable to locate the Incenteev composer extra configuration.';
    const ERROR_INSTALLER_CONFIG_NOT_FOUND = 'Error, unable to locate installer configuration.';

    /**
     * @var array
     */
    private $composerExtras;

    /**
     * Construct
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $composer = $this->event->getComposer();
        $package = $composer->getPackage();

        $this->composerExtras = $package->getExtra();
        $this->environmentConfigs = $this->getEnvironmentConfigs();

        $this->install();
    }

    /**
     * Run the installer
     *
     * Creates a singleton of the Installer.
     *
     * @param Event $event
     *
     * @return self
     */
    public static function run(Event $event)
    {
        return new self($event);
    }

    /**
     * Install
     *
     * @return void
     */
    private function install()
    {
        $this->installHtaccess();
    }

    /**
     * Load environment configuration
     *
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function getEnvironmentConfigs()
    {
        $installerConfig = $this->getInstallerConfig();
        $incenteevConfig = $this->getIncenteevConfig();

        $yamlParser = new Parser();
        $environmentConfig = $yamlParser->parse(
            file_get_contents(
                $installerConfig[self::CONFIG_KEY_CONFIG_FILE]
            )
        );

        $parameterKeyName = $incenteevConfig[self::INCENTEEV_PARAMETER_KEY];

        if (false === array_key_exists($parameterKeyName, $environmentConfig)) {
            throw new \InvalidArgumentException(
                sprintf(
                    self::ERROR_INVALID_ENVIRONMENT_CONFIG,
                    self::INCENTEEV_PARAMETER_KEY
                )
            );
        }

        return $environmentConfig[$parameterKeyName];
    }

    /**
     * Install Htaccess file
     *
     * @return bool
     */
    public function installHtaccess()
    {
        $installerConfig = $this->getInstallerConfig();
        $environmentConfig = $this->getEnvironmentConfigs();
        $htaccessInstaller = new HtaccessInstaller(
            $installerConfig,
            $environmentConfig
        );

        $htaccessInstallation = $htaccessInstaller->generate();

        return $htaccessInstallation;
    }

    /**
     * Get the Incenteev config
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    private function getIncenteevConfig()
    {
        if (false === array_key_exists(self::EXTRAS_KEY_INCENTEEV, $this->composerExtras)) {
            throw new \InvalidArgumentException(
                self::ERROR_INCENTEEV_EXTRA_MISSING
            );
        }

        return $this->composerExtras[self::EXTRAS_KEY_INCENTEEV];
    }

    /**
     * Get the Installer config
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    private function getInstallerConfig()
    {
        if (false === array_key_exists(self::EXTRAS_KEY_INSTALLER, $this->composerExtras)) {
            throw new \InvalidArgumentException(
                self::ERROR_INSTALLER_CONFIG_NOT_FOUND
            );
        }

        return $this->composerExtras[self::EXTRAS_KEY_INSTALLER];
    }
}
