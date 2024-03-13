$(document).ready(function() {
    $(".delete-button").click(function() {
        var tweetID = $(this).closest(".tweet-container").find("input[name='delete_tweet']").val();

        // Ajaxリクエストを送信
        $.ajax({
            url: "delete_tweet.php",
            method: "POST",
            data: { tweetID: tweetID },
            success: function(response) {
                // ページの再読み込み
                location.reload();
            },
            error: function(xhr, status, error) {
                console.error("Ajax request failed:", error);
            }
        });
    });
});
