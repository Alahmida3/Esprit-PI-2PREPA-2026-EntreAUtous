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
        $sql = "INSERT INTO garages (`nom_garage`, `adresse`, `email`, `telephone`, `heure-ouv`, `heure_fer`) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $data['nom_garage'],
            $data['adresse'],
            $data['email'],
            $data['telephone'],
            $data['heure_ouv'],
            $data['heure_fer'],
        ]);
    }

    public function supprimer(string $nom): bool
    {
        $sql = "DELETE FROM garages WHERE `nom_garage` = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nom]);

        return $stmt->rowCount() > 0;
    }

    public function modifier(array $data): bool
    {
        $sql = "UPDATE garages SET `nom_garage` = ?, `adresse` = ?, `email` = ?, `telephone` = ?, `heure-ouv` = ?, `heure_fer` = ? WHERE `nom_garage` = ?";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $data['nom_garage'],
            $data['adresse'],
            $data['email'],
            $data['telephone'],
            $data['heure_ouv'],
            $data['heure_fer'],
            $data['nom_garage'],
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

    public function fetchGarageByName(string $name): ?array
    {
        $sql = "SELECT `id-garage` AS `id_garage`, `id-responsable` AS `id_responsable`, `nom_garage`, `adresse`, `email`, `telephone`, `heure-ouv` AS `heure_ouv`, `heure_fer` AS `heure_fer` FROM garages WHERE `nom_garage` = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$name]);

        $garage = $stmt->fetch();

        return $garage === false ? null : $garage;
    }

    public function fetchAllGarages(): array
    {
        $sql = "SELECT `id-garage` AS `id_garage`, `id-responsable` AS `id_responsable`, `nom_garage`, `adresse` AS `adresse`, `email`, `telephone`, `heure-ouv` AS `heure_ouv`, `heure_fer` AS `heure_fer` FROM `garages`";
        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll();
    }

    public function fetchGaragesById(string $searchId): array
    {
        $sql = "SELECT `id-garage` AS `id_garage`, `id-responsable` AS `id_responsable`, `nom_garage`, `adresse` AS `adresse`, `email`, `telephone`, `heure-ouv` AS `heure_ouv`, `heure_fer` AS `heure_fer` FROM `garages` WHERE `id-garage` LIKE ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['%' . $searchId . '%']);

        return $stmt->fetchAll();
    }

    public function fetchGaragesByName(string $searchName): array
    {
        $sql = "SELECT `id-garage` AS `id_garage`, `id-responsable` AS `id_responsable`, `nom_garage`, `adresse` AS `adresse`, `email`, `telephone`, `heure-ouv` AS `heure_ouv`, `heure_fer` AS `heure_fer` FROM `garages` WHERE `nom_garage` LIKE ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['%' . $searchName . '%']);

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

    public function fetchServicesById(string $searchId): array
    {
        $sql = "SELECT `id-service` AS `id_service`, `id_garage`, `nom_service`, `prix` FROM `services` WHERE `id-service` LIKE ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['%' . $searchId . '%']);

        return $stmt->fetchAll();
    }

    public function fetchServicesByName(string $searchName): array
    {
        $sql = "SELECT `id-service` AS `id_service`, `id_garage`, `nom_service`, `prix` FROM `services` WHERE `nom_service` LIKE ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['%' . $searchName . '%']);

        return $stmt->fetchAll();
    }

    public function fetchAllServicesSortedByPrice(string $order = 'ASC'): array
    {
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $sql = "SELECT `id-service` AS `id_service`, `id_garage`, `nom_service`, `prix` FROM `services` ORDER BY CAST(`prix` AS DECIMAL(10,2)) $order";
        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll();
    }

    public function ajouter_s(array $data): bool
    {
        $sql = "INSERT INTO `services` (`id_garage`, `nom_service`, `prix`) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
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
        $sql = "SELECT 
                    g.`id-garage` AS `id_garage`, 
                    g.`id-responsable` AS `id_responsable`,
                    g.`nom_garage`,
                    g.`adresse`,
                    g.`email`,
                    g.`telephone`,
                    g.`heure-ouv` AS `heure_ouv`,
                    g.`heure_fer` AS `heure_fer`,
                    GROUP_CONCAT(s.`nom_service` SEPARATOR ' / ') AS services_list
                FROM `garages` g
                LEFT JOIN `services` s ON g.`id-garage` = s.`id_garage`
                GROUP BY g.`id-garage`, g.`id-responsable`, g.`nom_garage`, g.`adresse`, g.`email`, g.`telephone`, g.`heure-ouv`, g.`heure_fer`
                ORDER BY g.`id-garage`";
        $stmt = $this->pdo->query($sql);
        $garages = $stmt->fetchAll();
        
        // Convert services_list string to array for compatibility
        foreach ($garages as &$garage) {
            $garage['services'] = !empty($garage['services_list']) ? explode(' / ', $garage['services_list']) : [];
            unset($garage['services_list']);
        }
        
        return $garages;
    }

    public function fetchOpenGarages(): array
    {
        $sql = "SELECT `nom_garage`, `heure_fer` FROM `garages` WHERE TIME(NOW()) BETWEEN `heure-ouv` AND `heure_fer`";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function fetchGaragesByServiceName(string $serviceName): array
    {
        $garages = $this->fetchGarageServices();
        $searchTerm = strtolower(trim($serviceName));
        $filtered = [];

        foreach ($garages as $garage) {
            foreach ($garage['services'] as $service) {
                if (stripos($service, $searchTerm) !== false) {
                    $filtered[] = $garage;
                    break;
                }
            }
        }

        return $filtered;
    }

    public function fetchTotalGarages(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM garages";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch();

        return (int) ($result['total'] ?? 0);
    }

    public function fetchServiceCountsByGarage(): array
    {
        $sql = "SELECT g.`id-garage` AS id_garage, g.nom_garage, COUNT(s.`id-service`) AS service_count
                FROM garages g
                LEFT JOIN services s ON g.`id-garage` = s.id_garage
                GROUP BY g.`id-garage`, g.nom_garage
                ORDER BY g.`id-garage`";
        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll();
    }

    public function fetchAverageServicePrice(): float
    {
        $sql = "SELECT AVG(CAST(prix AS DECIMAL(10,2))) AS avg_price FROM services";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch();

        return $result && $result['avg_price'] !== null ? (float) $result['avg_price'] : 0.0;
    }


    public function ajouterNotification(string $type_action, string $type_entite, string $message): void
{
    $sql = "INSERT INTO `notifications` (`type_action`, `type_entite`, `message`) VALUES (?, ?, ?)";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$type_action, $type_entite, $message]);
}

public function fetchNotifications(): array
{
    $sql = "SELECT * FROM `notifications` ORDER BY `date_notif` DESC LIMIT 20";
    $stmt = $this->pdo->query($sql);
    return $stmt->fetchAll();
}

public function marquerLu(int $id): void
{
    $sql = "UPDATE `notifications` SET `lu` = 1 WHERE `id` = ?";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$id]);
}
}
