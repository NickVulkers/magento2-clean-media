<?php
/**
 * @package     NickVulkers\Media
 * @author      <contact@nickvulkers.com>
 * @copyright   2023 Nick Vulkers
 * @license     MIT
 */

declare(strict_types=1);

namespace NickVulkers\CleanMedia\Console\Command;

use Symfony\Component\Console\Output\OutputInterface;
use NickVulkers\CleanMedia\Helper\CatalogMediaHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\Console\Cli;
use Magento\Framework\Filesystem;
use SplFileInfo;

/**
 * Class CatalogMedia
 */
class CatalogMediaUnused extends Command
{
    /**
     * Input key for removing unused images
     */
    const INPUT_KEY_REMOVE_UNUSED = 'remove_unused';
    /**
     * Input key for listing unused files
     */
    const INPUT_KEY_LIST_UNUSED = 'list_unused';
    /**
     * @var Filesystem
     */
    public Filesystem $filesystem;
    /**
     * @var CatalogMediaHelper
     */
    public CatalogMediaHelper $catalogMediaHelper;

    /**
     * @param Filesystem $filesystem
     * @param CatalogMediaHelper $catalogMediaHelper
     */
    public function __construct(
        Filesystem         $filesystem,
        CatalogMediaHelper $catalogMediaHelper,
    )
    {
        $this->filesystem = $filesystem;
        $this->catalogMediaHelper = $catalogMediaHelper;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setName('nickvulkers:catalog:media:unused')
            ->setDescription('Get information about unused catalog product media')
            ->addOption(
                self::INPUT_KEY_REMOVE_UNUSED,
                'r',
                InputOption::VALUE_NONE,
                'Remove unused product images'
            )
            ->addOption(
                self::INPUT_KEY_LIST_UNUSED,
                'u',
                InputOption::VALUE_NONE,
                'List unused media files'
            );

        $this->setHelp(
            <<<HELP
                This command shows information about the unused catalog product media.
                HELP
        );

        $this->setAliases(
            [
                'nv:catalog:media:unused',
                'nv:cat:media:unused',
                'nv:cat:med:unused',
                'nv:cat:media:un',
                'nv:cat:med:un',
            ]
        );

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $productMediaPath = $this->catalogMediaHelper->getProductMediaPath();

        if (!is_dir($productMediaPath)) {
            $output->writeln(sprintf('Cannot find "%s" folder.', $productMediaPath));
            $output->writeln('It appears there are no product images to analyze.');
            return Cli::RETURN_FAILURE;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $productMediaPath,
                \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
            )
        );

        $files = [];
        $unusedFiles = 0;
        $removedUnusedFiles = 0;
        $bytesFreed = 0;

        $mediaGalleryPaths = $this->catalogMediaHelper->getMediaGalleryPaths();

        /** @var $info SplFileInfo */
        foreach ($iterator as $info) {
            $filePath = str_replace($this->catalogMediaHelper->getProductMediaPath(), '', $info->getPathname());

            $files[] = $filePath;

            if (!in_array($filePath, $mediaGalleryPaths)) {
                $unusedFiles++;

                if ($input->getOption(self::INPUT_KEY_LIST_UNUSED)) {
                    $output->writeln('Unused file: ' . $filePath);
                }

                if ($input->getOption(self::INPUT_KEY_REMOVE_UNUSED)) {
                    $bytesFreed += filesize($info->getPathname());
                    $removedUnusedFiles += unlink($info->getPathname());
                    $output->writeln(sprintf('Unused "%s" was removed', $filePath));
                }
            }
        }

        $output->writeln('');
        $output->writeln('CatalogMediaUnused');
        $output->writeln(sprintf('Media Gallery entries: %s.', count($mediaGalleryPaths)));
        $output->writeln(sprintf('Files in directory: %s.', count($files)));

        if (!$input->getOption(self::INPUT_KEY_REMOVE_UNUSED)) {
            $output->writeln(sprintf('Unused files: %s.', $unusedFiles));
        } else {
            $output->writeln(sprintf('Removed unused files: %s.', $removedUnusedFiles));
        }

        $output->writeln('');

        if ($input->getOption(self::INPUT_KEY_REMOVE_UNUSED)) {
            $output->writeln(sprintf('Disk space freed: %s Mb', round($bytesFreed / 1024 / 1024)));
            $output->writeln('');
        }

        return Cli::RETURN_SUCCESS;
    }
}
