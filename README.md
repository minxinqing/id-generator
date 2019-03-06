# id生成器

## 环境依赖

1. PHP 7.0 +
2. [ext-Swoole 4.0 +](https://github.com/swoole/swoole-src)
3. [ext-seaslog 1.9+](https://github.com/SeasX/SeasLog)

## 安装
* Clone project
* Install requires `composer install`

## 运行
`php bin/server.php start`

## 访问
`curl http://127.0.0.1:8077/index/generate`

`
{
	"code": 0,
	"data": 410207999161012224
}
`

