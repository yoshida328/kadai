<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tweetID'])) {
    $host = "localhost";
    $dbname = "TwitterDB";
    $user = "root";
    $pass = "";

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $userID = $_SESSION['user_id'];
        $tweetID = $_POST['tweetID'];

        // ツイートの削除処理
        $stmt = $pdo->prepare("DELETE FROM TweetData WHERE TweetID = ? AND UserID = ?");
        $stmt->execute([$tweetID, $userID]);

        // 成功を通知
        echo "success";
    } catch (PDOException $e) {
        // エラーを通知
        echo "Error: " . $e->getMessage();
    } finally {
        $pdo = null;
    }
} else {
    // 不正なリクエスト
    echo "Invalid request";
}
?>