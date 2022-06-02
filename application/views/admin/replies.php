<div class="container">
  <div class="row">
      <div class="col-sm-3 hidden-xs">
	      <!-- left -->
	      <h3><i class="fa fa-navicon"></i> 菜单管理</h3>
	      <hr>
	      <ul class="nav nav-stacked">
	      	<li><a href="./index.php?route=admin/tags"><i class="fa fa-hashtag"></i> 标签</a></li>
	        <li><a href="./index.php?route=admin/users"><i class="fa fa-user"></i> 用户</a></li>
	        <li><a href="./index.php?route=admin/posts"><i class="fa fa-file"></i> 主题</a></li>
	        <li class="active"><a href="./index.php?route=admin/replies"><i class="fa fa-reply"></i> 回答</a></li>
<!--	        <li><a href="./index.php?route=admin/clean"><i class="fa fa-send"></i> 消息清理</a></li>-->
	      </ul>
	      <hr>
    </div>
    <div class="col-sm-9">
      <h3>回答列表</h3>
      <hr>
      <div class="table-responsive">
		<table class="table table-striped">
			<thead>
            <tr class="info">
                <td>内容</td>
                <td>用户名</td>
                <td class="text-center">主题</td>
                <td>创建时间</td>
                <td>状态</td>
                <td>操作</td>
            </tr>
            </thead>
		<?php
		if(!empty($replies)){
			foreach($replies as $reply){	?>
				 <tr>
				 	<td><div title="<?php echo $reply['rcontent'];?>" style="overflow: hidden;
text-overflow:ellipsis;
white-space: nowrap;width:200px;"><?php echo $reply['rcontent'];?></div></td>
				 	<td><?php echo $this->general("getnamebyuid",$reply['ruserid']);?></td>
				 	<td class="text-center"><a style="text-decoration:underline" target="_blank" href="./index.php?route=index/viewpost&pid=<?php echo $reply['pid'];?>">查看主题</a></td>
                    <td><?php echo $reply['rtime'];?></td>
                     <td><?php echo $reply['status']?'<span style="color: green">已审核</span>':'<span style="color: red">未审核</span>   ';?></td>
				 	<td>
                        <a class="btn btn-danger btn-xs" onclick="deletereply(<?php echo $reply['rid'];?>)"><i class="fa fa-trash" aria-hidden="true"></i> 删除</a>
                        <a class="btn btn-warning btn-xs" onclick="editpost(<?php echo $reply['rid'];?>,<?php echo $reply['status'];?>)"><i class="fa fa-exchange" aria-hidden="true"></i> 审核</a>
                    </td>
				 </tr>
		<?php	}
		}
		?>
		 </table>
	   </div>
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
<script>
function deletereply(rid){
	var index = layer.confirm('确定要删除吗？', {
		  btn: ['确定','取消'],
		  title: ['提示', 'font-size:18px;'],
		}, function(){
					$.post("./index.php?route=admin/deletereply",{rid:rid},function(dat){
						if(dat){
				  			layer.open({
								  title: '成功提示'
								  ,content: '删除成功!'
								  ,icon: 6
								  ,closeBtn: 0
								  ,yes: function(){
								  	window.parent.location.reload();
								  }
								});
				  		}else{
				  			layer.open({
								  title: '错误提示'
								  ,content: '遗憾！操作失败！'
								  ,icon: 5
								  ,closeBtn: 0
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
function editpost(pid,status){
    $.post("./index.php?route=admin/edit_reply",{rid:pid,status:status},function(dat){
        if(dat){
            layer.open({
                title: '成功提示'
                ,content: '操作成功!'
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

}
</script>