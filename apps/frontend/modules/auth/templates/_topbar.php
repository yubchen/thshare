    <div style="height:80px;margin-top:3px;">
        <img src="/img/logo.png">
    </div>

    <br/>
    <div style="position:absolute;top:10px;right:30px;">
        <button class="btn btn-large btn-primary" type="button" style="float:left;"><i class="icon-user icon-white"></i>&nbsp;<?php echo $sf_user->getAttribute("nick","未认证"); ?></button>
        <p style="float:left;padding:5px;text-align: center;font-size:12px;color:#666;">服务到期时间<br/><?php echo date("Y-m-d",strtotime($sf_user->getAttribute("deadline"))); ?></p>

    </div>