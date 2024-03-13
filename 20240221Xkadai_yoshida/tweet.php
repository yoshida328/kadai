<?php
session_start();

// データベース接続設定
$host = "localhost"; // データベースのホスト名
$dbname = "TwitterDB"; // データベース名
$user = "root"; // データベースのユーザー名
$pass = ""; // データベースのパスワード

// ログイン済みの場合、ユーザー情報を取得
if (isset($_SESSION['user_id'])) {
    $userID = $_SESSION['user_id'];
} else {
    // ログインしていない場合はログインページにリダイレクト
    header("Location: login.php");
    exit();
}

try {
    // PDOインスタンスの作成
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);

    // エラーモードを例外モードに設定
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ユーザーデータを取得
    $stmt = $pdo->prepare("SELECT ID, Username, ProfileImage FROM UserData WHERE ID = ?");
    $stmt->execute([$userID]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    // 過去に送信されたツイートを新しいものが上に来るように15件取得
    $stmt = $pdo->prepare("SELECT TweetID, TweetText, TweetImage, Timestamp FROM TweetData WHERE UserID = ? ORDER BY Timestamp DESC LIMIT 15");
    $stmt->execute([$userID]);
    $tweets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 画像アップロード処理
    $tweetImage = "";
    if (isset($_FILES['tweet_image']) && $_FILES['tweet_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "tweet_images/";  // アップロード先のディレクトリ
        $uploadFile = $uploadDir . basename($_FILES['tweet_image']['name']);
        move_uploaded_file($_FILES['tweet_image']['tmp_name'], $uploadFile);
        $tweetImage = $uploadFile;
    }

    // プリペアドステートメントを使用してデータベースにツイートデータを挿入
    if (isset($_POST['tweet_text'])) {
        $stmt = $pdo->prepare("INSERT INTO TweetData (UserID, TweetText, TweetImage) VALUES (?, ?, ?)");
        $stmt->execute([$userID, $_POST['tweet_text'], $tweetImage]);

        echo "ツイートが投稿されました！";
    } else {
        // ツイートテキストが送信されなかった場合のエラーメッセージ
        echo "ツイートテキストが空です。";
    }

    // ツイートの削除処理
    if (isset($_POST['delete_tweet'])) {
        $deleteTweetID = $_POST['delete_tweet'];
        $stmt = $pdo->prepare("DELETE FROM TweetData WHERE TweetID = ? AND UserID = ?");
        $stmt->execute([$deleteTweetID, $userID]);
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
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
    <title>Tweet</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="script.js"></script>
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo $userData['Username']; ?></h2>

        <!-- プロフィール画像とユーザー名の表示 -->
        <div class="user-info">
            <?php if (!empty($userData['ProfileImage'])): ?>
                <img class="profile-image" src="<?php echo $userData['ProfileImage']; ?>" alt="Profile Image">
            <?php else: ?>
                <p class="profile-image">No profile image available</p>
            <?php endif; ?>
            <p class="username"><?php echo $userData['Username']; ?></p>
        </div>

        <!-- ツイートフォームの表示 -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <label for="tweet_text">Tweet:</label>
            <textarea name="tweet_text" rows="4" required></textarea><br>

            <label for="tweet_image">Image:</label>
            <input type="file" name="tweet_image"><br>

            <input type="submit" value="Tweet">
        </form>

        <!-- 過去のツイートの表示 -->
        <?php foreach ($tweets as $tweet): ?>
            <div class="tweet-container">
                <div class="user-info">
                    <img class="profile-image" src="<?php echo $userData['ProfileImage']; ?>" alt="Profile Image">
                    <p class="username"><?php echo $userData['Username']; ?></p>
                </div>
                <div class="tweet-content">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <input type="hidden" name="delete_tweet" value="<?php echo $tweet['TweetID']; ?>">
                        <button type="submit" class="delete-button">削除</button>
                    </form>
                    <p class="tweet-text"><?php echo $tweet['TweetText']; ?></p>
                    <?php if (!empty($tweet['TweetImage'])): ?>
                        <img class="tweet-image" src="<?php echo $tweet['TweetImage']; ?>" alt="Tweet Image">
                    <?php endif; ?>
                    <p class="timestamp">Time: <?php echo $tweet['Timestamp']; ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
