<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/24 0024
 * Time: 下午 7:58
 */

define('__ROOT', dirname(dirname(dirname(__FILE__))));
include(__ROOT.'/inc/mysql.php');
include(__ROOT.'/vendor/autoload.php');

use \Yurun\Util\Chinese;
set_time_limit(0);
ini_set('memory_limit','1024M');
// 设为性能模式
Chinese::setMode('Memory');
$bids = isset($_POST['bids']) ? $_POST['bids'] : '';
$cli = false;
if(PHP_SAPI == 'cli'){
    $cli = true;
}
$bids = '11953,5411,11717,6612,11514,4398,5340,11394,4745,7083,2399,5940,4331,12323,8566,12644,3938,12047,12911,5422,66,3097,11421,5602,9124,11214,10795,6804';
if($bids){
    $flag = false;
    if($cli){
         echo "开始\n\r";
         echo "内存使用:".(memory_get_usage()/1024/1024)."M\r\n";
    }
    $list = $gDB->select('select id,title from book where id in ('.$bids.')');
    $book_arr = array();
    foreach ($list as $k_l=>$v_l) {
        if($cli) {
            echo "当前转换书:".$v_l['title']."\n\r";
        }
        $txt_name = Chinese::toTraditional($v_l['title'])[0];
        wirteFile($txt_name);
        $book_arr[$v_l['id']] = $txt_name;
        $arc_list = $gDB->select('select a.title,a.id,aa.txt from arc as a left join arcs as aa on a.id=aa.id where a.bid='.$v_l['id'].' order by a.seq asc');
        if(!empty($arc_list)){
            foreach ($arc_list as $k_a=>$v_a) {
                if($cli){
                    echo "当前转换章节:".$v_a['title']."\n\r";
                    echo "内存使用:".(memory_get_usage()/1024/1024)."M\r\n";
                }
                $title = Chinese::toTraditional($v_a['title'])[0]."\n\r";
                wirteFile($txt_name, $title);

                $temp = Chinese::toTraditional($v_a['txt'])[0];
                wirteFile($txt_name, $temp);
                $arc_str = "\n\r";
                wirteFile($txt_name, $arc_str);
                unset($temp);
            }
        }
        unset($arc_list);
        if($cli){
            echo "内存使用:".(memory_get_usage()/1024/1024)."M\r\n";
        }
    }
    exit (json_encode(array(
            'code' => 1,
            'msg' => 'success',
    )));
}

function wirteFile($filename, $data=''){
    $down = __ROOT.'/down/';
    if(!is_dir($down)){
        mkdir($down,0777,1);
    }
   file_put_contents($down.$filename.'.txt', $data, FILE_APPEND);
}

function downBook($book_arr){
    foreach ($book_arr as $k=>$v) {
        $filename = $v.'.txt';
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=".basename($filename));
        readfile($filename);
    }
}

?>

<!--<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
        <title>简体转繁体</title>
        <style>
            .contanter {padding: 15px 15px; margin: auto auto;}
            .hide { display: none;}
        </style>
    </head>
    <body>
        <div class="contanter">
            <form action="" method="post" onsubmit="return sub();">
                <textarea name="bids" id="bids" cols="60" rows="12" placeholder="填写书籍id: 例如1,2,3,4...."></textarea>
                <input type="submit" value="提交">
            </form>
        </div>
        <div class="cl hide">正在为您处理中......</div>
        <div>

        </div>
    </body>
    <script src="/s/wx/js/jquery.min.js"></script>
    <script type="application/javascript">
        function sub(){
            $('.contanter').hide();
            $('.cl').show();
            $.ajax({
                url : '/api/tool/simpleToFt.php',
                data: {
                    bids : $('#bids').val()
                },
                type : 'post',
                dataType : 'json',
                success:function(ret){
                    if(ret.code == 1) {
                        $('.contanter').show();
                        $('.cl').hide();
                    }
                }
            });
            return false;
        }
    </script>
</html>-->
