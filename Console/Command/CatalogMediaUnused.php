<?php
/**
 * @package     NickVulkers\Media
 * @author      <contact@nickvulkers.com>
 * @copyright   2023 Nick Vulkers
 * @license     MIT
 */

declare(strict_types=1);

namespace NickVulkers\CleanMedia\Console\Command;

use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Console\Cli;
use Magento\Framework\Filesystem;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zend_Db_Select;

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
     * @var ResourceConnection
     */
    private ResourceConnection $resource;

    /**
     * @param ResourceConnection $resource
     * @param Filesystem $filesystem
     */
    public function __construct(
        ResourceConnection $resource,
        Filesystem         $filesystem
    )
    {
        $this->resource = $resource;
        $this->filesystem = $filesystem;
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
        $productMediaPath = $this->getProductMediaPath();

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
        $cachedFiles = 0;
        $bytesFreed = 0;

        $mediaGalleryPaths = $this->getMediaGalleryPaths();

        /** @var $info SplFileInfo */
        foreach ($iterator as $info) {
            $filePath = str_replace($this->getProductMediaPath(), '', $info->getPathname());

            if (str_starts_with($filePath, '/cache/')) {
                $cachedFiles++;

                continue;
            }

            $files[] = $filePath;

            if (!in_array($filePath, $mediaGalleryPaths)) {
                $unusedFiles++;

                if ($input->getOption(self::INPUT_KEY_LIST_UNUSED)) {
                    $output->writeln('Unused file: ' . $filePath);
                }

                if ($input->getOption(self::INPUT_KEY_REMOVE_UNUSED)) {
                    $bytesFreed += filesize($info->getPathname());
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
            $output->writeln(sprintf('Removed unused files: %s.', $unusedFiles));
        }

        $output->writeln('');

        if ($input->getOption(self::INPUT_KEY_REMOVE_UNUSED)) {
            $output->writeln(sprintf('Disk space freed: %s Mb', round($bytesFreed / 1024 / 1024)));
            $output->writeln('');
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * @return array
     */
    private function getMediaGalleryPaths(): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName(Gallery::GALLERY_TABLE))
            ->reset(Zend_Db_Select::COLUMNS)->columns('value');

        return $connection->fetchCol($select);
    }

    /**
     * @return string
     */
    private function getMediaPath(): string
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    }

    /**
     * @return string
     */
    private function getProductMediaPath(): string
    {
        return $this->getMediaPath() . 'catalog/product';
    }
}
