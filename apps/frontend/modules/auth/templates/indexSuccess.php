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
                    <?php include_partial("auth/list", array("condition"=>$condition, "items"=>$items, "pager"=>$pager)); ?>
		</div>
		<div class="span3">
                    <?php include_partial("auth/blogAccounts", array("platforms"=>$platforms)); ?>
		</div>
	</div>
</div>