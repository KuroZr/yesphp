<?php
/* @RiyanCoday - 23/05/2024 */
/* auto complete all tasks */
error_reporting(0);
date_default_timezone_set('Asia/Jakarta');

function completeTask($id, $headers, $proxy) {
    $url = "https://api.yescoin.gold/task/finishTask";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $id);
    curl_setopt($ch, CURLOPT_PROXY, $proxy); // Set proxy
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5); // Set proxy type to SOCKS5
    return curl_exec($ch);
}

$tokens = file('token.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$proxies = file('prxy.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Pair each token with a proxy
$tokenProxyPairs = array_combine($tokens, $proxies);

foreach ($tokenProxyPairs as $token => $proxy) {
    $tokenId = array_search($token, $tokens) + 1;
    $headers = [
        "accept: application/json, text/plain, */*",
        "accept-language: en-US,en;q=0.9",
        "content-type: application/json",
        "origin: https://www.yescoin.gold",
        "priority: u=1, i",
        "referer: https://www.yescoin.gold/",
        "token: " . $token,
        "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 Edg/124.0.0.0"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.yescoin.gold/task/getCommonTaskList");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_PROXY, $proxy); // Set proxy
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5); // Set proxy type to SOCKS5
    $response = curl_exec($ch);
    curl_close($ch);
    $responseData = json_decode($response, true);

    if ($responseData && isset($responseData['data'])) {
        foreach ($responseData['data'] as $task) {
            $taskId = $task['taskId'];
            $complete = completeTask($taskId, $headers, $proxy);
            $jsT = json_decode($complete, true);

            if ($jsT['code'] == 0) {
                $bonusAmount = $jsT['data']['bonusAmount'];
                $coin = number_format($bonusAmount, 0, ',', '.');
                echo "\033[32mToken $tokenId: Success Bonus: {$coin} coin\033[0m\n";
            } else {
                echo "\033[31mToken $tokenId: Task ID {$taskId} - {$jsT['message']} \033[0m\n";
            }
        }
    } else {
        echo "\033[31mToken $tokenId: Invalid or no tasks available!\033[0m\n";
    }
}
?>
