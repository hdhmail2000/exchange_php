xuetong={
//舌标签
    show_tab:function(i){
        $('.lm_tab_'+i+' .list_'+i+'').click(function(){
            var _index = $(this).index();
            $(this).addClass('hover').siblings().removeClass('hover');
            $('.lm_tab_list_'+i+'').each(function(){
                $(this).find('.list_'+i+'').hide();
                $(this).find('.list_'+i+'').eq(_index).show();
                if($(this).find('.list_'+i+'').hasClass('auto')){
                    $(this).find('.list_'+i+'').css('height','auto');
                }
            })
        });
    },

    inputFB:function() {
        $('input.input_text').focus(function () {
            var t = $(this);
            if (t.attr('type') == 'text' || t.attr('type') == 'password')t.css({'border': '1px solid #EB3F3F', 'color': '#333'});
            if (t.val() == t.attr('placeholder')) t.val('');
        });
        $('input.input_text').blur(function () {
            var t = $(this);
            if (t.attr('type') == 'text' || t.attr('type') == 'password')t.css({'box-shadow': 'none', 'border': '1px solid #f6f6f8', 'color': '#333'});

        })
    },
    inputBian:function() {
        $('input.input_bian').focus(function () {
            var t = $(this);
            if (t.attr('type') == 'text' || t.attr('type') == 'password')t.css({'border': '1px solid #17ffd7', 'color': '#fff'});
            if (t.val() == t.attr('placeholder')) t.val('');
        });
        $('input.input_bian').blur(function () {
            var t = $(this);
            if (t.attr('type') == 'text' || t.attr('type') == 'password')t.css({'box-shadow': 'none', 'border': '1px solid #132024', 'color': '#fff'});

        })
    },
    //问号内容弹出解释
    show_wen:function(){
        $(".icon_wen").click(function(e){
            e.stopPropagation();
            $(this).next().show();
            var element = $(this).next().children("span");
            if(element.html()){
                if(element.html().length>20){
                    element.html("<marquee scrollamount=4>"+element.html()+"</marquee>");
                }
            }
        })
        $('html,body').click(function(){
            $('.show_wen').hide();
        })
        $('.show_wen').click(function(e){
            e.stopPropagation();
        })
    },
    //右侧导航
    top_rnav:function(){
        $('#js-menu').on('click', function () {
            $('.menu,#gb-main,.head').toggleClass('gbzp');
        });
        $('.backBtn').on('click', function () {
            $('.menu,#gb-main,.head').toggleClass('gbzp');
        });
        $('#gb-main').on('click', function() {
            if ($(this).hasClass('gbzp')) {
                $('.menu,#gb-main,.head').toggleClass('gbzp')
            }
        });
    },
    //密码隐藏显示
    eyes_box:function(){
        $(".eyes_box").click(function(){
            if($(this).attr("data-show")==1){//明文
                $(this).attr("data-show","2");
                $(this).children("i").toggleClass("open");
                $(this).parent("div").children(".mima_dd").hide();
                $(this).parent("div").children(".mima_wz").show();
                $(this).parent("div").children(".mima_wz").val($(this).parent("div").children(".mima_dd").val());
                return;
            }
            if($(this).attr("data-show")==2){//密文
                $(this).attr("data-show","1");
                $(this).children("i").removeClass("open");
                $(this).parent("div").children(".mima_dd").show();
                $(this).parent("div").children(".mima_wz").hide();
                $(this).parent("div").children(".mima_dd").val($(this).parent("div").children(".mima_wz").val());
                return;
            }
        });
		$('.mima_dd').change(function(){
			$(this).parent("div").children(".mima_wz").val($(this).val());
		})
		$('.mima_wz').change(function(){
			$(this).parent("div").children(".mima_dd").val($(this).val());
		})
    },
    //复制文本
    copy:function(){
        var Url2=document.getElementById("wallet_url");
        Url2.select();
        document.execCommand("Copy");
        alert("钱包地址已复制");
    },
    sendsms:function(){
        var wait=60;
        function time() {
            var o=document.getElementById("send_sms_1")
            if (wait == -1) {
                $('#send_sms').show();
                $('#send_sms_1').hide();
                wait = 60;
            } else {
                o.innerHTML=wait + " s";
                wait--;
                mobiletimer = setTimeout(function(){time()}, 1000)
            }
        }
        $('#send_sms').click(function(){
            $(this).hide();
            $('#send_sms_1').show();
            $('#send_voice').hide();
            $('#send_voice_1').show();
            time();
            time2();
        });
        var wait2=60;
        function time2() {
            var o=document.getElementById("send_voice_1")
            if (wait2 == -1) {
                $('#send_voice').show();
                $('#send_voice_1').hide();
                wait2 = 60;
            } else {
                o.innerHTML=wait2 + " s";
                wait2--;
                mobiletimer2 = setTimeout(function() {time2()}, 1000)
            }
        }
        $('#send_voice').click(function(){
            $(this).hide();
            $('#send_voice_1').show();
            $('#send_sms').hide();
            $('#send_sms_1').show();
            time();
            time2();
        });
    },
    foot_download:function(id){
        if(id==1){
            $('#foot_download').hide();
            $('#foot_download').removeAttr("id");
        }else{
            var nowTop = document.documentElement.scrollTop || document.body.scrollTop;
            $(window).scroll(function(){
                scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
                if(scrollTop < nowTop){
                    $('#foot_download').slideDown();
                }else{
                    $('#foot_download').hide();
                }
                nowTop = scrollTop;
            })
        }
    },
    title_tab:function(){
        $(".title").click(function(e){
            e.stopPropagation();
            $(".trade_con_all").show();
        })
        $('body,html').click(function(){
            $(".trade_con_all").hide();
        })
    },
    //判断手机类型
    uaFanction:function(){
        var UA = window.navigator.userAgent,
            IsAndroid = (/Android|HTC/i.test(UA) || !! (window.navigator['platform'] + '').match(/Linux/i)),
            IsIPad = !IsAndroid && /iPad/i.test(UA),
            IsIPhone = !IsAndroid && /iPod|iPhone/i.test(UA),
            IsIOS = IsIPad || IsIPhone;
        if(IsIOS){
            window.location.href='itms-services://?action=download-manifest&url=https://www.btctrade.com/upload/app/trade.plist';
        }
        if(IsAndroid){
            window.location.href='http://www.btctrade.com/upload/app/trade.apk';
        }
    },
    //弹出层
    showDialog:function(id, maskclick) {
        // 遮罩
        $('#' + id).removeClass('modal-out').addClass('styled-pane');
        var dialog = Dom(id);
        dialog.style.display = 'block';
        if (Dom('mask') == null) {
            $('body').prepend('<div class="ui-mask" id="mask" onselectstart="return false"></div>');
            if (!maskclick) $('#mask').bind('click', function () {hideDialog(id)})
        }
        var mask = Dom('mask');
        mask.style.display = 'inline-block';
        mask.style.width = document.body.offsetWidth + 'px';
        mask.style.height = document.body.scrollHeight + 'px';
        //居中
        var bodyW = document.documentElement.clientWidth;
        var bodyH = document.documentElement.clientHeight;
        var elW = dialog.offsetWidth;
        var elH = dialog.offsetHeight;
        dialog.style.left = (bodyW - elW) / 2 + 'px';
        dialog.style.top = (bodyH - elH) / 2 + 'px';
        dialog.style.position = 'fixed';
    },
    showDialog_foot:function(id, maskclick) {
        // 遮罩
        $('#' + id).removeClass('modal-out').addClass('styled-pane');
        var dialog = Dom(id);
        dialog.style.display = 'block';
        if (Dom('mask') == null) {
            $('body').prepend('<div class="ui-mask" id="mask" onselectstart="return false"></div>');
            if (!maskclick) $('#mask').bind('click', function () {hideDialog(id)})
        }
        var mask = Dom('mask');
        mask.style.display = 'inline-block';
        mask.style.width = document.body.offsetWidth + 'px';
        mask.style.height = document.body.scrollHeight + 'px';
        //居中
        var bodyW = document.documentElement.clientWidth;
        var bodyH = document.documentElement.clientHeight;
        var elW = dialog.offsetWidth;
        var elH = dialog.offsetHeight;
        dialog.style.left = (bodyW - elW) / 2 + 'px';
        dialog.style.bottom ='0px';
        dialog.style.position = 'fixed';
    },
    //关闭弹出层
    hideDialog:function(id, fn) {
        $('#' + id).removeClass('styled-pane').addClass('modal-out');
        $('#mask').addClass('out');
        setTimeout(function () {$('#' + id).hide(); $('#mask').remove();}, 300);
        if (typeof fn == 'function') fn();
    },
    //分享弹窗
    show_share:function(){
        var html='<div class="dialog_content styled-pane wb_100" id="show_share">' +
            '<div class="show_share wb_100 center">' +
            '<ul class="clear">' +
            '<li><a href="#"><img src="/wap/images/share_wb.png" class="icon_show_share"><p class="hui_d">微博</p></a></li>' +
            '<li><a href="#"><img src="/wap/images/share_qq.png" class="icon_show_share"><p class="hui_d">QQ</p></a></li>' +
            '<li><a href="#"><img src="/wap/images/share_wx.png" class="icon_show_share"><p class="hui_d">微信</p></a></li>' +
            '<li><a href="#"><img src="/wap/images/share_people.png" class="icon_show_share"><p class="hui_d">朋友圈</p></a></li>' +
            '</ul>' +
            '<div class="show_share_close">' +
            '<a id="closeBtn" onclick="xuetong.hideDialog(\'show_share\');" class="share_close2" title="取消">取消</a>' +
            '</div>' +
            '</div>' +
            '</div>';
        $('body').prepend(html);
        xuetong.showDialog_foot('show_share');
    },
    show_deposit:function(){
            var html='<div class="dialog_content styled-pane" id="show_deposit" style="width:80%;">' +
                        '<div class="show_deposit center po_re">' +
                        '<div class="show_deposit_top po_ab">' +
                        '<img src="/wap/images/dialog_deposit.png" class="dialog_icon">' +
                        '</div>' +
                        '<h2 class="show_title">充值成功！</h2> ' +
                        '<div class="show_close">' +
                        '<a id="closeBtn" onclick="xuetong.hideDialog(\'show_deposit\');" class="share_close" title="知道了">知道了</a>' +
                        '</div> ' +
                        '</div>' +
                        '</div>';
            $('body').prepend(html);
            xuetong.showDialog('show_deposit');
    },
    //礼品卡充值成功
    show_giftcard:function(){
        var html='<div class="dialog_content styled-pane" id="show_giftcard" style="width:80%;">' +
            '<div class="show_deposit center po_re">' +
            '<div class="show_deposit_top po_ab">' +
            '<img src="/wap/images/dialog_deposit2.png" class="giftcard_icon">' +
            '</div>' +
            '<h2 class="show_title">礼品卡充值成功！</h2> ' +
            '<p class="show_p">优质的你偶尔也会忘记好多从来都不关心的事，有我们就没有后顾之忧。</p>' +
            '<div class="show_close">' +
            '<a id="closeBtn" onclick="xuetong.hideDialog(\'show_deposit\');" class="share_close" title="知道了">知道了</a>' +
            '</div> ' +
            '</div>' +
            '</div>';
        $('body').prepend(html);
        xuetong.showDialog('show_giftcard');
    },
    //红包领取成功！
    show_red:function(){
        var html='<div class="dialog_content styled-pane" id="show_red" style="width:80%;">' +
            '<div class="show_deposit center po_re">' +
            '<div class="show_deposit_top po_ab">' +
            '<img src="/wap/images/dialog_red.png" class="red_icon">' +
            '</div>' +
            '<h2 class="show_title">红包领取成功！</h2> ' +
            '<div class="show_close">' +
            '<a id="closeBtn" onclick="xuetong.hideDialog(\'show_red\');" class="share_close" title="知道了">知道了</a>' +
            '</div> ' +
            '</div>' +
            '</div>';
        $('body').prepend(html);
        xuetong.showDialog('show_red');
    },
    //申请提现成功！
    show_withdraw:function(){
        var html='<div class="dialog_content styled-pane" id="show_withdraw" style="width:80%;">' +
            '<div class="show_deposit center po_re">' +
            '<div class="show_deposit_top po_ab">' +
            '<img src="/wap/images/dialog_withdraw.png" class="dialog_icon">' +
            '</div>' +
            '<h2 class="show_title">申请提现成功！</h2> ' +
            '<div class="show_close">' +
            '<a id="closeBtn" onclick="xuetong.hideDialog(\'show_withdraw\');" class="share_close" title="知道了">知道了</a>' +
            '</div> ' +
            '</div>' +
            '</div>';
        $('body').prepend(html);
        xuetong.showDialog('show_withdraw');
    },
    //找回密码成功！
    show_password:function(){
        var html='<div class="dialog_content styled-pane" id="show_password" style="width:80%;">' +
            '<div class="show_deposit center po_re">' +
            '<div class="show_deposit_top po_ab">' +
            '<img src="/wap/images/dialog_password.png" class="dialog_icon">' +
            '</div>' +
            '<h2 class="show_title">找回密码成功！</h2> ' +
            '<div class="show_close">' +
            '<a id="closeBtn" onclick="xuetong.hideDialog(\'show_password\');" class="share_close" title="知道了">知道了</a>' +
            '</div> ' +
            '</div>' +
            '</div>';
        $('body').prepend(html);
        xuetong.showDialog('show_password');
    },
    //找回密码成功！
    show_yzm: function () {
        var html = '<div class="dialog_content styled-pane" id="show_yzm" style="width:80%;">' +
            '<div class="show_deposit show_yzm center po_re">' +
            '<h2>请输入验证码！</h2>' +
            '<form>' +
            '<div class="dialog_box margin">' +
            '<input type="text" name="dialog_yzm left" id="captcha">' +
            '<a href="#" class="right">' +
            '<img id="dialogimg" class="verify_img2" onclick="show_captcha()"  src="/index/captcha?t=0.1490179113493706">' +
            '</a>' +
            '</div>' +
            '<div class="dialog_yzm_btn">' +
            '<a id="closeBtn" onclick="xuetong.hideDialog(\'show_yzm\');" class="hui_d">取消</a>' +
            '<a  onclick="getmocode()" class="orange">确认</a>' +
            '</div>' +
            '</form>' +
            '</div>' +
            '</div>';
            $('body').prepend(html);
            xuetong.showDialog('show_yzm');
    },
    input_cha:function(){
        $(".input,.text").focus(function(){
            $(this).find('.icon_circha').remove();
            $(this).before("<img src='/wap/images/icon_cha.png' onclick='xuetong.remove_val(this)' class='icon_circha'>");
            $(this).parents('.form_box').siblings().find('.icon_circha').remove();
        });
    },
    remove_val:function(obj){
        $(obj).next().val('');
        if ($(obj).next().attr('to_empty') == '1') {
            $(obj).parent().children('input').val('');
        }
    },
    //省市联动
    show_area: function () {
        var html = '<div class="dialog_content styled-pane" id="show_area" style="width:80%;">' +
            '<div class="show_deposit show_area center po_re">' +
            '<h2>请选择省市！</h2>' +
            '<div class="area_all po_re">' +
            ' <div class="left wb_50"><p>北京市</p><p>北京市</p><p>北京市</p><p>北京市</p><p>北京市</p><p>北京市</p></div>' +
            '<div class="left wb_50"><p>北京市</p><p>北京市</p><p>北京市</p><p>北京市</p><p>北京市</p><p>北京市</p></div>' +
            '<div class="area_all_line"></div>' +
            '</div>' +
            '<div class="area_btn" onclick="xuetong.hideDialog(\'show_area\')">确定</div>' +
            '</div>' +
            '</div>';
        $('body').prepend(html);
        xuetong.showDialog('show_area');
    },
    formatCount:function(count) {
        var countokuu = (count / 100000000).toFixed(3)
        var countwan = (count / 10000).toFixed(3)
        if (count > 100000000)
            return countokuu.substring(0, countokuu.lastIndexOf('.') + 3) + '亿'
        if (count > 10000)
            return countwan.substring(0, countwan.lastIndexOf('.') + 3) + '万'
        else
            return count
    },
    //总价
    buytotal:function(){
        $('#buy-total').html(formatCount(($('#price_in').val())*100 * ($('#numberin').val()*100)/10000));
        $('#sell-total').html(formatCount($('#price_out').val() * $('#numberout').val()));
        //if(typeof FINANCE == 'object') {
        //    $('#sell-max').html(formatfloat(FINANCE.data[coin + '_balance'], 4, 0));
        //}
    },
    vNum:function(o, len) {
        if (isNaN(o.value)) o.value = '';
        var value = len ? xuetong.formatfloat(o.value, len, 0) : parseInt(o.value);
        if (xuetong.badFloat(o.value, len)) o.value = value
    },
    badFloat:function(num, size){
        if(isNaN(num)) return true;
        num += '';
        if(-1 == num.indexOf('.')) return false;
        var f_arr = num.split('.');
        if(f_arr[1].length > size){
            return true;
        }
        return false;
    },
    formatfloat:function(f, size, add) {
        f = parseFloat(f);
        var conf = {0:[1,1], 2: [100, 0.01], 3: [1000, 0.001], 4: [10000, 0.0001], 5: [100000, 0.00001], 6: [1000000, 0.000001]};
        var conf = conf[size];
        var ff = Math.floor(f * conf[0]) / conf[0];
        if (add && f > ff) ff += conf[1];
        if (ff > 0) return ff;
        return 0;
    },
    // 根据委托填价格
    autotrust:function(_this,type){
    if(type == 'sale'){
        $('#price_in').val($(_this).children().eq(1).html().substr(1));
    }
    if(type == 'buy'){
        $('#price_out').val($(_this).children().eq(1).html().substr(1));
    }
}
}

$(function () {
   xuetong.inputFB();
    xuetong.inputBian();
    xuetong.input_cha();

});
function Dom(o) {return document.getElementById(o)};

function valiForm(){
	for(var i in vali){
		$('#' + i).focus(); $('#' + i).blur();
		if(!vali[i]) return false;
	}
	return true;
}

function checkmobile(mobile) {
	var patrn=/^13[0-9]{9}|15[0-9]{9}|18[0-9]{9}|147[0-9]{8}|17[0-9]{9}$/;
	if(!patrn.exec(mobile)) return false;
	return true;
}

function showGA(v,l,type){
	if(!v) return;
	$.get('/ajax/user2ga/email/' + v, function(d){
		type = type||'';
		if(d.status == 1){
			// GA
			$('#ga_pwd'+type).show();$('#ga_isclosed').hide();
		}else if(d.status == 2){
			// verify code
			$('#code').show();$('#ga_isclosed'+type).show();
			$('#captchaimg'+type).attr('src', '/index/captcha?t='+Math.random());
		}else if(d.msg){
			if(l == 1){
				$('.quick_login_x .tip').html(d.msg).show();
			}else if(l == 2){
				$('#emailmsg').html(d.msg).show();
			}else{
				if($('.z1 .tip').length == 0) $('.z1').append('<div class="tip"><b class="false">'+ d.msg+'</b></div>');
			}
		}
	}, 'json');
}

//lang
function lang(str, re){
	if(typeof langs == 'undefined') langs = false;
	if(langs && typeof langs[str] != 'undefined'){
		str = langs[str];
	}
	if(re) for(var r in re){
		str = str.replace(r, re[r]);
	}
	return str;
}
//取消委托
function trustcancel(id){
	$.get('/ajax/trustcancel/id/' + id, function(d){
		alert(d.msg);
		if(d.status){
			location.reload()
		}
	}, 'json');
}
var btn = {txt: ['', '短信验证码', '语音验证码'], obj: ['', document.getElementById('getcode1'), document.getElementById('getcode2')]};
function btnbind(noeach){
	var out = 1;
	for(var i = 1;i <= 2; i++){
		btn.obj[i].onclick = '';
		if(btn.obj[i].value==btn.txt[i] || btn.obj[i].value==lang('正在发送')){
			btn.obj[i].value = noeach? lang('正在发送'): lang('{second}秒', {"{second}":60});
			$(btn.obj[i]).prop('disabled', true)
		} else {
			var btnval = btn.obj[i].value.match(/\d+/);
			if(btnval > 0){
				btn.obj[i].value = lang('{second}秒', {"{second}":btnval-1});
			} else {
				out = 0;
				btn.obj[i].value = btn.txt[i];
				$(btn.obj[i]).prop('disabled', false);
				btn.obj[i].onclick = function(){
					getcodes($(this).val() == lang('短信验证码')? 1: 2);
				}
			}
		}
	}
	if(!noeach && out)setTimeout(btnbind, 1000);
}

function recode(){
	$('#captcha').val('').focus();
	$('#captchaimg').attr('src', '/index/captcha?t='+Math.random());
}
//captcha
function show_captcha() {
	$('#dialogimg').attr('src', '/index/captcha/?t=' + Math.random());
}