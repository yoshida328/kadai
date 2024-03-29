-- データベースの作成
CREATE DATABASE IF NOT EXISTS TwitterDB;
USE TwitterDB;

-- ユーザーデータのテーブルの作成
CREATE TABLE IF NOT EXISTS UserData (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(255) NOT NULL,
    ProfileImage VARCHAR(255),
    Password VARCHAR(255) NOT NULL, 
    UNIQUE(Username)
);

-- ツイートのテーブルの作成
CREATE TABLE IF NOT EXISTS TweetData (
    TweetID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT,
    FOREIGN KEY (UserID) REFERENCES UserData(ID),
    Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    TweetText TEXT NOT NULL,
    TweetImage VARCHAR(255),
    FOREIGN KEY (UserID) REFERENCES UserData(ID)
);