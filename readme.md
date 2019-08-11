# Cloud Computing Management System - slave
CCMS受控端，主控端见：https://github.com/yzsme/ccms-master-ce
## 部署
受控端仅支持Debian Stretch，以下操作在Debian Stretch下进行。
### 配置桥接网络
参考：https://github.com/yzsme/my-debian-utils/blob/master/nic-bridge.sh
### 使用部署脚本
```
wget -O slave-server-setup.sh https://github.com/yzsme/ccms-slave-ce/raw/master/resources/scripts/slave-server-setup.sh
bash slave-server-setup.sh
# 部署完毕须重启系统
reboot
```
### 配置证书
```
# 生成节点服务器证书，提升是否覆盖时，请输入yes
ccms-slave cert:generate-server --common-name 域名
# 签发客户端证书，用于主控端调用受控端
ccms-slave cert:issue 名称

# 重启Nginx与libvirtd：
systemctl restart nginx
systemctl restart libvirtd
```

### 到主控端添加节点
使用配置证书时签发的客户端证书，在主控端填写受控节点的信息添加节点即可：
![add node.png](https://i.loli.net/2019/08/11/vAeLwCtFb4pnROi.png)

### 测试节点是否能访问主控端
```
ccms-slave master:ping
```
