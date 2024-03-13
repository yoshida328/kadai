<?php
session_start();

// データベース接続設定
$host = "localhost";
$dbname = "TwitterDB";
$user = "root";
$pass = "";

try {
    // PDOインスタンスの作成
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);

    // エラーモードを例外モードに設定
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // フォームが送信された場合の処理
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // フォームからのデータ取得
        $username = $_POST['username'];
        $password = $_POST['password'];

        // プリペアドステートメントを使用してユーザーの情報を取得
        $stmt = $pdo->prepare("SELECT * FROM UserData WHERE Username = ?");
        $stmt->execute([$username]);
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

        // パスワードの検証
        if ($userRow && password_verify($password, $userRow['Password'])) {
            // ログイン成功
            $_SESSION['user_id'] = $userRow['ID'];
            $_SESSION['username'] = $userRow['Username'];
            header("Location: tweet.php"); // ログイン後のページにリダイレクト
            exit();
        } else {
            // ログイン失敗
            $loginError = "ユーザー名またはパスワードが正しくありません。";
        }
    }
} catch (PDOException $e) {
    die("データベース接続エラー: " . $e->getMessage());
} finally {
    // 接続を閉じる
    $pdo = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>

    <?php
    if (isset($loginError)) {
        echo "<p style='color: red;'>$loginError</p>";
    }
    ?>

    <!-- ログインフォームの表示 -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="username">Username:</label>
        <input type="text" name="username" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br>

        <input type="submit" value="Login">
    </form>
</body>
</html>