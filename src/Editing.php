<?php


namespace App;

use http\Exception;

class Editing
{
    private Database $database;
    private Session $session;

    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }

    public function editOMS(array $data): bool
    {
        $checkNum = '/^[0-9]+$/iu';
        $checkReg = '/^[а-яё.\-" ]+$/iu';

        $getUserId = $this->session->getData('user');

        if (empty($data['OMS_number'])) {
            throw new AuthorizationException('Введите номер');
        }
        if (!preg_match($checkNum, $data['OMS_number'])) {
            throw new AuthorizationException('Проверьте правильность введенного номера');
        }

        if (empty($data['OMS_issuer_id'])) {
            throw new AuthorizationException('Введите страховую медицинскую организацию');
        }
        if (!preg_match($checkReg, $data['OMS_issuer_id'])) {
            throw new AuthorizationException('Проверьте правильность введенной страховой медицинской организации');
        }

        if (empty($data['OMS_issue_date'])) {
            throw new AuthorizationException('Введите дату выдачи');
        }

        if (empty($data['OMS_form_series'])) {
            throw new AuthorizationException('Введите серию бланка');
        }
        if (!preg_match($checkNum, $data['OMS_form_series'])) {
            throw new AuthorizationException('Проверьте правильность введенной серии бланка');
        }

        if (empty($data['OMS_form_number'])) {
            throw new AuthorizationException('Введите номер бланка');
        }
        if (!preg_match($checkNum, $data['OMS_form_number'])) {
            throw new AuthorizationException('Проверьте правильность введенного номера бланка');
        }

        if (empty($data['OMS_authorized_employee'])) {
            throw new AuthorizationException('Введите представителя страховой медицинской организации');
        }
        if (!preg_match($checkReg, $data['OMS_authorized_employee'])) {
            throw new AuthorizationException('Проверьте правильность введенного представителя страховой медицинской организации');
        }

        $statement = $this->database->getConnection()->prepare(
            'UPDATE login_register.policy_oms SET OMS_number=:OMS_number, OMS_issuer_id=:OMS_issuer_id, OMS_issue_date=:OMS_issue_date, OMS_form_series=:OMS_form_series, OMS_form_number=:OMS_form_number, OMS_authorized_employee=:OMS_authorized_employee where people_id=:people_id'
        );

        $statement->execute([
            'people_id' => $getUserId["user_id"],
            'OMS_number' => $data['OMS_number'],
            'OMS_issuer_id' => $data['OMS_issuer_id'],
            'OMS_issue_date' => $data['OMS_issue_date'],
            'OMS_form_series' => $data['OMS_form_series'],
            'OMS_form_number' => $data['OMS_form_number'],
            'OMS_authorized_employee' => $data['OMS_authorized_employee'],
        ]);

        $statement = $this->database->getConnection()->prepare('select * from policy_oms where people_id=:people_id');
        $statement->execute(['people_id' => $getUserId["user_id"]]);
        $setData_OMS = $statement->fetch();

        $this->session->setData('policy_oms', [
            'OMS_number' => $setData_OMS['OMS_number'],
            'OMS_issuer_id' => $setData_OMS['OMS_issuer_id'],
            'OMS_issue_date' => $setData_OMS['OMS_issue_date'],
            'OMS_form_series' => $setData_OMS['OMS_form_series'],
            'OMS_form_number' => $setData_OMS['OMS_form_number'],
            'OMS_authorized_employee' => $setData_OMS['OMS_authorized_employee'],
            'authenticity' => $setData_OMS['authenticity'],
        ]);
        return true;
    }

    public function editOldOMS(array $data): bool
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
            'UPDATE login_register.policy_oldoms SET oldOMS_issuer_id=:oldOMS_issuer_id, oldOMS_form_series=:oldOMS_form_series, oldOMS_form_number=:oldOMS_form_number, surname_id=:surname_id, name_id=:name_id, middle_name_id=:middle_name_id, birth_date=:birth_date, sex_id=:sex_id, social_status=:social_status, place_of_work=:place_of_work, residence_country_id=:residence_country_id, 
residence_region_id=:residence_region_id, residence_city_id=:residence_city_id, residence_district_id=:residence_district_id, residence_settlement_id=:residence_settlement_id, residence_street_id=:residence_street_id, residence_house_id=:residence_house_id, residence_apartment_id=:residence_apartment_id, phone_id=:phone_id, contact_number=:contact_number, contact_date=:contact_date, beginning_date=:beginning_date,
expiry_date=:expiry_date, issue_date=:issue_date, insurant_name=:insurant_name, position_of_representative=:position_of_representative, representative=:representative, insurance_agent=:insurance_agent WHERE people_id=:people_id'
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

    public function editIAN(array $data): bool
    {
        $checkNum = '/^[0-9]+$/iu';
        $checkReg = '/^[а-яё.\- ]+$/iu';

        $getUserId = $this->session->getData('user');

        if (empty($data['personal_pension_account_number'])) {
            throw new AuthorizationException('Введите СНИЛС');
        }
        if (!preg_match($checkNum, $data['personal_pension_account_number'])) {
            throw new AuthorizationException('Проверьте правильность введенного номера СНИЛСа');
        }

        if (empty($data['registration_date_PPAN'])) {
            throw new AuthorizationException('Введите дату регистрации СНИЛСа');
        }

        if (empty($data['personal_taxpayer_number'])) {
            throw new AuthorizationException('Введите ИНН');
        }
        if (!preg_match($checkNum, $data['personal_taxpayer_number'])) {
            throw new AuthorizationException('Проверьте правильность введенного номера ИНН');
        }

        if (empty($data['registration_date_PTN'])) {
            throw new AuthorizationException('Введите дату постановки на учет');
        }

        if (empty($data['taxpayer_certificates_issuer_id'])) {
            throw new AuthorizationException('Введите название налогового органа');
        }
        if (!preg_match($checkReg, $data['taxpayer_certificates_issuer_id'])) {
            throw new AuthorizationException('Проверьте правильность названия налогового органа ');
        }

        if (empty($data['taxpayer_certificates_issue_department_code_id'])) {
            throw new AuthorizationException('Введите код подразделения');
        }
        if (!preg_match($checkNum, $data['taxpayer_certificates_issue_department_code_id'])) {
            throw new AuthorizationException('Проверьте правильность введенного кода подразделения');
        }

        if (empty($data['form_series'])) {
            throw new AuthorizationException('Введите серию бланка');
        }
        if (!preg_match($checkNum, $data['form_series'])) {
            throw new AuthorizationException('Проверьте правильность введенной серии бланка');
        }

        if (empty($data['form_number'])) {
            throw new AuthorizationException('Введите номер бланка');
        }
        if (!preg_match($checkNum, $data['form_number'])) {
            throw new AuthorizationException('Проверьте правильность введенного номера бланка');
        }

        if (empty($data['taxpayer_certificates_issue_date'])) {
            throw new AuthorizationException('Введите дату выдачи');
        }

        if (empty($data['authorized_employee'])) {
            throw new AuthorizationException('Введите должностное лицо');
        }
        if (!preg_match($checkReg, $data['authorized_employee'])) {
            throw new AuthorizationException('Проверьте правильность введенния должностного лица');
        }

        $statement = $this->database->getConnection()->prepare(
            'UPDATE login_register.individual_account_numbers SET personal_pension_account_number=:personal_pension_account_number, registration_date_PPAN=:registration_date_PPAN, personal_taxpayer_number=:personal_taxpayer_number, registration_date_PTN=:registration_date_PTN, taxpayer_certificates_issuer_id=:taxpayer_certificates_issuer_id,
taxpayer_certificates_issue_department_code_id=:taxpayer_certificates_issue_department_code_id, form_series=:form_series, form_number=:form_number, taxpayer_certificates_issue_date=:taxpayer_certificates_issue_date, authorized_employee=:authorized_employee WHERE people_id=:people_id'
        );

        $statement->execute([
            'people_id' => $getUserId["user_id"],
            'personal_pension_account_number' => $data['personal_pension_account_number'],
            'registration_date_PPAN' => $data['registration_date_PPAN'],
            'personal_taxpayer_number' => $data['personal_taxpayer_number'],
            'registration_date_PTN' => $data['registration_date_PTN'],
            'taxpayer_certificates_issuer_id' => $data['taxpayer_certificates_issuer_id'],
            'taxpayer_certificates_issue_department_code_id' => $data['taxpayer_certificates_issue_department_code_id'],
            'form_series' => $data['form_series'],
            'form_number' => $data['form_number'],
            'taxpayer_certificates_issue_date' => $data['taxpayer_certificates_issue_date'],
            'authorized_employee' => $data['authorized_employee'],
        ]);

        $statement = $this->database->getConnection()->prepare('select * from individual_account_numbers where people_id=:people_id');
        $statement->execute(['people_id' => $getUserId["user_id"]]);
        $setData_IAN = $statement->fetch();

        $this->session->setData('individual_account_numbers', [
            'personal_pension_account_number' => $setData_IAN['personal_pension_account_number'],
            'registration_date_PPAN' => $setData_IAN['registration_date_PPAN'],
            'personal_taxpayer_number' => $setData_IAN['personal_taxpayer_number'],
            'registration_date_PTN' => $setData_IAN['registration_date_PTN'],
            'taxpayer_certificates_issuer_id' => $setData_IAN['taxpayer_certificates_issuer_id'],
            'taxpayer_certificates_issue_department_code_id' => $setData_IAN['taxpayer_certificates_issue_department_code_id'],
            'form_series' => $setData_IAN['form_series'],
            'form_number' => $setData_IAN['form_number'],
            'taxpayer_certificates_issue_date' => $setData_IAN['taxpayer_certificates_issue_date'],
            'authorized_employee' => $setData_IAN['authorized_employee'],
            'authenticity' => $setData_IAN['authenticity'],
        ]);
        return true;
    }

    public function editM(array $data): bool
    {
        $checkFIO = '/^[а-яё ]+$/iu';
        $checkNum = '/^[0-9]+$/iu';
        $checkReg = '/^[а-яё.\- ]+$/iu';
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
            'UPDATE login_register.military SET surname_id=:surname_id, name_id=:name_id, middle_name_id=:middle_name_id, birth_date=:birth_date, etnicity_id=:etnicity_id, military_series=:military_series, military_number=:military_number, military_issuer_id=:military_issuer_id, military_issue_date=:military_issue_date, military_commissar=:military_commissar
WHERE people_id=:people_id'
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

    public function editIP(array $data): bool
    {
        $checkNum = '/^[0-9]+$/iu';
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
            'UPDATE login_register.international_passport SET issuing_state_id=:issuing_state_id, passport_type_id=:passport_type_id, country_code_id=:country_code_id, international_passports_series=:international_passports_series, international_passports_number=:international_passports_number, surname_id=:surname_id, name_id=:name_id, middle_name_id=:middle_name_id, birth_date=:birth_date, birth_place_country_id=:birth_place_country_id,
birth_place_region_id=:birth_place_region_id, birth_place_city_id=:birth_place_city_id, birth_place_district_id=:birth_place_district_id, birth_place_settlement_id=:birth_place_settlement_id, sex_id=:sex_id, citizenship_id=:citizenship_id, international_passports_issuer=:international_passports_issuer, international_passports_issue_date=:international_passports_issue_date, international_passports_expiry_date=:international_passports_expiry_date
WHERE people_id=:people_id'
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

    public function editP(array $data): bool
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
            'UPDATE login_register.passport SET surname_id=:surname_id, name_id=:name_id, middle_name_id=:middle_name_id, birth_date=:birth_date, birth_place_country_id=:birth_place_country_id, birth_place_region_id=:birth_place_region_id, birth_place_city_id=:birth_place_city_id, birth_place_district_id=:birth_place_district_id, birth_place_settlement_id=:birth_place_settlement_id, sex=:sex, passports_series=:passports_series,
passports_number=:passports_number, passports_issue_date=:passports_issue_date, passports_issuer_id=:passports_issuer_id, passports_issuer_code_id=:passports_issuer_code_id WHERE people_id=:people_id'
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

    public function editBC(array $data): bool
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
            'UPDATE login_register.birth_certificates SET birth_surname=:birth_surname, birth_name=:birth_name, birth_middle_name=:birth_middle_name, birth_date=:birth_date, birth_place_country_id=:birth_place_country_id, birth_place_region_id=:birth_place_region_id, birth_place_city_id=:birth_place_city_id, birth_place_district_id=:birth_place_district_id, birth_place_settlement_id=:birth_place_settlement_id, sex=:sex,
birth_record_date=:birth_record_date, birth_record_number=:birth_record_number, place_of_state_registration_id=:place_of_state_registration_id, birth_certificates_series=:birth_certificates_series, birth_certificates_number=:birth_certificates_number, birth_certificates_issue_date=:birth_certificates_issue_date WHERE people_id=:people_id'
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