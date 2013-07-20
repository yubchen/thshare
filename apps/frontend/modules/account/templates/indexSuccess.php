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
                        <p class="text-success">
                            <strong>微博同步管理</strong>
                        </p> <small>您已绑定以下 <cite>微博</cite> 账号</small>
                    </blockquote>
                    <hr/>
                      

                    <ul class="nav nav-list well well-large">
                            <li class="nav-header">已绑定的微博</li>
                    <?php if(count($platforms['bindPlatform'])==0):?>
                        <p>您还没有绑定的微博账号</p>
                    <?php else: ?>  
                        <?php for($i=0; $i<count($platforms['bindPlatform']); $i++ ):?>
                            <li class="">
                                
                                <div style="position:relative;height:100px;" >
                                    <img class="img-rounded" src="<?php echo $platforms['bindPlatform'][$i]["image"]; ?>" height="86" width="86" alt="">
                                    <p style="position:absolute;text-align:center;font-size:14px;top:8px;left:100px;"><?php echo $platforms['bindPlatform'][$i]["name"]; ?></p>
                                    <p style="position:absolute;top:40px;left:100px;font-size:12px;">
                                        <?php if($platforms['bindPlatform'][$i]["BlogAccount"][0]["name"]!=""): ?>
                                          <span style="color:#999999">账号:</span><cite><?php echo $platforms['bindPlatform'][$i]["BlogAccount"][0]["name"]; ?></cite>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <?php endif;?>
                                        <?php if($platforms['bindPlatform'][$i]["BlogAccount"][0]["expires_in"]!=0): ?>
                                          <span style="color:#999999">到期时间:</span><cite><?php echo date("Y-m-d H:m:s",$platforms['bindPlatform'][$i]["BlogAccount"][0]["expires_in"]); ?></cite>
                                        <?php else: ?>
                                          <span style="color:#999999">到期时间:</span><cite>不过期</cite>
                                        <?php endif; ?>
                                    </p>
                                    <p style="position:absolute;text-align: right;top:8px;right:10px;">
                                        <a class="btn btn-small btn-success" href="<?php echo url_for("@".$platforms['bindPlatform'][$i]["auth_action"]); ?>">重新绑定</a>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <a class="btn btn-small btn-warning" href="<?php echo url_for("@disbinding-account?account_id=".$platforms['bindPlatform'][$i]["BlogAccount"][0]['id']); ?>">解绑</a>
                                    </p>
                                </div>
                            </li>
                         <?php endfor;?>
                    <?php endif;?>
                    </ul>
                    <hr/>  
                    <ul class="nav nav-list">
                            <li class="nav-header">您还可以绑定</li>
                        <?php for($i=0; $i<count($platforms['unbindPlatform']); $i++ ):?>
                            <li class="">
                                
                                <div style="position:relative;height:100px;" >
                                    <img class="img-rounded" src="<?php echo $platforms['unbindPlatform'][$i]["image"]; ?>" height="86" width="86" alt="">
                                    <p style="position:absolute;text-align:center;font-size:14px;top:8px;left:100px;"><?php echo $platforms['unbindPlatform'][$i]["name"]; ?></p>
                                    
                                    <p style="position:absolute;text-align: right;top:8px;right:10px;">
                                        <a href="<?php echo url_for("@".$platforms['unbindPlatform'][$i]["auth_action"]); ?>">绑定</a>
                                    </p>
                                </div>
                            </li>
                         <?php endfor;?>
                    </ul>
                    

		</div>
		<div class="span3">
                    
		</div>
	</div>
</div>
<br/><br/>
