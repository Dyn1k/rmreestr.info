<?php


namespace App;

use http\Exception;

class SearchUsers
{
    private Database $database;
    private Session $session;

    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }

    public function searchUsers(array $data): bool
    {
        $getUserId = $this->session->getData('user');

        if (empty($data['surname_id']) && empty($data['name_id']) && empty($data['middle_name_id']) && empty($data['birth_date']) && empty($data['birth_place_country_id']) && empty($data['birth_place_region_id']) && empty($data['birth_place_city_id'])) {
            throw new AuthorizationException('Вы не заполнили ни одного поля');
        }

        if(empty($data['surname_id']))
        {
            $data['surname_id'] = null;
        }
        if(empty($data['name_id']))
        {
            $data['name_id'] = null;
        }
        if(empty($data['middle_name_id']))
        {
            $data['middle_name_id'] = null;
        }
        if(empty($data['birth_date']))
        {
            $data['birth_date'] = null;
        }
        if(empty($data['birth_place_country_id']))
        {
            $data['birth_place_country_id'] = null;
        }
        if(empty($data['birth_place_region_id']))
        {
            $data['birth_place_region_id'] = null;
        }
        if(empty($data['birth_place_city_id']))
        {
            $data['birth_place_city_id'] = null;
        }

        $statement = $this->database->getConnection()->prepare(
            'SELECT user.surname, user.middle_name, user.user_id, user.name, user.email, user.username,
birth_certificates.people_id, birth_certificates.birth_date, birth_certificates.birth_place_country_id, birth_certificates.birth_place_region_id, birth_certificates.birth_place_city_id
FROM user left outer join birth_certificates on user.user_id=birth_certificates.people_id where user.surname like (:surname) and user.name like (:name) and user.middle_name like (:middle_name) or birth_certificates.birth_date like (:birth_date) or
birth_certificates.birth_place_country_id like (:birth_place_country_id) or birth_certificates.birth_place_region_id like (:birth_place_region_id) or birth_certificates.birth_place_city_id like (:birth_place_city_id) group by user.surname, user.middle_name'
        );

        $statement->execute([
            'surname' => $data['surname_id'],
            'name' => $data['name_id'],
            'middle_name' => $data['middle_name_id'],
            'birth_date' => $data['birth_date'],
            'birth_place_country_id' => $data['birth_place_country_id'],
            'birth_place_region_id' => $data['birth_place_region_id'],
            'birth_place_city_id' => $data['birth_place_city_id'],
        ]);

        $this->session->setData('search_users', $statement->fetchAll());
        if ($this->session->getData('search_users') == NULL) {
            throw new AuthorizationException('Пользователь не найден');
        }
        return true;
    }
}