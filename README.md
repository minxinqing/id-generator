# id生成器

使用snowflake算法，依赖swoole扩展，实现php版的id生成器。
阿里云4核8G的机器上，开启4个worker，QPS过万，可以满足一般场景的使用。
（QPS上限多少不知道，因为测试机load太高，压不上去了）

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
