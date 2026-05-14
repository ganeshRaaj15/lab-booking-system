<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\NativeUserSerializer;
use App\Models\FacultyModel;
use CodeIgniter\Shield\Entities\User;
use Throwable;

class NativeProfileController extends BaseController
{
    protected NativeUserSerializer $serializer;
    protected FacultyModel $facultyModel;

    public function __construct()
    {
        helper('auth');
        $this->serializer = new NativeUserSerializer();
        $this->facultyModel = new FacultyModel();
    }

    public function show()
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Unauthenticated.',
                ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'user' => $this->serializer->serialize($user),
            'editable' => $this->canEditProfile($user),
            'editable_reason' => $this->canEditProfile($user)
                ? null
                : 'Profile updates for PIC and Manager roles are managed by Admin.',
            'faculties' => array_map(static function (array $faculty): array {
                return [
                    'id' => (int) $faculty['id'],
                    'code' => (string) ($faculty['code'] ?? ''),
                    'name_bm' => (string) ($faculty['name_bm'] ?? ''),
                    'name_en' => (string) ($faculty['name_en'] ?? ''),
                    'is_fkmp' => (bool) ($faculty['is_fkmp'] ?? false),
                    'label' => trim(((string) ($faculty['code'] ?? '')) . ' - ' . ((string) ($faculty['name_en'] ?? ''))),
                ];
            }, $this->facultyModel->orderBy('name_bm', 'ASC')->findAll()),
        ]);
    }

    public function update()
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Unauthenticated.',
                ]);
        }

        if (! $this->canEditProfile($user)) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Profile updates for PIC and Manager roles are managed by Admin.',
                ]);
        }

        $payload = $this->requestPayload();
        $rules = [
            'username' => 'required|min_length[3]|max_length[30]',
            'full_name' => 'permit_empty|max_length[120]',
            'phone' => 'permit_empty|max_length[40]',
            'faculty_id' => 'permit_empty|integer',
            'email' => 'required|valid_email|max_length[255]',
            'password' => 'permit_empty|min_length[8]',
            'password_confirm' => 'matches[password]',
        ];

        if (! $this->validateData($payload, $rules)) {
            $errors = $this->validator->getErrors();
            $firstError = is_array($errors) && $errors !== [] ? (string) reset($errors) : '';

            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => 'error',
                    'message' => $firstError !== '' ? $firstError : 'Invalid profile update payload.',
                    'errors' => $errors,
                ]);
        }

        $db = db_connect();
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $username = trim((string) ($payload['username'] ?? ''));

        $existingEmail = $db->table('auth_identities')
            ->where('type', 'email_password')
            ->where('LOWER(secret) =', $email)
            ->where('user_id !=', $user->id)
            ->countAllResults();
        if ($existingEmail > 0) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Email already exists.',
                ]);
        }

        $existingUsername = $db->table('users')
            ->where('username', $username)
            ->where('id !=', $user->id)
            ->countAllResults();
        if ($existingUsername > 0) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Username already exists.',
                ]);
        }

        $photoUpload = $this->handleProfilePhotoUpload();
        if (isset($photoUpload['response'])) {
            return $photoUpload['response'];
        }
        $photoPath = $photoUpload['path'];

        $updateData = [
            'username' => $username,
            'full_name' => trim((string) ($payload['full_name'] ?? '')) ?: null,
            'phone' => trim((string) ($payload['phone'] ?? '')) ?: null,
            'faculty_id' => ($payload['faculty_id'] ?? '') !== '' ? (int) $payload['faculty_id'] : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $oldProfilePhoto = null;
        if ($photoPath !== null) {
            $row = $db->table('users')->select('profile_photo')->where('id', $user->id)->get()->getRowArray();
            $oldProfilePhoto = trim((string) ($row['profile_photo'] ?? ''));
            $updateData['profile_photo'] = $photoPath;
        }

        $db->table('users')
            ->where('id', $user->id)
            ->update($updateData);

        if ($oldProfilePhoto !== null && $oldProfilePhoto !== '') {
            $oldFile = FCPATH . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $oldProfilePhoto), '/\\');
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        $db->table('auth_identities')
            ->where('user_id', $user->id)
            ->where('type', 'email_password')
            ->set('secret', $email)
            ->set('updated_at', date('Y-m-d H:i:s'))
            ->update();

        $password = (string) ($payload['password'] ?? '');
        if ($password !== '') {
            $db->table('auth_identities')
                ->where('user_id', $user->id)
                ->where('type', 'email_password')
                ->set('secret2', password_hash($password, PASSWORD_DEFAULT))
                ->set('updated_at', date('Y-m-d H:i:s'))
                ->update();
        }

        $fresh = auth()->user();
        if (! $fresh instanceof User) {
            $fresh = $user;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Profile updated successfully.',
            'user' => $this->serializer->serialize($fresh),
        ]);
    }

    protected function canEditProfile(User $user): bool
    {
        return ! $user->inGroup('pic') && ! $user->inGroup('manager');
    }

    protected function requestPayload(): array
    {
        $contentType = strtolower((string) $this->request->getHeaderLine('Content-Type'));
        if (str_contains($contentType, 'application/json')) {
            try {
                $json = $this->request->getJSON(true);
                if (is_array($json) && $json !== []) {
                    return $json;
                }
            } catch (Throwable) {
                // Fall back to form fields when the request body is not valid JSON.
            }
        }

        $post = $this->request->getPost();

        return is_array($post) ? $post : [];
    }

    /**
     * @return array{path: string|null, response?: \CodeIgniter\HTTP\ResponseInterface}
     */
    protected function handleProfilePhotoUpload(): array
    {
        $photoFile = $this->request->getFile('profile_photo');
        if (! $photoFile || $photoFile->getError() === UPLOAD_ERR_NO_FILE) {
            return ['path' => null];
        }

        if (! $photoFile->isValid()) {
            return [
                'path' => null,
                'response' => $this->response
                    ->setStatusCode(422)
                    ->setJSON([
                        'status' => 'error',
                        'message' => $photoFile->getErrorString(),
                    ]),
            ];
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (! in_array($photoFile->getMimeType(), $allowedTypes, true)) {
            return [
                'path' => null,
                'response' => $this->response
                    ->setStatusCode(422)
                    ->setJSON([
                        'status' => 'error',
                        'message' => 'Profile photo must be a JPG, PNG, or WEBP image.',
                    ]),
            ];
        }

        $destination = rtrim(FCPATH, DIRECTORY_SEPARATOR . '/\\') . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'users';
        if (is_file($destination)) {
            return [
                'path' => null,
                'response' => $this->response
                    ->setStatusCode(500)
                    ->setJSON([
                        'status' => 'error',
                        'message' => 'Profile photo upload path is blocked by a file on the server.',
                    ]),
            ];
        }

        if (! is_dir($destination) && ! @mkdir($destination, 0775, true) && ! is_dir($destination)) {
            return [
                'path' => null,
                'response' => $this->response
                    ->setStatusCode(500)
                    ->setJSON([
                        'status' => 'error',
                        'message' => 'Profile photo upload directory could not be created on the server.',
                    ]),
            ];
        }

        if (! is_writable($destination)) {
            @chmod($destination, 0775);
        }

        if (! is_writable($destination)) {
            return [
                'path' => null,
                'response' => $this->response
                    ->setStatusCode(500)
                    ->setJSON([
                        'status' => 'error',
                        'message' => 'Profile photo upload directory is not writable on the server.',
                    ]),
            ];
        }

        $newName = $photoFile->getRandomName();

        try {
            $photoFile->move($destination, $newName);
        } catch (Throwable $exception) {
            return [
                'path' => null,
                'response' => $this->response
                    ->setStatusCode(500)
                    ->setJSON([
                        'status' => 'error',
                        'message' => 'Unable to store the profile photo on the server: ' . $exception->getMessage(),
                    ]),
            ];
        }

        return ['path' => 'images/users/' . $newName];
    }
}
