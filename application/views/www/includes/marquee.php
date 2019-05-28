<!-- marquee --------------------------------->             
<section class="wrapper mqbox">
  <div class="marqueebox">
      <div id="marquue_icon"><img src="<?php echo ASSETS_URL?>/www/images/marquue_icon.png" alt="最新公告"/></div>
                               
      <ul id="marquee" class="marquee">
<!--      	  --><?php //if(isset($newsList)):?>
<!--          --><?php //foreach($newsList as $row):?>
<!--          <li><a href="#" data-toggle="modal" data-target="#marquee_info">--><?php //echo $row["word"]?><!--</a> </li>-->
<!--			--><?php //endforeach;?>
<!--            --><?php //endif;?>
          <li>
              <a href="#" data-toggle="modal" data-target="#marquee_info">
                  <my-marquee :messages="messages"/>
              </a>
          </li>
      </ul>
  </div>                  
</section>

  <!-- 最新消息彈出視窗-->
  <div class="modal fade" id="marquee_info">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">最新消息</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
					<ul>
                <?php if(isset($newsList)):?>
                <?php foreach($newsList as $row):?>
                 <li class="news_info"><a><?php echo $row["word"]?></a></li>
                <?php endforeach;?>
                <?php endif;?>
          </ul>

        </div>
        
        <!-- Modal footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-coffee btn-sm" data-dismiss="modal"><i class="fas fa-times"></i> 關閉</button>
          <!--<button type="button" class="btn btn-coffee btn-sm" data-dismiss="modal"><i class="fas fa-times-circle"></i> 不再顯示</button>-->
        </div>
        
      </div>
    </div>
  </div>

