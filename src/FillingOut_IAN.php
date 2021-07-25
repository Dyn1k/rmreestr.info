<?php


namespace App;

use http\Exception;

class FillingOut_IAN
{
    private Database $database;
    private Session $session;

    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }
    public function individAccNumbers(array $data): bool
    {
        $checkFIO = '/^[а-яё]+$/iu';
        $checkNum = '/^[0-9]+$/iu';
        $checkReg = '/^[а-яё.\- ]+$/iu';
        $checkIssCode = '/^[0-9\-]+$/iu';

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
            'INSERT INTO login_register.individual_account_numbers (people_id, personal_pension_account_number, registration_date_PPAN, personal_taxpayer_number, registration_date_PTN, taxpayer_certificates_issuer_id, taxpayer_certificates_issue_department_code_id, form_series, form_number, taxpayer_certificates_issue_date, authorized_employee, authenticity)
 VALUES (:people_id, :personal_pension_account_number, :registration_date_PPAN, :personal_taxpayer_number, :registration_date_PTN, :taxpayer_certificates_issuer_id, :taxpayer_certificates_issue_department_code_id, :form_series, :form_number, :taxpayer_certificates_issue_date, :authorized_employee, :authenticity)'
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
            'authenticity' => 0,
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
}