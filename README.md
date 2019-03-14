# basicfinder-api
BasicFinder 是集数据标注、任务管理为一体的AI数据标注平台. 实现图片,文本,语音,视频,3d点云的标注功能.支持分类的添加和管理;拖拽式模板编辑配置. 支持工序的串并行，多工序项目的流程配置与多团队分发管控. 支持任务的灵活配置. 独有的数据分发系统保证用户领取作业高效精准. 多达百种数据导出格式. 作业每一步的操作记录都可追溯, 可回显结果等.

# 安装方法

1.命令安装
php composer.phar require basicfinder/basicfinder-api *
或
composer require basicfinder/basicfinder-api *



# 代码中使用

``` php
	$basicfinderApi = new BasicfinderApi();
        $basicfinderApi->init($basicfinderAccount['app_key'], $basicfinderAccount['app_version'], $basicfinderAccount['username'], $basicfinderAccount['password']);
        $accessToken = $basicfinderApi->getAccessToken();
        //var_dump($accessToken);
        
        $list = $basicfinderApi->projects();
        var_dump($list);
```


