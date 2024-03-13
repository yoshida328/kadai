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
} catch (PDOException $e) {
    die("データベース接続エラー: " . $e->getMessage());
}

// フォームが送信された場合の処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // フォームからのデータ取得
    $username = $_POST['username'];
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    try {
        // 既に同じユーザーネームが存在するか確認
        $stmt = $pdo->prepare("SELECT * FROM UserData WHERE Username = ?");
        $stmt->execute([$username]);

        if ($stmt->rowCount() > 0) {
            // 同じユーザーネームが存在する場合はエラーメッセージを表示
            echo "Error: ユーザーネームが既に存在します。";
        } else {
            // 画像アップロード処理
            $profile_image = "";
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = "profile_images/";  // アップロード先のディレクトリ
                $uploadFile = $uploadDir . basename($_FILES['profile_image']['name']);
                move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile);
                $profile_image = $uploadFile;
            }

            // パスワードのハッシュ化
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // プリペアドステートメントを使用してデータベースにデータ挿入
            $stmt = $pdo->prepare("INSERT INTO UserData (Username, Password, ProfileImage) VALUES (?, ?, ?)");
            $stmt->execute([$username, $passwordHash, $profile_image]);

            // 登録成功時にlogin.phpにリダイレクト
            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    } finally {
        // 接続を閉じる
        $pdo = null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
</head>
<body>
    <h2>User Registration</h2>

    <!-- フォームの表示 -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <label for="username">Username:</label>
        <input type="text" name="username" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br>

        <label for="profile_image">Profile Image:</label>
        <input type="file" name="profile_image"><br>

        <input type="submit" value="Register">
    </form>
</body>
</html>