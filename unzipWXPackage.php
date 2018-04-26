<?php
$package = $argv[1];
$unzipSave = 'unzip_'.$package;
try{
    $file = loadFile($package,$unzipSave);
    $headerInfo = unzipHeader($file);
    for ($i = 0; $i < $header['fileCount']; $i++) {
        $nameLength = $unpackULong();
        $f = [
            'nameLength' => $nameLength,
            'name' => $unpackStr($nameLength),
            'offset' => $unpackULong(),
            'size' => $unpackULong(),
        ];
        echo "Unpacking file {$f['name']} ({$f['size']}bytes)...\n";
        $f['content'] = substr($file, $f['offset'], $f['size']);
        $unpackedFiles[] = $f;
        $destFile = $targetDir . $f[ 'name'];
        $destDir = dirname($destFile);
        if (!is_dir($destDir)){
            mkdir($destDir, 0777, true);
        }
        file_put_contents($targetDir . $f['name'], $f['content']);
    }
} catch (Exception $e) {
    echo 'Error:'.$e->getMessage();
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
        echo '正在解压....';
    }  
    return $file;  
}

function unzipHeader()
{
    $type = array(
        'firstMark' => 'n',  //ushort 大端字节序 2个字节
        'info1' => 'N',         //ulong 大端字节序 全为0
        'indexInfoLength' => 'N',   //索引长度16位
        'bodyInfoLength' => 'N',    //文件数据段长度16位储存
        'lastMark' => 'n',     //头部信息结束标志
        'fileCount' => 'N',     //文件数量统计
    );
    foreach ($type as $key => $value) {
        $format .= ($vale.$key.'/');   //拼装unpack函数所需的format
    }
    return unpack($format,$file);
}