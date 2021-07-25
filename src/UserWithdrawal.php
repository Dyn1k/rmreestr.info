<?php


namespace App;


class UserWithdrawal
{
    private Database $database;


    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function conclusion()
    {
        $statement = $this->database->getConnection()->prepare(
            'select user_id, username, surname, name, middle_name, email from user'
        );
        $statement->execute();
        $data = $statement->fetchAll();
        return $data;
    }

    public function users()
    {
        if ($_GET['user_id']) {
            $user_id = $_GET['user_id'];
            $statement = $this->database->getConnection()->prepare('SELECT user_id FROM user WHERE user_id=:user_id');
            $statement->execute(['user_id' => $user_id]);
            $result = $statement->fetch();
            if (!empty($result)) {
                $statement = $this->database->getConnection()->prepare('select * from user WHERE user_id=:user_id' );
                $statement->execute(['user_id' => $result['user_id']]);
                $data = $statement->fetch();
            }

        }
        return $data;
    }

    public function birth_certificates_user()
    {
        if ($_GET['user_id']) {
            $user_id = $_GET['user_id'];
            $statement = $this->database->getConnection()->prepare('SELECT user_id FROM user WHERE user_id=:user_id');
            $statement->execute(['user_id' => $user_id]);
            $result = $statement->fetch();
            if (!empty($result)) {
                $statement = $this->database->getConnection()->prepare('select * from birth_certificates WHERE people_id=:people_id' );
                $statement->execute(['people_id' => $result['user_id']]);
                $data = $statement->fetch();
            }

        }
        return $data;
    }

    public function passport_user()
    {
        if ($_GET['user_id']) {
            $user_id = $_GET['user_id'];
            $statement = $this->database->getConnection()->prepare('SELECT user_id FROM user WHERE user_id=:user_id');
            $statement->execute(['user_id' => $user_id]);
            $result = $statement->fetch();
            if (!empty($result)) {
                $statement = $this->database->getConnection()->prepare('select * from passport WHERE people_id=:people_id' );
                $statement->execute(['people_id' => $result['user_id']]);
                $data = $statement->fetch();
            }

        }
        return $data;
    }

    public function international_passport_user()
    {
        if ($_GET['user_id']) {
            $user_id = $_GET['user_id'];
            $statement = $this->database->getConnection()->prepare('SELECT user_id FROM user WHERE user_id=:user_id');
            $statement->execute(['user_id' => $user_id]);
            $result = $statement->fetch();
            if (!empty($result)) {
                $statement = $this->database->getConnection()->prepare('select * from international_passport WHERE people_id=:people_id' );
                $statement->execute(['people_id' => $result['user_id']]);
                $data = $statement->fetch();
            }

        }
        return $data;
    }

    public function military_user()
    {
        if ($_GET['user_id']) {
            $user_id = $_GET['user_id'];
            $statement = $this->database->getConnection()->prepare('SELECT user_id FROM user WHERE user_id=:user_id');
            $statement->execute(['user_id' => $user_id]);
            $result = $statement->fetch();
            if (!empty($result)) {
                $statement = $this->database->getConnection()->prepare('select * from military WHERE people_id=:people_id' );
                $statement->execute(['people_id' => $result['user_id']]);
                $data = $statement->fetch();
            }

        }
        return $data;
    }

    public function individual_account_numbers_user()
    {
        if ($_GET['user_id']) {
            $user_id = $_GET['user_id'];
            $statement = $this->database->getConnection()->prepare('SELECT user_id FROM user WHERE user_id=:user_id');
            $statement->execute(['user_id' => $user_id]);
            $result = $statement->fetch();
            if (!empty($result)) {
                $statement = $this->database->getConnection()->prepare('select * from individual_account_numbers WHERE people_id=:people_id' );
                $statement->execute(['people_id' => $result['user_id']]);
                $data = $statement->fetch();
            }

        }
        return $data;
    }

    public function policy_oms_user()
    {
        if ($_GET['user_id']) {
            $user_id = $_GET['user_id'];
            $statement = $this->database->getConnection()->prepare('SELECT user_id FROM user WHERE user_id=:user_id');
            $statement->execute(['user_id' => $user_id]);
            $result = $statement->fetch();
            if (!empty($result)) {
                $statement = $this->database->getConnection()->prepare('select * from policy_oms WHERE people_id=:people_id' );
                $statement->execute(['people_id' => $result['user_id']]);
                $data = $statement->fetch();
            }

        }
        return $data;
    }

    public function policy_oldoms_user()
    {
        if ($_GET['user_id']) {
            $user_id = $_GET['user_id'];
            $statement = $this->database->getConnection()->prepare('SELECT user_id FROM user WHERE user_id=:user_id');
            $statement->execute(['user_id' => $user_id]);
            $result = $statement->fetch();
            if (!empty($result)) {
                $statement = $this->database->getConnection()->prepare('select * from policy_oldoms WHERE people_id=:people_id' );
                $statement->execute(['people_id' => $result['user_id']]);
                $data = $statement->fetch();
            }

        }
        return $data;
    }
}