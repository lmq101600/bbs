<div class="container">
  <div class="row">
    <ul id="myTab" class="nav nav-pills  form-padding">
<!--		    <li><a href="./index.php?route=index/mycenter">修改头像</a></li>-->
		    <li class="active"><a href="./index.php?route=index/mypub">我的主题</a></li>
		    <li><a href="./index.php?route=index/myrep">我的回答</a></li>
<!--		    <li><a href="./index.php?route=msg/showmsg">我的消息</a></li>-->
		    <li><a href="./index.php?route=index/mypsw">修改密码</a></li>
		    <li><a href="./index.php?route=index/whoami">自我介绍</a></li>
		</ul>
		<div id="myTabContent" class="tab-content  form-padding">
		    <div class="tab-pane fade in active">
<?php
if(!empty($mypubs)){	?>
    <div class="table-responsive">
    <table class="table table-striped">
        <thead><tr class="info">
            <td>主题</td>
            <td>板块</td>
            <td>发布时间</td>
            <td>状态</td>
        </tr>
        </thead>
        <?php

            foreach($mypubs as $pub){	?>
                <tr>
                    <td><a target="_blank" href="./index.php?route=index/viewpost&pid=<?php echo $pub['id'];?>"><?php echo $pub['title'];?></a></td>
                    <td><?php echo $this->general("gettagname",$pub['tagid']);?></td>
                    <td><?php echo $pub['time'];?></td>
                    <td><?php echo $pub['status']?'<span style="color: green">已审核</span>':'<span style="color: red">未审核</span>   ';?></td>

                </tr>
            <?php	}

        ?>
    </table>
</div>
<!--	<ul class="list-group mypubulresult">-->
<!--	--><?php
//	foreach($mypubs as $pub){	?>
<!--		<li class="list-group-item">-->
<!--			<a target="_blank" href="./index.php?route=index/viewpost&pid=--><?php //echo $pub['id'];?><!--">--><?php //echo $pub['title'];?><!--</a>-->
<!--            <span>--><?php //echo $pub['status']?'<span style="color: green">已审核</span>':'<span style="color: red">未审核</span>   ';?><!--</span>-->
<!--		</li>-->
<!--	--><?php //	}	?>
<!--</ul>-->
<?php
}
?>
<?php if(!empty($show)){	?>
<nav style="text-align: center"> 
<ul class="pagination">
	<?php
	foreach($show as $page){
		echo "<li>".$page."</li>";	
	}	?>
	</ul>
</nav>
	<script>
	$(function(){
		var currenturl = window.location.href.split("page=");
		var currentpage =  currenturl[1];
		$(".fy"+currentpage).parent().addClass("active");
	})	
	</script>
<?php } ?>
			</div>
		</div>
  </div>
</div>
<script>
    function deletepost(pid){
        var index = layer.confirm('确定要删除吗？', {
            btn: ['确定','取消'],
            title: ['提示', 'font-size:18px;'],
        }, function(){
            $.post("./index.php?route=admin/deletepost",{pid:pid},function(dat){
                if(dat){
                    layer.open({
                        title: '成功提示'
                        ,content: '删除成功!'
                        ,icon: 6
                        ,yes: function(){
                            window.parent.location.reload();
                        }
                    });
                }else{
                    layer.open({
                        title: '错误提示'
                        ,content: '遗憾！操作失败！'
                        ,icon: 5
                        ,yes: function(rel){
                            layer.close(rel);
                        }
                    });
                }
            })
        }, function(){
            layer.close(index);
        });
    }
</script>