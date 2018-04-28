<?php
$package = $argv[1];
$unzipSave = 'unzip_'.$package;
try{
    $file = loadFile($package,$unzipSave);
    $headerInfo = unzipHeader($file);
    for ($i = 0; $i < $headerInfo['fileCount']; $i++) {
        $nameLength = unpackULong($file,$ptr);
        $fileData = array(  'nameLength' => $nameLength,
                            'name' => getName($nameLength),
                            'offset' => unpackULong($file,$ptr),
                            'size' =>unpackULong($file,$ptr)
                        );
        echo "Unpacking file {$fileData['name']} ({$fileData['size']}bytes)...\n";
        $f['content'] = substr($file, $fileData['offset'], $fileData['size']);
        $unpackedFiles[] = $fileData;
        $destFile = $targetDir . $fileData[ 'name'];
        $destDir = dirname($destFile);
        if (!is_dir($destDir)){
            mkdir($destDir, 0777, true);
        }
        file_put_contents($targetDir . $fileData['name'], $fileData['content']);
    }
} catch (Exception $e) {
    echo 'Error:'.$e->getMessage();
}

function getName($length,&$data,&$ptr)
{
    $ptr = $ptr + $length;
    return substr($data,$ptr-$length,$length);
}

function unpackUlong(&$data,&$ptr)
{
    $ptr = $ptr + 4;
    return unpack('N',substr($data, $ptr-4, 4))[1];
}

function unpackUshort(&$data,&$ptr)
{
    $ptr = $ptr + 2;
    return unpack('n', substr($file, $ptr-2, 2))[1];
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

function unzipHeader($file)
{
    $type = array(
        'firstMark' => 'n',  //ushort 大端字节序 2个字节
        'info1' => 'N',         //ulong 大端字节序 全为0
        'indexInfoLength' => 'N',   //索引长度16位
        'bodyInfoLength' => 'N',    //文件数据段长度16位储存
        'lastMark' => 'n',     //头部信息结束标志
        'fileCount' => 'N',     //文件数量统计
    );
    $format = '';
    foreach ($type as $key => $value) {
        $format .= ($value.$key.'/');   //拼装unpack函数所需的format
    }
    return unpack($format,$file);
}
