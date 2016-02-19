//找到url中匹配的字符串
function findInUrl(str){
	url = location.href;
	return url.indexOf(str) == -1 ? false : true;
}
//获取url参数
function queryString(key){
	return (document.location.search.match(new RegExp("(?:^\\?|&)"+key+"=(.*?)(?=&|$)"))||['',null])[1];
}

//产生指定范围的随机数
function randomNumb(minNumb,maxNumb){
	var rn=Math.round(Math.random()*(maxNumb-minNumb)+minNumb);
	return rn;
}

var wHeight;
var coolDownTime;
$(document).ready(function(){
	wHeight=$(window).height();
	if(wHeight<832){
		wHeight=832;
	}
	$('.pageOuter').height(wHeight);
	$('.page').height(wHeight);	
	$('.h832').css('padding-top',(wHeight-832)/2+'px');
	
	if(isGaming){
		$('.page2').show();
	}
	else{
		if(isEnd){
			$('.coolDownTime').html('<br><span>活动已经结束</span>');
			$('.page1').show();
		}
		else{
			$('.page1').show();
			getRTime();
			coolDownTime=setInterval(function(){
				getRTime();
			},1000);
		}
	}
});

function getRTime(){
	EndTime= new Date(EndTime); //截止时间
	var NowTime = new Date();
	var t =EndTime.getTime() - NowTime.getTime();
	if(t<=0){
		clearInterval(coolDownTime);
		$('.page1').hide();
		$('.page2').show();
		gameStart();
	}
	else{
		$('.page1').show();
	}

	var d=Math.floor(t/1000/60/60/24);
	var h=Math.floor(t/1000/60/60%24);
	var m=Math.floor(t/1000/60%60);
	var s=Math.floor(t/1000%60);
	
	if(h.toString().length<=1){
		h='0'+h;
	}
	if(m.toString().length<=1){
		m='0'+m;
	}
	if(s.toString().length<=1){
		s='0'+s;
	}
	
	$('.ch').html(h);
	$('.cm').html(m);
	$('.cs').html(s);
}

function gameStart(){
	isGaming=true;
	$('.bg1').hide();
	$('.bg2').show();
	
	if(window.DeviceMotionEvent) {
		var speed = 20;    // 用来判定的加速度阈值，太大了则很难触发
		var x, y, z, lastX, lastY, lastZ;
		x = y = z = lastX = lastY = lastZ = 0;

		window.addEventListener('devicemotion', function(event){
			var acceleration = event.accelerationIncludingGravity;
			x = acceleration.x;
			y = acceleration.y;
			if(Math.abs(x-lastX) > speed || Math.abs(y-lastY) > speed) {
				// 用户设备摇动了，触发响应操作
				// 此处的判断依据是用户设备的加速度大于我们设置的阈值
				yaoFn();
				iClearTime=setTimeout(function(){
					clearYao();
				},1000);
			}
			lastX = x;
			lastY = y;
		}, false);
	}
	else{
		alert('您的设备不支持html摇一摇');
	}
	
}

var iClearTime;
var yaoStep=0;
var isYaoGet=false;
var hasRequest = false;

function yaoFn(){
	clearTimeout(iClearTime);
	var p=parseInt($('span').html());
	yaoStep++;
	if(yaoStep>=65){
		yaoGet();
		return false;
	}
	else{
		$('.page2Img1').fadeOut(500);
		$('.page2Img2').fadeOut(500);
		$('.page3Img1').stop().animate({top:-754+yaoStep*10},100,'linear');
	}
}

function clearYao(){
	if(!isYaoGet){
		yaoStep=0;
		$('.page3Img1').stop().animate({top:-754},1000,'linear');
		$('.page2Img1').fadeIn(500);
		$('.page2Img2').fadeIn(500);
	}
}

function yaoGet(){
	isYaoGet=true;
	$('.page3Img1').stop().animate({top:-104},100,'linear').css('background-position','-640px 0');

	if( hasRequest == false ){
		setTimeout(function(){
			hasRequest = true;
			$.getJSON('/lottery',function(json){
				if(json.ret == 0){
					if( json.drawNum == 0){
						goPage6(json.credit);
					}
					else{
						goPage4(json.credit,json.drawNum);
					}
				}
				else {
					goPage5();
				}
			});
			//goPage4();//中奖显示浮层 还有机会
			//goPage5();//未中奖显示浮层
			//goPage6();//中奖显示浮层 没有机会
		},1000);
	}
	
}

function goPage4(redPoint,chanceNo){//中奖显示浮层 还有机会
	$('.page2').fadeOut(500);
	$('.page4').fadeIn(500);
	$('.getRedLine span').html(redPoint);
	$('.chanceLine span').html(chanceNo);
}

function goPage5(){//未中奖显示浮层
	$('.page2').fadeOut(500);
	$('.page5').fadeIn(500);
}

function goPage6(redPoint){//中奖显示浮层 没有机会
	$('.page2').fadeOut(500);
	$('.page6').fadeIn(500);
	$('.getRedLine span').html(redPoint);
}

function playAgain(){
	window.location.reload();
}

function showShare(){
	$('.popBg').fadeIn(500);
	$('.shareImg').fadeIn(500);
}

function closePop(){
	$('.popBg').fadeOut(500);
	$('.shareImg').fadeOut(500);
	$('.popBg2').fadeOut(500);
	$('.rule').fadeOut(500);
}

function showRule(){
	$('.popBg2').fadeIn(500);
	$('.rule').fadeIn(500);
}






























