<style>
    .foot{display:none;}
    
</style>
<form method='POST' onSubmit="return is_empty()" action='<?php echo url_for("@login") ?>' style="position:absolute;height:160px;width:300px;top:50%;left:50%;margin-left:-150px;margin-top:-80px;">
        <fieldset>
                 <label style="font-size:20px;font-family: '微软雅黑';margin-bottom: 20px;">您登陆淘宝的账号:</label>
                 <p>
                    <input id='taobao_account' name='nick' type="text" style="height:50px;font-size:32px;line-height:50px;font-family: '微软雅黑';"/> 
                    <button type="submit" class="btn btn-large btn-primary" style="height:60px;width:70px;position:absolute;right:0;top:40px;font-weight:bold;font-family: '微软雅黑'">进入</button>
                 </p>
                 <p style='text-align:left;'>
                     <a class="btn btn-small" href='<?php echo url_for("@to-auth-taobao") ?>'>我第一次使用</a>
                 </p>
        </fieldset>
</form>
<script type="text/javascript">
    function is_empty(){
        var nick = $("#taobao_account").val();
        if(nick===''){
            return false;
        }else{
            return true;
        }
    }
</script>
