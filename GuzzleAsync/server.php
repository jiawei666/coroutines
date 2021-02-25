<?php
function GetHeaders($code,$status,$content="no content",$content_type="text/html;charset=utf-8"){
    $header = '';
    $header .= "HTTP/1.1 {$code} {$status}\r\n";
    $header .= "Date: ".gmdate('D, d M Y H:i:s T')."\r\n";
    $header .= "Content-Type: {$content_type}\r\n";
    $header .= "Content-Length: ".strlen($content)."\r\n\r\n";//必须2个\r\n表示头部信息结束
    $header .= $content;
    return $header;
}


$socket = stream_socket_server("tcp://0.0.0.0:8000");
stream_set_blocking($socket, 0);
while ( $clientSocket = stream_socket_accept($socket)) {
    $data = fread($clientSocket, 8192);
    $dataArr = explode(PHP_EOL, $data);
//    $msg = "Received following request:\n\n|| $data || hahsdhas";
//    $msgLength = strlen($msg);
    $requestHead = explode(' ', $dataArr[0]);
    $response = GetHeaders(200, 'OK', $requestHead[1]);

    $sec = rand(1, 2);
    sleep($sec);
    fwrite($clientSocket, $response);
    fclose($clientSocket);
}

fclose($socket);
?>