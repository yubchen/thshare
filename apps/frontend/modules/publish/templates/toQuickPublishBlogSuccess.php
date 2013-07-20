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
                    <form action="<?php echo url_for("@quick-publish-blog") ?>" method="POST">
                        <fieldset>
                                 <legend>宝贝的url地址</legend>
                                 <label>今天还可以发布<strong><?php echo $last_publish_times; ?></strong>次</label>
                                 <input type="text" name="url"/> 
                                 <span class="help-block">请直接从浏览器的地址栏复制宝贝的网址.此分享模式为分享到公共的微博平台上</span> 
                                 <button type="submit" class="btn">确定</button>
                                 <span style="color:red"><?php echo $errormsg; ?></span>
                        </fieldset>
                    </form>
		</div>
		<div class="span3">
                    <?php include_partial("auth/blogAccounts", array("platforms"=>$platforms)); ?>
		</div>
	</div>
</div>