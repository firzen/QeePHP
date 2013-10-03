</div></div></div>
<div class="clear"></div>
<script>
var stayAlive=Date.parse(new Date())/1000;
$(document).ready(function(){
	//多国时钟
    $("#div_countrytime").load(countryTime());
    //自动保持在线
    $(document).scroll(function(){
        now =Date.parse(new Date())/1000;
        if (now - stayAlive > 600){
            stayAlive=now;
            $.get('<?php echo url("myibay/stayAlive")?>');
        }
    });   
})
</script>
<br/><br/>
<center>
<div id="div_countrytime"> </div><br/>
<div style="font-size: 12px;width:800px" >
<div style="float:left;margin-left:120px;">
	
</div>
<div style="float:left;padding-top:10px;margin-left:20px">
	Copyright &copy; 2007- <script type="text/javascript">  var d = new Date(); yr = d.getFullYear(); document.write(yr);</script>  iBay365 爱贝网 [<a href="http://www.ibay365.com">WWW.IBAY365.COM</a>]&nbsp;&nbsp;&nbsp;<?=Helper_Util::runtimelength()?>&nbsp;S<br>
        <a href="http://www.miibeian.gov.cn/" type="popwindow">沪ICP备08002380号</a>
</div>
</div>
 </center>

<?php @$eif=@new EbayInterface_base(); if ($eif->production==false):?>
<div style="width: 120px;position: absolute;top:0px;left:0px;" class="notice"></div>
<?php endif;?>

