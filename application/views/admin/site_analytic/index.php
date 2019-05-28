
<script src="<?php echo ASSETS_URL?>/admin/js/highcharts.js"></script>
<script>
    (function(w,d,s,g,js,fs){
        g=w.gapi||(w.gapi={});g.analytics={q:[],ready:function(f){this.q.push(f);}};
        js=d.createElement(s);fs=d.getElementsByTagName(s)[0];
        js.src='https://apis.google.com/js/platform.js';
        fs.parentNode.insertBefore(js,fs);js.onload=function(){g.load('analytics');};
    }(window,document,'script'));
</script>



<script>

    gapi.analytics.ready(function() {

			/**
	   * Authorize the user immediately if the user has already granted access.
	   * If no access has been created, render an authorize button inside the
	   * element with the ID "embed-api-auth-container".
	   */
	
        gapi.analytics.auth.authorize({
            container: 'embed-api-auth-container',
            //clientid: '374924032810-2j48liram1t4gvoef30a1f47ar8gsp1i.apps.googleusercontent.com'
			clientid: '452474600031-d1sgi2u8dk7poa6g3b3fu0dl2llonion.apps.googleusercontent.com'
        });
		


		/**
	   * Create a ViewSelector for the first view to be rendered inside of an
	   * element with the id "view-selector-1-container".
	   */
        var viewSelector1 = new gapi.analytics.ViewSelector({
            container: 'view-selector-1-container'
        });

        /**
	   * Create a ViewSelector for the second view to be rendered inside of an
	   * element with the id "view-selector-2-container".
	   */
        var viewSelector2 = new gapi.analytics.ViewSelector({
            container: 'view-selector-2-container'
        });

		var viewSelector3 = new gapi.analytics.ViewSelector({
            container: 'view-selector-3-container'
        });

		
		var viewSelector4 = new gapi.analytics.ViewSelector({
            container: 'view-selector-4-container'
        });
	
		var viewSelector5 = new gapi.analytics.ViewSelector({
            container: 'view-selector-5-container'
        });

		var viewSelector6 = new gapi.analytics.ViewSelector({
            container: 'view-selector-6-container'
        });

		var viewSelector7 = new gapi.analytics.ViewSelector({
            container: 'view-selector-7-container'
        });
		
		var viewSelector8 = new gapi.analytics.ViewSelector({
            container: 'view-selector-8-container'
        });
		
		
        // Render both view selectors to the page.
        viewSelector1.execute();
        viewSelector2.execute();
		viewSelector3.execute();
		viewSelector4.execute();
		viewSelector5.execute();
		viewSelector6.execute();
		viewSelector7.execute();
		viewSelector8.execute();


        /**
	   * Create the first DataChart for top countries over the past 30 days.
	   * It will be rendered inside an element with the id "chart-1-container".
	   */
        var dataChart1 = new gapi.analytics.googleCharts.DataChart({
            query: {
                metrics: 'ga:sessions',
                dimensions: 'ga:country',
                'start-date': '30daysAgo',
                'end-date': 'yesterday',
                'max-results': 6,
                sort: '-ga:sessions'
            },
            chart: {
                container: 'chart-1-container',
                type: 'PIE',
                options: {
                    title:'國家',
					width: '100%',
					is3D: true,
                    //pieHole: 4/9
                }
            }
        });


        /**
	   * Create the second DataChart for top countries over the past 30 days.
	   * It will be rendered inside an element with the id "chart-2-container".
	   */
        var dataChart2 = new gapi.analytics.googleCharts.DataChart({
            query: {
                metrics: 'ga:sessions',
                dimensions: 'ga:date',
                'start-date': '30daysAgo',
                'end-date': 'today'
            },
            chart: {
                container: 'chart-2-container',
                type: 'LINE',
                options: {
					title:'每天瀏覽次數',
					curveType:'none',
                    width: '100%',
					
                    //pieHole: 4/9
                }
            }
        });

		var dataChart3 = new gapi.analytics.googleCharts.DataChart({
           query: {
			  'dimensions': 'ga:pagePath',
			  'metrics': 'ga:avgTimeOnPage',
			  //'sort': '-ga:sessions',
			  //'max-results': '6'
			},
			chart: {
			  type: 'LINE',
			  container: 'chart-3-container',
			  options: {
				title:'頁面停留時間',
				
				width: '100%'
			  }
			}
			
        });
		
		var dataChart4 = new gapi.analytics.googleCharts.DataChart({
           query: {
			  'dimensions': 'ga:browser',
			  'metrics': 'ga:sessions',
			  'sort': '-ga:sessions',
			  //'max-results': '6'
			},
			chart: {
			  type: 'TABLE',
			  container: 'chart-4-container',
			  options: {
				title:'瀏覽器類別',
				width: '100%'
			  }
			}
        });
		

		var dataChart5 = new gapi.analytics.googleCharts.DataChart({
            query: {
                metrics: 'ga:users',
                dimensions: 'ga:hour',
                //'start-date': '30daysAgo',
               // 'end-date': 'today'
            },
            chart: {
                container: 'chart-5-container',
                type: 'LINE',
                options: {
					title:'每小時瀏覽人次',
                    width: '100%',
                    //pieHole: 4/9
                }
            }
        });
		
		var dataChart6 = new gapi.analytics.googleCharts.DataChart({
            query: {
                metrics: 'ga:sessions',
                dimensions: 'ga:deviceCategory',
                //'start-date': '30daysAgo',
                //'end-date': 'yesterday',
                //'max-results': 6,
                sort: '-ga:sessions'
            },
            chart: {
                container: 'chart-6-container',
                type: 'COLUMN',
                options: {
					title:'桌機與行動裝置瀏覽次數(  Desktop:桌機，mobile:行動裝置  )',
                    width: '100%',
					
                    //pieHole: 4/9
                }
            }
        });
		

		var dataChart7 = new gapi.analytics.googleCharts.DataChart({
            query: {
                metrics: 'ga:sessions',
                dimensions: 'ga:userType',
                //'start-date': '30daysAgo',
                //'end-date': 'yesterday',
                //'max-results': 6,
                sort: '-ga:sessions'
            },
            chart: {
                container: 'chart-7-container',
                type: 'COLUMN',
                options: {
					title:'回訪者與新訪者次數 (Returning Visitor:回訪者 , New Visitor:新訪者)',
                    width: '100%',
                    //pieHole: 4/9
                }
            }
        });
		

		var dataChart8 = new gapi.analytics.googleCharts.DataChart({
            query: {
                metrics: 'ga:sessions',
                dimensions: 'ga:city',
                //'start-date': '30daysAgo',
                //'end-date': 'yesterday',
                //'max-results': 6,
                sort: '-ga:sessions'
            },
            chart: {
                container: 'chart-8-container',
                type: 'COLUMN',
                options: {
					title:'城市',
                    width: '100%',
                    //pieHole: 4/9
                }
            }
        });

        /**
	   * Update the first dataChart when the first view selecter is changed.
	   */
        viewSelector1.on('change', function(ids) {
            dataChart1.set({query: {ids: ids}}).execute();
			//console.log(dataChart1);
			//alert(typeof dataChart1.IP);
        });

        /**
	   * Update the second dataChart when the second view selecter is changed.
	   */
        viewSelector2.on('change', function(ids) {
            dataChart2.set({query: {ids: ids}}).execute();
			//console.log(dataChart2);
			//var jj = eval(dataChart2);
			//console.log(jj);
        });
		
		
		viewSelector3.on('change', function(ids) {
            dataChart3.set({query: {ids: ids}}).execute();
			
        });
		
		
		viewSelector4.on('change', function(ids) {
            dataChart4.set({query: {ids: ids}}).execute();
        });


		viewSelector5.on('change', function(ids) {
            dataChart5.set({query: {ids: ids}}).execute();
        });

		viewSelector6.on('change', function(ids) {
            dataChart6.set({query: {ids: ids}}).execute();
        });

		viewSelector7.on('change', function(ids) {
            dataChart7.set({query: {ids: ids}}).execute();
        });

		viewSelector8.on('change', function(ids) {
            dataChart8.set({query: {ids: ids}}).execute();
        });
    });

	

</script>


<script type="text/JavaScript">
	$(function(){
		$('#toDay').click(function(){	//本日
			$('#find7').val($(this).attr('data-d1') + ' 00:00:00');
			$('#find8').val($(this).attr('data-d2') + ' 23:59:59');
		});
		
		$('#yeDay').click(function(){	//昨日
			$('#find7').val($(this).attr('data-d1') + ' 00:00:00');
			$('#find8').val($(this).attr('data-d2') + ' 23:59:59');
		});
		
		$('#toWeek').click(function(){	//本週
			$('#find7').val($(this).attr('data-d1') + ' 00:00:00');
			$('#find8').val($(this).attr('data-d2') + ' 23:59:59');
		});
	
		$('#yeWeek').click(function(){	//本週
			$('#find7').val($(this).attr('data-d1') + ' 00:00:00');
			$('#find8').val($(this).attr('data-d2') + ' 23:59:59');
		});
		$('#toMonth').click(function(){	//本月
			$('#find7').val($(this).attr('data-d1') + ' 00:00:00');
			$('#find8').val($(this).attr('data-d2') + ' 23:59:59');
		});
		$('#ymMonth').click(function(){	//上月
			$('#find7').val($(this).attr('data-d1') + ' 00:00:00');
			$('#find8').val($(this).attr('data-d2') + ' 23:59:59');
		});
		
	});
</script>





<div class="row">
	<form  method="get" action="">
        <div class="col-xs-12 col-sm-5">
            <div class="form-group">
                <div class="input-daterange  input-group">
                    <input type="text" class="input-sm form-control jqdatetime" name="find7" id="find7" value="<?php echo  @$_REQUEST["find7"] ?>" placeholder="開始日期"  />
                    <span class="input-group-addon">
                        <i class="fa fa-exchange"></i>
                    </span>
                    <input type="text" class="input-sm form-control jqdatetime" name="find8" id="find8" value="<?php echo @$_REQUEST["find8"]?>" placeholder="結束日期"   />
                    <!--<span class="input-group-addon">帳號</span>
                    <input type="text" class="input-sm form-control" name="find9" id="find9" value="<?php //echo @$_REQUEST["find9"]?>" placeholder="帳號(搭配日期)"   />-->
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-5">
            <div class="form-group">
                <div class="btn-group">
                    <button type="sbumit" class="btn btn-white btn-sm btn-primary">搜尋</button>
                    <button type="sbumit" class="btn btn-white btn-sm btn-primary" id="toDay" data-d1="<?php echo date('Y-m-d')?>" data-d2="<?php echo date('Y-m-d')?>">本日</button>
                    <button type="sbumit" class="btn btn-white btn-sm btn-primary" id="yeDay" data-d1="<?php echo date('Y-m-d',strtotime("-1 day"))?>" data-d2="<?php echo date('Y-m-d',strtotime("-1 day"))?>">昨日</button>
                    <button type="sbumit" class="btn btn-white btn-sm btn-primary" id="toWeek" data-d1="<?php echo $toWeek['d1']?>" data-d2="<?php echo $toWeek['d2']?>">本週</button>
                    <button type="sbumit" class="btn btn-white btn-sm btn-primary" id="yeWeek" data-d1="<?php echo $yeWeek['d1']?>" data-d2="<?php echo $yeWeek['d2']?>">上週</button>
                    <button type="sbumit" class="btn btn-white btn-sm btn-primary" id="toMonth" data-d1="<?php echo $toMonth['d1']?>" data-d2="<?php echo $toMonth['d2']?>">本月</button>
                    <button type="sbumit" class="btn btn-white btn-sm btn-primary" id="ymMonth" data-d1="<?php echo $ymMonth['d1']?>" data-d2="<?php echo $ymMonth['d2']?>">上月</button>
                </div>
            </div>
        </div>
    </form>    
</div>


<div class="col-sm-12">
	<div class="widget-box transparent">
		<div class="widget-header">
			<h4 class="widget-title lighter smaller">
				<i class="ace-icon fa fa-bar-chart orange2"></i>全館遊戲點擊排行
			</h4>
			<!--<div class="widget-toolbar no-border">
				<div class="btn-group">
					<button type="submit" class="btn btn-white btn-sm btn-primary" id="toWeek" data-d1="<?php echo $toWeek['d1']?>" data-d2="<?php echo $toWeek['d2']?>">本週</button>
					<button type="submit" class="btn btn-white btn-sm btn-primary" id="yeWeek" data-d1="<?php echo $yeWeek['d1']?>" data-d2="<?php echo $yeWeek['d2']?>">上週</button>
				</div>
			</div>-->
		</div>
		<div class="widget-body">
			<div class="widget-main padding-4">
				<!--<h4 class="smaller lighter red"><?php echo $toWeek['d1']?> ~ <?php echo $toWeek['d2']?></h4>-->
				<div id="container"></div>
			 
			</div>
		</div>
	</div>
</div>


<div class="row">
	<div class="col-sm-4">
		<div class="widget-box transparent">
			<div class="widget-header">
				<h4 class="widget-title lighter smaller">
					<i class="ace-icon fa fa-signal red"></i>拋售排行前10名
				</h4>
			</div>
			
			<div class="widget-body">
				<div class="widget-main padding-4">
					<div class="comments"><?php //var_dump($rowOrders_2);?>
						<?php if(isset($rowOrders_2)){?>
						<?php foreach($rowOrders_2 as $row):?>
						<div class="itemdiv commentdiv">
							<div class="user">
								<img  src="<?php echo ASSETS_URL?>/admin/images/avatars/avatar2.png" />
							</div>
							<div class="body">
								<div class="name">
									 <a href="<?php echo site_url(SYSTEM_URL."/Member/index?find2=".tb_sql("u_id","member",$row["mem_num"]))?>"><?php echo tb_sql("u_id","member",$row["mem_num"])?>(<?php echo tb_sql("u_name","member",$row["mem_num"])?>)</a>
								</div>
								<div class="text">
									<i class="ace-icon fa fa-usd red"></i>
									<?php echo number_format($row["su_mount"],0)?>
								</div>
							</div>
			
							
						</div>
						<?php endforeach;?>
						<?php }?>
					</div>   
				</div>
			</div>
			
		</div>
	</div>

	<div class="col-sm-4">
		<div class="widget-box transparent">
			<div class="widget-header">
				<h4 class="widget-title lighter smaller">
					<i class="ace-icon fa fa-signal red"></i>儲值排行前10名
				</h4>
			</div>
			<div class="widget-body">
				<div class="widget-main padding-4">
					<div class="comments"><?php //var_dump($rowOrders);?>
						<?php if(isset($rowOrders)):?>
						<?php foreach($rowOrders as $row):?>
						<div class="itemdiv commentdiv">
							<div class="user">
								<img  src="<?php echo ASSETS_URL?>/admin/images/avatars/avatar2.png" />
							</div>
							<div class="body">
								<div class="name">
									 <a href="<?php echo site_url(SYSTEM_URL."/Member/index?find2=".tb_sql("u_id","member",$row["mem_num"]))?>"><?php echo tb_sql("u_id","member",$row["mem_num"])?>(<?php echo tb_sql("u_name","member",$row["mem_num"])?>)</a>
								</div>
								<div class="text">
									<i class="ace-icon fa fa-usd red"></i>
									<?php echo number_format($row["sum_amount"],0)?>
								</div>
							</div>
			
							
						</div>
						<?php endforeach;?>
						<?php endif;?>
					</div>   
				</div>
			</div>
		</div>
	</div>
	<?php //var_dump($this->_ci_cached_vars);?>
	<div class="col-sm-4">
		<div class="widget-box transparent">
			<div class="widget-header">
				<h4 class="widget-title lighter smaller">
					<i class="ace-icon fa fa-signal red"></i>(拋售/儲值)* % 排行前10名
				</h4>
			</div>
			<div class="widget-body">
				<div class="widget-main padding-4">
					<div class="comments">
						<?php if(isset($rowOrders_3)): $i=0?>
						<?php 
							foreach($rowOrders_3 as $row){ 
							if($i<10){ 
							//var_dump($i);
						?>
							<div class="itemdiv commentdiv">
								<div class="user">
									<img  src="<?php echo ASSETS_URL?>/admin/images/avatars/avatar2.png" />
								</div>
								<div class="body">
									<div class="name">
										 <a href="<?php echo site_url(SYSTEM_URL."/Member/index?find2=".tb_sql("u_id","member",$row["num"]))?>"><?php echo tb_sql("u_id","member",$row["num"])?>(<?php echo tb_sql("u_name","member",$row["num"])."|".$row['num']?>)</a>
									</div>
									<div class="text">
										
										<?php echo number_format($row["sell_percent_all_ok"],0)?>&nbsp;<i class="fa fa-percent red" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;(<?php echo $row["smem"]."/".$row['smbt']?>)
										
									</div>
								</div>
									
								
							</div>
						<?php 
							
							$i++;
							};
						}
						?>
						<?php endif;?>
					</div>
					
					
				</div>
			</div>
		</div>
	</div>
	
	
	
</div><br>

<h4 class="widget-title lighter smaller">
	<i class="ace-icon fa fa-bar-chart orange2"></i>各館遊戲輸贏排行前10名
</h4><br>

<div class="row">
	
	<div class="col-sm-6">
		<div class="widget-box transparent">
			<div class="widget-header">
				<h4 class="widget-title lighter smaller">
					<i class="ace-icon fa fa-bar-chart orange2"></i>北京賽車 <div class="name">玩家人數:&nbsp;<?php echo $rowOrders_5_player?></div> 
				</h4>
				<!--<div class="widget-toolbar no-border">
					<div class="btn-group">
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="toWeek" data-d1="<?php echo $toWeek['d1']?>" data-d2="<?php echo $toWeek['d2']?>">本週</button>
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="yeWeek" data-d1="<?php echo $yeWeek['d1']?>" data-d2="<?php echo $yeWeek['d2']?>">上週</button>
					</div>
				</div>-->
			</div>
			<div class="widget-body">
				<div class="widget-main padding-4">
					<!--<h4 class="smaller lighter red"><?php echo $toWeek['d1']?> ~ <?php echo $toWeek['d2']?></h4>-->
					<div id="container_k168"></div>
				 
				</div>
			</div>
		</div>
	</div>
	
	
	<div class="col-sm-6">
		<div class="widget-box transparent">
			<div class="widget-header">
				<h4 class="widget-title lighter smaller">
					<i class="ace-icon fa fa-bar-chart orange2"></i>super體育 <div class="name">玩家人數:&nbsp;<?php echo $rowOrders_6_player?></div> 
				</h4>
				<!--<div class="widget-toolbar no-border">
					<div class="btn-group">
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="toWeek" data-d1="<?php echo $toWeek['d1']?>" data-d2="<?php echo $toWeek['d2']?>">本週</button>
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="yeWeek" data-d1="<?php echo $yeWeek['d1']?>" data-d2="<?php echo $yeWeek['d2']?>">上週</button>
					</div>
				</div>-->
			</div>
			<div class="widget-body">
				<div class="widget-main padding-4">
					<!--<h4 class="smaller lighter red"><?php echo $toWeek['d1']?> ~ <?php echo $toWeek['d2']?></h4>-->
					<div id="container_super_sport"></div>
				 
				</div>
			</div>
		</div>
	</div>
	
	
	<div class="col-sm-6">
		<div class="widget-box transparent">
			<div class="widget-header">
				<h4 class="widget-title lighter smaller">
					<i class="ace-icon fa fa-bar-chart orange2"></i>贏家體育 <div class="name">玩家人數:&nbsp;<?php echo $rowOrders_ssb_player?></div> 
				</h4>
				<!--<div class="widget-toolbar no-border">
					<div class="btn-group">
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="toWeek" data-d1="<?php echo $toWeek['d1']?>" data-d2="<?php echo $toWeek['d2']?>">本週</button>
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="yeWeek" data-d1="<?php echo $yeWeek['d1']?>" data-d2="<?php echo $yeWeek['d2']?>">上週</button>
					</div>
				</div>-->
			</div>
			<div class="widget-body">
				<div class="widget-main padding-4">
					<!--<h4 class="smaller lighter red"><?php echo $toWeek['d1']?> ~ <?php echo $toWeek['d2']?></h4>-->
					<div id="container_ssb_sport"></div>
				 
				</div>
			</div>
		</div>
	</div>
	
	
	<div class="col-sm-6">
		<div class="widget-box transparent">
			<div class="widget-header">
				<h4 class="widget-title lighter smaller">
					<i class="ace-icon fa fa-bar-chart orange2"></i>沙龍 <div class="name">玩家人數:&nbsp;<?php echo $rowOrders_7_player?></div> 
				</h4>
				<!--<div class="widget-toolbar no-border">
					<div class="btn-group">
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="toWeek" data-d1="<?php echo $toWeek['d1']?>" data-d2="<?php echo $toWeek['d2']?>">本週</button>
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="yeWeek" data-d1="<?php echo $yeWeek['d1']?>" data-d2="<?php echo $yeWeek['d2']?>">上週</button>
					</div>
				</div>-->
			</div>
			<div class="widget-body">
				<div class="widget-main padding-4">
					<!--<h4 class="smaller lighter red"><?php echo $toWeek['d1']?> ~ <?php echo $toWeek['d2']?></h4>-->
					<div id="container_sa"></div>
				 
				</div>
			</div>
		</div>
	</div>
	
	
	<div class="col-sm-6">
		<div class="widget-box transparent">
			<div class="widget-header">
				<h4 class="widget-title lighter smaller">
					<i class="ace-icon fa fa-bar-chart orange2"></i>DG真人 <div class="name">玩家人數:&nbsp;<?php echo $rowOrders_8_player?></div> 
				</h4>
				<!--<div class="widget-toolbar no-border">
					<div class="btn-group">
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="toWeek" data-d1="<?php echo $toWeek['d1']?>" data-d2="<?php echo $toWeek['d2']?>">本週</button>
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="yeWeek" data-d1="<?php echo $yeWeek['d1']?>" data-d2="<?php echo $yeWeek['d2']?>">上週</button>
					</div>
				</div>-->
			</div>
			<div class="widget-body">
				<div class="widget-main padding-4">
					<!--<h4 class="smaller lighter red"><?php echo $toWeek['d1']?> ~ <?php echo $toWeek['d2']?></h4>-->
					<div id="container_dg"></div>
				 
				</div>
			</div>
		</div>
	</div>
	
	<div class="col-sm-6">
		<div class="widget-box transparent">
			<div class="widget-header">
				<h4 class="widget-title lighter smaller">
					<i class="ace-icon fa fa-bar-chart orange2"></i>歐博真人 <div class="name">玩家人數:&nbsp;<?php echo $rowOrders_9_player?></div> 
				</h4>
				<!--<div class="widget-toolbar no-border">
					<div class="btn-group">
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="toWeek" data-d1="<?php echo $toWeek['d1']?>" data-d2="<?php echo $toWeek['d2']?>">本週</button>
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="yeWeek" data-d1="<?php echo $yeWeek['d1']?>" data-d2="<?php echo $yeWeek['d2']?>">上週</button>
					</div>
				</div>-->
			</div>
			<div class="widget-body">
				<div class="widget-main padding-4">
					<!--<h4 class="smaller lighter red"><?php echo $toWeek['d1']?> ~ <?php echo $toWeek['d2']?></h4>-->
					<div id="container_ab"></div>
				 
				</div>
			</div>
		</div>
	</div>
	
	<div class="col-sm-6">
		<div class="widget-box transparent">
			<div class="widget-header">
				<h4 class="widget-title lighter smaller">
					<i class="ace-icon fa fa-bar-chart orange2"></i>Ameba <div class="name">玩家人數:&nbsp;<?php echo $rowOrders_ameba_player?></div> 
				</h4>
				<!--<div class="widget-toolbar no-border">
					<div class="btn-group">
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="toWeek" data-d1="<?php echo $toWeek['d1']?>" data-d2="<?php echo $toWeek['d2']?>">本週</button>
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="yeWeek" data-d1="<?php echo $yeWeek['d1']?>" data-d2="<?php echo $yeWeek['d2']?>">上週</button>
					</div>
				</div>-->
			</div>
			<div class="widget-body">
				<div class="widget-main padding-4">
					<!--<h4 class="smaller lighter red"><?php echo $toWeek['d1']?> ~ <?php echo $toWeek['d2']?></h4>-->
					<div id="container_ameba"></div>
				 
				</div>
			</div>
		</div>
	</div>
	
	
	<div class="col-sm-6">
		<div class="widget-box transparent">
			<div class="widget-header">
				<h4 class="widget-title lighter smaller">
					<i class="ace-icon fa fa-bar-chart orange2"></i>7pk <div class="name">玩家人數:&nbsp;<?php echo $rowOrders_7pk_player?></div> 
				</h4>
				<!--<div class="widget-toolbar no-border">
					<div class="btn-group">
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="toWeek" data-d1="<?php echo $toWeek['d1']?>" data-d2="<?php echo $toWeek['d2']?>">本週</button>
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="yeWeek" data-d1="<?php echo $yeWeek['d1']?>" data-d2="<?php echo $yeWeek['d2']?>">上週</button>
					</div>
				</div>-->
			</div>
			<div class="widget-body">
				<div class="widget-main padding-4">
					<!--<h4 class="smaller lighter red"><?php echo $toWeek['d1']?> ~ <?php echo $toWeek['d2']?></h4>-->
					<div id="container_7pk"></div>
				 
				</div>
			</div>
		</div>
	</div>
	
	
	<div class="col-sm-6">
		<div class="widget-box transparent">
			<div class="widget-header">
				<h4 class="widget-title lighter smaller">
					<i class="ace-icon fa fa-bar-chart orange2"></i>QT <div class="name">玩家人數:&nbsp;<?php echo $rowOrders_qt_player?></div> 
				</h4>
				<!--<div class="widget-toolbar no-border">
					<div class="btn-group">
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="toWeek" data-d1="<?php echo $toWeek['d1']?>" data-d2="<?php echo $toWeek['d2']?>">本週</button>
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="yeWeek" data-d1="<?php echo $yeWeek['d1']?>" data-d2="<?php echo $yeWeek['d2']?>">上週</button>
					</div>
				</div>-->
			</div>
			<div class="widget-body">
				<div class="widget-main padding-4">
					<!--<h4 class="smaller lighter red"><?php echo $toWeek['d1']?> ~ <?php echo $toWeek['d2']?></h4>-->
					<div id="container_qt"></div>
				 
				</div>
			</div>
		</div>
	</div>
	
	
	<div class="col-sm-6">
		<div class="widget-box transparent">
			<div class="widget-header">
				<h4 class="widget-title lighter smaller">
					<i class="ace-icon fa fa-bar-chart orange2"></i>SUPER六合彩<div class="name">玩家人數:&nbsp;<?php echo $rowOrders_slottery_player?></div> 
				</h4>
				<!--<div class="widget-toolbar no-border">
					<div class="btn-group">
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="toWeek" data-d1="<?php echo $toWeek['d1']?>" data-d2="<?php echo $toWeek['d2']?>">本週</button>
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="yeWeek" data-d1="<?php echo $yeWeek['d1']?>" data-d2="<?php echo $yeWeek['d2']?>">上週</button>
					</div>
				</div>-->
			</div>
			<div class="widget-body">
				<div class="widget-main padding-4">
					<!--<h4 class="smaller lighter red"><?php echo $toWeek['d1']?> ~ <?php echo $toWeek['d2']?></h4>-->
					<div id="container_slottery"></div>
				 
				</div>
			</div>
		</div>
	</div>
	
	
	<div class="col-sm-6">
		<div class="widget-box transparent">
			<div class="widget-header">
				<h4 class="widget-title lighter smaller">
					<i class="ace-icon fa fa-bar-chart orange2"></i>捕魚機 <div class="name">玩家人數:&nbsp;<?php echo $rowOrders_fish_player?></div> 
				</h4>
				<!--<div class="widget-toolbar no-border">
					<div class="btn-group">
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="toWeek" data-d1="<?php echo $toWeek['d1']?>" data-d2="<?php echo $toWeek['d2']?>">本週</button>
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="yeWeek" data-d1="<?php echo $yeWeek['d1']?>" data-d2="<?php echo $yeWeek['d2']?>">上週</button>
					</div>
				</div>-->
			</div>
			<div class="widget-body">
				<div class="widget-main padding-4">
					<!--<h4 class="smaller lighter red"><?php echo $toWeek['d1']?> ~ <?php echo $toWeek['d2']?></h4>-->
					<div id="container_fish"></div>
				 
				</div>
			</div>
		</div>
	</div>
	
	
	<div class="col-sm-6">
		<div class="widget-box transparent">
			<div class="widget-header">
				<h4 class="widget-title lighter smaller">
					<i class="ace-icon fa fa-bar-chart orange2"></i>賓果 <div class="name">玩家人數:&nbsp;<?php echo $rowOrders_bingo_player?></div> 
				</h4>
				<!--<div class="widget-toolbar no-border">
					<div class="btn-group">
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="toWeek" data-d1="<?php echo $toWeek['d1']?>" data-d2="<?php echo $toWeek['d2']?>">本週</button>
						<button type="submit" class="btn btn-white btn-sm btn-primary" id="yeWeek" data-d1="<?php echo $yeWeek['d1']?>" data-d2="<?php echo $yeWeek['d2']?>">上週</button>
					</div>
				</div>-->
			</div>
			<div class="widget-body">
				<div class="widget-main padding-4">
					<!--<h4 class="smaller lighter red"><?php echo $toWeek['d1']?> ~ <?php echo $toWeek['d2']?></h4>-->
					<div id="container_bingo"></div>
				 
				</div>
			</div>
		</div>
	</div>
	
	
	
	
	
	
</div>	
	
	
	
	
	
	
	
	
</div>	



<div id="embed-api-auth-container"></div>
<div id="view-selector-1-container" style="display:none"></div>
<div id="view-selector-2-container" style="display:none"></div>
<div id="view-selector-3-container" style="display:none"></div>
<div id="view-selector-4-container" style="display:none"></div>
<div id="view-selector-5-container" style="display:none"></div>
<div id="view-selector-6-container" style="display:none"></div>
<div id="view-selector-7-container" style="display:none"></div>
<div id="view-selector-8-container" style="display:none"></div>



<div id="chart-1-container" class="animated flipInY col-lg-4 col-md-4 col-sm-12 col-xs-12" ></div>
<div id="chart-8-container" class="animated flipInY col-lg-4 col-md-4 col-sm-12 col-xs-12" ></div>
<div id="chart-6-container" class="animated flipInY col-lg-4 col-md-4 col-sm-12 col-xs-12" ></div>
<!--
<div id="chart-3-container" class="animated flipInY col-lg-12 col-md-8 col-sm-12 col-xs-12" ></div>
<div id="chart-5-container" class="animated flipInY col-lg-12 col-md-8 col-sm-12 col-xs-12" ></div>
<div id="chart-2-container" class="animated flipInY col-lg-6 col-md-8 col-sm-12 col-xs-12" ></div>
<div id="chart-7-container" class="animated flipInY col-lg-6 col-md-3 col-sm-12 col-xs-12" ></div>
<div id="chart-4-container" class="animated flipInY col-lg-4 col-md-4 col-sm-12 col-xs-12" ></div>
-->




<div class="text-center">
	<?php echo @$pagination ?>
    <!--<ul class="pagination">
        <li><a href="#"><i class="ace-icon fa fa-angle-double-left"></i></a></li>
        <li><a href="#">1</a></li>
        <li class="active"><a href="#">2</a></li>
        <li><a href="#"><i class="ace-icon fa fa-angle-double-right"></i></a></li>
    </ul>-->
</div>
<script type="text/javascript">
$(function () {

    $('#container').highcharts({
		
        chart: {
            type: 'column'
        },
        title: {
            text: '遊戲點擊報表'
        },
       // subtitle: {
           // text: '本周營運'
       // },
        xAxis: {
            categories: [
			<?php
				foreach($rowOrders_4 as $row){
			?>
                '<?php echo $row['makers_name'];?>',
			<?php
				}
			?>	
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '點擊次數'
            },
			labels: {
				formatter:function(){
					return Highcharts.numberFormat(this.value,0,null,',') + '次';	
				}
			}
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormatter:function(){return '<tr><td style="color:'+ this.series.color +';padding:0">'+ '次數' +': </td>' +
            '<td style="padding:0"><b>'+ Highcharts.numberFormat(this.y,0,null,',') +'</b></td></tr>'},
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
            name: '遊戲',
            data: [<?php echo $gameTimes; ?>],
        }]
    });
	
	
	
	$('#container_k168').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: ''
        },
       // subtitle: {
           // text: '本周營運'
       // },
        xAxis: {
            categories: [
			<?php
				foreach($rowOrders_5 as $row){
			?>
                '<a href="<?php echo site_url(SYSTEM_URL."/Member/index?find2=".tb_sql("u_id","member",$row["mem_num"]))?>"><?php echo tb_sql("u_id","member",$row["mem_num"])?>(<?php echo tb_sql("u_name","member",$row["mem_num"])?>)</a>',
			<?php
				}
			?>	
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '輸贏'
            },
			labels: {
				formatter:function(){
					return Highcharts.numberFormat(this.value,0,null,',') + '元';	
				}
			}
			
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormatter:function(){return '<tr><td style="color:'+ this.series.color +';padding:0">'+ this.series.name +': </td>' +
            '<td style="padding:0"><b>'+ Highcharts.numberFormat(this.y,0,null,',') +'</b></td></tr>'},
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
            name: '輸贏',
            data: [<?php echo $k168_wOrL; ?>],
			color:'#f7a35c'
        },{
            name: '勝率',
            data: [<?php echo $k168_win_rate; ?>],
        }]
    });
	
	
	$('#container_super_sport').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: ''
        },
       // subtitle: {
           // text: '本周營運'
       // },
        xAxis: {
            categories: [
			<?php
				foreach($rowOrders_6 as $row){
			?>
                '<a href="<?php echo site_url(SYSTEM_URL."/Member/index?find2=".tb_sql("u_id","member",$row["mem_num"]))?>"><?php echo tb_sql("u_id","member",$row["mem_num"])?>(<?php echo tb_sql("u_name","member",$row["mem_num"])?>)</a>',
			<?php
				}
			?>	
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '輸贏'
            },
			labels: {
				formatter:function(){
					return Highcharts.numberFormat(this.value,0,null,',') + '元';	
				}
			}
			
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormatter:function(){return '<tr><td style="color:'+ this.series.color +';padding:0">'+ this.series.name +': </td>' +
            '<td style="padding:0"><b>'+ Highcharts.numberFormat(this.y,0,null,',') +'</b></td></tr>'},
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
            name: '輸贏',
            data: [<?php echo $super_wOrL; ?>],
			color:'#f7a35c'
        },{
            name: '勝率',
            data: [<?php echo $super_win_rate; ?>],
			color:'#8085e8'
        }]
    });
	
	
	$('#container_ssb_sport').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: ''
        },
       // subtitle: {
           // text: '本周營運'
       // },
        xAxis: {
            categories: [
			<?php
				foreach($rowOrders_6 as $row){
			?>
                '<a href="<?php echo site_url(SYSTEM_URL."/Member/index?find2=".tb_sql("u_id","member",$row["mem_num"]))?>"><?php echo tb_sql("u_id","member",$row["mem_num"])?>(<?php echo tb_sql("u_name","member",$row["mem_num"])?>)</a>',
			<?php
				}
			?>	
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '輸贏'
            },
			labels: {
				formatter:function(){
					return Highcharts.numberFormat(this.value,0,null,',') + '元';	
				}
			}
			
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormatter:function(){return '<tr><td style="color:'+ this.series.color +';padding:0">'+ this.series.name +': </td>' +
            '<td style="padding:0"><b>'+ Highcharts.numberFormat(this.y,0,null,',') +'</b></td></tr>'},
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
            name: '輸贏',
            data: [<?php echo $ssb_wOrL; ?>],
			color:'#f7a35c'
        },{
            name: '勝率',
            data: [<?php echo $ssb_win_rate; ?>],
			color:'#8085e8'
        }]
    });
	
	
	$('#container_sa').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: ''
        },
       // subtitle: {
           // text: '本周營運'
       // },
        xAxis: {
            categories: [
			<?php
				foreach($rowOrders_7 as $row){
			?>
                '<a href="<?php echo site_url(SYSTEM_URL."/Member/index?find2=".tb_sql("u_id","member",$row["mem_num"]))?>"><?php echo tb_sql("u_id","member",$row["mem_num"])?>(<?php echo tb_sql("u_name","member",$row["mem_num"])?>)</a>',
			<?php
				}
			?>	
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '輸贏'
            },
			labels: {
				formatter:function(){
					return Highcharts.numberFormat(this.value,0,null,',') + '元';	
				}
			}
			
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormatter:function(){return '<tr><td style="color:'+ this.series.color +';padding:0">'+ this.series.name +': </td>' +
            '<td style="padding:0"><b>'+ Highcharts.numberFormat(this.y,0,null,',') +'</b></td></tr>'},
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
            name: '輸贏',
            data: [<?php echo $sa_wOrL; ?>],
			color:'#f7a35c'
        },{
            name: '勝率',
            data: [<?php echo $sa_win_rate; ?>],
			color:'#8085e8'
        }]
    });
	
	
	$('#container_dg').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: ''
        },
       // subtitle: {
           // text: '本周營運'
       // },
        xAxis: {
            categories: [
			<?php
				foreach($rowOrders_8 as $row){
			?>
                '<a href="<?php echo site_url(SYSTEM_URL."/Member/index?find2=".tb_sql("u_id","member",$row["mem_num"]))?>"><?php echo tb_sql("u_id","member",$row["mem_num"])?>(<?php echo tb_sql("u_name","member",$row["mem_num"])?>)</a>',
			<?php
				}
			?>	
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '輸贏'
            },
			labels: {
				formatter:function(){
					return Highcharts.numberFormat(this.value,0,null,',') + '元';	
				}
			}
			
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormatter:function(){return '<tr><td style="color:'+ this.series.color +';padding:0">'+ this.series.name +': </td>' +
            '<td style="padding:0"><b>'+ Highcharts.numberFormat(this.y,0,null,',') +'</b></td></tr>'},
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
            name: '輸贏',
            data: [<?php echo $dg_wOrL; ?>],
			color:'#f7a35c'
        },{
            name: '勝率',
            data: [<?php echo $dg_win_rate; ?>],
			color:'#8085e8'
        }]
    });
	
	
	
	$('#container_ab').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: ''
        },
       // subtitle: {
           // text: '本周營運'
       // },
        xAxis: {
            categories: [
			<?php
				foreach($rowOrders_9 as $row){
			?>
                '<a href="<?php echo site_url(SYSTEM_URL."/Member/index?find2=".tb_sql("u_id","member",$row["mem_num"]))?>"><?php echo tb_sql("u_id","member",$row["mem_num"])?>(<?php echo tb_sql("u_name","member",$row["mem_num"])?>)</a>',
			<?php
				}
			?>	
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '輸贏'
            },
			labels: {
				formatter:function(){
					return Highcharts.numberFormat(this.value,0,null,',') + '元';	
				}
			}
			
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormatter:function(){return '<tr><td style="color:'+ this.series.color +';padding:0">'+ this.series.name +': </td>' +
            '<td style="padding:0"><b>'+ Highcharts.numberFormat(this.y,0,null,',') +'</b></td></tr>'},
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
            name: '輸贏',
            data: [<?php echo $ab_wOrL; ?>],
			color:'#f7a35c'
        },{
            name: '勝率',
            data: [<?php echo $ab_win_rate; ?>],
			color:'#8085e8'
        }]
    });
	
	
	$('#container_ameba').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: ''
        },
       // subtitle: {
           // text: '本周營運'
       // },
        xAxis: {
            categories: [
			<?php
				foreach($rowOrders_ameba as $row){
			?>
                '<a href="<?php echo site_url(SYSTEM_URL."/Member/index?find2=".tb_sql("u_id","member",$row["mem_num"]))?>"><?php echo tb_sql("u_id","member",$row["mem_num"])?>(<?php echo tb_sql("u_name","member",$row["mem_num"])?>)</a>',
			<?php
				}
			?>	
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '輸贏'
            },
			labels: {
				formatter:function(){
					return Highcharts.numberFormat(this.value,0,null,',') + '元';	
				}
			}
			
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormatter:function(){return '<tr><td style="color:'+ this.series.color +';padding:0">'+ this.series.name +': </td>' +
            '<td style="padding:0"><b>'+ Highcharts.numberFormat(this.y,0,null,',') +'</b></td></tr>'},
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
            name: '輸贏',
            data: [<?php echo $ameba_wOrL; ?>],
			color:'#f7a35c'
        },{
            name: '勝率',
            data: [<?php echo $ameba_win_rate; ?>],
			color:'#8085e8'
        }]
    });
	
	
	$('#container_7pk').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: ''
        },
       // subtitle: {
           // text: '本周營運'
       // },
        xAxis: {
            categories: [
			<?php
				foreach($rowOrders_7pk as $row){
			?>
                '<a href="<?php echo site_url(SYSTEM_URL."/Member/index?find2=".tb_sql("u_id","member",$row["mem_num"]))?>"><?php echo tb_sql("u_id","member",$row["mem_num"])?>(<?php echo tb_sql("u_name","member",$row["mem_num"])?>)</a>',
			<?php
				}
			?>	
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '輸贏'
            },
			labels: {
				formatter:function(){
					return Highcharts.numberFormat(this.value,0,null,',') + '元';	
				}
			}
			
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormatter:function(){return '<tr><td style="color:'+ this.series.color +';padding:0">'+ this.series.name +': </td>' +
            '<td style="padding:0"><b>'+ Highcharts.numberFormat(this.y,0,null,',') +'</b></td></tr>'},
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
            name: '輸贏',
            data: [<?php echo $pk_wOrL; ?>],
			color:'#f7a35c'
        },{
            name: '勝率',
            data: [<?php echo $pk_win_rate; ?>],
			color:'#8085e8'
        }]
		
    });
	
	
	$('#container_qt').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: ''
        },
       // subtitle: {
           // text: '本周營運'
       // },
        xAxis: {
            categories: [
			<?php
				foreach($rowOrders_qt as $row){
			?>
                '<a href="<?php echo site_url(SYSTEM_URL."/Member/index?find2=".tb_sql("u_id","member",$row["mem_num"]))?>"><?php echo tb_sql("u_id","member",$row["mem_num"])?>(<?php echo tb_sql("u_name","member",$row["mem_num"])?>)</a>',
			<?php
				}
			?>	
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '輸贏'
            },
			labels: {
				formatter:function(){
					return Highcharts.numberFormat(this.value,0,null,',') + '元';	
				}
			}
			
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormatter:function(){return '<tr><td style="color:'+ this.series.color +';padding:0">'+ this.series.name +': </td>' +
            '<td style="padding:0"><b>'+ Highcharts.numberFormat(this.y,0,null,',') +'</b></td></tr>'},
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
            name: '輸贏',
            data: [<?php echo $qt_wOrL; ?>],
			color:'#f7a35c'
        },{
            name: '勝率',
            data: [<?php echo $qt_win_rate; ?>],
			color:'#8085e8'
        }]
		
    });
	
	
	$('#container_slottery').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: ''
        },
       // subtitle: {
           // text: '本周營運'
       // },
        xAxis: {
            categories: [
			<?php
				foreach($rowOrders_slottery as $row){
			?>
                '<a href="<?php echo site_url(SYSTEM_URL."/Member/index?find2=".tb_sql("u_id","member",$row["mem_num"]))?>"><?php echo tb_sql("u_id","member",$row["mem_num"])?>(<?php echo tb_sql("u_name","member",$row["mem_num"])?>)</a>',
			<?php
				}
			?>	
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '輸贏'
            },
			labels: {
				formatter:function(){
					return Highcharts.numberFormat(this.value,0,null,',') + '元';	
				}
			}
			
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormatter:function(){return '<tr><td style="color:'+ this.series.color +';padding:0">'+ this.series.name +': </td>' +
            '<td style="padding:0"><b>'+ Highcharts.numberFormat(this.y,0,null,',') +'</b></td></tr>'},
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
            name: '輸贏',
            data: [<?php echo $slottery_wOrL; ?>],
			color:'#f7a35c'
        },{
            name: '勝率',
            data: [<?php echo $slottery_win_rate; ?>],
			color:'#8085e8'
        }]
		
    });
	
	
	$('#container_fish').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: ''
        },
       // subtitle: {
           // text: '本周營運'
       // },
        xAxis: {
            categories: [
			<?php
				foreach($rowOrders_fish as $row){
			?>
                '<a href="<?php echo site_url(SYSTEM_URL."/Member/index?find2=".tb_sql("u_id","member",$row["mem_num"]))?>"><?php echo tb_sql("u_id","member",$row["mem_num"])?>(<?php echo tb_sql("u_name","member",$row["mem_num"])?>)</a>',
			<?php
				}
			?>	
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '輸贏'
            },
			labels: {
				formatter:function(){
					return Highcharts.numberFormat(this.value,0,null,',') + '元';	
				}
			}
			
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormatter:function(){return '<tr><td style="color:'+ this.series.color +';padding:0">'+ this.series.name +': </td>' +
            '<td style="padding:0"><b>'+ Highcharts.numberFormat(this.y,0,null,',') +'</b></td></tr>'},
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
            name: '輸贏',
            data: [<?php echo $fish_wOrL; ?>],
			color:'#f7a35c'
        },{
            name: '勝率',
            data: [<?php echo $fish_win_rate; ?>],
			color:'#8085e8'
        }]
		
    });
	
	
	$('#container_bingo').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: ''
        },
       // subtitle: {
           // text: '本周營運'
       // },
        xAxis: {
            categories: [
			<?php
				foreach($rowOrders_bingo as $row){
			?>
                '<a href="<?php echo site_url(SYSTEM_URL."/Member/index?find2=".tb_sql("u_id","member",$row["mem_num"]))?>"><?php echo tb_sql("u_id","member",$row["mem_num"])?>(<?php echo tb_sql("u_name","member",$row["mem_num"])?>)</a>',
			<?php
				}
			?>	
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '輸贏'
            },
			labels: {
				formatter:function(){
					return Highcharts.numberFormat(this.value,0,null,',') + '元';	
				}
			}
			
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormatter:function(){return '<tr><td style="color:'+ this.series.color +';padding:0">'+ this.series.name +': </td>' +
            '<td style="padding:0"><b>'+ Highcharts.numberFormat(this.y,0,null,',') +'</b></td></tr>'},
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
            name: '輸贏',
            data: [<?php echo $bingo_wOrL; ?>],
			color:'#f7a35c'
        },{
            name: '勝率',
            data: [<?php echo $bingo_win_rate; ?>],
			color:'#8085e8'
        }]
		
    });
	
	
	
	
	
	
	
	
	
	
	
	
	
});

$('#container').load(function(){
	var chart = $('#container').highcharts();
	chart.reflow();
})

</script>

