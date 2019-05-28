<script src="<?php echo ASSETS_URL?>/admin/js/highcharts.js"></script>
<script type="text/JavaScript">
    $(function(){

		$('#toWeek').click(function(){	//本週
			$('#find7').val($(this).attr('data-d1'));
			$('#find8').val($(this).attr('data-d2'));
		});
	
		$('#yeWeek').click(function(){	//本週
			$('#find7').val($(this).attr('data-d1'));
			$('#find8').val($(this).attr('data-d2'));
		});
		$('#toMonth').click(function(){	//本月
			$('#find7').val($(this).attr('data-d1'));
			$('#find8').val($(this).attr('data-d2'));
		});
		$('#ymMonth').click(function(){	//上月
			$('#find7').val($(this).attr('data-d1'));
			$('#find8').val($(this).attr('data-d2'));
		});
    });
</script>
<div class="col-xs-12">
	<div class="row">
    	<div class="infobox-container">
    		<div class="infobox infobox-red">
            	<div class="infobox-icon"><i class="ace-icon fa fa-usd"></i></div>
                <div class="infobox-data">
                	<span class="infobox-data-number"><?php echo number_format(@$ATMTOTAL,0)?></span>
                    <div class="infobox-content">儲值</div>
                </div>
                
            </div>
            <div class="infobox infobox-green">
            	<div class="infobox-icon"><i class="ace-icon fa fa-gavel"></i></div>
                <div class="infobox-data">
                	<span class="infobox-data-number">-<?php echo number_format(@$SELLTOTAL,0)?></span>
                    <div class="infobox-content">拋售</div>
                </div>
                
            </div>
            <div class="infobox infobox-orange">
            	<div class="infobox-icon"><i class="ace-icon fa fa-random"></i></div>
                <div class="infobox-data">
                	<span class="infobox-data-number"><?php echo number_format((@$ATMTOTAL-@$SELLTOTAL),0)?></span>
                    <div class="infobox-content">小計</div>
                </div>
            </div>
            <div class="infobox infobox-pink">
            	<div class="infobox-icon"><i class="ace-icon fa fa-heart"></i></div>
                <div class="infobox-data">
                	<span class="infobox-data-number"><?php echo number_format(@$POINTTOTAL,0)?></span>
                    <div class="infobox-content">紅利</div>
                </div>
                
            </div>
    	</div>
    </div>
    
    <div class="hr hr32 hr-dotted"></div>
    
    <div class="row">
    	<div class="col-sm-9">
        	<div class="widget-box transparent">
            	<div class="widget-header">
                	<h4 class="widget-title lighter smaller">
                    	<i class="ace-icon fa fa-bar-chart orange2"></i>本周營運表
                    </h4>
                    <div class="widget-toolbar no-border">
                    <form name="charform" method="post">
                    <input type="text" class="input-sm jqdate" name="find7" id="find7" value="<?php echo  @$_REQUEST["find7"] ?>" placeholder="開始日期"  />
                    <span class="">
                        <i class="fa fa-exchange"></i>
                    </span>
                    <input type="text" class="input-sm jqdate" name="find8" id="find8" value="<?php echo @$_REQUEST["find8"]?>" placeholder="結束日期"   />                    
                    	<div class="btn-group">
							<button type="sbumit" class="btn btn-white btn-sm btn-primary">搜尋</button>
                        	<button type="submit" class="btn btn-white btn-sm btn-primary" id="toWeek" data-d1="<?php echo $toWeek['d1']?>" data-d2="<?php echo $toWeek['d2']?>">本週</button>
                            <button type="submit" class="btn btn-white btn-sm btn-primary" id="yeWeek" data-d1="<?php echo $yeWeek['d1']?>" data-d2="<?php echo $yeWeek['d2']?>">上週</button>
                            <button type="submit" class="btn btn-white btn-sm btn-primary" id="toMonth" data-d1="<?php echo $toMonth['d1']?>" data-d2="<?php echo $toMonth['d2']?>">本月</button>
                            <button type="submit" class="btn btn-white btn-sm btn-primary" id="ymMonth" data-d1="<?php echo $ymMonth['d1']?>" data-d2="<?php echo $ymMonth['d2']?>">上月</button>
                        </div>
                    </form>
                    </div>
                </div>
                <div class="widget-body">
                	<div class="widget-main padding-4">
                    	<h4 class="smaller lighter red"><?php echo $find7?> ~ <?php echo $find8?></h4>
						<div id="container"></div>
                     
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="widget-box transparent">
                <div class="widget-header">
                    <h4 class="widget-title lighter smaller">
                        <i class="ace-icon fa fa-signal red"></i>本週儲值排行
                    </h4>
                </div>
                <div class="widget-body">
                    <div class="widget-main padding-4">
                        <div class="comments">
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
                                        <?php echo number_format($row["order_total"],0)?>
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
        <div class="col-sm-9">
            <div class="widget-box transparent">
                <div class="widget-header">
                    <h4 class="widget-title lighter smaller">
                        <i class="ace-icon fa fa-bar-chart orange2"></i>本周登錄
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
                        <h4 class="smaller lighter red"><?php echo $find7?> ~ <?php echo $find8?></h4>
                        <div id="container2"></div>

                    </div>
                </div>
            </div>
        </div>



    </div>
    
</div>

<script type="text/javascript">
$(function () {


    $('#container2').highcharts({

        chart: {
			type: 'column'
        },
        xAxis: {
            categories: [
            <?php
                for($i=0;$i<$totalday;$i++){
            ?>
                '<?php echo date("Y-m-d",strtotime($find7."+ $i day"))?>',
            <?php
                }
            ?>  

            ],
			crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '人次'
            },
			labels: {
				formatter:function(){
					return Highcharts.numberFormat(this.value,0,null,',') + '人';	
				}
			}
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormatter:function(){return '<tr><td style="color:'+ this.series.color +';padding:0">'+ this.series.name +': </td>' +
            '<td style="padding:0"><b>'+ Highcharts.numberFormat(this.y,0,null,',') +' 人</b></td></tr>'},
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
        title: {
            text: '本週登錄/註冊/儲值/匯款'  
        },
        series: [{
            name: '登錄人數',
            data: [
            <?php
                for($i=0;$i<$totalday;$i++){
            ?>
                <?php echo $memberLogin[date("Y-m-d",strtotime($find7."+ $i day"))] ; ?>,
            <?php
                }
            ?>  
            ]        
        }, {
            name: '註冊人數',
            data: [
            <?php
                for($i=0;$i<$totalday;$i++){
            ?>
                <?php echo $memberreg[date("Y-m-d",strtotime($find7."+ $i day"))] ; ?>,
            <?php
                }
            ?> 
            ]
        }, {
            name: '儲值人數',
            data: [<?php echo implode(',',@$memberSave)?>]
        }, {
            name: '匯款人數',
            data: [<?php echo implode(',',@$memberBank)?>]
        }
        ]
    });






    $('#container').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: '營運報表'
        },
       // subtitle: {
           // text: '本周營運'
       // },
        xAxis: {
            categories: [
			<?php
				for($i=0;$i<$totalday;$i++){
			?>
                '<?php echo date("Y-m-d",strtotime($find7."+ $i day"))?>',
			<?php
				}
			?>	
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '金額(NTD)'
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
            '<td style="padding:0"><b>$'+ Highcharts.numberFormat(this.y,0,null,',') +' 元</b></td></tr>'},
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
            name: '儲值',
            data: [<?php echo implode(',',@$ATM)?>],
        }, {
            name: '匯款',
            data: [<?php echo implode(',',@$Bank)?>]
        }, {
            name: '拋售',
            data: [<?php echo implode(',',@$Sell)?>]
        }, {
            name: '紅利',
            data: [<?php echo implode(',',@$Point)?>]
        }]
    });
});
$('#container').load(function(){
	var chart = $('#container').highcharts();
	chart.reflow();
})
</script>