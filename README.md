# DcatSaas extension

[![Latest Stable Version](http://poser.pugx.org/mouyong/dcat-saas/v)](https://packagist.org/packages/mouyong/dcat-saas) [![Total Downloads](http://poser.pugx.org/mouyong/dcat-saas/downloads)](https://packagist.org/packages/mouyong/dcat-saas) [![Latest Unstable Version](http://poser.pugx.org/mouyong/dcat-saas/v/unstable)](https://packagist.org/packages/mouyong/dcat-saas) [![License](http://poser.pugx.org/mouyong/dcat-saas/license)](https://packagist.org/packages/mouyong/dcat-saas) [![PHP Version Require](http://poser.pugx.org/mouyong/dcat-saas/require/php)](https://packagist.org/packages/mouyong/dcat-saas)

## 介绍

一款帮助你在 30 分钟内从 0 搭建 Laravel SaaS 化项目的插件。

在 [fresns/plugin-manager](http://gitee.com/fresns/plugin-manager) 与 [fresns/market-manager](https://gitee.com/fresns/market-manager) 的插件架构设计下，采用 [Tenancy 3.x](https://tenancyforlaravel.com/)、[Dcat-Admin](http://www.dcatadmin.com/)、[Laravel](https://laravel.com/) 可快速建立属于你的 SaaS 业务框架。

初期可采用 [dcat-admin](https://learnku.com/docs/dcat-admin/2.x) 完成中心应用开账号，租户应用建立业务逻辑。

后期推荐采用 [scui](https://lolicode.gitee.io/scui-doc/) 将项目前后端分离化（相关模板可单独联系作者了解详情）。


## 前置要求

- Laravel 9+
- Tenancy 3+
- DcatAdmin 2.*
- fresns/plugin-manager ^2
- fresns/market-manager ^1
- 项目已完成 fresns/plugin-manager、fresns/market-manager 的安装。


## 安装

### 初始化

`composer.json`
```
{
    ...
    "minimum-stability": "dev",
    "prefer-stable": true,
    ...
}
```

```
composer create-project --prefer-dist laravel/laravel saas-test
cd saas-test

初始化 git 仓库
git init
git add .
git commit -m "feat: Init."

composer config repositories.market-manager vcs https://gitee.com/fresns/market-manager
composer config repositories.plugin-manager vcs https://gitee.com/fresns/plugin-manager

composer require fresns/market-manager:dev-master
composer require fresns/plugin-manager:2.x-dev

配置 数据库
配置 项目信息
	APP_NAME
	APP_URL

	DB_HOST
	DB_DATABASE
	DB_USERNAME
	DB_PASSWORD

执行迁移，增加 plugins 表

php artisan migrate

提交仓库变动。方便查看 saas 初始化的文件

从 github 下载 dcat-saas 扩展
php artisan market:require mouyong/dcat-saas

或从 zip 压缩包安装 dcat-saas
php artisan market:require ~/dcat-saas.zip

激活插件
php artisan plugin:activate DcatSaas

初始化 saas 信息
php artisan saas:install
```


### 使用

```
# 常用命令
php artisan saas                                # 查看 saas 扩展包提供的命令
php artisan saas:install                        # 初始化 saas（会提示是否覆盖）
php artisan saas:demo-add                       # 添加租户 可指定租户名，默认 foo
php artisan saas:demo-del                       # 删除租户 可指定租户名，默认 foo
php artisan saas:menu-export                    # 导出菜单（默认会尝试导出 foo 租户多菜单）
php artisan saas:menu-reset                     # 重置菜单 (根据导出的菜单进行租户菜单重置)

php artisan tenants:list                        # 查看租户列表
php artisan tenants:migrate --tenants foo       # 执行 foo 租户的迁移，开发阶段建议指定租户，部署阶段可不指定，以批量运行租户迁移
php artisan tenants:rollback --tenants foo      # 回滚 foo 租户的迁移，开发阶段建议指定租户，部署阶段可不指定，以批量运行租户迁移的回滚操作

# 其他命令自行查看
...
```


### 前后端分离的部署示例

宝塔缓存与前端代理的配置示例：

搭配 flow.aliyun.com，可完成项目 devops 自动化部署。

```
# scui
location ~* ^/(dashboard|tenants-assets|img|config|pc) {
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_pass http://localhost:3000;
    # proxy_pass http://192.168.0.34:3000;
    # proxy_pass http://xxx.hecs.example.com;
    # proxy_pass https://xxx-xxx-dashboard.oss-cn-shenzhen.aliyuncs.com;
}

# scui
location ~* ^/(dashboard|tenants-assets|img|config|pc) {
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_pass https://xxx-dashboard-aone.oss-cn-shenzhen.aliyuncs.com;
}

# scui
location ~* ^/(platform|platform-assets|img|config) {
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_pass https://xxx-dashboard-platform.oss-cn-shenzhen.aliyuncs.com;
}

# 有缓存的配置
location ~* ^/assets {
    proxy_cache cache_one;
    proxy_cache_key $host$uri$is_args$args;
    add_header Nginx-Cache "$upstream_cache_status";
    proxy_cache_valid 200 304 1m;
    proxy_cache_valid 404 500 502 503 504 1m;
    proxy_cache_valid any 1s;
    expires 1s;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_pass http://test-csim-dashboard.oss.iwnweb.com;
}
```


## 协议
[Apache 2.0](LICENSE)


## 开发者交流群
[插件世界技术交流群](https://qm.qq.com/cgi-bin/qm/qr?k=JdZJTPzOEsDo3gCR1ENENRXqWPmM-67l&jump_from=webapi&authKey=5EZ6xwqKptmf3U3QMT/IkclubXceZt2JWqkiQbfwXiELv2d4roHTMX32MmBWoi4q) ID: 305795002
