## lake-admin

*  后台管理系统 doc文件夹
*  ipv6全部长度例子：`[2001:0db8:3c4d:0015:0000:0000:1a2f:1a2b]:8000`，共计长46位

## 控制台表格

~~~
// 实例化一个Table对象
$table = new \think\console\Table();
// 设置表头（可选）
$table->setHeader();
// 设置表格数据
$table->setRows();
// 添加单行数据（可选）
$table->addRow();
// 设置表格样式（可选）
$table->setStyle();
// 渲染表格输出
$content = $table->render();
~~~