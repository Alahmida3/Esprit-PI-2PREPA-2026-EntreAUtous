<?php
require_once __DIR__ . '/../Model/MonModule.php';

class MonModuleController
{
    private MonModule $model;

    public function __construct(MonModule $model)
    {
        $this->model = $model;
    }

    public function ajouter(array $postData): array
    {
        $id_garage = trim($postData['id_garage'] ?? '');
        $id_responsable = trim($postData['id_responsable'] ?? '');
        $nom_garage = trim($postData['nom_garage'] ?? '');
        $adresse = trim($postData['adresse'] ?? '');
        $email = trim($postData['email'] ?? '');
        $telephone = trim($postData['telephone'] ?? '');
        $heure_ouv = trim($postData['heure_ouv'] ?? '');
        $heure_fer = trim($postData['heure_fer'] ?? '');

        $errors = [];  //tableau erreur 
        if (empty($id_garage) || strlen($id_garage) > 5) {
            $errors[] = 'ID Garage est requis et ne doit pas dépasser 5 caractères.';
        }
        if (empty($id_responsable) || strlen($id_responsable) > 5) {
            $errors[] = 'ID Responsable est requis et ne doit pas dépasser 5 caractères.';
        }
        if (empty($nom_garage) || strlen($nom_garage) > 15) {
            $errors[] = 'Nom Garage est requis et ne doit pas dépasser 15 caractères.';
        }
        if (empty($adresse) || strlen($adresse) > 15) {
            $errors[] = 'Adresse est requise et ne doit pas dépasser 15 caractères.';
        }
        if (empty($email) || strlen($email) > 15 || !str_contains($email, '@')) {
            $errors[] = 'Email est requis, doit contenir "@" et ne doit pas dépasser 15 caractères.';
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
                'id_garage' => $id_garage,
                'id_responsable' => $id_responsable,
                'nom_garage' => $nom_garage,
                'adresse' => $adresse,
                'email' => $email,
                'telephone' => $telephone,
                'heure_ouv' => $heure_ouv,
                'heure_fer' => $heure_fer,
            ];

            $inserted = $this->model->ajouter($data); //apl el ajouter mel model 

            if ($inserted) {
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

    public function supprimer(string $delete_id): array
    {
        $delete_id = trim($delete_id);

        if (empty($delete_id)) {   // tetfa9Ed fl id vide wala le 
            return [
                'successMessage' => '',
                'errorMessage' => 'ID Garage à supprimer est requis.',
            ];
        }

        if (mb_strlen($delete_id) > 5) {
            return [
                'successMessage' => '',
                'errorMessage' => 'L\'ID ne doit pas dépasser 5 caractères.',
            ];
        }

        try {
            $deleted = $this->model->supprimer($delete_id); //appelle mtae supprimer ml model

            if ($deleted) {
                return [
                    'successMessage' => 'Garage supprimé avec succès.',
                    'errorMessage' => '',
                ];
            }

            return [
                'successMessage' => '',
                'errorMessage' => 'Aucun garage trouvé avec cet ID.',
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
        $id_garage = trim($postData['id_garage'] ?? '');
        $id_responsable = trim($postData['id_responsable'] ?? '');
        $nom_garage = trim($postData['nom_garage'] ?? '');
        $adresse = trim($postData['adresse'] ?? '');
        $email = trim($postData['email'] ?? '');
        $telephone = trim($postData['telephone'] ?? '');
        $heure_ouv = trim($postData['heure_ouv'] ?? '');
        $heure_fer = trim($postData['heure_fer'] ?? '');

        $errors = [];

        if (empty($id_garage) || strlen($id_garage) > 5) {
            $errors[] = 'ID Garage est requis et ne doit pas dépasser 5 caractères.';
        }
        if (empty($id_responsable) || strlen($id_responsable) > 5) {
            $errors[] = 'ID Responsable est requis et ne doit pas dépasser 5 caractères.';
        }
        if (empty($nom_garage) || strlen($nom_garage) > 15) {
            $errors[] = 'Nom Garage est requis et ne doit pas dépasser 15 caractères.';
        }
        if (empty($adresse) || strlen($adresse) > 15) {
            $errors[] = 'Adresse est requise et ne doit pas dépasser 15 caractères.';
        }
        if (empty($email) || strlen($email) > 15 || !str_contains($email, '@')) {
            $errors[] = 'Email est requis, doit contenir "@" et ne doit pas dépasser 15 caractères.';
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
                'id_garage' => $id_garage,
                'id_responsable' => $id_responsable,
                'nom_garage' => $nom_garage,
                'adresse' => $adresse,
                'email' => $email,
                'telephone' => $telephone,
                'heure_ouv' => $heure_ouv,
                'heure_fer' => $heure_fer,
            ]);

            if ($updated) {
                return [
                    'successMessage' => 'Garage modifié avec succès.',
                    'errorMessage' => '',
                ];
            }

            return [
                'successMessage' => '',
                'errorMessage' => 'Aucune modification enregistrée ou ID introuvable.',
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

    public function getServiceById(string $id): ?array
    {
        return $this->model->fetchServiceById($id);
    }

    public function getServices(): array
    {
        return $this->model->fetchAllServices();
    }

    public function ajouter_s(array $postData): array
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
        if (empty($nom_service) || strlen($nom_service) > 20) {
            $errors[] = 'Nom Service est requis et ne doit pas dépasser 20 caractères.';
        }
        if (empty($prix) || strlen($prix) > 10) {
            $errors[] = 'Prix est requis et ne doit pas dépasser 10 caractères.';
        }

        if (!empty($errors)) {
            return ['successMessage' => '', 'errorMessage' => implode("\n", $errors)];
        }

        try {
            $inserted = $this->model->ajouter_s([
                'id_service' => $id_service,
                'id_garage' => $id_garage,
                'nom_service' => $nom_service,
                'prix' => $prix,
            ]);

            if ($inserted) {
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
        if (empty($nom_service) || strlen($nom_service) > 20) {
            $errors[] = 'Nom Service est requis et ne doit pas dépasser 20 caractères.';
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
                return ['successMessage' => 'Service modifié avec succès.', 'errorMessage' => ''];
            }
            return ['successMessage' => '', 'errorMessage' => 'Aucune modification enregistrée ou ID Service introuvable.'];
        } catch (PDOException $e) {
            return ['successMessage' => '', 'errorMessage' => 'Erreur lors de la modification : ' . $e->getMessage()];
        }
    }
}
