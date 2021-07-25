<?php


namespace App;

use http\Exception;

class FillingOut_OMS
{
    private Database $database;
    private Session $session;

    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }
    public function policy_oms(array $data): bool
    {
        $checkFIO = '/^[а-яё]+$/iu';
        $checkNum = '/^[0-9]+$/iu';
        $checkReg = '/^[а-яё.\-" ]+$/iu';
        $checkIssCode = '/^[0-9\-]+$/iu';

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
            'INSERT INTO login_register.policy_oms (people_id, OMS_number, OMS_issuer_id, OMS_issue_date, OMS_form_series, OMS_form_number, OMS_authorized_employee, authenticity)
 VALUES (:people_id, :OMS_number, :OMS_issuer_id, :OMS_issue_date, :OMS_form_series, :OMS_form_number, :OMS_authorized_employee, :authenticity)'
        );

        $statement->execute([
            'people_id' => $getUserId["user_id"],
            'OMS_number' => $data['OMS_number'],
            'OMS_issuer_id' => $data['OMS_issuer_id'],
            'OMS_issue_date' => $data['OMS_issue_date'],
            'OMS_form_series' => $data['OMS_form_series'],
            'OMS_form_number' => $data['OMS_form_number'],
            'OMS_authorized_employee' => $data['OMS_authorized_employee'],
            'authenticity' => 0,
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
}