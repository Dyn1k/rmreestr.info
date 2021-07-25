<?php


namespace App;

use http\Exception;

class FillingOut_BC
{
    private Database $database;
    private Session $session;

    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }

    public function birthCertificates(array $data): bool
    {
        $checkFIO = '/^[а-яё]+$/iu';
        $checkNum = '/^[0-9]+$/iu';
        $checkReg = '/^[а-яё. ]+$/iu';

        $getUserId = $this->session->getData('user');

        if (empty($data['birth_surname'])) {
            throw new AuthorizationException('Введите фамилию');
        }
        if (!preg_match($checkFIO, $data['birth_surname'])) {
            throw new AuthorizationException('Проверьте правильность введенной фамилии');
        }

        if (empty($data['birth_name'])) {
            throw new AuthorizationException('Введите имя');
        }
        if (!preg_match($checkFIO, $data['birth_name'])) {
            throw new AuthorizationException('Проверьте правильность введенного имени');
        }

        if (empty($data['birth_middle_name']) && empty($data['check-mid-name'])) {
            throw new AuthorizationException('Введите отчество');
        } else {
            if(!empty($data['check-mid-name'])) {
                $data['birth_middle_name'] = '-';
            }
        }
        if (!preg_match($checkFIO, $data['birth_middle_name']) && empty($data['check-mid-name'])) {
            throw new AuthorizationException('Проверьте правильность введенного отчества');
        }

        if (empty($data['birth_date'])) {
            throw new AuthorizationException('Введите дату рождения');
        }

        if (empty($data['birth_place_country_id'])) {
            throw new AuthorizationException('Введите название страны');
        }
        if (!preg_match($checkReg, $data['birth_place_country_id'])) {
            throw new AuthorizationException('Проверьте правильность названия введенной страны');
        }

        if (empty($data['birth_place_region_id'])) {
            throw new AuthorizationException('Введите название субъекта');
        }
        if (!preg_match($checkReg, $data['birth_place_region_id'])) {
            throw new AuthorizationException('Проверьте правильность названия введенного субъекта');
        }

        if (empty($data['birth_place_city_id'])) {
            throw new AuthorizationException('Введите название города');
        }
        if (!preg_match($checkReg, $data['birth_place_city_id'])) {
            throw new AuthorizationException('Проверьте правильность названия введенного города');
        }

        if (empty($data['birth_place_district_id'])) {
            throw new AuthorizationException('Введите название района');
        }
        if (!preg_match($checkReg, $data['birth_place_district_id'])) {
            throw new AuthorizationException('Проверьте правильность названия введенного района');
        }

        if (empty($data['birth_place_settlement_id'])) {
            $data['birth_place_settlement_id'] = '-';
        }

        if (empty($data['sex'])) {
            throw new AuthorizationException('Выберите пол');
        }

        if (empty($data['birth_record_date'])) {
            throw new AuthorizationException('Введите дату записи акта о рождении');
        }

        if (empty($data['birth_record_number'])) {
            throw new AuthorizationException('Введите номер записи акта о рождении');
        }
        if (!preg_match($checkNum, $data['birth_record_number'])) {
            throw new AuthorizationException('Проверьте правильность введенного номера записи акта о рождении');
        }

        if (empty($data['place_of_state_registration_id'])) {
            throw new AuthorizationException('Введите место государственной регистрации');
        }
        if (!preg_match($checkReg, $data['place_of_state_registration_id'])) {
            throw new AuthorizationException('Проверьте правильность введенного места государственной регистрации');
        }

        if (empty($data['birth_certificates_series'])) {
            throw new AuthorizationException('Введите серию свидетельства о рождении');
        }

        if (empty($data['birth_certificates_number'])) {
            throw new AuthorizationException('Введите номер свидетельства о рождении');
        }
        if (!preg_match($checkNum, $data['birth_certificates_number'])) {
            throw new AuthorizationException('Проверьте правильность введенного номера свидетельства о рождении');
        }

        if (empty($data['birth_certificates_issue_date'])) {
            throw new AuthorizationException('Введите дату выдачи свидетельства о рождении');
        }

         $statement = $this->database->getConnection()->prepare(
             'INSERT INTO login_register.birth_certificates (people_id, birth_surname, birth_name, birth_middle_name, birth_date, birth_place_country_id, birth_place_region_id, birth_place_city_id, birth_place_district_id, birth_place_settlement_id, sex, birth_record_date, birth_record_number, place_of_state_registration_id, birth_certificates_series, birth_certificates_number, birth_certificates_issue_date, authenticity)
 VALUES (:people_id, :birth_surname, :birth_name, :birth_middle_name, :birth_date, :birth_place_country_id, :birth_place_region_id, :birth_place_city_id, :birth_place_district_id, :birth_place_settlement_id, :sex, :birth_record_date, :birth_record_number, :place_of_state_registration_id, :birth_certificates_series, :birth_certificates_number, :birth_certificates_issue_date, :authenticity)'
         );

        $statement->execute([
            'people_id' => $getUserId["user_id"],
            'birth_surname' => $data['birth_surname'],
            'birth_name' => $data['birth_name'],
            'birth_date' => $data['birth_date'],
            'birth_middle_name' => $data['birth_middle_name'],
            'birth_place_country_id' => $data['birth_place_country_id'],
            'birth_place_region_id' => $data['birth_place_region_id'],
            'birth_place_city_id' => $data['birth_place_city_id'],
            'birth_place_district_id' => $data['birth_place_district_id'],
            'birth_place_settlement_id' => $data['birth_place_settlement_id'],
            'sex' => $data['sex'],
            'birth_record_date' => $data['birth_record_date'],
            'birth_record_number' => $data['birth_record_number'],
            'place_of_state_registration_id' => $data['place_of_state_registration_id'],
            'birth_certificates_series' => $data['birth_certificates_series'],
            'birth_certificates_number' => $data['birth_certificates_number'],
            'birth_certificates_issue_date' => $data['birth_certificates_issue_date'],
            'authenticity' => 0,
        ]);

        $statement = $this->database->getConnection()->prepare('select * from birth_certificates where people_id=:people_id');
        $statement->execute(['people_id' => $getUserId["user_id"]]);
        $setData = $statement->fetch();

       $this->session->setData('birth_certificates', [
           'birth_surname' => $setData['birth_surname'],
           'birth_name' => $setData['birth_name'],
           'birth_middle_name' => $setData['birth_middle_name'],
           'birth_date' => $setData['birth_date'],
           'birth_place_country_id' => $setData['birth_place_country_id'],
           'birth_place_region_id' => $setData['birth_place_region_id'],
           'birth_place_city_id' => $setData['birth_place_city_id'],
           'birth_place_district_id' => $setData['birth_place_district_id'],
           'birth_place_settlement_id' => $setData['birth_place_settlement_id'],
           'sex' => $setData['sex'],
           'birth_record_date' => $setData['birth_record_date'],
           'birth_record_number' => $setData['birth_record_number'],
           'place_of_state_registration_id' => $setData['place_of_state_registration_id'],
           'birth_certificates_series' => $setData['birth_certificates_series'],
           'birth_certificates_number' => $setData['birth_certificates_number'],
           'birth_certificates_issue_date' => $setData['birth_certificates_issue_date'],
           'authenticity' => $setData['authenticity'],
           ]);
        return true;
    }
}