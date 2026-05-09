<?php
require_once __DIR__ . '/../Models/MonModule.php';

class MonModuleController
{
    private MonModule $model;

    public function __construct(MonModule $model)
    {
        $this->model = $model;
    }

    public function ajouter(array $postData): array
    {
        $nom_garage = trim($postData['nom_garage'] ?? '');
        $adresse = trim($postData['adresse'] ?? '');
        $email = trim($postData['email'] ?? '');
        $telephone = trim($postData['telephone'] ?? '');
        $heure_ouv = trim($postData['heure_ouv'] ?? '');
        $heure_fer = trim($postData['heure_fer'] ?? '');

        $errors = [];  //tableau erreur 
        if (empty($nom_garage) || strlen($nom_garage) > 100) {
            $errors[] = 'Nom Garage est requis et ne doit pas dépasser 100 caractères.';
        }
        if (empty($adresse) || strlen($adresse) > 100) {
            $errors[] = 'Adresse est requise et ne doit pas dépasser 100 caractères.';
        }
        if (empty($email) || strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email est requis, doit être au format valide (exemple@domaine.com) et ne doit pas dépasser 100 caractères.';
        }
        if (empty($telephone) || strlen($telephone) > 8) {
            $errors[] = 'Téléphone est requis et ne doit pas dépasser 8 caractères.';
        }
        if (empty($heure_ouv)) {
            $errors[] = 'Heure Ouverture est requise.';
        }
        if (empty($heure_fer)) {
            $errors[] = 'Heure Fermeture est requise.';
        }

        if (!empty($errors)) {
            return [
                'successMessage' => '',
                'errorMessage' => implode("\n", $errors),
            ];
        }

        try {
            $data = [
                'nom_garage' => $nom_garage,
                'adresse' => $adresse,
                'email' => $email,
                'telephone' => $telephone,
                'heure_ouv' => $heure_ouv,
                'heure_fer' => $heure_fer,
            ];

            $inserted = $this->model->ajouter($data); //apl el ajouter mel model 

            if ($inserted) {
                $this->model->ajouterNotification('AJOUT', 'GARAGE', 'Garage "' . $nom_garage . '" ajouté avec succès.');

                return [
                    'successMessage' => 'Garage ajouté avec succès.',
                    'errorMessage' => '',
                ];
            }

            return [
                'successMessage' => '',
                'errorMessage' => 'Impossible d\'ajouter le garage.',
            ];
        } catch (PDOException $e) {
            return [
                'successMessage' => '',
                'errorMessage' => 'Erreur lors de l\'ajout : ' . $e->getMessage(),
            ];
        }
    }

    public function supprimer(string $delete_nom): array
    {
        $delete_nom = trim($delete_nom);

        if (empty($delete_nom)) {   // tetfa9Ed fl id vide wala le 
            return [
                'successMessage' => '',
                'errorMessage' => 'Nom Garage à supprimer est requis.',
            ];
        }

        if (mb_strlen($delete_nom) > 100) {
            return [
                'successMessage' => '',
                'errorMessage' => 'Le nom du garage ne doit pas dépasser 100 caractères.',
            ];
        }

        try {
            $deleted = $this->model->supprimer($delete_nom); //appelle mtae supprimer ml model

            if ($deleted) {
                $this->model->ajouterNotification('SUPPRESSION', 'GARAGE', 'Garage "' . $delete_nom . '" supprimé.');

                return [
                    'successMessage' => 'Garage supprimé avec succès.',
                    'errorMessage' => '',
                ];
            }

            return [
                'successMessage' => '',
                'errorMessage' => 'Aucun garage trouvé avec ce nom.',
            ];
        } catch (PDOException $e) {
            return [
                'successMessage' => '',
                'errorMessage' => 'Erreur lors de la suppression : ' . $e->getMessage(),
            ];
        }
    }

    public function modifier(array $postData): array
    {
        $nom_garage = trim($postData['nom_garage'] ?? '');
        $adresse = trim($postData['adresse'] ?? '');
        $email = trim($postData['email'] ?? '');
        $telephone = trim($postData['telephone'] ?? '');
        $heure_ouv = trim($postData['heure_ouv'] ?? '');
        $heure_fer = trim($postData['heure_fer'] ?? '');

        $errors = [];

        if (empty($nom_garage) || strlen($nom_garage) > 100) {
            $errors[] = 'Nom Garage est requis et ne doit pas dépasser 100 caractères.';
        }
        if (empty($adresse) || strlen($adresse) > 100) {
            $errors[] = 'Adresse est requise et ne doit pas dépasser 100 caractères.';
        }
        if (empty($email) || strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email est requis, doit être au format valide (exemple@domaine.com) et ne doit pas dépasser 100 caractères.';
        }
        if (empty($telephone) || strlen($telephone) > 8) {
            $errors[] = 'Téléphone est requis et ne doit pas dépasser 8 caractères.';
        }
        if (empty($heure_ouv)) {
            $errors[] = 'Heure Ouverture est requise.';
        }
        if (empty($heure_fer)) {
            $errors[] = 'Heure Fermeture est requise.';
        }

        if (!empty($errors)) {
            return [
                'successMessage' => '',
                'errorMessage' => implode("\n", $errors),
            ];
        }

        try {
            $updated = $this->model->modifier([
                'nom_garage' => $nom_garage,
                'adresse' => $adresse,
                'email' => $email,
                'telephone' => $telephone,
                'heure_ouv' => $heure_ouv,
                'heure_fer' => $heure_fer,
            ]);

            if ($updated) {
                $this->model->ajouterNotification('MODIFICATION', 'GARAGE', 'Garage "' . $nom_garage . '" modifié.');
                return [
                    'successMessage' => 'Garage modifié avec succès.',
                    'errorMessage' => '',
                ];
            }

            return [
                'successMessage' => '',
                'errorMessage' => 'Aucune modification enregistrée ou nom introuvable.',
            ];
        } catch (PDOException $e) {
            return [
                'successMessage' => '',
                'errorMessage' => 'Erreur lors de la modification : ' . $e->getMessage(),
            ];
        }
    }

    public function getGarageById(string $id): ?array
    {
        return $this->model->fetchGarageById($id);
    }

    public function getGarageByName(string $name): ?array
    {
    return $this->model->fetchGarageByName($name);
    }

    public function getOpenGarages(): array
    {
        return $this->model->fetchOpenGarages();
    }

    public function getGarages(): array
    {
        return $this->model->fetchAllGarages();
    }

    public function getGarageServices(): array
    {
        return $this->model->fetchGarageServices();
    }

    public function getStatistics(): array
    {
        return [
            'total_garages' => $this->model->fetchTotalGarages(),
            'total_services' => count($this->model->fetchAllServices()),
            'average_service_price' => $this->model->fetchAverageServicePrice(),
        ];
    }

    public function getServiceCountsByGarage(): array
    {
        return $this->model->fetchServiceCountsByGarage();
    }

    public function searchGaragesByServiceName(string $serviceName): array
    {
        return $this->model->fetchGaragesByServiceName($serviceName);
    }

    public function searchGaragesByName(string $searchName): array
    {
        return $this->model->fetchGaragesByName($searchName);
    }

    public function getServiceById(string $id): ?array
    {
        return $this->model->fetchServiceById($id);
    }

    public function searchServicesByName(string $searchName): array
    {
        return $this->model->fetchServicesByName($searchName);
    }

    public function getServices(): array
    {
        return $this->model->fetchAllServices();
    }

    public function searchGaragesById(string $searchId): array
    {
        return $this->model->fetchGaragesById($searchId);
    }

    public function searchServicesById(string $searchId): array
    {
        return $this->model->fetchServicesById($searchId);
    }

    public function getServicesSortedByPrice(string $order = 'ASC'): array
    {
        return $this->model->fetchAllServicesSortedByPrice($order);
    }

    public function ajouter_s(array $postData): array
    {
        $id_garage = trim($postData['id_garage'] ?? '');
        $nom_service = trim($postData['nom_service'] ?? '');
        $prix = trim($postData['prix'] ?? '');

        $errors = [];
        if (empty($id_garage) || strlen($id_garage) > 5) {
            $errors[] = 'ID Garage est requis et ne doit pas dépasser 5 caractères.';
        }
        if (empty($nom_service) || strlen($nom_service) > 200) {
            $errors[] = 'Nom Service est requis et ne doit pas dépasser 200 caractères.';
        }
        if (empty($prix) || strlen($prix) > 10) {
            $errors[] = 'Prix est requis et ne doit pas dépasser 10 caractères.';
        }

        if (!empty($errors)) {
            return ['successMessage' => '', 'errorMessage' => implode("\n", $errors)];
        }

        try {
            $inserted = $this->model->ajouter_s([
                'id_garage' => $id_garage,
                'nom_service' => $nom_service,
                'prix' => $prix,
            ]);

            if ($inserted) {
                $this->model->ajouterNotification('AJOUT', 'SERVICE', 'Service "' . $nom_service . '" ajouté au garage ID ' . $id_garage . '.');
                return ['successMessage' => 'Service ajouté avec succès.', 'errorMessage' => ''];
            }

            return ['successMessage' => '', 'errorMessage' => 'Impossible d\'ajouter le service.'];
        } catch (PDOException $e) {
            return ['successMessage' => '', 'errorMessage' => 'Erreur lors de l\'ajout : ' . $e->getMessage()];
        }
    }

    public function supprimer_s(string $deleteId): array
    {
        $deleteId = trim($deleteId);
        if (empty($deleteId)) {
            return ['successMessage' => '', 'errorMessage' => 'ID Service à supprimer est requis.'];
        }
        if (mb_strlen($deleteId) > 5) {
            return ['successMessage' => '', 'errorMessage' => 'ID Service ne doit pas dépasser 5 caractères.'];
        }

        try {
            $deleted = $this->model->supprimer_s($deleteId);
            if ($deleted) {
                $this->model->ajouterNotification('SUPPRESSION', 'SERVICE', 'Service ID "' . $deleteId . '" supprimé.');
                return ['successMessage' => 'Service supprimé avec succès.', 'errorMessage' => ''];
            }
            return ['successMessage' => '', 'errorMessage' => 'Aucun service trouvé avec cet ID.'];
        } catch (PDOException $e) {
            return ['successMessage' => '', 'errorMessage' => 'Erreur lors de la suppression : ' . $e->getMessage()];
        }
    }

    public function modifier_s(array $postData): array
    {
        $id_service = trim($postData['id_service'] ?? '');
        $id_garage = trim($postData['id_garage'] ?? '');
        $nom_service = trim($postData['nom_service'] ?? '');
        $prix = trim($postData['prix'] ?? '');

        $errors = [];
        if (empty($id_service) || strlen($id_service) > 5) {
            $errors[] = 'ID Service est requis et ne doit pas dépasser 5 caractères.';
        }
        if (empty($id_garage) || strlen($id_garage) > 5) {
            $errors[] = 'ID Garage est requis et ne doit pas dépasser 5 caractères.';
        }
        if (empty($nom_service) || strlen($nom_service) > 200) {
            $errors[] = 'Nom Service est requis et ne doit pas dépasser 200 caractères.';
        }
        if (empty($prix) || strlen($prix) > 10) {
            $errors[] = 'Prix est requis et ne doit pas dépasser 10 caractères.';
        }

        if (!empty($errors)) {
            return ['successMessage' => '', 'errorMessage' => implode("\n", $errors)];
        }

        try {
            $updated = $this->model->modifier_s([
                'id_service' => $id_service,
                'id_garage' => $id_garage,
                'nom_service' => $nom_service,
                'prix' => $prix,
            ]);

            if ($updated) {
                $this->model->ajouterNotification('MODIFICATION', 'SERVICE', 'Service "' . $nom_service . '" (ID ' . $id_service . ') modifié.');
                return ['successMessage' => 'Service modifié avec succès.', 'errorMessage' => ''];
            }
            return ['successMessage' => '', 'errorMessage' => 'Aucune modification enregistrée ou ID Service introuvable.'];
        } catch (PDOException $e) {
            return ['successMessage' => '', 'errorMessage' => 'Erreur lors de la modification : ' . $e->getMessage()];
        }
    }

    public function getNotifications(): array
{
    return $this->model->fetchNotifications();
}

public function marquerNotifLue(int $id): void
{
    $this->model->marquerLu($id);
}
}
