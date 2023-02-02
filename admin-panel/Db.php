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

    function Auth($login, $password)
    {
        try {
            if (strlen($login) < 3 || strlen($password) < 1) {
                return false;
            }
            $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :login');
            $stmt->execute(['login' => $login]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!strcmp($password, $user['password'])) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function getSettings()
    {
        try {
            $stmt = $this->db->query('SELECT * FROM settings');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function getMembers()
    {
        try {
            $stmt = $this->db->query('SELECT * FROM members');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function updatePump($pump_for_chan, $pump_for_page)
    {
        $stmt = $this->db->prepare(
            'UPDATE settings SET value = :pump_for_chan WHERE name = :pump_chan'
        );
        $stmt->execute(['pump_for_chan' => $pump_for_chan, 'pump_chan' => 'pump_for_chan']);
        $stmt = $this->db->prepare(
            'UPDATE settings SET value = :pump_for_page WHERE name = :pump_page'
        );
        $stmt->execute(['pump_for_page' => $pump_for_page, 'pump_page' => 'pump_for_page']);
        return true;
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

    function getPageByID($id)
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM pages WHERE id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function delPageByID($id)
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM pages WHERE id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function updatePageByID($id, $url)
    {
        $stmt = $this->db->prepare(
            'UPDATE pages SET url = :url WHERE id = :id'
        );
        $stmt->execute(['id' => $id, 'url' => $url]);
        return true;
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

    function getChannelByID($id)
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM channels WHERE id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function updateChannelByID($id, $chan)
    {
        $stmt = $this->db->prepare(
            'UPDATE channels SET chan = :chan WHERE id = :id'
        );
        $stmt->execute(['id' => $id, 'chan' => $chan]);
        return true;
    }

    function delChannelByID($id)
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM channels WHERE id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
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

    function getTickerByID($id)
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM tickers WHERE id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function updateTickerByID($id, $ticker, $keywords, $pump = 0)
    {
        $stmt = $this->db->prepare(
            'UPDATE tickers SET ticker = :ticker, keywords = :keywords, pump = :pump WHERE id = :id'
        );
        $stmt->execute(['id' => $id, 'ticker' => $ticker, 'keywords' => $keywords, 'pump' => $pump]);
        return true;
    }

    function delTickerByID($id)
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM tickers WHERE id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
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

    function getStopWordByID($id)
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM stopwords WHERE id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function updateStopWordByID($id, $stopword)
    {
        $stmt = $this->db->prepare(
            'UPDATE stopwords SET stopword = :stopword WHERE id = :id'
        );
        $stmt->execute(['id' => $id, 'stopword' => $stopword]);
        return true;
    }

    function delStopWordByID($id)
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM stopwords WHERE id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
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

    function getWordUpByID($id)
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM wordsup WHERE id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function updateWordUpByID($id, $word_for_up)
    {
        $stmt = $this->db->prepare(
            'UPDATE wordsup SET word_for_up = :word_for_up WHERE id = :id'
        );
        $stmt->execute(['id' => $id, 'word_for_up' => $word_for_up]);
        return true;
    }

    function delWordUpByID($id)
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM wordsup WHERE id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
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

    function getWordDownByID($id)
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM wordsdown WHERE id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function updateWordDownByID($id, $word_for_down)
    {
        $stmt = $this->db->prepare(
            'UPDATE wordsdown SET word_for_down = :word_for_down WHERE id = :id'
        );
        $stmt->execute(['id' => $id, 'word_for_down' => $word_for_down]);
        return true;
    }

    function delWordDownByID($id)
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM wordsdown WHERE id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function getSandbox()
    {
        try {
            $stmt = $this->db->query('SELECT * FROM sandbox ORDER BY id DESC');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function getSandboxByID($id)
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM sandbox WHERE id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function delSandboxByID($id)
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM sandbox WHERE id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            // print $e for Log here!
            return false;
        }
    }

    function addUser($username, $token)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, password, token) VALUES (:username, :password, :token)'
        );
        $stmt->execute(['username' => $username, 'password' => 'pAssw0rd', 'token' => $token]);
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

    function addTicker($ticker, $keywords = '')
    {
        $stmt = $this->db->prepare(
            'INSERT INTO tickers (ticker, keywords) VALUES (:ticker, :keywords)'
        );
        $stmt->execute(['ticker' => $ticker, 'keywords' => $keywords]);
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