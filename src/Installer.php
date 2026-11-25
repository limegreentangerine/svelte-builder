<?php

declare(strict_types=1);

namespace Limegreentangerine\SvelteBuilder;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Plugin\PluginInterface;
use Composer\IO\IOInterface;

class Installer implements PluginInterface, EventSubscriberInterface
{
    private IOInterface $io;
    private Composer $composer;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'post-package-install' => 'onPostPackageInstall',
            'post-package-update' => 'onPostPackageUpdate',
        ];
    }

    public function onPostPackageInstall(PackageEvent $event): void
    {
        $this->runPostInstall();
    }

    public function onPostPackageUpdate(PackageEvent $event): void
    {
        $this->runPostInstall();
    }

    private function runPostInstall(): void
    {
        $scriptPath = __DIR__ . '/../bin/bsc-post-install';
        
        if (!file_exists($scriptPath)) {
            $this->io->writeError("<warning>Post-install script not found at {$scriptPath}</warning>");
            return;
        }

        $this->io->write('<info>Running SvelteBuilder post-install tasks...</info>');
        
        $process = proc_open(
            ['php', $scriptPath],
            [0 => STDIN, 1 => STDOUT, 2 => STDERR],
            $pipes,
            getcwd()
        );

        if (!is_resource($process)) {
            $this->io->writeError('<error>Unable to execute post-install script</error>');
            return;
        }

        $exitCode = proc_close($process);
        if ($exitCode !== 0) {
            $this->io->writeError("<error>Post-install script failed with exit code {$exitCode}</error>");
        }
    }
}
