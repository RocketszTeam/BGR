<!----客服-------------->
<div class="side_service  d-none d-md-block" id="side_service">
	<div class="side_bg">
    <h3><i class="fas fa-comment-dots"></i> 線上客服</h3>
	<a href="#" target="_blank"><div class="ser_icon"><div class="LINE"></div>borinborin00011</div></a>
    <!-- <a href=""><div class="ser_icon"><div class="wechat"></div> text</div></a> -->
	<div class="hr"></div>
	<a class="btn btn-side" href="javascript:openwindow('#' + window.document.title)"> 聯絡客服</a>
	<a class="btn btn-side btn-side2" href="<?php echo site_url("Guide?kind=7")?>"> 代理加盟</a>
	<div class="hr mt-1 d-none d-sm-block"></div>
	<div class="chart mt-1 d-none d-sm-block"><img src="<?php echo ASSETS_URL?>/www/images/chart.png" alt="BOMA" class="img-fluid"></div>
    <div class="sideX">
        <div class="ser_close"><i class="fas fa-times" aria-hidden="true"></i></div>
        <div class="ser_open"><i class="fas fa-times" aria-hidden="true"></i></div>
    </div>
    </div>
</div>

<script>
	$(document).ready(function(){
		$(".ser_close").click(function(){
			$(".side_service").css("right", "-200px");
			$(".ser_close").css("display", "none");
			$(".ser_open").css("display", "block");
		});
	
		$(".ser_open").click(function(){
			$(".side_service").css("right", "0px");
			$(".ser_close").css("display", "block");
			$(".ser_open").css("display", "none");
		});
	});
</script>


<!----客服-------------->
<div class="ser_time  d-none d-md-block" id="ser_time">
	<div class="side_bg">
    <!-- <h3><i class="fas fa-gavel"></i> 維修時間</h3> -->

    <a href="#monday"><h4>星期一<i class="fas fa-caret-down float-right"></i></h4></a>
	<div id="monday">
        <ul>
	        <li><span>沙龍真人</span><br/>10:30-14:00</li>
			<li><span>WM真人</span><br/>12:00~14:00</li>
	        <li><span>SUPER體育</span><br/>12:00-16:00</li>
	        <li><span>六合彩球</span><br/>10:00~16:00</li>
        </ul>
    </div>

	<a href="#tuesday"><h4>星期三<i class="fas fa-caret-down float-right"></i></h4></a>
	<div id="tuesday">
        <ul>
	        <li><span>DG真人</span><br/>07:00-09:00</li>
	        <li><span>歐博真人</span><br/>08:00-12:00</li>
        </ul>
	</div>

    <div class="sideX">
        <div class="time_open">維修時間 <i class="fa fa-chevron-left" aria-hidden="true"></i></div>
        <div class="time_close">維修時間 <i class="fa fa-chevron-right" aria-hidden="true"></i></div>
    </div>

    </div>
</div>

<script>
	$(document).ready(function(){
		$(".time_close").click(function(){
			$(".ser_time").css("left", "0px");
			$(".time_close").css("display", "none");
			$(".time_open").css("display", "block");			
				
		});
	
		$(".time_open").click(function(){
			$(".ser_time").css("left", "-125px");
			$(".time_close").css("display", "block");
			$(".time_open").css("display", "none");					
		});
	});
</script>
