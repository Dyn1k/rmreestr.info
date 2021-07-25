<?php


namespace App;

use http\Exception;

class FillingOut_IP
{
    private Database $database;
    private Session $session;

    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }

    public function international_passport(array $data): bool
    {
        $checkFIO = '/^[а-яё]+$/iu';
        $checkNum = '/^[0-9]+$/iu';
        $checkReg = '/^[а-яё.\- ]+$/iu';
        $checkIssCode = '/^[0-9\-]+$/iu';
        $checkInternationalPass = '/^[а-яёa-z\- ]+$/iu';
        $checkInternationalPassCode = '/^[а-яёa-z0-9\- ]+$/iu';

        $getUserId = $this->session->getData('user');

        if (empty($data['issuing_state_id'])) {
            throw new AuthorizationException('Введите государство выдачи');
        }
        if (!preg_match($checkInternationalPass, $data['issuing_state_id'])) {
            throw new AuthorizationException('Проверьте правильность введенного государства выдачи');
        }

        if (empty($data['passport_type_id'])) {
            throw new AuthorizationException('Введите тип');
        }
        if (!preg_match($checkInternationalPass, $data['passport_type_id'])) {
            throw new AuthorizationException('Проверьте правильность введенного типа');
        }

        if (empty($data['country_code_id'])) {
            throw new AuthorizationException('Введите код государства');
        }
        if (!preg_match($checkInternationalPassCode, $data['country_code_id'])) {
            throw new AuthorizationException('Проверьте правильность введенного кода государства');
        }

        if (empty($data['international_passports_series'])) {
            throw new AuthorizationException('Введите серию');
        }
        if (!preg_match($checkNum, $data['international_passports_series'])) {
            throw new AuthorizationException('Проверьте правильность введенной серии');
        }

        if (empty($data['international_passports_number'])) {
            throw new AuthorizationException('Введите номер');
        }
        if (!preg_match($checkNum, $data['international_passports_number'])) {
            throw new AuthorizationException('Проверьте правильность введенного номера');
        }

        if (empty($data['surname_id'])) {
            throw new AuthorizationException('Введите фамилию');
        }
        if (!preg_match($checkInternationalPass, $data['surname_id'])) {
            throw new AuthorizationException('Проверьте правильность введенной фамилии');
        }

        if (empty($data['name_id'])) {
            throw new AuthorizationException('Введите имя');
        }
        if (!preg_match($checkInternationalPass, $data['name_id'])) {
            throw new AuthorizationException('Проверьте правильность введенного имени');
        }

        if (empty($data['middle_name_id']) && empty($data['check_mid_name'])) {
            throw new AuthorizationException('Введите отчество');
        } else {
            if(!empty($data['check_mid_name'])) {
                $data['middle_name_id'] = '-';
            }
        }
        if (!preg_match($checkInternationalPass, $data['middle_name_id']) && empty($data['check_mid_name'])) {
            throw new AuthorizationException('Проверьте правильность введенного отчества');
        }

        if (empty($data['birth_date'])) {
            throw new AuthorizationException('Введите дату рождения');
        }

        if (empty($data['birth_place_country_id'])) {
            throw new AuthorizationException('Введите название страны');
        }
        if (!preg_match($checkInternationalPass, $data['birth_place_country_id'])) {
            throw new AuthorizationException('Проверьте правильность названия введенной страны');
        }

        if (empty($data['birth_place_region_id'])) {
            throw new AuthorizationException('Введите название субъекта');
        }
        if (!preg_match($checkInternationalPass, $data['birth_place_region_id'])) {
            throw new AuthorizationException('Проверьте правильность названия введенного субъекта');
        }

        if (empty($data['birth_place_city_id'])) {
            throw new AuthorizationException('Введите название города');
        }
        if (!preg_match($checkInternationalPass, $data['birth_place_city_id'])) {
            throw new AuthorizationException('Проверьте правильность названия введенного города');
        }

        if (empty($data['birth_place_district_id'])) {
            throw new AuthorizationException('Введите название района');
        }
        if (!preg_match($checkInternationalPass, $data['birth_place_district_id'])) {
            throw new AuthorizationException('Проверьте правильность названия введенного района');
        }

        if (empty($data['birth_place_settlement_id'])) {
            $data['birth_place_settlement_id'] = '-';
        }

        if (empty($data['sex_id'])) {
            throw new AuthorizationException('Выберите пол');
        }

        if (empty($data['citizenship_id'])) {
            throw new AuthorizationException('Введите гражданство');
        }
        if (!preg_match($checkInternationalPass, $data['citizenship_id'])) {
            throw new AuthorizationException('Проверьте правильность введенного гражданства');
        }

        if (empty($data['international_passports_issuer'])) {
            throw new AuthorizationException('Введите орган, выдавший документ');
        }
        if (!preg_match($checkInternationalPassCode, $data['international_passports_issuer'])) {
            throw new AuthorizationException('Проверьте правильность введенного органа, выдавшего документ');
        }

        if (empty($data['international_passports_issue_date'])) {
            throw new AuthorizationException('Введите дату выдачи');
        }

        if (empty($data['international_passports_expiry_date'])) {
            throw new AuthorizationException('Введите дату окончания срока');
        }

        $statement = $this->database->getConnection()->prepare(
            'INSERT INTO login_register.international_passport (people_id, issuing_state_id, passport_type_id, country_code_id, international_passports_series, international_passports_number,  surname_id, name_id, middle_name_id, birth_date, birth_place_country_id, birth_place_region_id, birth_place_city_id, birth_place_district_id, birth_place_settlement_id, sex_id, citizenship_id, international_passports_issuer, international_passports_issue_date, international_passports_expiry_date, authenticity)
 VALUES (:people_id, :issuing_state_id, :passport_type_id, :country_code_id, :international_passports_series, :international_passports_number,  :surname_id, :name_id, :middle_name_id, :birth_date, :birth_place_country_id, :birth_place_region_id, :birth_place_city_id, :birth_place_district_id, :birth_place_settlement_id, :sex_id, :citizenship_id, :international_passports_issuer, :international_passports_issue_date, :international_passports_expiry_date, :authenticity)'
        );

        $statement->execute([
            'people_id' => $getUserId["user_id"],
            'issuing_state_id' => $data['issuing_state_id'],
            'passport_type_id' => $data['passport_type_id'],
            'country_code_id' => $data['country_code_id'],
            'international_passports_series' => $data['international_passports_series'],
            'international_passports_number' => $data['international_passports_number'],
            'surname_id' => $data['surname_id'],
            'name_id' => $data['name_id'],
            'middle_name_id' => $data['middle_name_id'],
            'birth_date' => $data['birth_date'],
            'birth_place_country_id' => $data['birth_place_country_id'],
            'birth_place_region_id' => $data['birth_place_region_id'],
            'birth_place_city_id' => $data['birth_place_city_id'],
            'birth_place_district_id' => $data['birth_place_district_id'],
            'birth_place_settlement_id' => $data['birth_place_settlement_id'],
            'sex_id' => $data['sex_id'],
            'citizenship_id' => $data['citizenship_id'],
            'international_passports_issuer' => $data['international_passports_issuer'],
            'international_passports_issue_date' => $data['international_passports_issue_date'],
            'international_passports_expiry_date' => $data['international_passports_expiry_date'],
            'authenticity' => 0,
        ]);

        $statement = $this->database->getConnection()->prepare('select * from international_passport where people_id=:people_id');
        $statement->execute(['people_id' => $getUserId["user_id"]]);
        $setData_IP = $statement->fetch();

        $this->session->setData('international_passport', [
            'issuing_state_id' => $setData_IP['issuing_state_id'],
            'passport_type_id' => $setData_IP['passport_type_id'],
            'country_code_id' => $setData_IP['country_code_id'],
            'international_passports_series' => $setData_IP['international_passports_series'],
            'international_passports_number' => $setData_IP['international_passports_number'],
            'surname_id' => $setData_IP['surname_id'],
            'name_id' => $setData_IP['name_id'],
            'middle_name_id' => $setData_IP['middle_name_id'],
            'birth_date' => $setData_IP['birth_date'],
            'birth_place_country_id' => $setData_IP['birth_place_country_id'],
            'birth_place_region_id' => $setData_IP['birth_place_region_id'],
            'birth_place_city_id' => $setData_IP['birth_place_city_id'],
            'birth_place_district_id' => $setData_IP['birth_place_district_id'],
            'birth_place_settlement_id' => $setData_IP['birth_place_settlement_id'],
            'sex_id' => $setData_IP['sex_id'],
            'citizenship_id' => $setData_IP['citizenship_id'],
            'international_passports_issuer' => $setData_IP['international_passports_issuer'],
            'international_passports_issue_date' => $setData_IP['international_passports_issue_date'],
            'international_passports_expiry_date' => $setData_IP['international_passports_expiry_date'],
            'authenticity' => $setData_IP['authenticity'],
        ]);
        return true;
    }

}