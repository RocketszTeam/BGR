var pass_reg=/^(?!([^a-zA-Z]+|\D+)$)[a-zA-Z0-9]{6,25}$/,amount_reg=/^[0-9]*[1-9][0-9]*$/;function submitCheck(){$("#withdrawalForm").valid()&&($("#withdrawalForm").attr("action",CI_URL+"Manger/withdrawal_do.aspx"),$("#withdrawalForm").submit())}$(function(){$("#withdrawalForm").validate({onkeyup:!1,errorClass:"alert alert-danger",errorElement:"div",rules:{amount:{required:!0,digits:function(a){return amount_reg.test(a)},min:1e3,max:parseInt($("#tmpAmount").val())}},messages:{amount:{required:"請輸入轉出點數",digits:"轉出點數請輸入正整數",min:"最少轉出{0}點",max:"轉出點數不得超過{0}點"}}}),$("#withdrawalBTN").bind("click",submitCheck)});