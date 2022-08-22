## tenancy 扩展初始化说明

1. 安装扩展包
`composer require stancl/tenancy`

2. 执行初始化 `php artisan tenancy:install` 生成如下文件：
```
config/tenancy.php
routes/tenant.php
TenancyServiceProvider.php
migrations. Remember to run [php artisan migrate]!
database/migrations/tenant folder.
```

3. 修改服务提供者 `app/Providers/TenancyServiceProvider.php` 增加创建租户时初始化数据的功能
`// Jobs\SeedDatabase::class,` => `Jobs\SeedDatabase::class,`

4. 生成 `Tenant` 模型，继承默认的 `Tenant` 模型，然后更新 `config/tenancy.php` 的 `tenant_model` 配置
`php artisan make:model Tenant`