(function (window){
    
    var YrMid = {};

    /**
     * 62进制字典
     */
    YrMid.str62keys = [
        "0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", 
        "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", 
        "u", "v", "w", "x", "y", "z",
        "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", 
        "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", 
        "U", "V", "W", "X", "Y", "Z"
    ];
    
    YrMid.strIntkeys = [
        "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"
    ];

    /**
     * 62进制值转换为10进制
     * @param {String} str62 62进制值
     * @return {String} 10进制值
     */
    YrMid.str62to10 = function(str62) {
        var i10 = 0;
            for (var i = 0; i < str62.length; i++) {
            var n = str62.length - i - 1;
            var s = str62[i];
            i10 += this.str62keys.indexOf(s) * Math.pow(62, n);
        }
        return i10;
    };

    /**
     * 10进制值转换为62进制
     * @param {String} int10 10进制值
     * @return {String} 62进制值
     */
    YrMid.int10to62 = function(int10) {
        var s62 = '';
        var r = 0;
        while (int10 != 0){
            r = int10 % 62;
            s62 = this.str62keys[r] + s62;
            int10 = Math.floor(int10 / 62);
        }
        return s62;
    };

    /**
     * URL字符转换为mid
     * @param {String} url 微博URL字符，如 "wr4mOFqpbO"
     * @return {String} 微博mid，如 "201110410216293360"
     */
    YrMid.UrlToMid = function(url) {
        var mid = '';
        url = url.substring(3);
        for (var i = url.length - 4; i > -4; i = i - 4){ //从最后往前以4字节为一组读取URL字符
            var offset1 = i < 0 ? 0 : i;
            var offset2 = i + 4;
            var str = url.substring(offset1, offset2);
            str = this.str62to10(str);
            if (offset1 > 0) {//若不是第一组，则不足7位补0
                while (str.length < 7){
                    str = '0' + str;
                }
            }
            mid = str + mid;
        }
        return (parseInt(mid)-1020000);
    };

    /**
     * mid转换为URL字符
     * @param {String} mid 微博mid，如 "201110410216293360"
     * @return {String} 微博URL字符，如 "wr4mOFqpbO"
     */
    YrMid.MidToUrl = function(mid) {
        mid = (parseInt(mid)+1020000)+'';
        var url = '';
        for (var i = mid.length - 7; i > -7; i = i - 7){ //从最后往前以7字节为一组读取mid
            var offset1 = i < 0 ? 0 : i;
            var offset2 = i + 7;
            var num = mid.substring(offset1, offset2);
            num = this.int10to62(num);
            url = num + url;
        }
        //var f1 = this.strIntkeys[(1+Math.floor(Math.random()*(10-1)))];
        //var f2 = this.str62keys[(10+Math.floor(Math.random()*(60-10)))];
        if(parseInt(mid[1]) === 0){
            var f1 = parseInt(mid[1])+1;
        } else {
            var f1 = parseInt(mid[1]);
        }
        return 'N'+mid[1]+mid[2]+''+url;
    };

    YrMid.disp = function($this, url, tmpurl, d) {
        $this.data('oldurl', url).data('midflag', 1);
        tmpurl = tmpurl.replace('http://','');
        var tmp = tmpurl.split('/');
        if(d != undefined){
            var re = new RegExp(d,"");
            tmp[2] = d+this.MidToUrl(tmp[2].replace(re,''));
        } else {
            tmp[2] = this.MidToUrl(tmp[2]);
        }
        $this.attr('href', 'http://'+tmp.join('/'));
    };
    
    window.Date.prototype.Format = function (fmt) {
        var o = {
            "Y+": this.getFullYear(), //年份 
            "M+": this.getMonth() + 1, //月份 
            "d+": this.getDate(), //日 
            "h+": this.getHours(), //小时 
            "m+": this.getMinutes(), //分 
            "s+": this.getSeconds(), //秒 
            "q+": Math.floor((this.getMonth() + 3) / 3), //季度 
            "S": this.getMilliseconds() //毫秒 
        };
        if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
        for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
        return fmt;
    };

    //全局对象 - 当命名空间使用
    jQuery.YR = {};
    
    //微信信息命名空间
    jQuery.YR['Wechat'] = {
        share : {
            title   : '',
            content : '',
            picurl  : '',
            url     : '',
            callback: undefined
        }
    };

    //依赖jQuery
    jQuery.extend({
        isTouchDevice : navigator.userAgent.match(/(iPhone|iPod|iPad|Android|playbook|silk|BlackBerry|BB10|Windows Phone|Tizen|Bada|webOS|IEMobile|Opera Mini)/),
        isTouch : (('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0) || (navigator.maxTouchPoints)),
        /*
            注册命名空间函数 - 使多方配合时尽量不产生冲突
            @param string sNameSpace 例如 var c = $.namespace(a.b.c); 【*错误使用: var c = $.namespace(jQuery.YR);】
            @return object
        */
        namespace: function(sNameSpace) {
            if(!sNameSpace || !sNameSpace.length){
                return null;
            }
            var levels = sNameSpace.split( ".");
            var currentNS = jQuery.YR;
            var index = 0;
            if(levels[0] == 'jQuery' || levels[0] == "$"){
                index = 1;
                if(levels[1] == 'YR'){
                    index = 2;
                }
            }
            for(var i=index, total = levels.length; i < total; i++) {
                currentNS[levels[i]] = currentNS[levels[i]] || {};
                currentNS = currentNS[levels[i]];
            }
            return currentNS;
        },
        //检查命名空间是否存在
        checkNamespace:function(sNameSpace){
            if(!sNameSpace || !sNameSpace.length){
                return false;
            }
            var levels = sNameSpace.split( ".");
            var currentNS = jQuery.YR;
            var index = 0;
            if(levels[0] == 'jQuery' || levels[0] == "$"){
                index = 1;
                if(levels[1] == 'YR'){
                    index = 2;
                }
            }
            for(var i=index, total = levels.length; i < total; i++) {
                if(currentNS[levels[i]] == undefined || currentNS[levels[i]] == null){
                    return false;
                }
                currentNS = currentNS[levels[i]];
            }
            return true;
        },
        mid:YrMid,
        //获取DOM元素 - 根据ID
        id: function(id){
			return document.getElementById(id);
		},
        //获取doc 兼容浏览器
		doc: function(){
			var back_doc=(document.compatMode != "BackCompat") ? document.documentElement : document.body;
			return window.navigator.userAgent.indexOf("Opera")>-1? document.body : back_doc;
		},
        //窗口改变时
        windowResize: function(callback) {
            $(window).resize(function(){
                callback.apply(this);
            });          
        },
        //随机函数
        randomFun: function(min, max){
            return Math.floor(min + Math.random() * (max - min));
        },
        getDownloadUrl: function(yingyongbaoUrl, iosUrl, androidUrl){//获取对应的下载链接
            var userAgentInfo = window.navigator.userAgent,
                Agents = ["MicroMessenger", "Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod"],      
                flag = false,       
                v = 0;
            for (; v < Agents.length; v++){     
                if (userAgentInfo.indexOf(Agents[v]) > 0) { 
                    flag = true; 
                    break;  
                }  
            }
            if (flag) {
                if (Agents[v] == 'MicroMessenger') {
                    return yingyongbaoUrl;
                }
                if (Agents[v] == 'Android') {
                    return androidUrl;
                }

                if (Agents[v] == 'iPhone' || Agents[v] == 'iPad' || Agents[v] == 'iPod') {
                    return iosUrl;
                }             
            }
        },
        //校验客户端请求
        appRequest: {
            is: function(){
                var cookieVal = $.getCookie('YR-App-N');
                if (cookieVal === undefined || cookieVal === null || cookieVal == '') {
                    return false;
                }
                return true;
            },
            hasName: function(name){
                if (typeof name === 'string') {
                    name = [name];
                }
                var cookieVal = $.getCookie('YR-App-N');
                if (cookieVal === undefined || cookieVal === null || cookieVal == '') {
                    return false;
                }
                if (name.indexOf(cookieVal) > -1) {
                    return true;
                }
                return false;
            },
            hasSystem: function(system){
                if (typeof system === 'string') {
                    system = [system];
                }
                var cookieVal = $.getCookie('YR-App-S');
                if (cookieVal === undefined || cookieVal === null || cookieVal == '') {
                    return false;
                }
                if (system.indexOf(cookieVal) > -1) {
                    return true;
                }
                return false;
            },
            hasVersion: function(version){
                if (typeof version === 'string') {
                    version = [version];
                }
                var cookieVal = $.getCookie('YR-App-V');
                if (cookieVal === undefined || cookieVal === null || cookieVal == '') {
                    return false;
                }
                if (version.indexOf(cookieVal) > -1) {
                    return true;
                }
                return false;
            },
            getVersion: function(){
                var cookieVal = $.getCookie('YR-App-V');
                if (cookieVal === undefined || cookieVal === null || cookieVal == '') {
                    return 0;
                }else{
                	return cookieVal;
                }
            },
            share: function(){
                if ($.appRequest.hasName('utanbaby')) {
                   window.location.href = 'utanbaby://share?callback=';
                }
                if ($.appRequest.hasName('dayima')) {
                   window.location.href = 'utandayima://share?callback=';
                }
                if ($.appRequest.hasName('guimi')) {
                   window.location.href = 'guimi://share?callback=';
                }
            },
            login: function(){
                if ($.appRequest.hasName('utanbaby')) {
                   window.location.href = 'http://gmlm.utan.com/public/html/login';
                }
                if ($.appRequest.hasName('dayima')) {
                   window.location.href = 'http://gmlm.utan.com/public/html/login';
                }
                if ($.appRequest.hasName('guimi')) {
                   window.location.href = 'guimi://needLogin';
                }
            },
            getLoginProtocol: function(){
                if ($.appRequest.hasName('utanbaby')) {
                   return 'http://gmlm.utan.com/public/html/login';
                }
                if ($.appRequest.hasName('dayima')) {
                   return 'http://gmlm.utan.com/public/html/login';
                }
                if ($.appRequest.hasName('guimi')) {
                   return 'guimi://needLogin';
                }
                return '###';
            }
        },
        //判断是否来至于微信
        detectWechat: function(){
            var ua = window.navigator.userAgent.toLowerCase();
            if(ua.match(/MicroMessenger/i) == 'micromessenger'){
                return true;
            }else{
                return false;
            }
        },
        //微信分享
        wechatShare: function(appId, timestamp, nonceStr, signature, shareContentObj, callback){
            $.YR.Wechat.share.title   = shareContentObj['title'];
            $.YR.Wechat.share.content = shareContentObj['content'];
            $.YR.Wechat.share.url     = shareContentObj['url'];
            $.YR.Wechat.share.picurl  = shareContentObj['pic'];
            $.YR.Wechat.share.callback  = callback;
            wx.config({
                debug: false,
                appId: appId,
                timestamp: timestamp,
                nonceStr: nonceStr,
                signature: signature,
                jsApiList: [
                    'onMenuShareTimeline',
                    'onMenuShareAppMessage'
                ]
            });
            wx.ready(function () {
                // 在这里调用 API
                wx.checkJsApi({
                    jsApiList: [
                      'getNetworkType',
                      'previewImage'
                    ],
                    success: function (res) {

                    }
                });
                //分享朋友
                wx.onMenuShareAppMessage({
                    title: $.YR.Wechat.share.title,
                    desc: $.YR.Wechat.share.content,
                    link: $.YR.Wechat.share.url,
                    imgUrl: $.YR.Wechat.share.picurl,
                    trigger: function (res) {
                    },
                    success: function (res) {
                        if ($.YR.Wechat.share.callback != undefined) {
                            $.YR.Wechat.share.callback.apply(this);
                        }
                    },
                    cancel: function (res) {
                    },
                    fail: function (res) {
                    }
                });

                //分享朋友圈
                wx.onMenuShareTimeline({
                    title: $.YR.Wechat.share.content,
                    desc: $.YR.Wechat.share.content,
                    link: $.YR.Wechat.share.url,
                    imgUrl: $.YR.Wechat.share.picurl,
                    trigger: function (res) {
                    },
                    success: function (res) {
                        if ($.YR.Wechat.share.callback != undefined) {
                            $.YR.Wechat.share.callback.apply(this);
                        }
                    },
                    cancel: function (res) {
                    },
                    fail: function (res) {
                    }
                });

                wx.onMenuShareQQ({
                    title: $.YR.Wechat.share.title,
                    desc: $.YR.Wechat.share.content,
                    link: $.YR.Wechat.share.url,
                    imgUrl: $.YR.Wechat.share.picurl,
                    success: function () { 
                       if ($.YR.Wechat.share.callback != undefined) {
                            $.YR.Wechat.share.callback.apply(this);
                        }
                    },
                    cancel: function () { 
                    }
                });

                wx.onMenuShareWeibo({
                    title: $.YR.Wechat.share.title,
                    desc: $.YR.Wechat.share.content,
                    link: $.YR.Wechat.share.url,
                    imgUrl: $.YR.Wechat.share.picurl,
                    success: function () { 
                        if ($.YR.Wechat.share.callback != undefined) {
                            $.YR.Wechat.share.callback.apply(this);
                        }
                    },
                    cancel: function () { 
                    }
                });

                wx.onMenuShareQZone({
                    title: $.YR.Wechat.share.title,
                    desc: $.YR.Wechat.share.content,
                    link: $.YR.Wechat.share.url,
                    imgUrl: $.YR.Wechat.share.picurl,
                    success: function () { 
                        if ($.YR.Wechat.share.callback != undefined) {
                            $.YR.Wechat.share.callback.apply(this);
                        }
                    },
                    cancel: function () { 
                    }
                });
            });
        },
        wechatShareInit: function(callback){//获取微信分享必须的参数
            if ($.detectWechat()) {
                var url = window.location.href;
                //获取微信分享需要的信息
                $.getJSON('http://ryx.utan.com/getwechatinfo?jsoncallback=?', {url:url}, function(result){
                    $.wechatShare(result.data.appId, result.data.timestamp, result.data.nonceStr, result.data.signature, initAppShareContentObj, callback);
                }, 'json');
            }
        },
        /**
         * code          app标识 例如：renyuxian 
         * 
         * isDirectOpen  0=不直接打开 1=直接打开
         * 
         * isDownloadTip 0=不弹出下载提示 1=弹层下载提示
         * 
         * specifiedDownloadUrl = 指定下载URL
         * 
         * bandElems     需要绑定时间的元素 
         * 例如：
         * bandElems= '#a' 
         * bandElems = '#a,#b' 
         * bandElems = '.a' 
         * bandElems = ['.a', '#b'] 
         * 
         * callback 回调函数
         * 例如：
         * callback = function() {
         *      //todo
         * }
         * 
         * demo: 
         * $.openOrInstallApp('renyuxian', 1, 0);
         * $.openOrInstallApp('renyuxian', 1, 0, 'http://xxxx');
         * $.openOrInstallApp('renyuxian', 0, 1, '', '#ceshiId');
         * $.openOrInstallApp('renyuxian', 0, 1, '', '#ceshiId', function(){
         *      //todo
         * });
         */
        openOrInstallApp: function(code, isDirectOpen, isDownloadTip, specifiedDownloadUrl, bandElems, callback) {//打开或安装APP
            
            if (isDirectOpen === undefined) {
                isDirectOpen = 0;
            }
            
            if (isDownloadTip === undefined) {
                isDownloadTip = 0;
            }
            
            if (1 === jQuery.YR['openOrInstallAppClick']) {
                return false;
            }
            jQuery.YR['openOrInstallAppClick'] = 1;
            
            var ua = navigator.userAgent.toLowerCase();

            var runFun = function()
            {
                var codeList = jQuery.YR['openOrInstallAppCfgs'];
                
                if (codeList[code] === undefined || codeList[code] === null || codeList[code] === '') {
                    jQuery.YR['openOrInstallAppClick'] = 0;
                    return false;
                }

                if ($.appRequest.is()) {//是APP打开
                    if ($.appRequest.hasSystem('android')) {
                        window.location.href = codeList[code]['androidDownload'];
                    } else {
                        window.location.href = codeList[code]['iosDownload'];
                    }
                } else {//非APP打开
                    var system = 'android';
                    if (navigator.userAgent.match(/(iPhone|iPod|iPad);?/i)) {
                        system = 'ios';
                    }
                    var protocolUrl = codeList[code]['iosProtocol'];
                    var downloadUrl = codeList[code]['iosDownload'];
                    if ($.detectWechat()) {//微信打开
                        downloadUrl = codeList[code]['wechatDownload'];
                    }
                    if (system === 'android') {
                        protocolUrl = codeList[code]['androidProtocol'];
                        downloadUrl = codeList[code]['androidDownload'];
                        if ($.detectWechat()) {//微信打开
                            downloadUrl = codeList[code]['wechatDownload'];
                        }
                    }
                    
                    if (isDownloadTip === 1) {
                        var box = '<div id="openAppBoxId" style="font-size:16px;width: '+document.body.clientWidth+'px; height: 100%; top: 0px;margin: 0 auto;z-index: 999999;position: fixed;background-color: rgba(0,0,0,.3);display: -webkit-box;-webkit-box-pack: center;-webkit-box-align: center;"><div style="padding: 8px 15px;border-radius: 8px;background-color: #FFF;"><p style="margin: 0;color: #333;" id="openAppBoxOpenId">'+codeList[code]['openTips']+'</p><div id="openAppBoxDownId"  style="display:none;padding: 20px;text-align: center;"><a style="font-size:16px;display: inline-block;color: #FFF;background: #ff5000;margin-right: 15px;padding: 7px 10px;border-radius: 8px;text-decoration: none;" id="openAppBoxDownloadBtnId" target="_download">下载客户端</a><a style="font-size:16px;padding: 7px 10px;border-radius: 8px;display: inline-block;color: #FFF;background: #5f646e;text-decoration: none;" onclick="$(\'#openAppBoxId\').hide();">逛逛别的</a></div></div></div>';

                        if ($('#openAppBoxId').get(0) != null) {
                            $('#openAppBoxDownId').hide();
                            $('#openAppBoxOpenId').html(codeList[code]['openTips']);
                            $('#openAppBoxId').css('display', '-webkit-box');
                        } else {
                            $(box).appendTo(document.body);
                        }
                        
                        $('#openAppBoxDownloadBtnId').attr('href', downloadUrl);
                    } else {
                        var box = '<div id="openAppBoxId" style="font-size:16px;width: '+document.body.clientWidth+'px; height: 100%; top: 0px;margin: 0 auto;z-index: 999999;position: fixed;background-color: rgba(0,0,0,.3);display: -webkit-box;-webkit-box-pack: center;-webkit-box-align: center;"><div style="padding: 8px 15px;border-radius: 8px;background-color: #FFF;"><p style="margin: 0;color: #333;" id="openAppBoxOpenId">'+codeList[code]['openTips']+'</p><div id="openAppBoxDownId"  style="display:none;padding: 20px;text-align: center;"><a style="font-size:16px;display: inline-block;color: #FFF;background: #ff5000;margin-right: 15px;padding: 7px 10px;border-radius: 8px;text-decoration: none;" id="openAppBoxDownloadBtnId" target="_download">下载客户端</a><a style="font-size:16px;padding: 7px 10px;border-radius: 8px;display: inline-block;color: #FFF;background: #5f646e;text-decoration: none;" onclick="$(\'#openAppBoxId\').hide();">逛逛别的</a></div></div></div>';

                        if ($('#openAppBoxId').get(0) != null) {
                            $('#openAppBoxDownId').hide();
                            $('#openAppBoxOpenId').html(codeList[code]['openTips']);
                            $('#openAppBoxId').css('display', '-webkit-box');
                        } else {
                            $(box).appendTo(document.body);
                        }
                    }
                    
                    var ifr = null;
                    if ($.detectWechat()) {
                        window.location.href = downloadUrl;
                    } else if ( ua.indexOf('qq/') > -1 || ( ua.indexOf('safari') > -1 && ua.indexOf('os 9_') > -1 ) || /(iPhone|iPad|iPod|iOS)/i.test(ua)) {//创建a对象
                        window.location.href = protocolUrl;
                    } else {//创建iframe对象
                        ifr = document.createElement('iframe');
                        ifr.style.display = 'none';
                        ifr.style.width = '0';
                        ifr.style.height = '0';
                        ifr.style.border = '0';
                        ifr.frameborder = '0';
                        ifr.src = protocolUrl;
                        document.body.appendChild(ifr);
                    }
                    
                    window.setTimeout(function(){
                        if(ifr != null){
                            $(ifr).remove();
                        }
                        if (isDownloadTip === 1) {
                            $('#openAppBoxOpenId').html('此功能需要访问客户端才能使用哦');
                            $('#openAppBoxDownId').show();
                        } else {
                            if (!$.detectWechat()) {
                                if (specifiedDownloadUrl != undefined && specifiedDownloadUrl != null && specifiedDownloadUrl != '') {
                                    window.location.href = specifiedDownloadUrl;
                                } else {
                                    window.location.href = downloadUrl;
                                }
                            }
                            $('#openAppBoxId').hide();
                        }
                        jQuery.YR['openOrInstallAppClick'] = 0;
                    }, 1000);
                }
            };
            
            var initFun = function()
            {
                if (bandElems != undefined) {//绑定事件
                    if ($.isArray(bandElems)) {
                        for (var be in bandElems) {
                            $(bandElems[be]).off(runFun);
                            $(bandElems[be]).on('click', runFun);
                        }
                    } else {
                        $(bandElems).off(runFun);
                        $(bandElems).on('click', runFun);
                    }  
                }
                
                if (isDirectOpen === 1) {//直接打开
                    runFun();
                }
            };
            
            if (jQuery.YR['openOrInstallAppCfgs'] === undefined) {
                $.getJSON('http://www.utan.com/info/getappcfgs?jsoncallback=?', function(result){
                    jQuery.YR['openOrInstallAppCfgs'] = result.data;
                    initFun();
                }, 'json');
            } else {
                initFun();
            }
        },
        //获取滚动条 top left width height
		getScroll: function() {
			var t, l, w, h;
			if (document.documentElement && document.documentElement.scrollTop) {
				t = document.documentElement.scrollTop;
				l = document.documentElement.scrollLeft;
				w = document.documentElement.scrollWidth;
				h = document.documentElement.scrollHeight;
			} else if (document.body) {
				t = document.body.scrollTop;
				l = document.body.scrollLeft;
				w = document.body.scrollWidth;
				h = document.body.scrollHeight;
			}
			return {t: t, l: l, w: w, h: h}
		},
        //鼠标在屏幕的X坐标
		pointerX: function(event) {
			if(!event) event = window.event;
			return event.pageX || (event.clientX +
			(document.documentElement.scrollLeft || document.body.scrollLeft));
		},
        //鼠标在屏幕的Y坐标
		pointerY: function(event) {
			if(!event) event = window.event;
			return event.pageY || (event.clientY +
			(document.documentElement.scrollTop || document.body.scrollTop));
		},
        //延迟执行函数
		delayExec : function (fun, time, that){
			time=(time==undefined)? 100:time;
			var timer=window.setInterval(function(){
				window.clearInterval(timer);
				if(jQuery.isFunction(fun)) {
                    if(that == undefined){
                        that = this;
                    }
					fun.apply(that);
				} else {
					eval(fun);
				}
			},time);
		},
        //设置COOKIE expire [+- 1/天]
		setCookie : function (name, value, expire, domain, issecure){
			if(domain==undefined || domain==null || domain=="")domain = getCookieDomain();
			var secure=(issecure==undefined || issecure==null || issecure=="")? true : false;
			if(expire!=undefined && expire!=null && expire!=""){
				var date = new Date ();
				if(expire<=0)date.setTime(date.getTime()-(1*1000*3600*24));
				else date.setTime(date.getTime()+(expire*1000*3600*24));
				expire=";expires="+date.toGMTString();
			}
			else expire="";
			document.cookie=name+"="+escape(value)+expire+";path=/;domain="+domain+";"+secure;

		},
        //获取cookie
		getCookie : function (name, mode){
			var arr = document.cookie.match(new RegExp("(^| )"+name+"=([^;]*)(;|$)"));
			if(arr != null) {
				switch(mode){
					case 1:
						return unescape(decodeURIComponent(arr[2]));
						break;
					default:
						return unescape(arr[2]);
						break;
				}
			}
			return "";
		},
        //删除COOKIE expire [+- 1/天]
		delCookie : function (name, domain, issecure){
			if(domain==undefined || domain==null || domain=="")domain = getCookieDomain();
			var secure=(issecure==undefined || issecure==null || issecure=="")? true : false;
            var date = new Date ();
            date.setTime(date.getTime()-(1*1000*3600*24));
            var expire = ";expires="+date.toGMTString();
            var value  = '';
			document.cookie=name+"="+escape(value)+expire+";path=/;domain="+domain+";"+secure;
		},
        _GET:(function(){
            var url = window.document.location.href.toString();
            var u = url.split("?");
            if(typeof(u[1]) == "string"){
                u = u[1].split("&");
                var get = {};
                for(var i in u){
                    var j = u[i].split("=");
                    get[j[0]] = j[1];
                }
                return get;
            } else {
                return {};
            }
        })(),
        //检测是否是移动设备上的浏览器 - return 是 true | 否 false
        isMobileBrowser : function(type){//是否是移动设备上的浏览器
            var sUserAgent = navigator.userAgent.toLowerCase();
            var bIsIpad = sUserAgent.match(/ipad/i) == "ipad";
            var bIsIphoneOs = sUserAgent.match(/iphone os/i) == "iphone os";
            var bIsMidp = sUserAgent.match(/midp/i) == "midp";
            var bIsUc7 = sUserAgent.match(/rv:1.2.3.4/i) == "rv:1.2.3.4";
            var bIsUc = sUserAgent.match(/ucweb/i) == "ucweb";
            var bIsAndroid = sUserAgent.match(/android/i) == "android";
            var bIsCE = sUserAgent.match(/windows ce/i) == "windows ce";
            var bIsWM = sUserAgent.match(/windows mobile/i) == "windows mobile";
            if(type != undefined){
                switch(type){
                    case 'mac':
                        if(bIsIpad || bIsIphoneOs){
                            return true;
                        }
                        break;
                }
            } else {
                if(bIsIpad || bIsIphoneOs || bIsAndroid || bIsMidp || bIsUc7 || bIsUc || bIsCE || bIsWM){
                    return true;
                }
            }
            return false;
        },
        //自定义 GUID
        customGuid : function (){
			var guid = "";
			for (var i = 1; i <= 32; i++){
				var n = Math.floor(Math.random() * 16.0).toString(16);
				guid += n;
				if((i==8)||(i==12)||(i==16)||(i==20)) {
					guid += "-";
				}
			}
			return guid;
		},
        //全角转换成半角 , = 65292 ：=65306 '=8216 “=8220 ？=65311 。=12290 ；65307
        CtoH: function(str, isFilter){
            var result="";
            var filter = {65292:1, 65306:1, 8216:1, 8220:1, 65311:1, 12290:1, 65307:1};
            for (var i = 0; i < str.length; i++){
                if(isFilter != false && filter[str.charCodeAt(i)] == 1){
                    result+= String.fromCharCode(str.charCodeAt(i));
                } else {
                    if (str.charCodeAt(i)==12288){
                        result+= String.fromCharCode(str.charCodeAt(i)-12256);
                        continue;
                    }
                    if (str.charCodeAt(i)>65280 && str.charCodeAt(i)<65375) result+= String.fromCharCode(str.charCodeAt(i)-65248);
                    else result+= String.fromCharCode(str.charCodeAt(i));
                }
            }
            return result;
        },
        //url 跳转
		redirect: function(url, mode){
			switch (mode) {
				case 'parentReload':
					parent.window.location.reload();
					break;
				case 'parentReplace':
					parent.window.location.replace(url);
					break;
				case 'replace':
					window.location.replace(url);
					break;
				case 'href':
					window.location.href = url;
					break;
				default:
					window.location.reload(true);
					break;
			}
		},
        //获取字符串的字节长度
		byteLength: function(str){
			if (typeof str == "undefined") {
				return 0;
			}
			var matchStr = str.match(/[^\x00-\x80]/g);
			return (str.length + (!matchStr ? 0 : matchStr.length));
		},
		//检查字符串的长度，并且有回调函数，就触发回调函数
		checkLength: function(str, callback){
			var min = 41, max = 140, tmp = null, realLen = 0;
			if(typeof str == 'object'){
				min = str.min;
				max = str.max;
				tmp = str.con;
			} else {
				tmp = str;
			}
			var inputLen = $.trim(tmp).length;
			if (inputLen > 0) {
				var regexp = new RegExp("(http://)+(([-A-Za-z0-9]+(.[-A-Za-z0-9]+)*(.[-A-Za-z]{2,5}))|([0-9]{1,3}(.[0-9]{1,3}){3}))(:[0-9]*)?(/[-A-Za-z0-9_$.+!*(),;:@&=?/~#%]*)*","gi");
				var urls = tmp.match(regexp) || [];
				var urlCount = 0;
				for ( var i = 0, len = urls.length; i < inputLen; i++) {
					var count = $.byteLength(urls[i]);
					if (count > min) {
						urlCount += count <= max ? 23 : (23 + count - max);
						tmp = tmp.replace(urls[i], "");
					}
				}
				var tmpLen = $.byteLength(tmp);
				if(urlCount + tmpLen > 0) {
					realLen = Math.ceil((urlCount + tmpLen) / 2);
				}
			}
			if($.isFunction(callback)){
				callback.apply(this, [realLen, max]);
			}
			return realLen;
		},
        /*
         * 获取 input text | textarea
         */
        getCursorPos:function(textObj){
            var pos = 0;
            if($.browser.msie){
                textObj.focus();
                var d=null;
                d = document.selection.createRange();
                var	e = d.duplicate();
                if(textObj.tagName != undefined && textObj.tagName == 'INPUT'){
                    e.setEndPoint("StartToStart", textObj.createTextRange());
                    pos = e.text.length;
                } else {
                    e.moveToElementText(textObj);
                    e.setEndPoint("EndToEnd",d);
                    textObj.selectionStart=e.text.length-d.text.length;
                    textObj.selectionEnd=textObj.selectionStart+d.text.length;
                    pos = textObj.selectionStart
                }
            } else if(textObj.selectionStart||textObj.selectionStart=="0"){
                pos = textObj.selectionStart;
            }
            return pos;
        },
        /*
			选中指定范围内容，在此位置输入
			begin 开始位置
			end   结束位置
			textObj input[text]对象
		*/
		selectionRange: function(begin, end, textObj){
			if(textObj.createTextRange){
				var range = textObj.createTextRange();
				end -= textObj.value.length;
				range.moveEnd("character", end);
				range.moveStart("character", begin);
				range.select();
			}else{
			    if(begin == end){
			        end = begin = begin*2;
			    }
				textObj.setSelectionRange(begin, end);
				textObj.focus();
			}
		},
        /*
			往input[text]中插入内容
			con     需要插入的内容
			textObj input[text]对象
		*/
		insertConToInput: function(con, textObj){
			if (document.all && textObj.createTextRange && textObj.caretPos) {
				var caretPos = textObj.caretPos;
				caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == '' ? con + '' : con;
			} else if (textObj.setSelectionRange) {
				var rangeStart = textObj.selectionStart;
				var rangeEnd = textObj.selectionEnd;
				var tempStr1 = textObj.value.substring(0, rangeStart);
				var tempStr2 = textObj.value.substring(rangeEnd);
				textObj.value = tempStr1 + con + tempStr2;
				textObj.focus();
				var len = con.length;
				textObj.setSelectionRange(rangeStart + len, rangeStart + len);
				textObj.focus();
			} else if(document.selection) {
				textObj.focus();
				var sel = document.selection.createRange();
				sel.text = con;
				sel.select();
			} else {
				textObj.value += con;
			}
			return textObj.value;
		},
        //清除拖拽时对象上的一些不必要|有阻碍的事件
        clearDrag:function(obj){
            var tmp=null;
            if(window.ActiveXObject){
                if(obj.length==undefined){
                    obj.onselectstart=function(){return false;};
                    obj.ondragstart=function(){return false;};
                    obj.unselectable="on";
                }
                else{
                    for(tmp in obj){
                        tmp.onselectstart=function(){return false;};
                        tmp.ondragstart=function(){return false;};
                        tmp.unselectable="on";
                    }
                }
            } else {
                if(obj.length==undefined){
                    if(obj.style==undefined)return;
                    obj.style.mozUserSelect='none';
                    obj.style.userSelect='none';
                    obj.style.KhtmlUserSelect = 'none';
                }
                else{
                    for(tmp in obj){
                        if(tmp.style==undefined)continue;
                        tmp.style.mozUserSelect='none';
                        tmp.style.userSelect='none';
                        tmp.style.KhtmlUserSelect='none';
                    }
                }
            }
        },
        //禁止复制文字 target 目标对象
        disableSelection:function(target){
            //IE route
            if(typeof target.onselectstart!="undefined"){
                target.onselectstart=function(){
                    return false;
                }
            } else if(typeof target.style.MozUserSelect!="undefined"){ //Firefox route
               target.style.MozUserSelect="none";
            } else { //All other route (ie: Opera)
               target.onmousedown=function(){
                   return false
               }
            }
        },
        /*
            仅获取图片大小 - 根据预设尺寸
            @param int iw 图片宽
            @param int ih 图片高
            @param int w  预设图片宽
            @param int h  预设图片高 - 可选
        */
        getAdjustedImageSize:function(iw, ih, w, h){
            if(h == undefined){
                if(iw > w){
                    var scale = w/iw;
                    return {w:w,h:ih * scale};
                } else {
                    return {w:iw,h:ih};
                }
            } else {

                if(iw < w && ih < h){// all <
                    return {w:iw,h:ih};
                }

                if(iw == w && ih == h){// all =
                    return {w:w,h:h};
                }

                if(w == h){//预设值 等比

                    if(iw > w && ih > h){// all >
                        if(iw > ih){
                            var scale = w/iw;
                            return {w:w,h:ih * scale};
                        } else if(ih > iw){
                            var scale = h/ih;
                            return {w:iw * scale,h:h};
                        } else {
                            return {w:w,h:h};
                        }
                    } else if(iw > w && ih < h) {// 2
                        var scale = w/iw;
                        return {w:w,h:ih * scale};
                    } else if(iw < w && ih > h){// 2
                        var scale = h/ih;
                        return {w:iw * scale,h:h};
                    }
                } else if(w > h){//预设值 w > h

                    if(iw > w && ih > h){// all >

                        if(iw > ih){

                            if(ih/iw < h/w){
                                var scale = w/iw;
                                return {w:w,h:ih * scale};
                            } else if(ih/iw > h/w){
                                var scale = h/ih;
                                return {w:w,h:ih * scale};
                            } else {
                                return {w:w,h:h};
                            }

                        } else if(ih > iw){

                            var scale = h/ih;
                            return {w:w * scale,h:h};

                        } else {

                            return {w:h,h:h};
                        }

                    } else if(iw > w && ih < h) {// 2
                        var scale = w/iw;
                        return {w:w,h:ih * scale};
                    } else if(iw < w && ih > h){// 2
                        var scale = h/ih;
                        return {w:iw * scale,h:h};
                    }
                } else if (h > w){//预设值 h > w

                    if(iw > w && ih > h){// all >

                        if(iw > ih){
                            var scale = w/iw;
                            return {w:w,h:h * scale};
                        } else if(ih > iw){
                            var scale = iw/ih;
                            return {w:iw * scale,h:h};
                        } else {
                            return {w:w,h:w};
                        }

                    } else if(iw > w && ih < h) {
                        var scale = h/ih;
                        return {w:iw * scale,h:h};
                    } else if(iw < w && ih > h){
                        var scale = w/iw;
                        return {w:w,h:h * scale};
                    }
                }

            }
        },
        /*
            设置图片大小
            @param object self 图片对象
            @param int w  预设图片宽
            @param int h  预设图片高 - 可选
        */
        setImageSize:function(self, w, h){
            if($(self).data('isreload') == 'on'){
                var img = new Image();
                img.onload = function(){
                    var wh = $.getAdjustedImageSize(img.width, img.height, w, h);
                    self.width = wh.w;
                    self.height = wh.h;
                }
                img.src = self.src;
            } else {
                var wh = $.getAdjustedImageSize(self.width, self.height, w, h);
                self.width = wh.w;
                self.height = wh.h;
            }
        },
        //阻止事件冒泡,使成为捕获型事件触发机制
        stopBubble: function(e){
            // 如果提供了事件对象，则这是一个非IE浏览器
            if(e && e.stopPropagation){
                //因此它支持W3C的stopPropagation()方法
                e.stopPropagation();
            } else {
                //否则，我们需要使用IE的方式来取消事件冒泡
                window.event.cancelBubble = true;
            }
        },
        //当按键后,不希望按键继续传递给如HTML文本框对象时,可以取消返回值.即停止默认事件默认行为.
        //阻止浏览器的默认行为
        stopDefault: function(e){
            //阻止默认浏览器动作(W3C)
            if(e && e.preventDefault){
                //IE中阻止函数器默认动作的方式
                e.preventDefault();
            } else {
                window.event.returnValue = false;
            }
            return false;
        },
        //iframe 自适应高度 宽度，仅使用在iframe页面中
        iframeAutoSize:function(iframeId, opt){
            if(opt == undefined || opt == null){
                opt = {};
            }
            if(opt.w == 1){
                var maxWidth = Math.max(
                    document.documentElement["clientWidth"],
                    document.body["scrollWidth"], document.documentElement["scrollWidth"],
                    document.body["offsetWidth"], document.documentElement["offsetWidth"]
                );
            }
            var maxHeight = Math.max(
                document.documentElement["clientHeight"],
                document.body["scrollHeight"], document.documentElement["scrollHeight"],
                document.body["offsetHeight"], document.documentElement["offsetHeight"]
            );
            var pt = parent;
            switch(opt.pt){
                case 'top':
                    pt = top;
                    break;
            }
            var iframeObj = pt.document.getElementById(iframeId);
            if(iframeObj == null){
                return false;
            }
            if(opt.w == 1){
                iframeObj.style.width = maxWidth +'px';
            }
            iframeObj.style.height = maxHeight +'px';
        },
        //右键菜单
        contextMenu: function(menuBoxId, containerId, elemClass, menus) {
            
            var self = this;
            var containerDom = $('#'+containerId).get(0);
            var menuBoxDom   = $('#'+menuBoxId).get(0);

            if (menuBoxDom === null || menuBoxDom === undefined) {
                //构造菜单HTML
                var htmlStr = '<div id="'+menuBoxId+'" class="rmenu">';
                for (var m in menus) {
                    if (m == menus.length - 1) {
                        var classN = 'rmenu-item';
                    } else {
                        var classN = 'rmenu-item rmenu-item-bb';
                    }
                    htmlStr += '<div class="'+classN+'"> \
                        <a href="###" class="PROGRAM-context_menu_a" onclick="return false;" data-i="'+m+'"><i class="'+menus[m]['icon']+'"></i>'+menus[m]['name']+'</a> \
                    </div>';
                }
                htmlStr += '</div>';  
                $(htmlStr).appendTo(document.body);
                menuBoxDom = $('#'+menuBoxId).get(0);
            }

            //绑定菜单点击事件
            $('.PROGRAM-context_menu_a').on('click', function(){
                var menuIndex = $(this).data('i');
                menus[menuIndex]['click'].apply(self);
            });

            var showMenu = function(e){//显示菜单
                        
                /*获取当前鼠标右键按下后的位置，据此定义菜单显示的位置*/
                var rightedge  = containerDom.clientWidth - e.clientX;
                var bottomedge = containerDom.clientHeight- e.clientY;

                /*如果从鼠标位置到容器右边的空间小于菜单的宽度，就定位菜单的左坐标（Left）为当前鼠标位置向左一个菜单宽度*/
                if (rightedge < menuBoxDom.offsetWidth) {          
                    menuBoxDom.style.left = containerDom.scrollLeft + e.clientX - menuBoxDom.offsetWidth + "px";
                } else {/*否则，就定位菜单的左坐标为当前鼠标位置*/
                    menuBoxDom.style.left = containerDom.scrollLeft + e.clientX + "px";
                }
                
                /*如果从鼠标位置到容器下边的空间小于菜单的高度，就定位菜单的上坐标（Top）为当前鼠标位置向上一个菜单高度*/
                if (bottomedge < menuBoxDom.offsetHeight) {
                    menuBoxDom.style.top = containerDom.scrollTop + e.clientY - menuBoxDom.offsetHeight + "px";
                } else {/*否则，就定位菜单的上坐标为当前鼠标位置*/
                    menuBoxDom.style.top = containerDom.scrollTop + e.clientY + "px";
                }
                
                menuBoxDom.style.display = 'block';
            };
            
            var hideMenu = function(){
                menuBoxDom.style.display = 'none';
            };
            
            //绑定元素显示右键菜单
            $(containerDom).on('contextmenu', '.'+elemClass, function(e){
                e.cancelBubble = true;
                e.stopPropagation();
                showMenu(e);
                self = this;
                return false;
            });
            
            //隐藏右键菜单
            $(document.body).on('click', function(){
                hideMenu();
            });
            
            //阻止右键菜单中再使用右键
            $(menuBoxDom).on('contextmenu', function(e){
                e.cancelBubble = true;
                e.stopPropagation();
                return false;
            });  
        }
    });
    
    jQuery.browser={};(function(){jQuery.browser.msie=false; jQuery.browser.version=0;if(navigator.userAgent.match(/MSIE ([0-9]+)\./)){ jQuery.browser.msie=true;jQuery.browser.version=RegExp.$1;}})();    

    //依赖jQuery
    jQuery.fn.extend({
        //延迟执行函数 - 可以把当前对象this传入到fun中
        delayExec : function (fun, time){
            $.delayExec(fun, time, this);
		},
        //加载图片自动缩放
        LoadImage: function(scaling,width,height,loadpic){
            if(loadpic==null)loadpic="load3.gif";
            return this.each(function(){
                var t=$(this);
                var src=$(this).attr("src");
                var img=new Image();
                img.src=src;
                //自动缩放图片
                var autoScaling=function(){
                    if(scaling){

                        if(img.width>0 && img.height>0){
                            if(img.width/img.height>=width/height){
                                if(img.width>width){
                                    t.width(width);
                                    t.height((img.height*width)/img.width);
                                }else{
                                    t.width(img.width);
                                    t.height(img.height);
                                }
                            }
                            else{
                                if(img.height>height){
                                    t.height(height);
                                    t.width((img.width*height)/img.height);
                                }else{
                                    t.width(img.width);
                                    t.height(img.height);
                                }
                            }
                        }
                    }
                }
                if(img.complete){
                    autoScaling();
                    return;
                }
                $(this).attr("src","");
                var loading=$("<img alt=\"加载中...\" title=\"图片加载中...\" src=\""+loadpic+"\" />");
                t.hide();
                t.after(loading);
                $(img).load(function(){
                    autoScaling();
                    loading.remove();
                    t.attr("src",this.src);
                    t.show();
                });
            });
        },
        //监控时时输入[input-text]时，[停顿]间隔多少毫秒后执行callback
        inputEnterRealtime:function(callback, opts){
            if(typeof opts != 'object'){
                opts = {};
            }
            var args = [];
            if(opts['args'] != undefined){
                args = opts['args'];
            }
            var self = this;
            if(opts['speed'] == undefined){
                $(this).unbind('keypress keyup input').bind('keypress keyup input', function(){
                    callback.apply(self[0], args);
                });
            } else {
                var realtimer = null;
                $(this).unbind('keypress keyup input').bind('keypress keyup input', function(){
                    window.clearInterval(realtimer);
                    realtimer = null;
                    realtimer = window.setInterval(function(){
                        window.clearInterval(realtimer);
                        realtimer = null;
                        callback.apply(self[0], args);
                    }, parseInt(opts['speed']));
                });
            }
        }
    });

})(window);