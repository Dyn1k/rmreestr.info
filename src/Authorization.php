<?php
namespace App;

use http\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Authorization
{
    private Database $database;
    private Session $session;

    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }

    public function register(array $data): bool
    {
        $checkFIO = '/^[а-яё]+$/iu';
        $checkPass = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,15}$/';

        if (empty($data['surname'])) {
            throw new AuthorizationException('Введите фамилию');
        }
        if (!preg_match($checkFIO, $data['surname'])) {
            throw new AuthorizationException('Проверьте правильность введенной фамилии');
        }
        if (empty($data['name'])) {
            throw new AuthorizationException('Введите имя');
        }
        if (!preg_match($checkFIO, $data['name'])) {
            throw new AuthorizationException('Проверьте правильность введенного имени');
        }

        if (empty($data['middle-name']) && empty($data['check-mid-name'])) {
            throw new AuthorizationException('Введите отчество');
        } else {
            if(!empty($data['check-mid-name'])) {
                $data['middle-name'] = '-';
            }
        }
        if (!preg_match($checkFIO, $data['middle-name']) && empty($data['check-mid-name'])) {
            throw new AuthorizationException('Проверьте правильность введенного отчества');
        }

        if (empty($data['username'])) {
            throw new AuthorizationException('Введите идентификатор');
        }

        if (empty($data['email'])) {
            throw new AuthorizationException('Введите электронную почту');
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new AuthorizationException('Проверьте правильность введенного адреса электронной почты');
        }

        if (empty($data['password'])) {
            throw new AuthorizationException('Введите пароль');
        }
        if (!preg_match($checkPass, $data['password'])) {
            throw new AuthorizationException('Ваш пароль слишком слабый');
        }

        if ($data['password'] !== $data['confirm-password']) {
            throw new AuthorizationException('Пароли не совпадают');
        }

        if (empty($data['check_reg'])) {
            throw new AuthorizationException('Вы не дали согласие на обработку персональных данных');
        }

        $statement = $this->database->getConnection()->prepare(
            'SELECT * FROM user WHERE email = :email'
        );
        $statement->execute([
            'email' => $data['email']
        ]);

        $user = $statement->fetch();
        if(!empty($user)) {
            throw new AuthorizationException('Пользователь с такой электронной почтой уже существует');
        }

        $statement = $this->database->getConnection()->prepare(
            'SELECT * FROM user WHERE username = :username'
        );

        $statement->execute([
            'username' => $data['username']
        ]);

        $user = $statement->fetch();
        if(!empty($user)) {
            throw new AuthorizationException('Пользователь с таким идентификатором уже существует');
        }

        if(!empty($data['email'])) {
            $hash = md5($data['username'] . time());
            $mail = new phpMailer();
            $mail->isSMTP();
            $mail->Host = '';
            $mail->SMTPAuth = true;
            $mail->Username = '';
            $mail->Password = '';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            $mail->setFrom('dunik1299@gmail.com', 'rm-reestr.info');
            $mail->addAddress($data['email'], $data['name'] . ' ' . $data['middle-name']);

            $mail->Subject = 'Тест';
            $mail->msgHTML('<html><body>
                <h1>Здравствуйте!</h1>
                <p>Что бы подтвердить Email, перейдите по <a href="http://rmreestr.info/email-confirmed?hash=' . $hash . '">ссылке</a></p>
                </html></body>');

            if (!$mail->send()) {
                throw new AuthorizationException('По техническим причинам, письмо не было отправлено на ваш электронный адрес');
            }
        }
        $statement = $this->database->getConnection()->prepare(
            'INSERT INTO user (surname, name, middle_name, email, username, password, hash, email_confirmed, role) VALUES ( :surname, :name, :middle_name,:email, :username, :password, :hash, :email_confirmed, :role)'
        );

        $statement->execute([
            'surname' => $data['surname'],
            'name' => $data['name'],
            'middle_name' => $data['middle-name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'hash' => $hash,
            'email_confirmed' => false,
            'role' => 'user',
        ]);
        return true;
    }

    public function login(string $email, $password): bool
    {
        if (empty($email)) {
            throw new AuthorizationException('Введите электронную почту');
        }
        if (empty($password)) {
            throw new AuthorizationException('Введите пароль');
        }

        $statement = $this->database->getConnection()->prepare(
            'SELECT * FROM user WHERE email = :email;'
        );
        $statement-> execute([
            'email' => $email
        ]);

        $user = $statement->fetch();

        if(empty($user)) {
            throw new AuthorizationException('Пожалуйста, проверьте правильность написания электронной почты');
        }

        if($user['email_confirmed'] == false) {
            throw new AuthorizationException('Пожалуйста, подтвердите свою электронную почту');
        }

        $statement = $this->database->getConnection()->prepare('select * from user where user_id=:user_id');
        $statement->execute(['user_id' => $user['user_id']]);
        $name = $statement->fetch();

        if(password_verify($password, $user['password'])) {
            $this->session->setData('user', [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'name' => $name['name'],
                'surname' => $name['surname'],
                'middle_name' => $name['middle_name'],
                'role' => $name['role'],
            ]);

            $getUserId = $this->session->getData('user');
            $statement = $this->database->getConnection()->prepare('select * from birth_certificates where people_id=:people_id');
            $statement->execute(['people_id' => $getUserId["user_id"]]);
            $setData = $statement->fetch();
            if (!empty($setData)) {
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
            }

            $statement = $this->database->getConnection()->prepare('select * from passport where people_id=:people_id');
            $statement->execute(['people_id' => $getUserId["user_id"]]);
            $setData_P = $statement->fetch();

            if (!empty($setData_P)) {
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
            }

            $statement = $this->database->getConnection()->prepare('select * from individual_account_numbers where people_id=:people_id');
            $statement->execute(['people_id' => $getUserId["user_id"]]);
            $setData_IAN = $statement->fetch();

            if (!empty($setData_IAN)) {
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
            }

            $statement = $this->database->getConnection()->prepare('select * from policy_oms where people_id=:people_id');
            $statement->execute(['people_id' => $getUserId["user_id"]]);
            $setData_OMS = $statement->fetch();

            if (!empty($setData_OMS)) {
                $this->session->setData('policy_oms', [
                    'OMS_number' => $setData_OMS['OMS_number'],
                    'OMS_issuer_id' => $setData_OMS['OMS_issuer_id'],
                    'OMS_issue_date' => $setData_OMS['OMS_issue_date'],
                    'OMS_form_series' => $setData_OMS['OMS_form_series'],
                    'OMS_form_number' => $setData_OMS['OMS_form_number'],
                    'OMS_authorized_employee' => $setData_OMS['OMS_authorized_employee'],
                    'authenticity' => $setData_OMS['authenticity'],
                ]);
            }

            $statement = $this->database->getConnection()->prepare('select * from policy_oldoms where people_id=:people_id');
            $statement->execute(['people_id' => $getUserId["user_id"]]);
            $setData_oldOMS = $statement->fetch();

            if (!empty($setData_oldOMS)) {
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
            }

            $statement = $this->database->getConnection()->prepare('select * from international_passport where people_id=:people_id');
            $statement->execute(['people_id' => $getUserId["user_id"]]);
            $setData_IP = $statement->fetch();
            if (!empty($setData_IP)) {
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
            }

                $statement = $this->database->getConnection()->prepare('select * from military where people_id=:people_id');
                $statement->execute(['people_id' => $getUserId["user_id"]]);
                $setData_M = $statement->fetch();
                if (!empty($setData_M)) {
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
            }

            return true;
        }
        throw new AuthorizationException('Пожалуйста, проверьте правильность написания пароля');
    }
}