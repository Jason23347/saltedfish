@section('head_main')

<style>
	@font-face {
		font-family: msyh;
		src: url('../fonts/msyh.ttf');
	}
	[v-cloak] {
		display: none;
	}
	body{font-family: msyh;}
	#logo{height: 50px;margin-top: -15px;width: 40px;}
	.lb{float:left;color:white;}
	.act{background-color:#FF6633;}
	#navi-item a:hover{opacity:0.5;color:white;}
	#navi-item a{color:white;transition-duration:0.3s;}
    .one-row{white-space: nowrap;-o-text-overflow:ellipsis;text-overflow: ellipsis;}
</style>
<nav id="topbanner" class="navbar" role="navigation" style="width:100%;background-color:#FD9860;border-radius:0;border:none;color:white;position:fixed;top:0;left:0;z-index:10;">
	<div class="container-fluid">
	<div class="navbar-header">
		<button type="button" class="navbar-toggle" data-toggle="collapse"
				data-target="#navi-item" style="border:1px solid white;opacity:0.7;">
			<span style="color:white;" class="sr-only">切换导航</span>
			<span style="color:white;">More</span>
		</button>
		<a class="navbar-brand" style="overflow:hidden;" href="#">
			<div class="row">
				<div class="col-xs-4"><img id="logo" src="./pic/beikelogo.png"></div>
				<div class="col-xs-8" style="padding:0 0 0 15px;font-size:25px;"><span class="lb">iBuy</span></div>
			</div>
		</a>
	</div>
	<div class="collapse navbar-collapse" id="navi-item">
		<ul class="nav navbar-nav">
			<li><a href="/">商城</a></li>
			<li v-if="is_login"><a href="./users/index.php">个人中心</a></li>
			<li v-if="is_login"><a @click="logout">注销</a></li>
		</ul>
		<ul v-if="!is_login" class="nav navbar-nav navbar-right">
			<li><a href="login">登陆</a></li>
			<li><a href="register">注册</a></li>
		</ul>
		<ul v-else-if="is_login" class="nav navbar-nav navbar-right">
			<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" style="padding:5px;overflow:hidden;">
					<div style="width:40px;height:40px;border-radius:20px;float:left;margin-left:15px;" :style="bg"></div>
					<span class="lb" style="line-height:40px;margin-left:10px;margin-right:15px;" v-cloak>@{{info.nickname}}<b class="caret" style="margin-left:5px;"></b></span>
				</a>
				<ul class="dropdown-menu" style="background-color:#FF6633;">
                    <li><a href="/home/edit-profile">个人信息编辑</a></li>
                    <li><a href="/goods/upload">上传商品</a></li>
                    <li><a href="/home/orders">我的订单</a></li>
                </ul>
			</li>
		</ul>
	</div>
	</div>
</nav>

<script>
	var bg_ch = function(url){
		return {
			backgroundImage:'url("'+url+'")',
			backgroundSize:'cover',
			backgroundPosition:'center',
			backgroundRepeat:'no-repeat',
		};
	};
	var is_login = false;
	var self_info = new Vue({
		el:'#topbanner',
		data:{
			info:{},
			is_login:false,
		},
		computed:{
			bg:function(){return bg_ch(this.info.header);},
		},
		methods:{
			logout:function(){
				$.getJSON('logout',function(data){
					console.log(data);
					if (data.status == 'success') {
						window.location = '/';
					}
				});
			},
		},
		//todo:怀疑废弃
		/*
		created:function(){
			$.getJSON("api/get_user",function(data){
				if(data.status == "success"){
					console.log(data);
					self_info.info = data.self_info;
					self_info.is_login = true;
					is_login = true;
				}else{
					self_info.is_login = false;
					is_login = false;
				}
			});
		},
		*/
	});
</script>

<?php
    if(isset($_GET['search'])){
        $target = array(
            'type'      =>  'search',
            'search'    =>  $_GET['search'],
            'page'      =>  1,
        );
        if (isset($_GET['page']))  $target['page'] = $_GET['page'];
        echo "<script>var target = ".json_encode($target).";</script>";
    }elseif (isset($_GET['category'],$_GET['level'])) {
        $target = array(
            'type'      =>  'category',
            'category'  =>  $_GET['category'],
            'level'     =>  $_GET['level'],
            'page'      =>  1,
        );
        if (isset($_GET['page']))  $target['page'] = $_GET['page'];
        echo "<script>
                var target = ".json_encode($target).";
            </script>";
    }else{
        echo "<script>var target = null;</script>";
    }
?>
@show