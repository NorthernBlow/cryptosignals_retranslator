<?php


class Database
{

    protected   $server_db = 'localhost',
                $name_db = 'test',
                $user_db = 'forum_user_db',
                $password_db = 'qwe123';

    protected   $db;

    function __construct()
    {
        date_default_timezone_set('Europe/Moscow');

        try {
            $this->db = new PDO("mysql:host=$this->server_db;dbname=$this->name_db;charset=utf8mb4", $this->user_db, $this->password_db);
        } catch (PDOException $e) {
            echo "Ошибка!: ", $e->getMessage();
            die();
        }
    }


    function getUsers()
    {
        try {
            $stmt = $this->db->query('SELECT * FROM users ORDER BY id DESC');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function getPages()
    {
        try {
            $stmt = $this->db->query('SELECT * FROM pages ORDER BY id DESC');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function getChannels()
    {
        try {
            $stmt = $this->db->query('SELECT * FROM channels ORDER BY id DESC');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function getTickers()
    {
        try {
            $stmt = $this->db->query('SELECT * FROM tickers ORDER BY id DESC');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function getStopWords()
    {
        try {
            $stmt = $this->db->query('SELECT * FROM stopwords ORDER BY id DESC');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function getWordsUp()
    {
        try {
            $stmt = $this->db->query('SELECT * FROM wordsup ORDER BY id DESC');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function getWordsDown()
    {
        try {
            $stmt = $this->db->query('SELECT * FROM wordsdown ORDER BY id DESC');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function addUser($username, $token)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, token) VALUES (:username, :token)'
        );
        $stmt->execute(['username' => $username, 'token' => $token]);
        return true;
    }

    function addPage($url)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO pages (url) VALUES (:url)'
        );
        $stmt->execute(['url' => $url]);
        return true;
    }

    function addChannel($chan)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO channels (chan) VALUES (:chan)'
        );
        $stmt->execute(['chan' => $chan]);
        return true;
    }

    function addTicker($ticker)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO tickers (ticker) VALUES (:ticker)'
        );
        $stmt->execute(['ticker' => $ticker]);
        return true;
    }

    function addStopWord($stopword)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO stopwords (stopword) VALUES (:stopword)'
        );
        $stmt->execute(['stopword' => $stopword]);
        return true;
    }

    function addWordUp($word_for_up)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO wordsup (word_for_up) VALUES (:word_for_up)'
        );
        $stmt->execute(['word_for_up' => $word_for_up]);
        return true;
    }

    function addWordDown($word_for_down)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO wordsdown (word_for_down) VALUES (:word_for_down)'
        );
        $stmt->execute(['word_for_down' => $word_for_down]);
        return true;
    }
}