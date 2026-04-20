<?php
class MonModule
{
    private PDO $pdo;   //var thez el cnx 

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function ajouter(array $data): bool
    {
        $sql = "INSERT INTO garages (`id-garage`, `id-responsable`, `nom_garage`, `adresse`, `email`, `telephone`, `heure-ouv`, `heure_fer`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $data['id_garage'],
            $data['id_responsable'],
            $data['nom_garage'],
            $data['adresse'],
            $data['email'],
            $data['telephone'],
            $data['heure_ouv'],
            $data['heure_fer'],
        ]);
    }

    public function supprimer(string $id): bool
    {
        $sql = "DELETE FROM garages WHERE `id-garage` = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public function modifier(array $data): bool
    {
        $sql = "UPDATE garages SET `id-responsable` = ?, `nom_garage` = ?, `adresse` = ?, `email` = ?, `telephone` = ?, `heure-ouv` = ?, `heure_fer` = ? WHERE `id-garage` = ?";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $data['id_responsable'],
            $data['nom_garage'],
            $data['adresse'],
            $data['email'],
            $data['telephone'],
            $data['heure_ouv'],
            $data['heure_fer'],
            $data['id_garage'],
        ]);

        return $stmt->rowCount() > 0;
    }

    public function fetchGarageById(string $id): ?array
    {
        $sql = "SELECT `id-garage` AS `id_garage`, `id-responsable` AS `id_responsable`, `nom_garage`, `adresse`, `email`, `telephone`, `heure-ouv` AS `heure_ouv`, `heure_fer` AS `heure_fer` FROM garages WHERE `id-garage` = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        $garage = $stmt->fetch();

        return $garage === false ? null : $garage;
    }

    public function fetchAllGarages(): array
    {
        $sql = "SELECT `id-garage` AS `id_garage`, `id-responsable` AS `id_responsable`, `nom_garage`, `adresse` AS `adresse`, `email`, `telephone`, `heure-ouv` AS `heure_ouv`, `heure_fer` AS `heure_fer` FROM `garages`";
        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll();
    }

    public function fetchServiceById(string $id): ?array
    {
        $sql = "SELECT `id-service` AS `id_service`, `id_garage`, `nom_service`, `prix` FROM `services` WHERE `id-service` = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        $service = $stmt->fetch();

        return $service === false ? null : $service;
    }

    public function fetchAllServices(): array
    {
        $sql = "SELECT `id-service` AS `id_service`, `id_garage`, `nom_service`, `prix` FROM `services`";
        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll();
    }

    public function ajouter_s(array $data): bool
    {
        $sql = "INSERT INTO `services` (`id-service`, `id_garage`, `nom_service`, `prix`) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $data['id_service'],
            $data['id_garage'],
            $data['nom_service'],
            $data['prix'],
        ]);
    }

    public function supprimer_s(string $id): bool
    {
        $sql = "DELETE FROM `services` WHERE `id-service` = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public function modifier_s(array $data): bool
    {
        $sql = "UPDATE `services` SET `id_garage` = ?, `nom_service` = ?, `prix` = ? WHERE `id-service` = ?";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $data['id_garage'],
            $data['nom_service'],
            $data['prix'],
            $data['id_service'],
        ]);

        return $stmt->rowCount() > 0;
    }

    public function fetchGarageServices(): array
    {
        $garages = $this->fetchAllGarages();

        $serviceSets = [
            ['Lavage', 'Réparation moteur'],
            ['Vidange', 'Contrôle freins'],
            ['Diagnostic électronique', 'Polissage'],
        ];

        foreach ($garages as $index => $garage) {
            $garage['services'] = $serviceSets[$index % count($serviceSets)];
            $garages[$index] = $garage;
        }

        return $garages;
    }

    public function fetchOpenGarages(): array
    {
        $sql = "SELECT `id-garage` AS `id_garage`, `nom_garage` FROM `garages` WHERE TIME(NOW()) BETWEEN `heure-ouv` AND `heure_fer`";
        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll();
    }
}
