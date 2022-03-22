<?php
//------------------
// 监测有几个人脸
//------------------
//检查有多少个脸型
var_dump(face_count('party.jpeg', 'haarcascade_frontalface_alt . xml'));
//返回脸型在图片中的位置参数，多个则返回数组

$arr = face_detect('party . jpeg', 'haarcascade_frontalface_alt2.xml');
print_r($arr);
?>
<?php
//------------------
// 人脸扭曲
//------------------
if ($_FILES) {
    $img = $_FILES['pic']['tmp_name'];
    $arr = face_detect($img, 'haarcascade_frontalface_alt2.xml');
    $arr1 = face_detect($img, 'haarcascade_frontalface_alt_tree.xml');
    if (is_array($arr1)) $all = array_merge($arr, $arr1);
    else $all = $arr;
    $im = new Imagick($img);
    $draw =new ImagickDraw();
    $borderColor = new ImagickPixel('red');
    $draw->setFillAlpha(0.0);
    $draw->setStrokeColor ($borderColor);
    $draw->setStrokeWidth (1);
    if (is_array($all)) {
        foreach ($all as $v) {
            $im_cl = $im->clone();
            $im_cl->cropImage($v['w'], $v['h'], $v['x'], $v['y']);
            $im_cl->swirlImage(60);
            $im->compositeImage($im_cl, Imagick::COMPOSITE_OVER, $v['x'], $v['y']);
            //$draw->rectangle($v['x'],$v['y'],$v['x']+$v['w'],$v['y']+$v['h']);
            //$im->drawimage($draw);
        }
    }
    header("Content-Type: image/png");
    echo $im;
} else {
    ?>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>


    <form method="POST" enctype="multipart/form-data">
        人脸识别试验：只支持jpg,png<br>
        上传一张图片 <input type="file" name="pic">
        <input type="submit" value="upload">
    </form>F
    <?php
}
?>
<?php
    //------------------
    // 人脸识别
    //------------------
header("Content-Type:text/html; charset:utf-8");
    $img = $_FILES['pic']['tmp_name'];
    $arr = face_detect($img, 'haarcascade_frontalface_alt2.xml');
    if (is_array($arr1)) {
        $all = array_merge($arr, $arr1);
    } else {
        $all = $arr;
    }
    $allowtype = 1;
    $fix_pic='';
    switch ($_FILES['pic']['type']) {
        case 'image/pjpeg':
            $fix_pic .= ".jpg";
            break;
        case 'image/jpeg':
            $fix_pic .= ".jpg";
            break;
        case 'image/x-png':
            $fix_pic .= ".png";
            break;
        case 'image/png':
            $fix_pic .= ".png";
            break;
        default:
            $allowtype = 0;
            break;
    }
    if ($allowtype == 0) {
        echo "文件格式错误：只运行jpg或png图片";
        exit;
    }
    $tmp_name = time();
    $src_pic = "./" . $tmp_name . $fix_pic;
    move_uploaded_file($_FILES['pic']['tmp_name'], $src_pic);
    $pic_src = $pic_dst = array();
    if (is_array($all)) {
        foreach ($all as $k => $v) {
            $tmp_name_new = $tmp_name . "_" . $k;
            $x = $v['x'];
            $y = $v['y'];
            $w = $v['w'];
            $h = $v['h'];
            $dst_pic = "./" . $tmp_name_new . $fix_pic;
            // echo $src_pic."<br>";
            // echo $dst_pic."<br>";
            $cmd = "/usr/local/bin/convert -crop " . $w . "x" . $h . "+" . $x . "+" . $y . " " . $src_pic . " " . $dst_pic;
            // echo $cmd."<br>";
            echo `$cmd`;
            $pic_src[] = "./" . $tmp_name . $fix_pic;
            $pic_dst[] = "./" . $tmp_name_new . $fix_pic;
        }
    }

    foreach ($pic_src as $key => $value) {
        echo "<img src='" . $value . "'> => <img src='" . $pic_dst[$key] . "'><br>";
    }
