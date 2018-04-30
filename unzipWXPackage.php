<?php
$package = $argv[1];
$unzipSave = $package.'_unzip';
$subSize = 18;
try{
    $file = loadFile($package,$unzipSave);
    $headerInfo = unzipHeader($file);
    for ($i = 0; $i < $headerInfo['fileCount']; $i++) {
        $nameLength = unpackULong($file,$subSize);
        $fileData = array(  'nameLength' => $nameLength,
                            'name' => getName($nameLength,$file,$subSize),
                            'offset' => unpackULong($file,$subSize),
                            'size' =>unpackULong($file,$subSize)
                        );
        echo "正在解压文件".$fileData['name']."共计".((int)$fileData['size']/1024)."KB...\n";
        $fileData['content'] = substr($file, $fileData['offset'], $fileData['size']);
        $unpackedFiles[] = $fileData;
        $destFile = $unzipSave . $fileData[ 'name'];
        $destDir = dirname($destFile);
        if (!is_dir($destDir)){
            mkdir($destDir, 0777, true);
        }
        file_put_contents($unzipSave . $fileData['name'], $fileData['content']);
    }
    echo '解压完成共计'.$headerInfo['fileCount'].'个文件';
} catch (Exception $e) {
    echo 'Error:'.$e->getMessage();
}

function getName($length,&$data,&$subSize)
{
    $subSize = $subSize + $length;
    return substr($data,$subSize-$length,$length);
}

function unpackUlong(&$data,&$subSize)
{
    $subSize = $subSize + 4;
    return unpack('N',substr($data, $subSize-4, 4))[1];
}

function unpackUshort(&$data,&$subSize)
{
    $subSize = $subSize + 2;
    return unpack('n', substr($file, $subSize-2, 2))[1];
}
function loadFile($package,$unzipSave)
{
    if (!is_file($package))
        throw new Exception("请输入正确的微信小程序地址！", 1);     
    if (!is_dir($unzipSave))
        mkdir($unzipSave);
    $file = file_get_contents($package);
    if ($file == false) {
        throw new Exception("请输入正确的微信小程序地址！", 1);
    }else {
        echo '正在解压....'.PHP_EOL;
    }  
    return $file;  
}

function unzipHeader($file)
{
    $type = array(
        'firstMark' => 'n',  //ushort 大端字节序 2个字节
        'info1' => 'N',         //ulong 大端字节序 全为0
        'indexInfoLength' => 'N',   //索引长度16位
        'bodyInfoLength' => 'n',    //文件数据段长度16位储存
        'lastMark' => 'n',     //头部信息结束标志
        'fileCount' => 'N',     //文件数量统计
    );
    $format = '';
    foreach ($type as $key => $value) {
        $format .= ($value.$key.'/');   //拼装unpack函数所需的format
    }
    return unpack($format,$file);
}
