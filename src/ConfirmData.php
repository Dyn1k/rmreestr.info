<?php


namespace App;


class ConfirmData
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function confirmDataBC(array $data): bool
    {
        if ($_GET['user_id']) {
            $user_id = $_GET['user_id'];
            $statement = $this->database->getConnection()->prepare('SELECT user_id FROM user WHERE user_id=:user_id');
            $statement->execute(['user_id' => $user_id]);
            $result = $statement->fetch();
            if (!empty($result)) {
                $statement = $this->database->getConnection()->prepare('UPDATE birth_certificates SET authenticity=1 WHERE people_id=:people_id' );
                $statement->execute(['people_id' => $result['user_id']]);
                $data = $statement->fetch();
            }

        }
        return $data;
    }

    public function confirmDataP(array $data): bool
    {
        if ($_GET['user_id']) {
            $user_id = $_GET['user_id'];
            $statement = $this->database->getConnection()->prepare('SELECT user_id FROM user WHERE user_id=:user_id');
            $statement->execute(['user_id' => $user_id]);
            $result = $statement->fetch();
            if (!empty($result)) {
                $statement = $this->database->getConnection()->prepare('UPDATE passport SET authenticity=1 WHERE people_id=:people_id' );
                $statement->execute(['people_id' => $result['user_id']]);
                $data = $statement->fetch();
            }

        }
        return $data;
    }

    public function confirmDataIAN(array $data): bool
    {
        if ($_GET['user_id']) {
            $user_id = $_GET['user_id'];
            $statement = $this->database->getConnection()->prepare('SELECT user_id FROM user WHERE user_id=:user_id');
            $statement->execute(['user_id' => $user_id]);
            $result = $statement->fetch();
            if (!empty($result)) {
                $statement = $this->database->getConnection()->prepare('UPDATE individual_account_numbers SET authenticity=1 WHERE people_id=:people_id' );
                $statement->execute(['people_id' => $result['user_id']]);
                $data = $statement->fetch();
            }

        }
        return $data;
    }

    public function confirmDataIP(array $data): bool
    {
        if ($_GET['user_id']) {
            $user_id = $_GET['user_id'];
            $statement = $this->database->getConnection()->prepare('SELECT user_id FROM user WHERE user_id=:user_id');
            $statement->execute(['user_id' => $user_id]);
            $result = $statement->fetch();
            if (!empty($result)) {
                $statement = $this->database->getConnection()->prepare('UPDATE international_passport SET authenticity=1 WHERE people_id=:people_id' );
                $statement->execute(['people_id' => $result['user_id']]);
                $data = $statement->fetch();
            }

        }
        return $data;
    }

    public function confirmDataM(array $data): bool
    {
        if ($_GET['user_id']) {
            $user_id = $_GET['user_id'];
            $statement = $this->database->getConnection()->prepare('SELECT user_id FROM user WHERE user_id=:user_id');
            $statement->execute(['user_id' => $user_id]);
            $result = $statement->fetch();
            if (!empty($result)) {
                $statement = $this->database->getConnection()->prepare('UPDATE military SET authenticity=1 WHERE people_id=:people_id' );
                $statement->execute(['people_id' => $result['user_id']]);
                $data = $statement->fetch();
            }

        }
        return $data;
    }

    public function confirmDataOldOMS(array $data): bool
    {
        if ($_GET['user_id']) {
            $user_id = $_GET['user_id'];
            $statement = $this->database->getConnection()->prepare('SELECT user_id FROM user WHERE user_id=:user_id');
            $statement->execute(['user_id' => $user_id]);
            $result = $statement->fetch();
            if (!empty($result)) {
                $statement = $this->database->getConnection()->prepare('UPDATE policy_oldoms SET authenticity=1 WHERE people_id=:people_id' );
                $statement->execute(['people_id' => $result['user_id']]);
                $data = $statement->fetch();
            }

        }
        return $data;
    }

    public function confirmDataOMS(array $data): bool
    {
        if ($_GET['user_id']) {
            $user_id = $_GET['user_id'];
            $statement = $this->database->getConnection()->prepare('SELECT user_id FROM user WHERE user_id=:user_id');
            $statement->execute(['user_id' => $user_id]);
            $result = $statement->fetch();
            if (!empty($result)) {
                $statement = $this->database->getConnection()->prepare('UPDATE policy_oms SET authenticity=1 WHERE people_id=:people_id' );
                $statement->execute(['people_id' => $result['user_id']]);
                $data = $statement->fetch();
            }

        }
        return $data;
    }
}