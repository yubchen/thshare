<blockquote>
    <p class="text-success">
        <strong>已绑定账号</strong>
    </p> <small>您已绑定以下 <cite>微博</cite> 账号</small>
</blockquote>
<hr/>
<?php if(count($platforms['bindPlatform'])==0):?>
    <p>您还没有绑定的微博账号</p>
<?php endif; ?>

    <?php for($i=0; $i<count($platforms['bindPlatform']); $i++ ):?>
    <?php if(($i)%4==0): ?>
        <ul class="thumbnails">
    <?php endif; ?>
        <li class="span3">
            <div class="thumbnail" style="text-align: center;">
                <a href="#">
                    <img src="<?php echo $platforms['bindPlatform'][$i]["image"]; ?>" height="80" width="80" alt="">
                </a>
                <p style="text-align:center;font-size:12px;margin:0;"><?php echo $platforms['bindPlatform'][$i]["name"]; ?></p>

            </div>
        </li>
     <?php if(($i+1)%4==0): ?>
        </ul>
     <?php endif; ?>
     <?php endfor;?>
</ul>
<br/><br/>
<blockquote>
    <p class="text-success">
        <strong>可绑定账号</strong>
    </p> <small>您可以再绑定以下 <cite>微博</cite> 账号</small>
</blockquote>
<hr/>  
<ul class="thumbnails">
<?php for($i=0; $i<count($platforms['unbindPlatform']); $i++ ):?>          

        <li class="span3">
            <div class="thumbnail" style="text-align: center;">
                <a href="#">
                    <img src="http://localhost<?php echo $platforms['unbindPlatform'][$i]["image"]; ?>" height="80" width="80" alt="">
                </a>
                <p style="text-align:center;font-size:12px;margin:0;"><?php echo $platforms['unbindPlatform'][$i]["name"]; ?></p>
                <p style="text-align:center;font-size:12px;margin:0;"><a target="_blank" href="<?php echo url_for("@".$platforms['unbindPlatform'][$i]['auth_action']); ?>">绑定</a> <a href="<?php echo $platforms['unbindPlatform'][$i]['register_url'] ?>">注册</a></p>
            </div>
        </li>

<?php endfor;?>
</ul>
