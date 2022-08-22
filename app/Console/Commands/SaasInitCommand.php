<?php

namespace Plugins\DcatSaas\Console\Commands;

use Plugins\DcatSaas\Support\Config\GenerateConfigReader;
use Plugins\DcatSaas\Support\Process;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SaasInitCommand extends Command
{
    use Traits\StubTrait;

    protected $signature = 'saas:init';

    protected $description = 'Init saas';

    /**
     * The laravel filesystem instance.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Execute the console command.
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function handle()
    {
        $this->filesystem = $this->laravel['files'];

        $this->generateFolders();
        $this->generateFiles();

        $this->call('tenancy:install');
        $this->initTenancyForProject();

        // composer dump-autoload
        // Process::run('composer dump-autoload', $this->output);

        $this->info("Saas init successfully");
    }

    public function initTenancyForProject()
    {
        // 1. 替换 config/tenancy.php 的 tenant_model 配置
        $this->replaceTenancyConfig();
        // 2. 更新 app/Providers/TenancyServiceProvider.php, 允许创建租户的时候通过 DatabaseSeeder 初始化数据
        $this->replaceTenancyProvider();
    }

    public function replaceTenancyConfig()
    {
        $content = File::get($filePath = config_path('tenancy.php'));
        $newContent = str_replace(
            [
                "use Stancl\Tenancy\Database\Models\Tenant;\n\nreturn [",
                "'tenant_model' => Tenant::class,",
                "'localhost',\n    ],",
                "'prefix' => 'tenant',",
                "_base' => 'tenant',",
                "'public' => '%storage_path%/app/public/',\n        ],"
            ],
            [
                "use Stancl\Tenancy\Database\Models\Tenant;\n\n\$prefix = env('DB_DATABASE') . '_';\n\nreturn [",
                "'tenant_model' => \App\Models\Tenant::class,",
                "'localhost',\n\t\tstr_replace(['http://', 'https://'], '', trim(env('APP_URL', ''), '/')),\n    ],",
                "'prefix' => \$prefix,",
                "_base' => \$prefix,",
                "'public' => '%storage_path%/app/public/',\n        ],\n\n\t\t
        /*
        * Use this to support Storage url method on local driver disks.
        * You should create a symbolic link which points to the public directory using command: artisan tenants:link
        * Then you can use tenant aware Storage url: Storage::disk('public')->url('file.jpg')
        * 
        * See https://github.com/archtechx/tenancy/pull/689
        */
        'url_override' => [
            // The array key is local disk (must exist in root_override) and value is public directory (%tenant_id% will be replaced with actual tenant id).
            'public' => 'public-%tenant_id%',
        ],",
            ],
            $content
        );
        File::put($filePath, $newContent);
    }

    public function replaceTenancyProvider()
    {
        $content = File::get($filePath = base_path('app/Providers/TenancyServiceProvider.php'));
        $newContent = str_replace(
            [
                "// Jobs\SeedDatabase::class,",
            ],
            [
                "Jobs\SeedDatabase::class,",
            ],
            $content
        );
        File::put($filePath, $newContent);
    }

    /**
     * Get the list of folders will created.
     *
     * @return array
     */
    public function getFolders()
    {
        return config('dcat-saas.paths.generator');
    }

    /**
     * Generate the folders.
     */
    public function generateFolders()
    {
        foreach ($this->getFolders() as $key => $folder) {
            $folder = GenerateConfigReader::read($key);

            if ($folder->generate() === false) {
                continue;
            }

            $path = config('dcat-saas.paths.saas', base_path()).'/'.$folder->getPath();

            $this->filesystem->makeDirectory($path, 0755, true);
            if (config('dcat-saas.stubs.gitkeep')) {
                $this->generateGitKeep($path);
            }
        }
    }

    /**
     * Generate git keep to the specified path.
     *
     * @param  string  $path
     */
    public function generateGitKeep($path)
    {
        $this->filesystem->put($path.'/.gitkeep', '');
    }

    /**
     * Remove git keep from the specified path.
     *
     * @param  string  $path
     */
    public function removeParentDirGitKeep(string $path)
    {
        if (config('dcat-saas.stubs.gitkeep')) {
            $dirName = dirname($path);
            if (count($this->filesystem->glob("$dirName/*")) >= 1) {
                $this->filesystem->delete("$dirName/.gitkeep");
            }
        }
    }

    /**
     * Get the list of files will created.
     *
     * @return array
     */
    public function getFiles()
    {
        return config('dcat-saas.stubs.files');
    }

    /**
     * Generate the files.
     */
    public function generateFiles()
    {
        foreach ($this->getFiles() as $stub => $file) {

            $path = config('dcat-saas.paths.saas', base_path()).'/'.$file;

            if ($keys = $this->getReplaceKeys($path)) {
                $file = $this->getReplacedContent($file, $keys);
                $path = $this->getReplacedContent($path, $keys);
            }

            if (! $this->filesystem->isDirectory($dir = dirname($path))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
                $this->removeParentDirGitKeep($dir);
            }

            $this->filesystem->put($path, $this->getStubContents($stub));
            $this->removeParentDirGitKeep($dir);

            $this->info("Created : {$path}");
        }
    }
}
