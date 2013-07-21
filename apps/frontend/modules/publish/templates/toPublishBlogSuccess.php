<style>
    .title{height:40px;overflow: hidden;}
    .title a{color:#000;}
</style>
<div class="container-fluid">
	<div class="row-fluid">
		<div class="span12">
                   <?php include_partial("auth/topbar"); ?>   
		</div>
	</div>
	<div class="row-fluid">
		<div class="span2">
                    <?php include_partial("auth/navigation"); ?>
		</div>
		<div class="span7">
                    <blockquote>
                        <p><?php echo $title ?>&nbsp;</p> 
                        <small><span class="label badge-success">价格:<?php echo $price ?></span>&nbsp;&nbsp;ID:<cite><?php echo $product_id ?></cite>&nbsp;&nbsp;宝贝地址: <cite><a target="_blank" href="http://item.taobao.com?id=<?php echo $product_id ?>">http://item.taobao.com?id=<?php echo $product_id ?></a></cite></small>
                    </blockquote>
                    <form method="POST" action="<?php echo url_for("@publish-blog"); ?>" onSubmit="return validateLength()">
                        <input id="title" type="hidden" value="<?php echo $title ?>" name="title">
                        <input id="product_id" type="hidden" value="<?php echo $product_id ?>" name="product_id">
                        <input id="pic_url" type="hidden" value="<?php echo $pic_url; ?>" name="pic_url">
                        <input id="price" type="hidden" value="<?php echo $price ?>" name="price">
                        <input id="is_public" type="hidden" value="<?php echo $public ?>" name="public">
                        <div style="padding:10px;">
                            <h4 style="font-family:'微软雅黑'">微博内容&nbsp;<small>(字数小于140个字，否则将导致部分内容丢失或发送不成功)</small></h4>
                            <p>
                                <a class="btn btn-mini" onclick="readTrends('hourly')">今日热门话题</a> 
                                <a class="btn btn-mini" onclick="readTrends('daily')">本周热门话题</a>
                                <a class="btn btn-mini" onclick="readTrends('weekly')">本月热门话题</a>
                                <a id="loading_topic" style="display:none">
                                    <img src="/img/loading-dot.gif"/>
                                </a>
                            </p>
                            <p id="hot_title">
                                
                            </p>
                            <textarea id="blog_content" name="content" style="width:90%;height:80px;"><?php echo $title ?> 详细: http://item.taobao.com?id=<?php echo $product_id ?> 价格:<?php echo $price ?>元</textarea>
                            <p style="text-align:left;padding:0;margin:0;"><button class="btn btn-small" type="button" onclick="preview()">预览效果</button>&nbsp;<span class="label label-warning">添加网址时请在网址后面添加空格</span></p>
                            <br/><strong>效果:</strong>&nbsp;&nbsp;<font id="total_count"></font>
                            <p style="margin-top:5px;" id="preview_area"></p>
                        </div>
                        <div style="padding:10px">
                            <h4 style="font-family:'微软雅黑'">微博图片</h4>
                            <img src="<?php echo $pic_url; ?>" style="max-height:220px;" class="img-polaroid">       
                        </div>
                        <div style="padding:10px 40px;text-align: right">
                            <div class="alert" style="text-align:left;float:left;font-size:12px;">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>提示：</strong> 该操作将分别发布以上内容到已绑定的微博账号，若您没有绑定的微博账号，请先对微博账号进行绑定
                            </div>
                            <input type="button" class="btn btn-primary btn-large" onclick="ajaxPublish()" value="扩散">

                        </div>
                    </form>
                    <script type="text/javascript">
                        function validateLength(){
                            var str = $("#blog_content").val();
                            if(str.length===0){
                                $("#total_count").html("<span class='label label-important'>微博内容不能为空</span>");                    
                                return false;
                            }
                            var regS = new RegExp("http://([^ ,\r\n]*)","gi");
                            var regS2 = new RegExp("ftp://([^ ,\r\n]*)","gi");
                            str = str.replace(regS,"一二三四五六七八九十零");
                            str = str.replace(regS2,"一二三四五六七八九十零");
                            if(str.length<140){
                                $("#total_count").html("<span class='label label-success'>共"+str.length+"字,还可以输入"+(140-str.length)+"字</span>");
                                return true;
                            }else{
                                $("#total_count").html("<span class='label label-important'>共"+str.length+"字,已超出"+(str.length-139)+"字</span>"); 
                                return false;
                            }
                            return false;
                        }
                        function preview(){
                            var str = $("#blog_content").val();
                            var regS = new RegExp("http://([^ ,\r\n]*)","gi");
                            var regS2 = new RegExp("ftp://([^ ,\r\n]*)","gi");
                            str = str.replace(regS,"http://t.cn/zQyVMcd");
                            str = str.replace(regS2,"http://t.cn/zQyVMcd");
                            $("#preview_area").html(str);
                            validateLength();
                        }
                        function ajaxPublish(){
                            if(!validateLength()){
                                return;
                            }
                            $("#screen").css("display","block");
                            $("#screen_bg").show("fast");
                            $("#sending").show("fast");
                            var title = $("#title").val();
                            var product_id = $("#product_id").val();
                            var pic_url = $("#pic_url").val();
                            var blog_content = $("#blog_content").val();
                            var is_public = $("#is_public").val();
                            var data = {
                                title:title,
                                product_id:product_id,
                                pic_url:pic_url,
                                content:blog_content,
                                public:is_public
                            };
                            $.ajax({
                                type:'post',
                                url:"<?php echo url_for("@publish-blog"); ?>",
                                data:data,
                                dataType:'json',
                                success:function(result){
                                   if(result.error!==0){
                                       alert(result.msg);
                                   }
                                   var success = result.success;
                                   var fail = result.fail;
                                   var host = result.host;
                                   var total = success.length+fail.length;
                                   var htmlstr = "<ul>";
                                   for(var i=0; i<success.length; i++){
                                       htmlstr += "<li class=''>";
                                       htmlstr += "<div class='img'>";
                                       htmlstr += "<img class='img-rounded' src='http://"+host+success[i].image+"'>";
                                       htmlstr += "</div>";
                                       htmlstr += "<div class='status'>";
                                       htmlstr += "<span class='label label-success'>成功</span>";
                                       htmlstr += "</div>";
                                       htmlstr += "</li>";
                                    }
                                    for(var i=0; i<fail.length; i++){
                                       htmlstr += "<li class=''>";
                                       htmlstr += "<div class='img'>";
                                       htmlstr += "<img class='img-rounded' src='http://"+host+fail[i].image+"'>";
                                       htmlstr += "</div>";
                                       htmlstr += "<div class='status'>";
                                       htmlstr += "<span class='label label-important'>失败</span>";
                                       htmlstr += "</div>";
                                       htmlstr += "</li>";  
                                    }
                                    htmlstr += "</ul>";
                                    var box = $("#publish_result");
                                    box.css('width',total*96+"px");
                                    box.css('margin-left',"-"+total*96/2+"px");
                                    $("#publish_result").html(htmlstr);
                                    $("#sending").hide("fast");
                                    $("#publish_result").show("fast");
                                    $("#close").show("slow");
                                },
                                error:function(){
                                         closeScreen();
                                         alert("网络出错,请稍后再试");
                                }
                           });
                        }
                        function closeScreen(){
                            $("#screen_bg").hide("slow");
                            $("#sending").hide("fast");
                            $("#publish_result").hide("fast");
                            $("#close").hide("fast");
                            $("#screen").hide("slow");
                        }
                        function readTrends(scrop){
                            $("#loading_topic").removeAttr("style");
                            var data = {
                                scrop:scrop
                            };
                            $.ajax({
                                type:'post',
                                url:"<?php echo url_for("@read-trends"); ?>",
                                data:data,
                                dataType:'json',
                                success:function(result){
                                   var strhtml = "";
                                   for(var i=0; i<result.length; i++){
                                      strhtml+="<span style='cursor:pointer' onclick=\"addTopic('#"+result[i].name+"#')\" class='label badge-inverse'>#"+result[i].name+"#</span>&nbsp;"; 
                                   }
                                   $("#hot_title").html(strhtml);
                                   $("#loading_topic").css("display","none");
                                },
                                error:function(){
                                         closeScreen();
                                         alert("网络出错,请稍后再试");
                                         $("#loading_topic").css("display","none");
                                }
                           });
                           
                        }
                        function addTopic(topic){
                            var content = $("#blog_content").val();
                            var flag = 0;
                            var str = '';
                            if(content[0]==="#"){
                                flag=1;
                            }else{
                                str = content;
                            }
                            if(flag===1){
                                var start = 0;
                                for(var i=1; i<content.length;i++){
                                    if(content[i]==="#"){
                                       start = 1; 
                                       continue;
                                    }
                                    if(start===1){
                                       str += content[i];   
                                    }
                                }
                            }
                            $("#blog_content").val(topic+" "+str.trim()); 
                        }
                    </script>
		</div>
		<div class="span3">
                    <?php include_partial("auth/blogAccounts", array("platforms"=>$platforms)); ?>
		</div>
	</div>
</div>
<div class="screen" id="screen" style="display:none">
    <div class="bg" id="screen_bg" style="display:none"></div>
    <div class="sending" id="sending" style="display:none">
        <div>
            <img src="http://www.d4cheng.com/img/sending2.gif">
            <span class="label">正在扩散... 请耐心等待1-2分钟</span>
        </div>
    </div>
    <div class="result" id="publish_result"  style="display:none"></div>
    <div class="close" id="close" style="display:none;" onclick="closeScreen()">关闭</div>
    <style>
        .screen{
            background: none;
            position:fixed;
            _position:absolute;
            top:0;
            left:0;
            height:100%;
            width:100%;
            z-index: 99;
        }
        .screen .bg{
            background:#000;
            position:absolute;
            height:100%;
            width:100%;
            top:0;
            left:0;
            opacity:0.3;
            filter:progid:DXImageTransform.Microsoft.Alpha(opacity=30);
            z-index: 100;
        }
        .screen .sending{
            background: none;
            height:44px;
            line-height: 44px;
            width:240px;
            position:absolute;
            left:50%;
            top:50%;
            margin-left:-120px;
            margin-top:-22px;
            z-index: 101;
        }
        .screen .sending span{
            color:#FFF;
            font-weight:bold;
        }
        .screen .result{
            position: absolute;
            height:106px;
            top:50%;
            left:50%;
            margin-top:-53px;
            z-index: 102;
        }
        .screen .result ul{
            list-style: none;
            padding:0;
            margin:0;
        }
        .screen .result ul li{
            display:block;
            height:106px;
            width:86px;
            float:left;
            margin-right:10px;
        }
        .screen .result ul li .status{
            text-align: center;
            height:20px;
            line-height:20px;
            margin-top:3px;
        }
        .screen .close{
            font-size:40px;
            font-family: '微软雅黑';
            color:#FFF;
            height:60px;
            width:120px;
            position: absolute;
            text-align: center;
            top:50%;
            left:50%;
            margin-top:-150px;
            margin-left:-60px;
            opacity:0.6;
            filter:progid:DXImageTransform.Microsoft.Alpha(opacity=60);
            z-index: 105;
        }
        .screen .close:hover{
            opacity:1;
            filter:progid:DXImageTransform.Microsoft.Alpha(opacity=100);
        }
    </style>
</div>