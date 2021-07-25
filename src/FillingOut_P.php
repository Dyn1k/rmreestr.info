<?php


namespace App;

use http\Exception;

class FillingOut_P
{
    private Database $database;
    private Session $session;

    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }

    public function passport(array $data): bool
    {
        $checkFIO = '/^[а-яё]+$/iu';
        $checkNum = '/^[0-9]+$/iu';
        $checkReg = '/^[а-яё.\- ]+$/iu';
        $checkIssCode = '/^[0-9\-]+$/iu';

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

        if (empty($data['middle_name_id']) && empty($data['check-mid-name'])) {
            throw new AuthorizationException('Введите отчество');
        } else {
            if(!empty($data['check-mid-name'])) {
                $data['middle_name_id'] = '-';
            }
        }
        if (!preg_match($checkFIO, $data['middle_name_id']) && empty($data['check-mid-name'])) {
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

        if (empty($data['passports_series'])) {
            throw new AuthorizationException('Введите серию');
        }
        if (!preg_match($checkNum, $data['passports_series'])) {
            throw new AuthorizationException('Проверьте правильность введенной серии');
        }

        if (empty($data['passports_number'])) {
            throw new AuthorizationException('Введите номер');
        }
        if (!preg_match($checkNum, $data['passports_number'])) {
            throw new AuthorizationException('Проверьте правильность введенного номера');
        }

        if (empty($data['passports_issue_date'])) {
            throw new AuthorizationException('Введите дату выдачи');
        }

        if (empty($data['passports_issuer_id'])) {
            throw new AuthorizationException('Введите орган выдачи');
        }
        if (!preg_match($checkReg, $data['passports_issuer_id'])) {
            throw new AuthorizationException('Проверьте правильность введенния органа выдачи');
        }

        if (empty($data['passports_issuer_code_id'])) {
            throw new AuthorizationException('Введите код подразделения');
        }
        if (!preg_match($checkIssCode, $data['passports_issuer_code_id'])) {
            throw new AuthorizationException('Проверьте правильность введенного кода подразделения');
        }

        $statement = $this->database->getConnection()->prepare(
            'INSERT INTO login_register.passport (people_id, surname_id, name_id, middle_name_id, birth_date, birth_place_country_id, birth_place_region_id, birth_place_city_id, birth_place_district_id, birth_place_settlement_id, sex, passports_series, passports_number, passports_issue_date, passports_issuer_id, passports_issuer_code_id, authenticity)
 VALUES (:people_id, :surname_id, :name_id, :middle_name_id, :birth_date, :birth_place_country_id, :birth_place_region_id, :birth_place_city_id, :birth_place_district_id, :birth_place_settlement_id, :sex, :passports_series, :passports_number, :passports_issue_date, :passports_issuer_id, :passports_issuer_code_id, :authenticity)'
        );

        $statement->execute([
            'people_id' => $getUserId["user_id"],
            'surname_id' => $data['surname_id'],
            'name_id' => $data['name_id'],
            'middle_name_id' => $data['middle_name_id'],
            'birth_date' => $data['birth_date'],
            'birth_place_country_id' => $data['birth_place_country_id'],
            'birth_place_region_id' => $data['birth_place_region_id'],
            'birth_place_city_id' => $data['birth_place_city_id'],
            'birth_place_district_id' => $data['birth_place_district_id'],
            'birth_place_settlement_id' => $data['birth_place_settlement_id'],
            'sex' => $data['sex'],
            'passports_series' => $data['passports_series'],
            'passports_number' => $data['passports_number'],
            'passports_issue_date' => $data['passports_issue_date'],
            'passports_issuer_id' => $data['passports_issuer_id'],
            'passports_issuer_code_id' => $data['passports_issuer_code_id'],
            'authenticity' => 0,
        ]);

        $statement = $this->database->getConnection()->prepare('select * from passport where people_id=:people_id');
        $statement->execute(['people_id' => $getUserId["user_id"]]);
        $setData_P = $statement->fetch();

        $this->session->setData('passport', [
            'surname_id' => $setData_P['surname_id'],
            'name_id' => $setData_P['name_id'],
            'middle_name_id' => $setData_P['middle_name_id'],
            'birth_date' => $setData_P['birth_date'],
            'birth_place_country_id' => $setData_P['birth_place_country_id'],
            'birth_place_region_id' => $setData_P['birth_place_region_id'],
            'birth_place_city_id' => $setData_P['birth_place_city_id'],
            'birth_place_district_id' => $setData_P['birth_place_district_id'],
            'birth_place_settlement_id' => $setData_P['birth_place_settlement_id'],
            'sex' => $setData_P['sex'],
            'passports_series' => $setData_P['passports_series'],
            'passports_number' => $setData_P['passports_number'],
            'passports_issue_date' => $setData_P['passports_issue_date'],
            'passports_issuer_id' => $setData_P['passports_issuer_id'],
            'passports_issuer_code_id' => $setData_P['passports_issuer_code_id'],
            'authenticity' => $setData_P['authenticity'],
        ]);
        return true;
    }

}