<?php


namespace App;

use http\Exception;

class FillingOut_OldOMS
{
    private Database $database;
    private Session $session;

    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }

    public function policy_oldOms(array $data): bool
    {
        $checkFIO = '/^[а-яё]+$/iu';
        $checkNum = '/^[0-9]+$/iu';
        $checkReg = '/^[а-яё.\-" ]+$/iu';
        $checkIssCode = '/^[0-9\-\+]+$/iu';
        $checkHomeNum = '/^[0-9\-\+а-я]+$/iu';

        $getUserId = $this->session->getData('user');

        if (empty($data['oldOMS_issuer_id'])) {
            throw new AuthorizationException('Введите страховую медицинскую организацию');
        }
        if (!preg_match($checkReg, $data['oldOMS_issuer_id'])) {
            throw new AuthorizationException('Проверьте правильность введенной страховой медицинской организации');
        }

        if (empty($data['oldOMS_form_series'])) {
            throw new AuthorizationException('Введите серию полиса');
        }
        if (!preg_match($checkNum, $data['oldOMS_form_series'])) {
            throw new AuthorizationException('Проверьте правильность введенной серии полиса');
        }

        if (empty($data['oldOMS_form_number'])) {
            throw new AuthorizationException('Введите номер полиса');
        }
        if (!preg_match($checkNum, $data['oldOMS_form_number'])) {
            throw new AuthorizationException('Проверьте правильность введенного номера полиса');
        }

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

        if (empty($data['sex_id'])) {
            throw new AuthorizationException('Выберите пол');
        }

        if (empty($data['social_status'])) {
            throw new AuthorizationException('Введите социальный статус');
        }
        if (!preg_match($checkFIO, $data['social_status'])) {
            throw new AuthorizationException('Проверьте правильность введенного социального статуса');
        }

        if (empty($data['place_of_work'])) {
            throw new AuthorizationException('Введите место работы');
        }
        if (!preg_match($checkReg, $data['place_of_work'])) {
            throw new AuthorizationException('Проверьте правильность введенного места работы');
        }

        if (empty($data['residence_country_id'])) {
            throw new AuthorizationException('Введите название страны');
        }
        if (!preg_match($checkReg, $data['residence_country_id'])) {
            throw new AuthorizationException('Проверьте правильность названия введенной страны');
        }

        if (empty($data['residence_region_id'])) {
            throw new AuthorizationException('Введите название субъекта');
        }
        if (!preg_match($checkReg, $data['residence_region_id'])) {
            throw new AuthorizationException('Проверьте правильность названия введенного субъекта');
        }

        if (empty($data['residence_city_id'])) {
            throw new AuthorizationException('Введите название города');
        }
        if (!preg_match($checkReg, $data['residence_city_id'])) {
            throw new AuthorizationException('Проверьте правильность названия введенного города');
        }

        if (empty($data['residence_district_id'])) {
            throw new AuthorizationException('Введите название района');
        }
        if (!preg_match($checkReg, $data['residence_district_id'])) {
            throw new AuthorizationException('Проверьте правильность названия введенного района');
        }

        if (empty($data['birth_place_settlement_id'])) {
            $data['birth_place_settlement_id'] = '-';
        }

        if (empty($data['residence_street_id'])) {
            throw new AuthorizationException('Введите название улицы');
        }
        if (!preg_match($checkReg, $data['residence_street_id'])) {
            throw new AuthorizationException('Проверьте правильность названия введенной улицы');
        }

        if (empty($data['residence_house_id'])) {
            throw new AuthorizationException('Введите номер дома');
        }
        if (!preg_match($checkHomeNum , $data['residence_house_id'])) {
            throw new AuthorizationException('Проверьте правильность названия введенного номера дома');
        }

        if (empty($data['residence_apartment_id'])) {
            $data['residence_apartment_id'] = '-';
        }

        if (empty($data['phone_id'])) {
            throw new AuthorizationException('Введите номер телефона');
        }
        if (!preg_match($checkIssCode, $data['phone_id'])) {
            throw new AuthorizationException('Проверьте правильность введенного номера телефона');
        }

        if (empty($data['contact_number'])) {
            throw new AuthorizationException('Введите номер договора обязательного медицинского страхования');
        }
        if (!preg_match($checkNum, $data['contact_number'])) {
            throw new AuthorizationException('Проверьте правильность введенного номера договора обязательного медицинского страхования');
        }

        if (empty($data['contact_date'])) {
            throw new AuthorizationException('Введите дату заключения договора обязательного медицинского страхования');
        }

        if (empty($data['beginning_date'])) {
            throw new AuthorizationException('Введите дату начала периода действия');
        }

        if (empty($data['expiry_date'])) {
            throw new AuthorizationException('Введите дату окончания периода действия');
        }

        if (empty($data['issue_date'])) {
            throw new AuthorizationException('Введите дату выдачи');
        }

        if (empty($data['insurant_name'])) {
            throw new AuthorizationException('Введите наименование страхователя');
        }
        if (!preg_match($checkReg, $data['insurant_name'])) {
            throw new AuthorizationException('Проверьте правильность введенного наменования страхователя');
        }

        if (empty($data['position_of_representative'])) {
            throw new AuthorizationException('Введите должность представителя');
        }
        if (!preg_match($checkReg, $data['position_of_representative'])) {
            throw new AuthorizationException('Проверьте правильность должности представителя');
        }

        if (empty($data['representative'])) {
            throw new AuthorizationException('Введите представителя');
        }
        if (!preg_match($checkReg, $data['representative'])) {
            throw new AuthorizationException('Проверьте правильность введения представителя');
        }

        if (empty($data['insurance_agent'])) {
            throw new AuthorizationException('Введите название страхового агента');
        }
        if (!preg_match($checkReg, $data['insurance_agent'])) {
            throw new AuthorizationException('Проверьте правильность введенного названия страхового агента');
        }

        $statement = $this->database->getConnection()->prepare(
            'INSERT INTO login_register.policy_oldoms (people_id, oldOMS_issuer_id, oldOMS_form_series, oldOMS_form_number, surname_id, name_id, middle_name_id, birth_date, sex_id, social_status, place_of_work, residence_country_id, residence_region_id, residence_city_id, residence_district_id, residence_settlement_id, residence_street_id, residence_house_id, residence_apartment_id, phone_id, contact_number, contact_date, beginning_date, expiry_date, issue_date, insurant_name, position_of_representative, representative, insurance_agent, authenticity)
 VALUES (:people_id, :oldOMS_issuer_id, :oldOMS_form_series, :oldOMS_form_number, :surname_id, :name_id, :middle_name_id, :birth_date, :sex_id, :social_status, :place_of_work, :residence_country_id, :residence_region_id, :residence_city_id, :residence_district_id, :residence_settlement_id, :residence_street_id, :residence_house_id, :residence_apartment_id, :phone_id, :contact_number, :contact_date, :beginning_date, :expiry_date, :issue_date, :insurant_name, :position_of_representative, :representative, :insurance_agent, :authenticity)'
        );

        $statement->execute([
            'people_id' => $getUserId["user_id"],
            'oldOMS_issuer_id' => $data['oldOMS_issuer_id'],
            'oldOMS_form_series' => $data['oldOMS_form_series'],
            'oldOMS_form_number' => $data['oldOMS_form_number'],
            'surname_id' => $data['surname_id'],
            'name_id' => $data['name_id'],
            'middle_name_id' => $data['middle_name_id'],
            'birth_date' => $data['birth_date'],
            'sex_id' => $data['sex_id'],
            'social_status' => $data['social_status'],
            'place_of_work' => $data['place_of_work'],
            'residence_country_id' => $data['residence_country_id'],
            'residence_region_id' => $data['residence_region_id'],
            'residence_city_id' => $data['residence_city_id'],
            'residence_district_id' => $data['residence_district_id'],
            'residence_settlement_id' => $data['residence_settlement_id'],
            'residence_street_id' => $data['residence_street_id'],
            'residence_house_id' => $data['residence_house_id'],
            'residence_apartment_id' => $data['residence_apartment_id'],
            'phone_id' => $data['phone_id'],
            'contact_number' => $data['contact_number'],
            'contact_date' => $data['contact_date'],
            'beginning_date' => $data['beginning_date'],
            'expiry_date' => $data['expiry_date'],
            'issue_date' => $data['issue_date'],
            'insurant_name' => $data['insurant_name'],
            'position_of_representative' => $data['position_of_representative'],
            'representative' => $data['representative'],
            'insurance_agent' => $data['insurance_agent'],
            'authenticity' => 0,
        ]);

        $statement = $this->database->getConnection()->prepare('select * from policy_oldoms where people_id=:people_id');
        $statement->execute(['people_id' => $getUserId["user_id"]]);
        $setData_oldOMS = $statement->fetch();

        $this->session->setData('policy_oldoms', [
            'oldOMS_issuer_id' => $setData_oldOMS['oldOMS_issuer_id'],
            'oldOMS_form_series' => $setData_oldOMS['oldOMS_form_series'],
            'oldOMS_form_number' => $setData_oldOMS['oldOMS_form_number'],
            'surname_id' => $setData_oldOMS['surname_id'],
            'name_id' => $setData_oldOMS['name_id'],
            'middle_name_id' => $setData_oldOMS['middle_name_id'],
            'birth_date' => $setData_oldOMS['birth_date'],
            'sex_id' => $setData_oldOMS['sex_id'],
            'social_status' => $setData_oldOMS['social_status'],
            'place_of_work' => $setData_oldOMS['place_of_work'],
            'residence_country_id' => $setData_oldOMS['residence_country_id'],
            'residence_region_id' => $setData_oldOMS['residence_region_id'],
            'residence_city_id' => $setData_oldOMS['residence_city_id'],
            'residence_district_id' => $setData_oldOMS['residence_district_id'],
            'residence_settlement_id' => $setData_oldOMS['residence_settlement_id'],
            'residence_street_id' => $setData_oldOMS['residence_street_id'],
            'residence_house_id' => $setData_oldOMS['residence_house_id'],
            'residence_apartment_id' => $setData_oldOMS['residence_apartment_id'],
            'phone_id' => $setData_oldOMS['phone_id'],
            'contact_number' => $setData_oldOMS['contact_number'],
            'contact_date' => $setData_oldOMS['contact_date'],
            'beginning_date' => $setData_oldOMS['beginning_date'],
            'expiry_date' => $setData_oldOMS['expiry_date'],
            'issue_date' => $setData_oldOMS['issue_date'],
            'insurant_name' => $setData_oldOMS['insurant_name'],
            'position_of_representative' => $setData_oldOMS['position_of_representative'],
            'representative' => $setData_oldOMS['representative'],
            'insurance_agent' => $setData_oldOMS['insurance_agent'],
            'authenticity' => $setData_oldOMS['authenticity'],
        ]);
        return true;
    }
}