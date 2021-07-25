<?php

namespace App;

class acceptEmail
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function accept($params): bool
    {
        if ($_GET['hash']) {
            $hash = $_GET['hash'];
            $statement = $this->database->getConnection()->prepare('SELECT user_id, email_confirmed FROM user WHERE hash=:hash');
            $statement->execute(['hash' => $hash]);
            $result = $statement->fetch();
            if (!empty($result)) {
                $statement = $this->database->getConnection()->prepare('UPDATE user SET email_confirmed=1 WHERE user_id=:user_id' );
                $statement->execute(['user_id' => $result['user_id']]);
            }

        }
        return true;
    }
}
