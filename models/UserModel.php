<?php

class UserModel extends BaseModel
{
    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT users.id, users.full_name, users.email, users.password, users.status, roles.name AS role_name
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE users.email = :email
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);

        $user = $statement->fetch();
        return $user ?: null;
    }

    public function emailOrPhoneExists(string $email, ?string $phone): bool
    {
        $sql = 'SELECT id FROM users WHERE email = :email';
        $parameters = ['email' => $email];

        if ($phone !== null) {
            $sql .= ' OR phone = :phone';
            $parameters['phone'] = $phone;
        }

        $statement = $this->pdo->prepare($sql . ' LIMIT 1');
        $statement->execute($parameters);

        return (bool) $statement->fetch();
    }

    public function createCustomer(string $fullName, string $email, ?string $phone, string $passwordHash): void
    {
        $roleStatement = $this->pdo->prepare('SELECT id FROM roles WHERE name = :name LIMIT 1');
        $roleStatement->execute(['name' => 'customer']);
        $roleId = $roleStatement->fetchColumn();

        if ($roleId === false) {
            throw new RuntimeException('Không tìm thấy vai trò customer trong bảng roles.');
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO users (role_id, full_name, email, phone, password)
             VALUES (:role_id, :full_name, :email, :phone, :password)'
        );
        $statement->execute([
            'role_id' => $roleId,
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'password' => $passwordHash,
        ]);
    }
}
