<?php


namespace App;

use http\Exception;

class FillingOut_M
{
    private Database $database;
    private Session $session;

    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }

    public function military(array $data): bool
    {
        $checkFIO = '/^[а-яё ]+$/iu';
        $checkNum = '/^[0-9]+$/iu';
        $checkReg = '/^[а-яё.\- ]+$/iu';
        $checkIssCode = '/^[0-9\-]+$/iu';
        $checkInternationalPassCode = '/^[а-яёa-z0-9\- ]+$/iu';

        $getUserId = $this->session->getData('user');

        if (empty($data['surname_id'])) {
            throw new AuthorizationException('Введите фамилию');
        }
        if (!preg_match($checkFIO, $data['surname_id'])) {
            throw new AuthorizationException('Проверьте правильность введенной фамилии');
        }

        if (empty($data['name_id'])) {
            throw new AuthorizationException('Введите имя');
        }
        if (!preg_match($checkFIO, $data['name_id'])) {
            throw new AuthorizationException('Проверьте правильность введенного имени');
        }

        if (empty($data['middle_name_id']) && empty($data['check_mid_name'])) {
            throw new AuthorizationException('Введите отчество');
        } else {
            if(!empty($data['check_mid_name'])) {
                $data['middle_name_id'] = '-';
            }
        }
        if (!preg_match($checkFIO, $data['middle_name_id']) && empty($data['check_mid_name'])) {
            throw new AuthorizationException('Проверьте правильность введенного отчества');
        }

        if (empty($data['birth_date'])) {
            throw new AuthorizationException('Введите дату рождения');
        }

        if (empty($data['etnicity_id'])) {
            throw new AuthorizationException('Введите национальность');
        }
        if (!preg_match($checkFIO, $data['etnicity_id'])) {
            throw new AuthorizationException('Проверьте правильность введенной национальности');
        }

        if (empty($data['military_series'])) {
            throw new AuthorizationException('Введите серию');
        }
        if (!preg_match($checkInternationalPassCode, $data['military_series'])) {
            throw new AuthorizationException('Проверьте правильность введенной серии');
        }

        if (empty($data['military_number'])) {
            throw new AuthorizationException('Введите номер');
        }
        if (!preg_match($checkNum, $data['military_number'])) {
            throw new AuthorizationException('Проверьте правильность введенного номера');
        }

        if (empty($data['military_issuer_id'])) {
            throw new AuthorizationException('Введите военный комиссариат');
        }
        if (!preg_match($checkReg, $data['military_issuer_id'])) {
            throw new AuthorizationException('Проверьте правильность введенного военного комиссариата');
        }

        if (empty($data['military_issue_date'])) {
            throw new AuthorizationException('Введите дату выдачи');
        }

        if (empty($data['military_commissar'])) {
            throw new AuthorizationException('Введите военного комиссара');
        }
        if (!preg_match($checkReg, $data['military_commissar'])) {
            throw new AuthorizationException('Проверьте правильность введенного военного комиссара');
        }

        $statement = $this->database->getConnection()->prepare(
            'INSERT INTO login_register.military (people_id, surname_id, name_id, middle_name_id, birth_date, etnicity_id, military_series, military_number, military_issuer_id, military_issue_date, military_commissar, authenticity)
 VALUES (:people_id, :surname_id, :name_id, :middle_name_id, :birth_date, :etnicity_id, :military_series, :military_number, :military_issuer_id, :military_issue_date, :military_commissar, :authenticity)'
        );

        $statement->execute([
            'people_id' => $getUserId["user_id"],
            'surname_id' => $data['surname_id'],
            'name_id' => $data['name_id'],
            'middle_name_id' => $data['middle_name_id'],
            'birth_date' => $data['birth_date'],
            'etnicity_id' => $data['etnicity_id'],
            'military_series' => $data['military_series'],
            'military_number' => $data['military_number'],
            'military_issuer_id' => $data['military_issuer_id'],
            'military_issue_date' => $data['military_issue_date'],
            'military_commissar' => $data['military_commissar'],
            'authenticity' => 0,
        ]);

        $statement = $this->database->getConnection()->prepare('select * from military where people_id=:people_id');
        $statement->execute(['people_id' => $getUserId["user_id"]]);
        $setData_M = $statement->fetch();

        $this->session->setData('military', [
            'surname_id' => $setData_M['surname_id'],
            'name_id' => $setData_M['name_id'],
            'middle_name_id' => $setData_M['middle_name_id'],
            'birth_date' => $setData_M['birth_date'],
            'etnicity_id' => $setData_M['etnicity_id'],
            'military_series' => $setData_M['military_series'],
            'military_number' => $setData_M['military_number'],
            'military_issuer_id' => $setData_M['military_issuer_id'],
            'military_issue_date' => $setData_M['military_issue_date'],
            'military_commissar' => $setData_M['military_commissar'],
            'authenticity' => $setData_M['authenticity'],
        ]);
        return true;
    }

}