<div class ="filter" style="position:relative;">
    <div class="btn-toolbar">
        <a class="btn <?php if($condition["showcase"]=="true"): ?>active<?php endif;?>" href="<?php echo url_for("@homepage_2")."?showcase=".($condition["showcase"]=="true"?"false":"true")."&column=".$condition["column"]."&query=".$condition['query']; ?>"><i class="icon-th-large"></i>橱窗推荐</a>
        <div class="btn-group"> 
            <a class="btn <?php if($condition["column"]=="list_time:desc"): ?>active<?php endif;?>" href="<?php if($condition["column"]!="list_time:desc"): ?><?php echo url_for("@homepage_2")."?column=list_time:desc&showcase=".$condition["showcase"]."&query=".$condition['query']; ?><?php endif;?>"><i class="icon-arrow-down"></i>上架时间</a>
            <a class="btn <?php if($condition["column"]=="delist_time:desc"): ?>active<?php endif;?>" href="<?php if($condition["column"]!="delist_time:desc"): ?><?php echo url_for("@homepage_2")."?column=delist_time:desc&showcase=".$condition["showcase"]."&query=".$condition['query']; ?><?php endif;?>"><i class="icon-arrow-down"></i>下架时间</a>
        </div>
    </div> 
    <form class="form-search" style="position:absolute;top:3px;right:10px;" method="get" action="<?php echo url_for("@homepage_2")."?"; ?>">
        <div class="input-append">
            <input type="text" value="<?php echo $condition['query']; ?>" class="span2 search-query" style="width:200px;" name="query">
            <input type="hidden" value="<?php echo $condition["column"]; ?>" name="column">
            <input type="hidden" value="<?php echo $condition['showcase']; ?>" name="showcase"> 
            <button type="submit" class="btn">搜索</button>
        </div>
    </form>
</div>
<hr/>
<?php for($i=0; $i<count($items->item); $i+=4 ):?>          
<ul class="thumbnails">
       <?php for($k=$i,$j=0; $k<count($items->item)&&$j<4; $k++,$j++):?>
        <li class="span3">
            <div class="thumbnail">
                <a target="_blank" href="http://item.taobao.com/item.htm?id=<?php echo $items->item[$k]->num_iid ?>">
                    <img src="<?php echo $items->item[$k]->pic_url ?>" height="160" width="160" alt="">
                </a>

                <p class="title"><a target="_blank" href="http://item.taobao.com/item.htm?id=<?php echo $items->item[$k]->num_iid ?>"><?php echo $items->item[$k]->title ?></a></p>
                <p><span class="label badge-important">价格:<?php echo $items->item[$k]->price ?></span></p>
                <p>
                    <a class="btn btn-mini btn-success" href="<?php echo url_for("@to-publish-blog?product_id=".$items->item[$k]->num_iid."&title=".$items->item[$k]->title."&price=".  urlencode($items->item[$k]->price)."&pic_url=".  urlencode($items->item[$k]->pic_url)); ?>">一键扩散</a>
                </p>
            </div>
        </li>
        <?php endfor;?>
</ul>
<?php endfor;?>


<div class="pagination pagination-large pagination-centered">
    <ul>
        <?php if($pager['currentPage']>1):?>
            <li><a href="<?php echo url_for("@homepage_2")."?page=".($pager['currentPage']-1)."&showcase=".$condition["showcase"]."&column=".$condition['column']."&query=".$condition['query']; ?>">&laquo;</a></li>
        <?php else: ?>
            <li class="disabled"><a>&laquo;</a></li>
        <?php endif; ?>
        <?php for($i=$pager['startDisplayPage'];$i<=$pager["endDisplayPage"]; $i++):?>
            <?php if($i == $pager['currentPage']): ?>
                <li class="active"><a href="<?php echo url_for("@homepage_2")."?page=".$i."&showcase=".$condition["showcase"]."&column=".$condition['column']."&query=".$condition['query']; ?>"><?php echo $i; ?></a></li>
            <?php else: ?>
                <li><a href="<?php echo url_for("@homepage_2")."?page=".$i."&showcase=".$condition["showcase"]."&column=".$condition['column']."&query=".$condition['query']; ?>"><?php echo $i; ?></a></li>
            <?php endif; ?>

        <?php endfor; ?>
        <?php if($pager["currentPage"]<$pager["totalPage"]):?>
            <li><a href="<?php echo url_for("@homepage_2")."?page=".($pager['currentPage']+1)."&showcase=".$condition["showcase"]."&column=".$condition['column']."&query=".$condition['query']; ?>">&raquo;</a></li>
        <?php else:?>
            <li class="disabled"><a href="#">&raquo;</a></li>
        <?php endif; ?>
    </ul>
</div>